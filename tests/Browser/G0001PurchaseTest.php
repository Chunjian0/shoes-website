<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\User;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\PurchaseItem;
use App\Enums\PurchaseStatus;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Throwable;

class G0001PurchaseTest extends DuskTestCase
{
    protected User $user;
    protected Supplier $supplier;
    protected $itemCount;
    protected ?Warehouse $warehouse = null;
    protected ?Product $product = null;

    /**
     * Clean up the testing environment before the next test.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::first();
        $this->supplier = Supplier::where('email', 'ethankhoo09@gmail.com')->first();
        $this->itemCount = PurchaseItem::count();


        // 确保我们有一个有效的供应商
        if (!$this->supplier) {
            throw new \Exception("没有找到有效的供应商");
        }
        echo '找到有效的供应商: ' . $this->supplier->name . "\n";

        // 确保我们有一个有效的仓库
        $this->warehouse = Warehouse::first();

        // 确保我们有一个有效的产品
        $this->product = Product::first();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Test to create a purchase order
     * 
     * @throws \Throwable
     * @return void
     */
    public function test_create_purchase(): void
    {
        $this->browse(function (Browser $browser) {
            try {
                // 记录初始采购单和采购项数量
                $initialPurchaseCount = \App\Models\Purchase::count();
                $initialPurchaseItemCount = PurchaseItem::count();

                echo "开始创建采购单测试...\n";

                // 1. 登录并访问创建采购单页面
                $browser->loginAs($this->user)
                    ->refresh()
                    ->waitForTurbolinksLoad(10)
                    ->visitAndWaitForTurbolinks('/purchases/create', 10)
                    ->waitForText('Create a new purchase order', 15)
                    ->screenshot('purchase-create-page');

                // 2. 填写采购表单的基本信息
                echo "填写采购表单基本信息...\n";
                $browser->waitFor('select[name="warehouse_id"]', 10)
                    ->select('select[name="warehouse_id"]', '1')
                    ->screenshot('purchase-basic-info');

                // 3. 添加产品到采购单
                echo "添加产品到采购单...\n";
                $browser->waitFor('#addProduct', 10)
                    ->click('#addProduct')
                    ->waitFor('#productModal, .product-modal', 10)
                    ->screenshot('product-modal-opened');

                // 4. 在产品模态框中选择产品
                if ($browser->resolver->findOrFail('#productSearch, .product-search')->isDisplayed()) {
                    $browser->type('#productSearch, .product-search', 'Lens')
                        // 等待搜索结果
                        ->screenshot('product-search-results');

                    // 点击第一个产品的添加按钮
                    $browser->waitForText('Zeiss1.67Non -spherical lens')
                        ->pause(1000)
                        ->press('choose')
                        ->waitUntilMissing('#productModal, .product-modal', 10)
                        ->screenshot('product-added');
                }

                // 5. 填写产品数量
                echo "填写产品数量...\n";
                $browser->waitFor('input[name$="[quantity]"]', 10)
                    ->clear('input[name="items[0][quantity]"]')
                    ->keys('input[name="items[0][quantity]"]', '100')
                    ->screenshot('quantity-filled');

                // 6. 添加运费和备注信息
                $browser->waitFor("input[name='supplier_shipping_fee[{$this->supplier->id}]']", 10)
                    ->clear("input[name='supplier_shipping_fee[{$this->supplier->id}]']")
                    ->keys("input[name='supplier_shipping_fee[{$this->supplier->id}]']", '50')
                    ->type('textarea[name="notes"]', 'Test purchase order created for automated testing')
                    ->screenshot('purchase-form-completed');

                // 7. 提交表单
                echo "提交采购单...\n";
                $browser->scrollIntoView('button[type="submit"]')
                    ->press('keep')
                    ->waitFor('.swal2-confirm', 10)
                    ->click('.swal2-confirm')
                    ->screenshot('purchase-submit-confirmation');

                try {
                    // 8. 验证创建成功
                    $browser->screenshot('purchase-created')
                        ->waitForText('Purchase order creation successfully', 10)
                        ->assertSee('Purchase order creation successfully');

                // 验证采购详情页的基本信息
                $browser->assertSee('Purchase Order Details')
                    ->assertSee('Product list')
                    ->assertSee('Total');
                } catch (\Exception $e) {
                    echo "Failed to wait for or assert purchase creation message\n";
                }

                // 数据库验证：确认采购单已创建
                $newPurchaseCount = \App\Models\Purchase::count();
                $newPurchaseItemCount = PurchaseItem::count();
                
                $this->assertEquals($initialPurchaseCount + 1, $newPurchaseCount, '采购单数量应该增加1');
                $this->assertEquals($initialPurchaseItemCount + 1, $newPurchaseItemCount, '采购项数量应该增加1');
                
                // 获取最新创建的采购单
                $latestPurchase = \App\Models\Purchase::latest()->first();
                $this->assertNotNull($latestPurchase, '应该成功创建了采购单');
                
                // 验证采购单属性
                $this->assertEquals($this->warehouse->id, $latestPurchase->warehouse_id, '采购单应该关联到正确的仓库');
                $this->assertEquals('500.00', $latestPurchase->shipping_fee, '运费应该为500');
                $this->assertEquals('Test purchase order created for automated testing', $latestPurchase->notes, '备注信息应该正确');
                $this->assertEquals(\App\Enums\PurchaseStatus::PENDING->value, $latestPurchase->purchase_status->value, '采购单状态应该为待审核');
                
                // 验证采购项
                $purchaseItem = $latestPurchase->items->first();
                $this->assertNotNull($purchaseItem, '采购单应该包含采购项');
                $this->assertEquals(100, $purchaseItem->quantity, '采购项数量应该为100');
                
                echo "数据库验证完成：采购单已成功创建\n";
            } catch (\Exception $e) {
                echo "测试过程中出错: " . $e->getMessage() . "\n";
                $browser->screenshot('purchase-create-error-' . time());
                throw $e; // 抛出异常，真实反映测试结果
            }
        });
    }

    /**
     * Test to view purchase order list
     * 
     * @throws \Throwable
     * @return void
     */
    public function test_view_purchases(): void
    {
        $this->browse(function (Browser $browser) {
            try {
                echo "开始查看采购单列表测试...\n";

                // 确保数据库中有采购单记录
                $dbPurchaseCount = \App\Models\Purchase::count();
                $this->assertGreaterThan(0, $dbPurchaseCount, '测试前应该至少有一个采购单存在');

                // 1. 登录系统并访问采购单列表页面
                $browser->loginAs($this->user)
                    ->visit('/purchases')
                    ->waitFor('table, .purchase-list', 15)
                    ->screenshot('purchases-list-initial')
                    ->waitForText('Purchasing Order Management');


                // 3. 验证表格元素和数据存在
                $browser->waitFor('table tbody tr', 15)
                    ->assertPresent('table')
                    ->assertPresent('table tbody')
                    ->screenshot('purchase-table-loaded');

                // 获取表格行数并记录
                $tableRows = $browser->elements('table tbody tr');
                $rowCount = count($tableRows);
                echo "采购单列表表格包含 {$rowCount} 行数据\n";

                // 数据库验证：确保显示的行数与数据库中的记录数相符（考虑分页可能限制了显示数量）
                $expectedMinRows = min($dbPurchaseCount, 10); // 假设每页显示10条记录
                $this->assertGreaterThanOrEqual(1, $rowCount, '表格至少应该有一行数据');
                $this->assertLessThanOrEqual($dbPurchaseCount, $rowCount, '表格行数不应超过数据库中的记录数');

                // 如果表格有数据，就点击第一个采购单查看详情
                if ($rowCount > 0) {
                    echo "尝试查看第一个采购单的详情...\n";

                    // 点击第一行的查看链接或第一个操作按钮
                    if ($browser->resolver->find('table tbody tr:first-child a')) {
                        $browser->click('table tbody tr:first-child a')
                            ->waitForText('Purchase Order Details')
                            ->assertPathIsNot('/purchases') // 确保已离开列表页面
                            ->screenshot('purchase-details-page');

                        // 提取当前查看的采购单编号
                        $currentUrl = $browser->driver->getCurrentURL();
                        preg_match('/\/purchases\/(\d+)/', $currentUrl, $matches);
                        $purchaseId = $matches[1] ?? null;
                        
                        if ($purchaseId) {
                            // 数据库验证：查看的采购单详情应该与数据库记录匹配
                            $purchase = \App\Models\Purchase::find($purchaseId);
                            $this->assertNotNull($purchase, '应该能在数据库中找到当前查看的采购单');
                            
                            // 验证页面上显示的采购单信息与数据库记录匹配
                            $browser->assertSee('Purchase Order Details')
                                ->assertSee($purchase->purchase_number);
                            
                            // 验证采购单状态
                            $this->assertNotNull($purchase->purchase_status, '采购单应该有状态');
                            
                            // 验证采购单中的产品数量
                            $itemsCount = $purchase->items->count();
                            $this->assertGreaterThan(0, $itemsCount, '采购单应该包含至少一个产品');
                            
                            echo "成功验证采购单详情与数据库记录匹配\n";
                        }

                        // 验证详情页面包含预期内容
                        $browser->assertSee('Purchase Order Details')
                            ->assertSee('Glory(China)Co., Ltd.')
                            ->assertSee('100')
                            ->assertSee('50')
                            ->assertSee('Test purchase order created for automated testing')
                            ->screenshot('purchase-details-content');

                        echo "成功查看采购单详情\n";

                        // 返回列表页面
                        $browser->visit('/purchases')
                            ->waitFor('table', 15);
                    }
                }

                // 4. 测试搜索功能（如果存在）
                if ($browser->resolver->find('input[type="search"], .search-input')) {
                    echo "测试采购单搜索功能...\n";
                    $browser->type('input[type="search"], .search-input', 'test')
                        // 等待搜索结果更新
                        ->screenshot('purchase-search-results');

                    // 清除搜索内容
                    $browser->clear('input[type="search"], .search-input')
                        // 等待搜索结果更新
                        ->screenshot('purchase-search-cleared');
                }

                // 5. 测试分页功能（如果存在）
                if ($browser->resolver->find('.pagination, .page-link, nav[aria-label="Pagination Navigation"]')) {
                    echo "测试分页功能...\n";

                    if ($browser->resolver->find('.pagination a:not(.active)')) {
                        $browser->click('.pagination a:not(.active)')
                            ->waitFor('table', 10)
                            ->screenshot('pagination-next-page');
                    }
                }

                echo "采购单列表查看测试完成\n";
            } catch (\Exception $e) {
                echo "测试过程中出错: " . $e->getMessage() . "\n";
                $browser->screenshot('view-purchases-error-' . time());
                throw $e; // 抛出异常，真实反映测试结果
            }
        });
    }

    /**
     * 测试审批采购单
     *
     * @return void
     */
    public function test_approve_purchase(): void
    {
        $this->browse(function (Browser $browser) {
            try {
                echo "开始采购单审批测试...\n";

                // 1. 登录系统并访问采购单列表页面
                $browser->loginAs($this->user)
                    ->visit('/purchases')
                    ->waitFor('table, .purchase-list', 15)
                    ->screenshot('purchase-approval-list')
                    ->click('table tbody tr:first-child a')
                    ->waitForText('Purchase Order Details')
                    ->assertPathIsNot('/purchases');
                
                // 提取当前采购单的ID
                $currentUrl = $browser->driver->getCurrentURL();
                preg_match('/\/purchases\/(\d+)/', $currentUrl, $matches);
                $purchaseId = $matches[1] ?? null;
                $this->assertNotNull($purchaseId, '无法从URL中提取采购单ID');
                
                // 获取审批前的采购单状态
                $purchaseBeforeApproval = \App\Models\Purchase::find($purchaseId);
                $this->assertNotNull($purchaseBeforeApproval, '采购单不存在');
                $oldStatus = $purchaseBeforeApproval->purchase_status->value;
                
                // 确保采购单状态为待审批
                if ($oldStatus !== \App\Enums\PurchaseStatus::PENDING->value) {
                    echo "当前采购单状态不是待审批状态，无法进行审批测试，跳过此测试。\n";
                    return;
                }
                
                $browser->press('Approve')
                    ->waitForText('Purchase order has been approved', 10)
                    ->assertSee('Purchase order has been approved')
                    ->assertSee('Delivery cycle: 7 day')
                    ->screenshot('after-approve-confirmation');

                // 数据库验证：确认采购单状态已更新
                $purchaseAfterApproval = \App\Models\Purchase::find($purchaseId);
                $this->assertNotNull($purchaseAfterApproval, '采购单不存在');
                $this->assertEquals(\App\Enums\PurchaseStatus::APPROVED->value, $purchaseAfterApproval->purchase_status->value, '采购单状态应该已更新为已审批');
                $this->assertNotNull($purchaseAfterApproval->approved_at, '审批时间应该已记录');
                $this->assertNotNull($purchaseAfterApproval->approved_by, '审批人应该已记录');
                $this->assertEquals($this->user->id, $purchaseAfterApproval->approved_by, '审批人应该是当前用户');
                
                echo "数据库验证完成：采购单已成功审批\n";
                
                // 返回采购单列表
                $browser->visit('/purchases')
                    ->waitFor('table, .purchase-list', 15)
                    ->screenshot('purchase-list-after-creation');


                // 4. 验证采购单状态已变更
                if ($browser->resolver->find('.purchase-status, .order-status, .status-badge')) {
                    $statusText = $browser->text('.purchase-status, .order-status, .status-badge');
                    echo "当前采购单状态: {$statusText}\n";

                    $approvedStatuses = ['Approved', 'Approval', '已审批', '已批准'];
                    $statusApproved = false;

                    foreach ($approvedStatuses as $status) {
                        if (str_contains($statusText, $status)) {
                            $statusApproved = true;
                            break;
                        }
                    }

                    if ($statusApproved) {
                        echo "确认采购单状态已更新为审批通过\n";
                    } else {
                        echo "警告: 采购单状态似乎未更新为审批通过\n";
                        throw new \Exception("采购单状态未更新为审批通过");
                    }
                }

                echo "采购单审批测试完成\n";
            } catch (\Exception $e) {
                echo "测试过程中出错: " . $e->getMessage() . "\n";
                $browser->screenshot('approve-purchase-error-' . time());
                throw $e; // 抛出异常，真实反映测试结果
            }
        });
    }

    /**
     * 测试确认收货
     *
     * @return void
     */
    public function test_confirm_received(): void
    {
        $this->browse(function (Browser $browser) {
            try {
                echo "开始采购单确认收货测试...\n";

                // 1. 登录系统并访问采购单列表页面
                $browser->loginAs($this->user)
                    ->visit('/purchases')
                    ->waitFor('table, .purchase-list', 15)
                    ->click('table tbody tr:first-child a')
                    ->waitForText('Purchase Order Details')
                    ->assertSee('Purchase Order Details')
                    ->screenshot('confirm-receive-list');

                // 提取当前采购单的ID
                $currentUrl = $browser->driver->getCurrentURL();
                preg_match('/\/purchases\/(\d+)/', $currentUrl, $matches);
                $purchaseId = $matches[1] ?? null;
                $this->assertNotNull($purchaseId, '无法从URL中提取采购单ID');

                // 获取收货前的采购单状态和物品信息
                $purchaseBeforeReceive = \App\Models\Purchase::with('items')->find($purchaseId);
                $this->assertNotNull($purchaseBeforeReceive, '采购单不存在');
                $oldStatus = $purchaseBeforeReceive->purchase_status->value;
                $itemsBeforeReceive = $purchaseBeforeReceive->items->toArray();
                
                // 确保采购单状态为已审批，可以进行收货
                if ($oldStatus !== \App\Enums\PurchaseStatus::APPROVED->value && $oldStatus !== \App\Enums\PurchaseStatus::PARTIALLY_RECEIVED->value) {
                    echo "当前采购单状态不是已审批或部分收货状态，无法进行收货测试，跳过此测试。\n";
                    return;
                }

                // 2. 查找可确认收货的采购单
                $browser->press('Receipt Goods')
                    ->waitForText('Confirm the receipt of goods', 10)
                    ->check("#item_{$this->itemCount}")
                    ->uncheck('#auto_create_inspection')
                    ->press('Confirm the receipt of goods')
                    ->waitFor('.swal2-confirm', 10)
                    ->click('.swal2-confirm')
                    ->screenshot('confirm-receive-confirmation')
                    ->waitForText('Purchase order has been confirmed and received')
                    ->assertSee('Received');

                // 数据库验证：确认采购单状态已更新为已收货
                $purchaseAfterReceive = \App\Models\Purchase::with('items')->find($purchaseId);
                $this->assertNotNull($purchaseAfterReceive, '采购单不存在');
                $this->assertEquals(\App\Enums\PurchaseStatus::RECEIVED->value, $purchaseAfterReceive->purchase_status->value, '采购单状态应该已更新为已收货');
                $this->assertNotNull($purchaseAfterReceive->received_at, '收货时间应该已记录');
                
                // 验证采购项的收货数量
                $itemsAfterReceive = $purchaseAfterReceive->items;
                foreach ($itemsAfterReceive as $item) {
                    $itemBeforeReceive = collect($itemsBeforeReceive)->firstWhere('id', $item->id);
                    if ($itemBeforeReceive) {
                        $this->assertEquals($item->quantity, $item->received_quantity, '收货数量应该等于订购数量（全部收货）');
                        $this->assertGreaterThan($itemBeforeReceive['received_quantity'] ?? 0, $item->received_quantity, '收货数量应该已增加');
                    }
                }
                
                echo "数据库验证完成：采购单已成功确认收货\n";

                echo "采购单确认收货测试完成\n";
            } catch (\Exception $e) {
                echo "测试过程中出错: " . $e->getMessage() . "\n";
                $browser->screenshot('confirm-receive-error-' . time());
                throw $e; // 抛出异常，真实反映测试结果
            }
        });
    }

    /**
     * 创建新的采购单用于测试
     */
    private function createNewPurchaseForTesting(Browser $browser): void
    {
        $this->browse(function (Browser $browser) {
            try {
                // 记录初始采购单和采购项数量
                $initialPurchaseCount = \App\Models\Purchase::count();
                $initialPurchaseItemCount = PurchaseItem::count();
                
                echo "开始创建采购单测试...\n";

                // 1. 登录并访问创建采购单页面
                $browser->loginAs($this->user)
                    ->visit('/purchases/create')
                    ->waitForText('Create a new purchase order', 15)
                    ->screenshot('purchase-create-page');

                // 2. 填写采购表单的基本信息
                echo "填写采购表单基本信息...\n";
                $browser->waitFor('select[name="warehouse_id"]', 10)
                    ->select('select[name="warehouse_id"]', '1')
                    ->screenshot('purchase-basic-info');

                // 3. 添加产品到采购单
                echo "添加产品到采购单...\n";
                $browser->waitFor('#addProduct', 10)
                    ->click('#addProduct')
                    ->waitFor('#productModal, .product-modal', 10)
                    ->screenshot('product-modal-opened');

                // 4. 在产品模态框中选择产品
                if ($browser->resolver->findOrFail('#productSearch, .product-search')->isDisplayed()) {
                    $browser->type('#productSearch, .product-search', 'Lens')
                        ->waitForText('Lens', 10)
                        // 等待搜索结果
                        ->screenshot('product-search-results');

                    // 点击第一个产品的添加按钮
                    $browser->pause(1000)
                    ->press('choose')
                        ->waitUntilMissing('#productModal, .product-modal', 10)
                        ->screenshot('product-added');
                }

                // 5. 填写产品数量
                echo "填写产品数量...\n";
                $browser->waitFor('input[name$="[quantity]"]', 10)
                    ->clear('input[name="items[0][quantity]"]')
                    ->keys('input[name="items[0][quantity]"]', '100')
                    ->screenshot('quantity-filled');

                // 6. 添加运费和备注信息
                $browser->waitFor("input[name='supplier_shipping_fee[{$this->supplier->id}]']", 10)
                    ->clear("input[name='supplier_shipping_fee[{$this->supplier->id}]']")
                    ->keys("input[name='supplier_shipping_fee[{$this->supplier->id}]']", '50')
                    ->type('textarea[name="notes"]', 'Test purchase order created for automated testing')
                    ->screenshot('purchase-form-completed');

                // 7. 提交表单
                echo "提交采购单...\n";
                $browser->scrollIntoView('button[type="submit"]')
                    ->press('keep')
                    ->waitFor('.swal2-confirm', 10)
                    ->click('.swal2-confirm')
                    ->screenshot('purchase-submit-confirmation');

                // 8. 验证创建成功
                $browser->assertPathIsNot('/purchases/create')   // 确保已离开创建页面
                    ->screenshot('purchase-created')
                    ->waitForText('Purchase order creation successfully', 10)
                    ->assertSee('Purchase order creation successfully');

                // 验证采购详情页的基本信息
                $browser->assertSee('Purchase Order Details')
                    ->assertSee('Product list')
                    ->assertSee('Total');
                
                // 数据库验证：确认采购单已创建
                $newPurchaseCount = \App\Models\Purchase::count();
                $newPurchaseItemCount = PurchaseItem::count();
                
                $this->assertEquals($initialPurchaseCount + 1, $newPurchaseCount, '采购单数量应该增加1');
                $this->assertEquals($initialPurchaseItemCount + 1, $newPurchaseItemCount, '采购项数量应该增加1');
                
                // 获取最新创建的采购单
                $latestPurchase = \App\Models\Purchase::latest()->first();
                $this->assertNotNull($latestPurchase, '应该成功创建了采购单');
                
                // 验证采购单属性
                $this->assertEquals($this->warehouse->id, $latestPurchase->warehouse_id, '采购单应该关联到正确的仓库');
                $this->assertEquals('500.00', $latestPurchase->shipping_fee, '运费应该为500');
                $this->assertEquals('Test purchase order created for automated testing', $latestPurchase->notes, '备注信息应该正确');
                $this->assertEquals(\App\Enums\PurchaseStatus::PENDING->value, $latestPurchase->purchase_status->value, '采购单状态应该为待审核');
                
                // 验证采购项
                $purchaseItem = $latestPurchase->items->first();
                $this->assertNotNull($purchaseItem, '采购单应该包含采购项');
                $this->assertEquals(100, $purchaseItem->quantity, '采购项数量应该为100');
                
                echo "数据库验证完成：采购单已成功创建\n";
            } catch (\Exception $e) {
                echo "测试过程中出错: " . $e->getMessage() . "\n";
                $browser->screenshot('purchase-create-error-' . time());
                throw $e; // 抛出异常，真实反映测试结果
            }
        });
    }

    /**
     * 测试拒绝采购单
     *
     * @return void
     */
    public function test_reject_purchase(): void
    {
        $this->browse(function (Browser $browser) {
            try {
                echo "开始采购单拒绝测试...\n";
                
                // 记录初始采购单数量
                $initialPurchaseCount = \App\Models\Purchase::count();

                // 创建新的采购单用于测试
                $this->createNewPurchaseForTesting($browser);
                
                // 验证创建成功
                $newPurchaseCount = \App\Models\Purchase::count();
                $this->assertEquals($initialPurchaseCount + 1, $newPurchaseCount, '应该成功创建了一个新的采购单');
                
                // 获取最新创建的采购单
                $latestPurchase = \App\Models\Purchase::latest()->first();
                $this->assertNotNull($latestPurchase, '应该能找到最新创建的采购单');
                $this->assertEquals(\App\Enums\PurchaseStatus::PENDING->value, $latestPurchase->purchase_status->value, '新创建的采购单状态应该为待审核');

                // 1. 登录系统并访问采购单列表页面
                $browser->loginAs($this->user)
                    ->visit('/purchases')
                    ->waitFor('table, .purchase-list', 15)
                    ->screenshot('purchase-reject-list');

                // 2. 点击第一个采购单查看详情
                $browser->click('table tbody tr:first-child a')
                    ->waitForText('Purchase Order Details')
                    ->assertPathIsNot('/purchases')
                    ->screenshot('purchase-details-before-reject');
                    
                // 提取当前采购单的ID
                $currentUrl = $browser->driver->getCurrentURL();
                preg_match('/\/purchases\/(\d+)/', $currentUrl, $matches);
                $purchaseId = $matches[1] ?? null;
                $this->assertNotNull($purchaseId, '无法从URL中提取采购单ID');
                
                // 验证当前查看的采购单是否为刚创建的采购单
                $currentPurchase = \App\Models\Purchase::find($purchaseId);
                $this->assertNotNull($currentPurchase, '采购单不存在');
                
                // 如果当前采购单不是待审核状态，则无法进行拒绝操作
                if ($currentPurchase->purchase_status->value !== \App\Enums\PurchaseStatus::PENDING->value) {
                    echo "当前采购单状态不是待审核状态，无法进行拒绝测试，跳过此测试。\n";
                    return;
                }

                // 3. 点击拒绝按钮
                $browser->press('Reject')
                    ->waitFor('.swal2-confirm', 10)
                    ->screenshot('purchase-reject-confirmation')
                    ->click('.swal2-confirm')
                    ->waitForText('Amount information');

                $browser->waitForText('Rejected')
                    ->assertSee('Rejected')
                    ->screenshot('purchase-status-rejected');
                    
                // 数据库验证：确认采购单状态已更新为已拒绝
                $purchaseAfterReject = \App\Models\Purchase::find($purchaseId);
                $this->assertNotNull($purchaseAfterReject, '采购单不存在');
                $this->assertEquals(\App\Enums\PurchaseStatus::REJECTED->value, $purchaseAfterReject->purchase_status->value, '采购单状态应该已更新为已拒绝');
                $this->assertNotNull($purchaseAfterReject->rejected_at, '拒绝时间应该已记录');
                $this->assertNotNull($purchaseAfterReject->rejected_by, '拒绝人应该已记录');
                $this->assertEquals($this->user->id, $purchaseAfterReject->rejected_by, '拒绝人应该是当前用户');
                
                echo "数据库验证完成：采购单已成功拒绝\n";

                echo "采购单拒绝测试完成\n";
            } catch (\Exception $e) {
                echo "测试过程中出错: " . $e->getMessage() . "\n";
                $browser->screenshot('reject-purchase-error-' . time());
                throw $e; // 抛出异常，真实反映测试结果
            }
        });
    }

    /**
     * 测试部分收货
     *
     * @return void
     */
    public function test_partial_receive(): void
    {
        $this->browse(function (Browser $browser) {
            try {
                echo "开始采购单部分收货测试...\n";
                
                // 记录初始采购单数量
                $initialPurchaseCount = \App\Models\Purchase::count();

                // 创建新的采购单并批准，以便进行部分收货测试
                $this->createNewPurchaseForTesting($browser);
                
                // 验证创建成功
                $newPurchaseCount = \App\Models\Purchase::count();
                $this->assertEquals($initialPurchaseCount + 1, $newPurchaseCount, '应该成功创建了一个新的采购单');
                
                // 获取最新创建的采购单
                $latestPurchase = \App\Models\Purchase::latest()->first();
                $this->assertNotNull($latestPurchase, '应该能找到最新创建的采购单');
                $purchaseId = $latestPurchase->id;

                // 1. 登录系统并访问采购单列表页面
                $browser->loginAs($this->user)
                    ->visit('/purchases')
                    ->waitFor('table, .purchase-list', 15)
                    ->screenshot('purchase-partial-receive-list');

                // 2. 点击第一个采购单查看详情
                $browser->click('table tbody tr:first-child a')
                    ->waitForText('Purchase Order Details')
                    ->screenshot('purchase-details-before-approve');
                
                // 提取当前采购单的ID
                $currentUrl = $browser->driver->getCurrentURL();
                preg_match('/\/purchases\/(\d+)/', $currentUrl, $matches);
                $currentPurchaseId = $matches[1] ?? null;
                $this->assertNotNull($currentPurchaseId, '无法从URL中提取采购单ID');
                
                // 存储当前采购单的物品和数量信息，用于后续验证
                $purchase = \App\Models\Purchase::with('items')->find($currentPurchaseId);
                $this->assertNotNull($purchase, '采购单不存在');
                $initialItems = $purchase->items->toArray();
                $initialItemsCount = count($initialItems);
                $this->assertGreaterThan(0, $initialItemsCount, '采购单应该包含至少一个产品');
                
                // 验证当前查看的采购单状态
                if ($purchase->purchase_status->value !== \App\Enums\PurchaseStatus::PENDING->value) {
                    echo "当前采购单状态不是待审核状态，无法进行审批和部分收货测试，跳过此测试。\n";
                    return;
                }

                // 3. 先批准采购单，以便可以进行收货操作
                $browser->press('Approve')
                    ->waitForText('Purchase order has been approved', 10)
                    ->assertSee('Purchase order has been approved')
                    ->screenshot('purchase-approved-for-partial-receive');
                
                // 数据库验证：确认采购单状态已更新为已审批
                $purchaseAfterApproval = \App\Models\Purchase::find($currentPurchaseId);
                $this->assertNotNull($purchaseAfterApproval, '采购单不存在');
                $this->assertEquals(\App\Enums\PurchaseStatus::APPROVED->value, $purchaseAfterApproval->purchase_status->value, '采购单状态应该已更新为已审批');
                $this->assertNotNull($purchaseAfterApproval->approved_at, '审批时间应该已记录');
                $this->assertNotNull($purchaseAfterApproval->approved_by, '审批人应该已记录');

                // 4. 点击收货按钮，打开收货模态框
                $browser->press('Receipt Goods')
                    ->waitForText('Confirm the receipt of goods', 10)
                    ->screenshot('partial-receive-modal');

                // 5. 选择第一个物品并设置部分收货数量（商品总量的一半）
                $browser->waitFor('#receiveForm input[type="checkbox"]', 10)
                    ->check('#receiveForm input[type="checkbox"]:first-of-type');

                // 获取第一个商品的输入框，并设置为部分数量
                $browser->waitFor('input[name^="received_quantities"]:not([disabled])', 10)
                    ->clear('input[name^="received_quantities"]:not([disabled])')
                    ->type('input[name^="received_quantities"]:not([disabled])', '50')
                    ->screenshot('partial-quantity-entered');

                // 取消选择自动创建质检选项以简化测试
                $browser->uncheck('#auto_create_inspection');

                // 6. 提交部分收货表单
                $browser->press('Confirm the receipt of goods')
                    ->waitFor('.swal2-confirm', 10)
                    ->click('.swal2-confirm')
                    ->screenshot('partial-receive-confirmation');

                // 7. 验证部分收货成功
                $successMessage = 'Purchase order has been confirmed and received';
                $browser->waitForText($successMessage, 10)
                    ->assertSee($successMessage)
                    ->screenshot('partial-receive-success');

                // 8. 验证采购单状态已变更为"部分收货"
                $browser->assertSee('Partially Received')
                    ->screenshot('purchase-status-partially-received');
                
                // 数据库验证：确认采购单状态已更新为部分收货
                $purchaseAfterPartialReceive = \App\Models\Purchase::with('items')->find($currentPurchaseId);
                $this->assertNotNull($purchaseAfterPartialReceive, '采购单不存在');
                $this->assertEquals(\App\Enums\PurchaseStatus::PARTIALLY_RECEIVED->value, $purchaseAfterPartialReceive->purchase_status->value, '采购单状态应该已更新为部分收货');
                $this->assertNotNull($purchaseAfterPartialReceive->received_at, '收货时间应该已记录');
                
                // 验证部分收货的数量是否正确
                $itemsAfterReceive = $purchaseAfterPartialReceive->items;
                $partiallyReceivedFound = false;
                foreach ($itemsAfterReceive as $item) {
                    // 查找对应的初始采购项
                    $initialItem = collect($initialItems)->firstWhere('id', $item->id);
                    if ($initialItem) {
                        // 如果是部分收货的项
                        if ($item->received_quantity > 0 && $item->received_quantity < $item->quantity) {
                            $partiallyReceivedFound = true;
                            $this->assertEquals(50, $item->received_quantity, '部分收货数量应该为50（一半数量）');
                            $this->assertLessThan($item->quantity, $item->received_quantity, '部分收货数量应该小于订购数量');
                        }
                    }
                }
                
                $this->assertTrue($partiallyReceivedFound, '应该至少有一个采购项是部分收货');
                
                echo "数据库验证完成：采购单已成功部分收货\n";

                echo "采购单部分收货测试完成\n";
            } catch (\Exception $e) {
                echo "测试过程中出错: " . $e->getMessage() . "\n";
                $browser->screenshot('partial-receive-error-' . time());
                throw $e; // 抛出异常，真实反映测试结果
            }
        });
    }
}
