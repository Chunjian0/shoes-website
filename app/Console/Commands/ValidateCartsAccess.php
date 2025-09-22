<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ValidateCartsAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cart:validate-access {--fix : Automatically fix detected issues}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '验证所有购物车的访问权限并检测可能的问题';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('开始验证购物车访问权限...');
        $shouldFix = $this->option('fix');
        
        // 1. 检查有customer_id的购物车是否都有对应的客户
        $this->info('步骤1: 检查购物车记录的完整性');
        
        $invalidCustomerCarts = Cart::whereNotNull('customer_id')
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('customers')
                    ->whereRaw('customers.id = carts.customer_id');
            })
            ->get();
            
        if ($invalidCustomerCarts->count() > 0) {
            $this->error("发现 {$invalidCustomerCarts->count()} 个购物车关联了不存在的客户");
            
            foreach ($invalidCustomerCarts as $cart) {
                $this->line(" - 购物车 #{$cart->id}, customer_id: {$cart->customer_id}");
                
                if ($shouldFix) {
                    $cart->customer_id = null;
                    $cart->save();
                    $this->info("   已修复: 移除了无效的customer_id");
                }
            }
        } else {
            $this->info("所有购物车都关联了有效的客户ID");
        }
        
        // 2. 检查所有已登录客户是否都有购物车
        $this->info('步骤2: 确保所有活跃客户都有购物车');
        
        $activeCustomers = Customer::whereNotNull('last_visit_at')
            ->whereRaw('last_visit_at > DATE_SUB(NOW(), INTERVAL 30 DAY)')
            ->get();
            
        $this->info("发现 {$activeCustomers->count()} 个活跃客户");
        
        $customersWithoutCart = 0;
        foreach ($activeCustomers as $customer) {
            $hasCart = Cart::where('customer_id', $customer->id)->exists();
            
            if (!$hasCart) {
                $customersWithoutCart++;
                $this->line(" - 客户 #{$customer->id} ({$customer->email}) 没有购物车");
                
                if ($shouldFix) {
                    $cart = new Cart();
                    $cart->customer_id = $customer->id;
                    $cart->type = 'default';
                    $cart->is_default = true;
                    $cart->name = '默认购物车';
                    $cart->save();
                    $this->info("   已修复: 创建了默认购物车 #{$cart->id}");
                }
            }
        }
        
        if ($customersWithoutCart === 0) {
            $this->info("所有活跃客户都有购物车");
        } else {
            $this->warn("发现 {$customersWithoutCart} 个活跃客户没有购物车");
        }
        
        // 3. 查找有session_id但没有customer_id的购物车
        $this->info('步骤3: 检查未认证的购物车');
        
        $unauthCarts = Cart::whereNull('customer_id')
            ->whereNotNull('session_id')
            ->get();
            
        if ($unauthCarts->count() > 0) {
            $this->warn("发现 {$unauthCarts->count()} 个未认证的购物车 (仅有session_id)");
            
            // 检查这些购物车是否有商品
            $cartsWithItems = 0;
            $emptyCartIds = [];
            
            foreach ($unauthCarts as $cart) {
                $itemCount = CartItem::where('cart_id', $cart->id)->count();
                
                if ($itemCount > 0) {
                    $cartsWithItems++;
                    $this->line(" - 购物车 #{$cart->id} 有 {$itemCount} 个商品");
                } else {
                    $this->line(" - 购物车 #{$cart->id} 为空");
                    $emptyCartIds[] = $cart->id;
                }
            }
            
            $this->info(" - {$cartsWithItems} 个未认证购物车包含商品");
            $this->info(" - " . count($emptyCartIds) . " 个未认证购物车为空");
            
            // 清理空购物车
            if ($shouldFix && !empty($emptyCartIds)) {
                Cart::whereIn('id', $emptyCartIds)->delete();
                $this->info("   已修复: 删除了 " . count($emptyCartIds) . " 个空购物车");
            }
        } else {
            $this->info("没有发现未认证的购物车");
        }
        
        // 4. 检查购物车项目是否都有有效的购物车
        $this->info('步骤4: 验证购物车项目的完整性');
        
        $invalidItems = CartItem::whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('carts')
                    ->whereRaw('carts.id = cart_items.cart_id');
            })
            ->get();
            
        if ($invalidItems->count() > 0) {
            $this->error("发现 {$invalidItems->count()} 个购物车项目关联了不存在的购物车");
            
            foreach ($invalidItems as $item) {
                $this->line(" - 购物车项目 #{$item->id}, 关联购物车ID: {$item->cart_id}, 产品ID: {$item->product_id}");
                
                if ($shouldFix) {
                    $item->delete();
                    $this->info("   已修复: 删除了孤立的购物车项目");
                }
            }
        } else {
            $this->info("所有购物车项目都有有效的购物车关联");
        }
        
        // 汇总结果
        $this->newLine();
        $this->info('验证完成！');
        
        if ($shouldFix) {
            $this->info('自动修复了所有检测到的问题');
        } else {
            $this->info('若要自动修复检测到的问题，请使用 --fix 选项：');
            $this->line('php artisan cart:validate-access --fix');
        }
        
        return Command::SUCCESS;
    }
}
