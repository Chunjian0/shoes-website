<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Setting;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Models\Warehouse;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\PurchaseService;
use App\Notifications\PurchaseOrderGenerated;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class AutoGeneratePurchaseOrders extends Command
{
    /**
     * 命令名称及参数
     *
     * @var string
     */
    protected $signature = 'purchase:auto-generate 
                            {--dry-run : 仅测试不实际创建采购单} 
                            {--warehouse= : 指定仓库ID}';
    
    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '自动检查库存水平并为低于阈值的商品生成采购单';

    /**
     * 采购服务实例
     */
    protected PurchaseService $purchaseService;

    /**
     * 库存服务实例
     */
    protected InventoryService $inventoryService;

    /**
     * 初始化命令
     */
    public function __construct(PurchaseService $purchaseService, InventoryService $inventoryService)
    {
        parent::__construct();
        $this->purchaseService = $purchaseService;
        $this->inventoryService = $inventoryService;
    }

    /**
     * 执行命令
     */
    public function handle()
    {
        // 检查自动采购是否启用
        $autoEnabled = Setting::where('key', 'auto_purchase_enabled')->first()?->value === 'true';
        
        if (!$autoEnabled) {
            $this->info('自动采购功能未启用，请在设置中启用此功能。');
            return 0;
        }

        // 获取仓库ID（命令行参数或默认设置）
        $warehouseId = $this->option('warehouse') ?? Setting::where('key', 'default_warehouse_id')->first()?->value;
        
        if (!$warehouseId) {
            $this->error('未指定仓库，请在设置中配置默认仓库或通过--warehouse参数指定。');
            return 1;
        }

        $warehouse = Warehouse::find($warehouseId);
        if (!$warehouse) {
            $this->error("ID为{$warehouseId}的仓库不存在。");
            return 1;
        }

        // 是否为测试运行
        $isDryRun = $this->option('dry-run');
        if ($isDryRun) {
            $this->info('【测试模式】仅显示将要执行的操作，不会实际创建采购单。');
        }

        try {
            // 获取低库存商品
            $this->info('开始检查库存水平...');
            $lowStockProducts = $this->getLowStockProducts($warehouseId);
            
            if (empty($lowStockProducts)) {
                $this->info('未发现需要补充的商品，所有商品库存水平正常。');
                return 0;
            }
            
            $this->info('发现 ' . count($lowStockProducts) . ' 个商品库存低于最低库存水平。');
            
            // 按供应商分组低库存商品
            $productsBySupplier = $this->groupProductsBySupplier($lowStockProducts);
            
            if (empty($productsBySupplier)) {
                $this->warn('未能为低库存商品找到合适的供应商，请检查供应商配置。');
                return 1;
            }

            // 创建采购单
            $createdOrders = 0;
            $generatedPurchases = [];
            foreach ($productsBySupplier as $supplierId => $products) {
                $supplier = Supplier::find($supplierId);
                if (!$supplier) {
                    $this->warn("无法找到ID为{$supplierId}的供应商，跳过相关商品的采购单生成。");
                    continue;
                }

                $this->info("为供应商 {$supplier->name} 准备采购单...");
                
                // 在测试模式下显示要采购的商品
                if ($isDryRun) {
                    $this->displayProductsInfo($products);
                    continue;
                }

                // 准备采购单数据
                $purchaseData = $this->preparePurchaseData($supplier, $warehouse, $products);
                
                // 创建采购单
                $purchase = $this->purchaseService->createPurchase($purchaseData);
                
                if ($purchase) {
                    $createdOrders++;
                    $generatedPurchases[] = $purchase;
                    $this->info("成功创建采购单：{$purchase->purchase_number}，包含 " . count($products) . " 个商品。");
                    
                    // 记录日志
                    Log::info('自动生成采购单', [
                        'purchase_id' => $purchase->id,
                        'purchase_number' => $purchase->purchase_number,
                        'supplier' => $supplier->name,
                        'product_count' => count($products),
                        'warehouse' => $warehouse->name
                    ]);
                }
            }

            if ($isDryRun) {
                $this->info('测试完成，未实际创建任何采购单。');
            } else {
                $this->info("自动采购完成，共创建 {$createdOrders} 个采购单。");
                
                // 发送通知
                if ($createdOrders > 0) {
                    $this->sendNotifications($generatedPurchases);
                }
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('执行自动采购过程中发生错误: ' . $e->getMessage());
            Log::error('自动采购失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * 获取库存低于最低库存水平的商品
     */
    private function getLowStockProducts(int $warehouseId): array
    {
        // 获取黑名单商品ID
        $blacklist = json_decode(Setting::where('key', 'auto_purchase_blacklist')->first()?->value ?? '[]', true);
        
        // 查询库存低于最低水平的商品
        $stocks = DB::table('stocks')
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'products.sku',
                'products.min_stock',
                'stocks.quantity'
            )
            ->where('stocks.warehouse_id', $warehouseId)
            ->where('products.is_active', true)
            ->whereRaw('stocks.quantity < products.min_stock')
            ->whereNotIn('products.id', $blacklist)
            ->get();
        
        return $stocks->toArray();
    }

    /**
     * 按首选供应商对商品进行分组
     */
    private function groupProductsBySupplier(array $products): array
    {
        $groupedProducts = [];
        
        foreach ($products as $product) {
            // 查找该商品的首选供应商
            $supplierProduct = SupplierProduct::where('product_id', $product->product_id)
                ->where('is_preferred', true)
                ->first();
            
            // 如果找不到首选供应商，查找任何一个供应商
            if (!$supplierProduct) {
                $supplierProduct = SupplierProduct::where('product_id', $product->product_id)
                    ->first();
            }
            
            // 如果找到供应商，则添加到分组中
            if ($supplierProduct) {
                if (!isset($groupedProducts[$supplierProduct->supplier_id])) {
                    $groupedProducts[$supplierProduct->supplier_id] = [];
                }
                
                // 计算需要采购的数量
                $product->order_quantity = $this->calculateOrderQuantity($product);
                $product->supplier_product_id = $supplierProduct->id;
                $product->purchase_price = $supplierProduct->purchase_price;
                $product->lead_time = $supplierProduct->lead_time;
                
                $groupedProducts[$supplierProduct->supplier_id][] = $product;
            }
        }
        
        return $groupedProducts;
    }

    /**
     * 根据设置计算采购数量
     */
    private function calculateOrderQuantity(object $product): int
    {
        $method = Setting::where('key', 'auto_purchase_quantity_method')->first()?->value ?? 'min_stock';
        
        // 当前缺口数量
        $shortage = max(0, $product->min_stock - $product->quantity);
        
        switch ($method) {
            case 'replenish_only':
                // 仅补充至最低库存水平
                return $shortage;
            
            case 'double_min_stock':
                // 采购两倍最低库存水平的数量
                return (int) ($product->min_stock * 2);
                
            case 'min_stock':
            default:
                // 采购最低库存水平的数量
                return (int) $product->min_stock;
        }
    }

    /**
     * 准备采购单数据
     */
    private function preparePurchaseData(Supplier $supplier, Warehouse $warehouse, array $products): array
    {
        $items = [];
        foreach ($products as $product) {
            $items[] = [
                'product_id' => $product->product_id,
                'supplier_product_id' => $product->supplier_product_id,
                'quantity' => $product->order_quantity,
                'purchase_price' => $product->purchase_price,
                'lead_time' => $product->lead_time
            ];
        }

        return [
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'items' => $items,
            'notes' => '系统自动生成的采购单',
            'auto_generated' => true
        ];
    }

    /**
     * 为测试模式显示采购商品信息
     */
    private function displayProductsInfo(array $products): void
    {
        $this->table(
            ['SKU', '商品名称', '当前库存', '最低库存', '将采购', '采购价格'],
            array_map(function ($product) {
                return [
                    $product->sku,
                    $product->product_name,
                    $product->quantity,
                    $product->min_stock,
                    $product->order_quantity,
                    number_format($product->purchase_price, 2) . ' 元'
                ];
            }, $products)
        );
    }

    /**
     * 发送系统自动采购通知
     */
    private function sendNotifications(array $purchases): void
    {
        try {
            // 获取配置了接收通知的用户ID
            $notifyUserIds = json_decode(
                Setting::where('key', 'auto_purchase_notify_users')->first()?->value ?? '[]',
                true
            );
            
            if (empty($notifyUserIds)) {
                $this->info('未配置通知用户，跳过通知发送。');
                return;
            }
            
            // 获取用户
            $users = User::whereIn('id', $notifyUserIds)->get();
            
            if ($users->isEmpty()) {
                $this->info('未找到匹配的通知用户，跳过通知发送。');
                return;
            }
            
            $this->info('发送自动采购通知给 ' . $users->count() . ' 个用户。');
            
            // 发送通知
            Notification::send($users, new PurchaseOrderGenerated($purchases));
            
            $this->info('通知发送成功。');
        } catch (\Exception $e) {
            $this->warn('发送通知时发生错误: ' . $e->getMessage());
            Log::error('自动采购通知发送失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 