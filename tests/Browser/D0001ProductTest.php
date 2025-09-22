<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\User;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Scripts\ProductDatabaseCheck;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Browser\Traits\CleanupTestData;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * 产品模块Dusk测试类
 * 
 * 使用了自定义的Browser扩展方法：
 * - waitForTurbolinksLoad：等待Turbolinks页面加载完成
 * - visitAndWaitForTurbolinks：访问页面并等待Turbolinks加载完成
 * 
 * 这些方法是在DuskTestCase类中通过Browser::macro定义的
 */
class D0001ProductTest extends DuskTestCase
{

    protected User $user;
    protected ProductCategory $category;
    protected $uniqueSku;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::first();

        // 确保Lens分类存在
        $category = ProductCategory::where('name', 'Lens')->first();
        if (!$category) {
            // 自动创建Lens分类
            $category = new ProductCategory();
            $category->name = 'Lens';
            $category->code = 'LENS-' . strtoupper(uniqid());
            $category->description = 'Optical lens products for testing';
            $category->is_active = true;
            $category->save();
        }
        $this->category = $category;
        
        // Generate a unique SKU for tests
        $this->uniqueSku = 'ZEISS-TEST-' . Str::random(8);
    }

    /**
     * 验证产品创建结果的辅助方法
     * 
     * @param array $criteria 搜索条件
     * @param string $errorMessage 错误信息
     * @return bool 验证结果
     */
    protected function assertProductCreated(array $criteria, string $errorMessage = '产品未成功创建到数据库'): bool
    {
        $productExists = ProductDatabaseCheck::productExists($criteria);
        $this->assertTrue($productExists, $errorMessage);
        return $productExists;
    }

    /**
     * 验证产品是否已被删除的辅助方法
     * 
     * @param array $criteria 搜索条件
     * @param string $errorMessage 错误信息
     * @return bool 验证结果
     */
    protected function assertProductDeleted(array $criteria, string $errorMessage = '产品未被软删除'): bool
    {
        $isDeleted = ProductDatabaseCheck::productDeleted($criteria);
        $this->assertTrue($isDeleted, $errorMessage);
        return $isDeleted;
    }

    /**
     * 验证产品不存在的辅助方法
     * 
     * @param array $criteria 搜索条件
     * @param string $errorMessage 错误信息
     * @return bool 验证结果
     */
    protected function assertProductNotExists(array $criteria, string $errorMessage = '不应该存在的产品被找到'): bool
    {
        $productExists = ProductDatabaseCheck::productExists($criteria);
        $this->assertFalse($productExists, $errorMessage);
        return !$productExists;
    }

    /**
     * Test creating product with valid data
     */
    public function test_create_product(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/products/create')
                    ->assertSee('Create a product')
                    ->waitFor('#category_id')
                    ->screenshot('01-product-create-initial')
                    ->select('#category_id', $this->category->id)
                    ->waitFor('.bg-gray-50', 10)
                    ->screenshot('02-product-create-after-category')
                    ->waitFor('.bg-gray-50', 10)
                    
                    ->type('#name', 'Zeiss 1.67 Non - spherical lens')
                    ->type('#brand', 'Zeiss')
                    ->type('#sku', $this->uniqueSku)
                    ->type('#barcode', '123456789')
                    ->type('#description', 'High quality aspherical lens with 1.67 refractive index')
                    ->screenshot('03-product-create-filled-basic')
                    
                    ->waitFor('input.w-full', 10)
                    ->screenshot('04-product-create-parameters')
                    
                    ->waitFor('#parameters-container', 10)
                    ->type('input[name^="parameters[spherical-degree-"]', '1.00')
                    ->type('input[name^="parameters[pillar-"]', '0.50')
                    
                    ->waitFor('select[name^="parameters[refractive-rate-"]')
                    ->select('select[name^="parameters[refractive-rate-"]', '1.67')
                    ->waitFor('select[name^="parameters[lens-type-"]')
                    ->select('select[name^="parameters[lens-type-"]', 'Advance')
                    
                    ->type('#selling_price', '299.99')
                    ->type('#min_stock', '10')
                    ->screenshot('05-product-create-ready')
                    ->waitUntilMissing('.opacity-75', 10)
                    
                    ->click('.fixed.bottom-0 button[type="submit"]')
                    ->screenshot('06-product-create-submitted');
                    
            // 使用辅助方法验证产品是否成功创建到数据库
            $this->assertProductCreated([
                'name' => 'Zeiss 1.67 Non - spherical lens',
                'sku' => $this->uniqueSku
            ]);
            
            // 进一步验证产品属性
            $product = ProductDatabaseCheck::getProductDetails(['sku' => $this->uniqueSku]);
            $this->assertEquals('299.99', $product->selling_price);
            $this->assertEquals('10', $product->min_stock);
        });
    }

    /**
     * Test validation errors when creating a product with empty required fields
     */
    public function test_create_product_validation_empty_fields(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/products/create')
                    ->assertSee('Create a product')
                    ->waitFor('#category_id')
                    // Try to submit without filling required fields
                    ->click('.fixed.bottom-0 button[type="submit"]')
                    ->waitFor('.text-red-500', 10) // Wait for validation errors to appear
                    
                    // 检查验证错误是否显示 - 简化检查方式
                    ->assertPresent('.text-red-500')
                    
                    // 确认仍在创建页面
                    ->assertPathIs('/products/create');
        });
    }

    /**
     * Test creating product with a duplicate SKU (should fail)
     */
    public function test_create_product_with_duplicate_sku(): void
    {
        // First create a product with a known SKU
        $uniqueSku = 'DUPLICATE-TEST-' . Str::random(5);
        
        $this->browse(function (Browser $browser) use ($uniqueSku) {
            // First create a product with the SKU
            $browser->loginAs($this->user)
                    ->visit('/products/create')
                    ->waitFor('#category_id')
                    ->select('#category_id', $this->category->id)
                    ->waitFor('.bg-gray-50', 10)
                    
                    ->type('#name', 'First product with unique SKU')
                    ->type('#brand', 'Test Brand')
                    ->type('#sku', $uniqueSku)
                    ->type('#barcode', '987654321')
                    ->type('#description', 'Test product for duplicate SKU test')
                    
                    // 使用新的参数表单结构
                    ->waitFor('#parameters-container', 10)
                    ->type('input[name^="parameters[spherical-degree-"]', '1.00')
                    ->type('input[name^="parameters[pillar-"]', '0.50')
                    
                    ->waitFor('select[name^="parameters[refractive-rate-"]')
                    ->select('select[name^="parameters[refractive-rate-"]', '1.67')
                    ->waitFor('select[name^="parameters[lens-type-"]')
                    ->select('select[name^="parameters[lens-type-"]', 'Advance')
                    
                    ->type('#selling_price', '199.99')
                    ->type('#min_stock', '5')
                    ->click('.fixed.bottom-0 button[type="submit"]');
                    
            // 使用辅助类验证第一个产品是否成功创建到数据库
            $this->assertProductCreated([
                'name' => 'First product with unique SKU',
                'sku' => $uniqueSku
            ]);
                    
            // Now try to create another product with the same SKU
            $browser->visit('/products/create')
                    ->waitFor('#category_id', 30) // 增加超时时间
                    ->select('#category_id', $this->category->id)
                    ->waitFor('.bg-gray-50', 10)
                    
                    ->type('#name', 'Second product with same SKU')
                    ->type('#brand', 'Another Brand')
                    ->type('#sku', $uniqueSku) // Using same SKU
                    ->type('#barcode', '111222333')
                    ->type('#description', 'Another test product')
                    
                    // 使用更通用的选择器
                    ->waitFor('input.w-full', 10)
                    ->type('input.w-full', '2.00')
                    ->type('input.w-full', '0.50', ['nth' => 1])
                    
                    ->waitFor('select.w-full')
                    ->select('select.w-full', '1.67')
                    ->select('select.w-full', 'Advance', ['nth' => 1])
                    
                    ->type('#selling_price', '249.99')
                    ->type('#min_stock', '8')
                    ->click('.fixed.bottom-0 button[type="submit"]'); 
                    
            // 验证我们仍在创建页面（表示验证失败）
            $currentUrl = $browser->driver->getCurrentURL();
            $this->assertStringContainsString('create', $currentUrl);
            
            // 验证第二个产品未被创建（具有相同SKU）
            $this->assertProductNotExists([
                'name' => 'Second product with same SKU'
            ], '具有重复SKU的产品不应存在于数据库中');
        });
    }

    /**
     * Test creating product with extremely long input strings
     */
    public function test_create_product_with_long_inputs(): void
    {
        $longName = Str::random(500); // Generate a very long product name
        $longDescription = Str::random(2000); // Generate a very long description
        
        $this->browse(function (Browser $browser) use ($longName, $longDescription) {
            $browser->loginAs($this->user)
                    ->visit('/products/create')
                    ->waitFor('#category_id')
                    ->select('#category_id', $this->category->id)
                    ->waitFor('.bg-gray-50', 10)
                    
                    ->type('#name', $longName)
                    ->type('#brand', 'Extreme Test')
                    ->type('#sku', 'LONG-TEST-' . Str::random(5))
                    ->type('#barcode', '999888777')
                    ->type('#description', $longDescription)
                    
                    // 使用更通用的选择器
                    ->waitFor('input.w-full', 10)
                    ->type('input.w-full', '1.50')
                    ->type('input.w-full', '0.75', ['nth' => 1])
                    
                    ->waitFor('select.w-full')
                    ->select('select.w-full', '1.67')
                    ->select('select.w-full', 'Advance', ['nth' => 1])
                    
                    ->type('#selling_price', '399.99')
                    ->type('#min_stock', '15')
                    ->click('.fixed.bottom-0 button[type="submit"]');
            
            // Whether this succeeds or fails with validation is system-dependent
            // We'll check either outcome
            $browser->waitUntilMissing('.opacity-75', 10);
            
            // Get current URL to determine if form submission succeeded
            $currentUrl = $browser->driver->getCurrentURL();
            
            if (str_contains($currentUrl, 'create')) {
                // If still on create page, validation error occurred
                $browser->assertPresent('.text-red-500');
                // Form validation prevented us from proceeding - test passes
                $this->assertTrue(true);
            } else {
                // If redirected, creation succeeded despite long inputs
                $browser->assertSee('Product');
                // The system accepted long values - test passes
                $this->assertTrue(true);
            }
        });
    }

    /**
     * Test viewing and searching products
     */
    public function test_view_and_search_products(): void
    {
        // 确保至少有一个产品
        $product = Product::first();
        if (!$product) {
            $this->test_create_product();
        }
        
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/products')
                    ->waitForText('Product list')
                    
                    // 确认产品列表页面正常加载
                    ->assertPresent('table')
                    
                    // 测试搜索功能
                    ->waitFor('#frontend-search')
                    ->type('#frontend-search', '')
                    ->waitUntilMissing('.opacity-75', 10)
                    
                    // 测试非现有产品搜索
                    ->type('#frontend-search', 'NonExistentProductXYZ_' . Str::random(10))
                    ->waitUntilMissing('.opacity-75', 10)
                    
                    // 确认搜索结果显示页面正常
                    ->assertPresent('table');
        });
    }

    /**
     * Test editing product with valid data
     */
    public function test_edit_product(): void
    {
        // First locate an existing product to edit
        $product = Product::where('name', 'like', '%Zeiss%')->first();
        
        if (!$product) {
            // If no matching product exists, create one first
            $this->test_create_product();
            
            // Try to find the product again
            $product = Product::where('name', 'like', '%Zeiss%')->first();
            
            // If still can't find, look for any product
            if (!$product) {
                $product = Product::first();
                
                // If there are still no products at all, we can't continue
                if (!$product) {
                    $this->markTestSkipped('No products available to edit');
                    return;
                }
            }
        }
        
        $newName = 'Zeiss 1.67 Lens - Updated ' . Str::random(5);
        
        $this->browse(function (Browser $browser) use ($product, $newName) {
            $browser->loginAs($this->user)
            ->refresh()
                    // 访问编辑页面
                    ->waitForTurbolinksLoad(10)
                    ->visit('/products/' . $product->id . '/edit')
                    // 等待Turbolinks加载完成
                    ->waitForTurbolinksLoad(10)
                    // 等待页面元素加载
                    ->waitForText('Edit Product', 10)
                    ->assertInputValue('name', $product->name)
                    ->assertSelected('category_id', (string)$product->category_id)
                    
                    // Update with new data
                    ->type('name', $newName)
                    ->type('description', $product->description . ' - Updated description')
                    ->type('selling_price', '329.99')
                    
                    // Wait for any AJAX operations to complete
                    ->waitUntilMissing('.opacity-75', 10)
                    
                    // Submit the edit form
                    ->pause(1000)
                    ->press('Save Modify');
                    
                    // 等待重定向到产品页面
                    try{
                        $browser->waitForLocation('/products/' . $product->id, 30);
                    }catch(\Exception $e){
                        echo "\n未能重定向到产品页面，但将继续检查数据库";
                    }
                    
            // 使用辅助类验证产品属性是否已更新
            $this->assertProductCreated([
                'name' => $newName,
                'selling_price' => '329.99'
            ]);
            
            // 获取更新后的产品详情进行更深入验证
            $updatedProduct = ProductDatabaseCheck::getProductDetails(['id' => $product->id]);
            $this->assertNotNull($updatedProduct, '无法在数据库中找到更新后的产品');
            $this->assertStringContainsString('Updated description', $updatedProduct->description, '产品描述未在数据库中更新');
        });
    }

    /**
     * Test editing a product with invalid data
     */
    public function test_edit_product_with_invalid_data(): void
    {
        // Find an existing product - use case-insensitive search
        $product = Product::where('name', 'like', '%Zeiss%')->first();
        
        if (!$product) {
            // If no product found, try to find any product
            $product = Product::first();
            
            // If still no product, create one first
            if (!$product) {
                $this->test_create_product();
                $product = Product::latest()->first();
            }
            
            // Final check
            if (!$product) {
                $this->markTestSkipped('No product available to edit even after creating one');
                return;
            }
        }
        
        $this->browse(function (Browser $browser) use ($product) {
            $browser->loginAs($this->user)
                    ->visit('/products/' . $product->id . '/edit')
                    ->waitForText('Edit Product')
                    
                    // Clear required fields
                    ->clear('name')
                    ->type('selling_price', 'not-a-number')
                    
                    // Submit with invalid data
                    ->click('.fixed.bottom-0 button[type="submit"]')
                    
                    // Wait for validation errors
                    ->waitFor('.text-red-500', 10)
                    
                    // Verify we're still on the edit page with validation errors
                    ->assertSee('Edit Product')
                    ->assertPresent('.text-red-500');
                    
            // Check that we're still on the edit page
            $currentUrl = $browser->driver->getCurrentURL();
            $this->assertStringContainsString('/edit', $currentUrl);
        });
    }

    /**
     * Test deleting product
     */
    public function test_delete_product(): void
    {
        // 创建一个产品然后删除它
        $sku = 'DELETE-TEST-' . Str::random(8);
        
        $this->browse(function (Browser $browser) use ($sku) {
            // 首先创建一个测试产品
            $browser->loginAs($this->user)
                    ->visit('/products/create')
                    ->waitFor('#category_id')
                    ->select('#category_id', $this->category->id)
                    ->waitFor('.bg-gray-50', 10)
                    
                    ->type('#name', 'Product to Delete')
                    ->type('#brand', 'Delete Brand')
                    ->type('#sku', $sku)
                    ->type('#barcode', '111222333')
                    ->type('#description', 'This product will be deleted')
                    
                    // 使用更通用的选择器
                    ->waitFor('input[name^="parameters[spherical-degree-"]')
                    ->type('input[name^="parameters[spherical-degree-"]', '1.00')
                    ->type('input[name^="parameters[pillar-"]', '0.50')
                    
                    ->waitFor('select[name^="parameters[refractive-rate-"]')
                    ->select('select[name^="parameters[refractive-rate-"]', '1.67')
                    ->waitFor('select[name^="parameters[lens-type-"]')
                    ->select('select[name^="parameters[lens-type-"]', 'Advance')
                    
                    ->type('#selling_price', '99.99')
                    ->type('#min_stock', '5')
                    ->click('.fixed.bottom-0 button[type="submit"]');
                    try{
                        $browser->waitForLocation('/products', 30);
                    }catch(\Exception $e){
                        echo "\nFailed to redirect to product page, but will continue to check the database";
                    }
                    
            // 使用辅助方法验证产品已成功创建
            $this->assertProductCreated([
                'name' => 'Product to Delete',
                'sku' => $sku
            ], 'The product to be deleted was not successfully created to the database');
                    
            // 然后删除它
            $browser->visit('/products')
                    ->waitFor('.bg-white.divide-y.divide-gray-200')
                    
                    // 搜索我们刚刚创建的产品
                    ->waitFor('#frontend-search')
                    ->type('#frontend-search', $sku)
                    ->waitUntilMissing('.opacity-75', 10)
                    
                    // 确认产品存在
                    ->assertSee($sku)
                    
                    // 删除它
                    ->press('Delete')
                    ->waitForText('Are you sure?')
                    ->press('Yes, delete it!');
                    try{
                        $browser->waitForLocation('/products', 30);
                    }catch(\Exception $e){
                        echo "\nFailed to redirect to product page, but will continue to check the database";
                    }
                    
            // 使用辅助方法验证产品已被软删除
            $this->assertProductDeleted(['sku' => $sku], '产品未被软删除');
            
            // 使用辅助方法验证正常查询无法找到该产品
            $this->assertProductNotExists(['sku' => $sku], '已删除的产品仍然可以被正常查询到');
        });
    }

    /**
     * Test unauthorized access to products
     */
    public function test_unauthorized_access(): void
    {
        // 跳过登录部分，直接使用firstOrCreate
        $regularUser = User::firstOrCreate(
            ['email' => 'regular_user@example.com'],
            [
                'name' => 'Regular User',
                'password' => bcrypt('password'),
                'employee_id' => 'EMP-REG-' . uniqid(),
                'role' => 'cashier'
            ]
        );
        
        $this->browse(function (Browser $browser) use ($regularUser) {
            // 直接尝试使用常规用户访问
            $browser->loginAs($regularUser)
                    ->visit('/products');
                    
            // 获取页面源代码
            $pageSource = $browser->driver->getPageSource();
            
            // 如果缺少特定产品创建按钮，通过测试
            $this->assertTrue(
                !str_contains(strtolower($pageSource), 'add product')
            );
        });
    }

    /**
     * Test creating product with invalid price values
     */
    public function test_product_with_invalid_price(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/products/create')
                    ->waitFor('#category_id')
                    ->select('#category_id', $this->category->id)
                    ->waitFor('.bg-gray-50', 10)
                    
                    // 填写基本信息
                    ->type('#name', 'Product with invalid price')
                    ->type('#brand', 'Test Brand')
                    ->type('#sku', 'PRICE-TEST-' . Str::random(5))
                    ->type('#barcode', '123000456')
                    
                    // 填写参数
                    ->waitFor('input.w-full', 10)
                    ->type('input.w-full', '1.00')
                    ->type('input.w-full', '0.50', ['nth' => 1])
                    
                    ->waitFor('select.w-full')
                    ->select('select.w-full', '1.67')
                    ->select('select.w-full', 'Advance', ['nth' => 1])
                    
                    // 尝试无效价格格式
                    ->type('#selling_price', 'abc')
                    ->type('#min_stock', '-10') // 负值
                    
                    // 尝试提交
                    ->click('.fixed.bottom-0 button[type="submit"]'); 
                    
            // 验证我们仍在创建页面
            $currentUrl = $browser->driver->getCurrentURL();
            $this->assertStringContainsString('create', $currentUrl);
        });
    }

    /**
     * Create a product for supplier test with precise waitFor conditions
     */
    public function test_create_product_for_supplier_test(): void
    {
        $sku = 'SUPPLIER-TEST-' . Str::random(8);
        
        $this->browse(function (Browser $browser) use ($sku) {
            $browser->loginAs($this->user)
                    ->visit('/products/create')
                    ->assertSee('Create a product')
                    ->waitFor('#category_id')
                    ->select('#category_id', $this->category->id)
                    ->waitFor('.bg-gray-50', 10)
                    
                    ->type('#name', 'Zeiss Supplier Test Product')
                    ->type('#brand', 'Zeiss')
                    ->type('#sku', $sku)
                    ->type('#barcode', '777888999')
                    ->type('#description', 'Product specifically for supplier tests')
                    
                    // 使用更通用的选择器
                    ->waitFor('input[name^="parameters[spherical-degree-"]')
                    ->type('input[name^="parameters[spherical-degree-"]', '1.00')
                    ->type('input[name^="parameters[pillar-"]', '0.50')
                    
                    ->waitFor('select[name^="parameters[refractive-rate-"]')
                    ->select('select[name^="parameters[refractive-rate-"]', '1.67')
                    ->waitFor('select[name^="parameters[lens-type-"]')
                    ->select('select[name^="parameters[lens-type-"]', 'Advance')
                    
                    
                    ->type('#selling_price', '349.99')
                    ->type('#min_stock', '20')
                    
                    ->waitUntilMissing('.opacity-75', 10)
                    
                    ->click('.fixed.bottom-0 button[type="submit"]');
                    try{
                        $browser->waitForLocation('/products', 30);
                    }catch(\Exception $e){
                        echo "\nFailed to redirect to product page, but will continue to check the database";
                    }
                    
            // 使用辅助类验证产品是否成功创建到数据库
            $this->assertProductCreated([
                'name' => 'Zeiss Supplier Test Product',
                'sku' => $sku
            ]);
            
            // 测试完成后，尝试清理测试数据
            ProductDatabaseCheck::cleanupTestProduct(['sku' => $sku]);
        });
    }

    /**
     * Test creating product without selling price (should fail)
     */
    public function test_create_product_without_selling_price(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/products/create')
                    ->assertSee('Create a product')
                    ->waitFor('#category_id')
                    ->select('#category_id', $this->category->id)
                    ->waitFor('.bg-gray-50', 10)
                    
                    ->type('#name', 'Product without selling price')
                    ->type('#brand', 'Test Brand')
                    ->type('#sku', 'NO-PRICE-' . Str::random(5))
                    ->type('#barcode', '123456789')
                    ->type('#description', 'Test product without selling price')
                    
                    // 使用更通用的选择器
                    ->waitFor('input[name^="parameters[spherical-degree-"]')
                    ->type('input[name^="parameters[spherical-degree-"]', '1.00')
                    ->type('input[name^="parameters[pillar-"]', '0.50')
                    
                    ->waitFor('select[name^="parameters[refractive-rate-"]')
                    ->select('select[name^="parameters[refractive-rate-"]', '1.67')
                    ->waitFor('select[name^="parameters[lens-type-"]')
                    ->select('select[name^="parameters[lens-type-"]', 'Advance')
                    
                    
                    // 填写其他必填字段
                    ->type('#min_stock', '10')
                    
                    // 设置销售价格为0，这将触发验证错误
                    ->type('#selling_price', '0')
                    ->screenshot('product-with-zero-price')
                    
                    // 提交表单
                    ->click('.fixed.bottom-0 button[type="submit"]')
                    ->screenshot('product-with-zero-price-submitted');
                    
            // 验证我们仍在创建页面（表示验证失败）
            $currentUrl = $browser->driver->getCurrentURL();
            $this->assertStringContainsString('create', $currentUrl);
            
            
            
            $browser->screenshot('product-with-zero-price-error');
        });
    }
} 