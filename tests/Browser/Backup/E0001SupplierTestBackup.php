<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\User;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\ProductCategory;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

class E0001SupplierTest extends DuskTestCase
{
    protected User $user;
    protected Product $product;
    protected ProductCategory $category;

    public function setUp(): void
    {
        parent::setUp();

        // Create a test user and give authorization permissions
        $this->user = User::factory()->create([
            'email' => 'supplier_test_' . uniqid() . '@example.com',
            'employee_id' => 'EMP' . uniqid()
        ]);
        $this->user->assignRole('super-admin');

        // Ensure the classification of lens
        $this->category = ProductCategory::where('name', 'Lens')->first();
        if (!$this->category) {
            throw new \RuntimeException('Test needs"Lens"Classification exists, please run the classification test first');
        }

        // Get the existing test products
        $this->product = Product::where('name', 'Zeiss1.67Non -spherical lens')->first();
        if (!$this->product) {
            throw new \RuntimeException('Tests require products"Zeiss1.67Non -spherical lens"Exist, please run the product test first');
        }
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Test creation supplier
     */
    public function test_create_supplier(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/suppliers/create')
                    ->waitForText('Create a supplier', 10)
                    ->type('name', 'Zeiss Optical Technology(Guangdong)Co Ltd.')
                    ->type('code', 'ZEISS-GD-' . uniqid())
                    ->type('phone', '020-12345678')
                    ->type('email', 'contact@zeiss-gd.com')
                    ->type('address', 'Huaxia Road, Zhujiang New City, Tianhe District, Guangzhou City, Guangdong Province10No. R & F Center25layer')
                    ->type('credit_limit', '1000000')
                    ->type('payment_term', '30')
                    ->check('is_active')
                    // Waiting for contact form loading and complete
                    ->waitFor('#contacts-container .contact-form')
                    ->pause(1000)
                    // Fill in Contact Information
                    ->tap(function ($browser) {
                        $browser->driver->executeScript("window.scrollTo(0, document.body.scrollHeight)");
                    })
                    ->type('input[name="contacts[0][name]"]', 'Zhang San')
                    ->type('input[name="contacts[0][position]"]', 'sales Manager')
                    ->type('input[name="contacts[0][phone]"]', '13800138000')
                    ->type('input[name="contacts[0][email]"]', 'zhangsan@zeiss-gd.com')
                    ->press('KEEP')
                    ->waitForText('Supplier\'s successful creation', 10);
        });
    }

    /**
     * Test viewing and searching suppliers
     */
    public function test_view_and_search_suppliers(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/suppliers')
                    ->waitForText('Supplier management')
                    ->assertSee('Supplier management')
                    // Verify the existence of just created suppliers
                    ->waitFor('.bg-white.divide-y.divide-gray-200')
                    ->pause(2000)
                    ->assertSee('Zeiss Optical Technology(Guangdong)Co Ltd.')
                    // Test search function
                    ->waitFor('#search')
                    ->type('#search', 'Zeiss')
                    ->press('search')
                    ->pause(2000)
                    ->assertSee('Zeiss Optical Technology(Guangdong)Co Ltd.')
                    // Test status screening
                    ->waitFor('#status')
                    ->select('#status', '1')
                    ->press('search')
                    ->pause(2000)
                    ->assertSee('Zeiss Optical Technology(Guangdong)Co Ltd.');
        });
    }

    /**
     * Test editor supplier
     */
    public function test_edit_supplier(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/suppliers')
                    ->waitFor('.bg-white.divide-y.divide-gray-200')
                    ->assertSee('Zeiss Optical Technology(Guangdong)Co Ltd.')
                    ->click('.text-yellow-600')
                    ->waitForText('Edit Supplier')
                    ->type('name', 'Zeiss Optical Technology(Guangdong)Co Ltd.')
                    ->press('KEEP')
                    ->waitForText('Supplier update successfully')
                    ->assertSee('Zeiss Optical Technology(Guangdong)Co Ltd.');
        });
    }

    /**
     * Test add supplier products
     */
    public function test_add_supplier_product(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/suppliers')
                    ->waitFor('.bg-white.divide-y.divide-gray-200')
                    ->assertSee('Zeiss Optical Technology(Guangdong)Co Ltd.')
                    // Click the supplier name to enter the details page
                    ->click('.text-indigo-600.hover\:text-indigo-900')
                    ->waitFor('.max-w-7xl')
                    ->waitForText('Basic Information')
                    ->waitForText('Contact Information')
                    ->waitForText('Supplier Products')
                    ->waitFor('#addProductBtn')
                    // Click to add product button
                    ->click('#addProductBtn')
                    ->waitFor('#productModal')
                    ->waitUntilMissing('.opacity-75')
                    // Search and select products
                    ->waitFor('#searchProduct')
                    ->type('#searchProduct', 'Zeiss1.67Non -spherical lens')
                    ->pause(2000)
                    ->waitFor('#productList')
                    ->waitFor('#productList input[type="radio"]')
                    ->click('#productList input[type="radio"]')
                    // Fill in product information
                    ->waitFor('#productFormFields')
                    ->waitUntilMissing('.opacity-75')
                    ->type('#supplier_product_code', 'SUP001')
                    ->type('#purchase_price', '100')
                    ->type('#tax_rate', '6')
                    ->type('#min_order_quantity', '10')
                    ->type('#lead_time', '7')
                    ->press('Save')
                    ->waitForReload()
                    // Verify whether the product is added successfully
                    ->waitFor('#supplierProductList')
                    ->waitUntilMissing('.opacity-75')
                    ->pause(2000)
                    ->assertSeeIn('#supplierProductList', 'Zeiss1.67Non -spherical lens')
                    ->assertSeeIn('#supplierProductList', 'SUP001')
                    ->assertSeeIn('#supplierProductList', '100.00');
        });
    }

    /**
     * Test edit supplier product
     */
    public function test_edit_supplier_product(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/suppliers')
                    ->waitFor('.bg-white.divide-y.divide-gray-200')
                    ->assertSee('Zeiss Optical Technology(Guangdong)Co Ltd.')
                    // Click on the supplier name to enter the details page
                    ->click('.text-indigo-600.hover\:text-indigo-900')
                    ->waitForText('Basic Information')
                    ->waitForText('Supplier Products')
                    // Wait for the product list to load
                    ->waitFor('#supplierProductList')
                    ->assertSee('Zeiss1.67Non -spherical lens')
                    ->assertSee('SUP001')
                    // Click the Edit button
                    ->click('button[title="edit"]')
                    ->waitUntilMissing('.opacity-75')
                    ->pause(1000)
                    // Update product information
                    ->type('supplier_product_code', 'SUP001-EDIT')
                    ->type('purchase_price', '120')
                    ->type('tax_rate', '7')
                    ->type('min_order_quantity', '15')
                    ->type('lead_time', '10')
                    // Save changes
                    ->press('Save')
                    // Wait for the page to refresh and verify the updated information
                    ->waitFor('#supplierProductList')
                    ->waitUntilMissing('.opacity-75')
                    ->pause(1000)
                    ->assertSee('Zeiss1.67Non -spherical lens')
                    ->assertSee('SUP001-EDIT')
                    ->assertSee('120.00');
        });
    }

    /**
     * Test delete supplier
     */
    public function test_delete_supplier(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/suppliers')
                    ->waitFor('.bg-white.divide-y.divide-gray-200')
                    ->assertSee('Zeiss Optical Technology(Guangdong)Co Ltd.')
                    ->click('.text-red-600')
                    ->waitForDialog()
                    ->acceptDialog()
                    ->waitForText('Supplier delete successfully')
                    ->pause(2000) // Waiting for the page refresh
                    ->refresh() // Refresh the page
                    ->waitFor('.bg-white.divide-y.divide-gray-200') // Waiting for list loading
                    ->waitFor('#search')
                    ->type('search', 'Zeiss Optical Technology(Guangdong)Co Ltd.')
                    ->press('search')
                    ->waitUntilMissing('.opacity-75')
                    ->waitFor('tbody tr td')
                    ->assertSeeIn('tbody tr td', 'No suppliers found');
        });
    }

    /**
     * Test the creation of the second supplier(Used for procurement test)
     */
    public function test_create_second_supplier(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/suppliers/create')
                    ->waitForText('Create a supplier', 10)
                    ->type('name', 'Depending on(Shanghai)Optical Co., Ltd.')
                    ->type('code', 'ESSILOR-SH-' . uniqid())
                    ->type('phone', '021-87654321')
                    ->type('email', 'contact@essilor-sh.com')
                    ->type('address', 'Keiyuan Road, Zhangjiang Hi -Tech Park, Pudong New District, Shanghai88Number')
                    ->type('credit_limit', '800000')
                    ->type('payment_term', '45')
                    ->check('is_active')
                    // Waiting for contact form loading and complete
                    ->waitFor('#contacts-container .contact-form')
                    ->pause(1000)
                    // Fill in Contact Information
                    ->tap(function ($browser) {
                        $browser->driver->executeScript("window.scrollTo(0, document.body.scrollHeight)");
                    })
                    ->type('input[name="contacts[0][name]"]', 'Li Si')
                    ->type('input[name="contacts[0][position]"]', 'Sales director')
                    ->type('input[name="contacts[0][phone]"]', '13900139000')
                    ->type('input[name="contacts[0][email]"]', 'lisi@essilor-sh.com')
                    ->press('KEEP')
                    ->waitForText('Supplier\'s successful creation', 10)
                    // Back to the supplier list page
                    ->visit('/suppliers')
                    ->waitFor('.bg-white.divide-y.divide-gray-200')
                    ->assertSee('Depending on(Shanghai)Optical Co., Ltd.')
                    // Click the supplier name to enter the details page
                    ->click('.text-indigo-600.hover\:text-indigo-900')
                    ->waitFor('.max-w-7xl')
                    ->waitFor('.bg-white.shadow-sm.rounded-lg')
                    ->waitForText('Basic Information')
                    ->waitFor('#addProductBtn')
                    // Add supplier products
                    ->pause(2000)
                    ->click('#addProductBtn')
                    ->waitFor('#productModal')
                    ->waitUntilMissing('.opacity-75')
                    ->waitFor('#searchProduct')
                    ->type('#searchProduct', 'Zeiss1.67Non -spherical lens')
                    ->waitFor('#productList')
                    ->waitFor('#productList input[type="radio"]')
                    ->click('#productList input[type="radio"]')
                    ->waitFor('#productFormFields')
                    ->waitUntilMissing('.opacity-75')
                    ->type('#supplier_product_code', 'ESL001')
                    ->type('#purchase_price', '95')
                    ->type('#tax_rate', '6')
                    ->type('#min_order_quantity', '20')
                    ->press('Save')
                    ->waitForReload()
                    ->waitFor('#supplierProductList')
                    ->waitUntilMissing('.opacity-75')
                    ->pause(2000)
                    ->assertSeeIn('#supplierProductList', 'Zeiss1.67Non -spherical lens');
        });
    }

    /**
     * Test the price protocol
     */
    public function test_add_price_agreement(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/suppliers')
                    ->waitFor('.bg-white.divide-y.divide-gray-200')
                    ->assertSee('Depending on(Shanghai)Optical Co., Ltd.')
                    // Click the supplier name to enter the details page
                    ->click('.text-indigo-600.hover\:text-indigo-900')
                    ->waitFor('.max-w-7xl')
                    ->waitFor('.bg-white.shadow-sm.rounded-lg')
                    ->waitForText('Basic Information')
                    // Wait for the product list to load
                    ->waitFor('#supplierProductList')
                    ->waitUntilMissing('.opacity-75')
                    ->pause(2000)
                    // Find and click the "Add Agreement" button in the product card
                    ->click('button[onclick^="showAgreementForm"]')
                    // Wait for the agreement form modal
                    ->waitFor('#agreementFormModal')
                    ->waitUntilMissing('.opacity-75')
                    // Fill in agreement information
                    ->select('#discount_type', 'discount_rate')
                    ->waitFor('#discountRateField')
                    ->type('#discount_rate', '10')
                    ->type('#min_quantity', '50')
                    ->press('keep')
                    ->waitForReload()
                    // Verify whether the agreement is successfully added
                    ->waitFor('.agreement-list-' . $this->product->id)
                    ->waitUntilMissing('.opacity-75')
                    ->pause(2000)
                    ->assertSee('10.00%')
                    ->assertSee('50');
        });
    }

    /**
     * Test delete price protocol
     */
    public function test_delete_price_agreement(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/suppliers')
                    ->waitFor('.bg-white.divide-y.divide-gray-200')
                    ->assertSee('Depending on(Shanghai)Optical Co., Ltd.')
                    // Click the supplier name to enter the details page
                    ->click('.text-indigo-600.hover\:text-indigo-900')
                    ->waitFor('.max-w-7xl')
                    ->waitFor('.bg-white.shadow-sm.rounded-lg')
                    ->waitForText('Basic Information')
                    ->waitFor('#supplierProductList')
                    ->waitUntilMissing('.opacity-75')
                    ->pause(2000)
                    // Ensure the price agreement exists
                    ->assertSee('10.00%')
                    ->assertSee('Minimum quantity: 50')
                    // Click the Delete button (Update selector)
                    ->click('.bg-gray-50.rounded-lg.p-3 button')
                    // Wait and confirm SweetAlert2 Dialog Box
                    ->waitFor('.swal2-popup')
                    ->press('Confirm to delete')
                    ->waitUntilMissing('.swal2-popup')
                    ->pause(2000)
                    // Verification protocol has been deleted
                    ->waitForReload()
                    ->assertDontSee('10.00%')
                    ->assertDontSee('Minimum quantity: 50');
        });
    }

    /**
     * Test delete supplier products
     */
    public function test_delete_supplier_product(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/suppliers')
                    ->waitFor('.bg-white.divide-y.divide-gray-200')
                    ->assertSee('Depending on(Shanghai)Optical Co., Ltd.')
                    // Click the supplier name to enter the details page
                    ->click('.text-indigo-600.hover\:text-indigo-900')
                    ->waitFor('.max-w-7xl')
                    ->waitFor('.bg-white.shadow-sm.rounded-lg')
                    ->waitForText('Basic Information')
                    ->waitFor('#supplierProductList')
                    // Click to delete button
                    ->click('#supplierProductList button svg.text-red-500')
                    ->press('Confirm to delete')
                    ->waitUntilMissing('.swal2-popup')
                    ->pause(2000)
                    // Verification protocol has been deleted
                    ->waitForReload()
                    // Verify whether the deletion is successful
                    ->pause(2000)
                    ->assertDontSee('ESL001-EDIT');
        });
    }

    /**
     * Test the third supplier(Used for follow -up test)
     */
    public function test_create_third_supplier(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/suppliers/create')
                    ->waitForText('Create a supplier', 10)
                    ->type('name', 'Glory(China)Co., Ltd.')
                    ->type('code', 'HOYA-CN-' . uniqid())
                    ->type('phone', '020-12345678')
                    ->type('email', 'ethankhoo09@gmail.com')
                    ->type('address', 'Huaxia Road, Zhujiang New City, Tianhe District, Guangzhou City, Guangdong Province10Number')
                    ->type('credit_limit', '1000000')
                    ->type('payment_term', '60')
                    ->check('is_active')
                    // Wait for the contact form to load and complete
                    ->waitFor('#contacts-container .contact-form')
                    ->pause(1000)
                    // Fill in the contact information
                    ->tap(function ($browser) {
                        $browser->driver->executeScript("window.scrollTo(0, document.body.scrollHeight)");
                    })
                    ->type('input[name="contacts[0][name]"]', 'King five')
                    ->type('input[name="contacts[0][position]"]', 'sales Manager')
                    ->type('input[name="contacts[0][phone]"]', '13800138000')
                    ->type('input[name="contacts[0][email]"]', 'wangwu@hoya-cn.com')
                    ->press('KEEP')
                    ->waitForText('Supplier\'s successful creation', 10)
                    // Return to the supplier list page
                    ->visit('/suppliers')
                    ->waitFor('.bg-white.divide-y.divide-gray-200')
                    ->assertSee('Glory(China)Co., Ltd.')
                    // Click on the supplier name to enter the details page
                    ->click('.text-indigo-600.hover\:text-indigo-900')
                    ->waitFor('.max-w-7xl')
                    ->waitFor('.bg-white.shadow-sm.rounded-lg')
                    ->waitForText('Basic Information')
                    ->waitFor('#addProductBtn')
                    ->pause(2000)
                    // Add supplier products
                    ->click('#addProductBtn')
                    ->waitFor('#productModal')
                    ->waitUntilMissing('.opacity-75')
                    ->waitFor('#searchProduct')
                    ->type('#searchProduct', 'Zeiss1.67Non -spherical lens')
                    ->pause(2000)
                    ->waitFor('#productList')
                    ->waitFor('#productList input[type="radio"]')
                    ->click('#productList input[type="radio"]')
                    ->waitFor('#productFormFields')
                    ->waitUntilMissing('.opacity-75')
                    ->type('#supplier_product_code', 'HOYA001')
                    ->type('#purchase_price', '85')
                    ->type('#tax_rate', '6')
                    ->type('#min_order_quantity', '30')
                    ->type('#lead_time', '7')
                    ->press('Save')
                    ->waitForReload()
                    ->waitFor('#supplierProductList')
                    ->waitUntilMissing('.opacity-75')
                    ->pause(2000)
                    ->assertSeeIn('#supplierProductList', 'Zeiss1.67Non -spherical lens')
                    // Add a fixed price type price agreement
                    ->waitFor('#supplierProductList')
                    ->waitUntilMissing('.opacity-75')
                    ->pause(2000)
                    // Find and click the "Add Agreement" button in the product card
                    ->click('button[onclick^="showAgreementForm"]')
                    // Wait for the agreement form modal
                    ->waitFor('#agreementFormModal')
                    ->waitUntilMissing('.opacity-75')
                    // Fill in agreement information
                    ->select('#discount_type', 'fixed_price')
                    ->waitFor('#fixedPriceField')
                    ->type('#price', '80')
                    ->type('#min_quantity', '100')
                    ->press('keep')
                    ->waitForReload()
                    // Verify whether the agreement is successfully added
                    ->waitFor('.agreement-list-' . $this->product->id)
                    ->waitUntilMissing('.opacity-75')
                    ->pause(2000)
                    ->assertSee('80.00')
                    ->assertSee('Minimum quantity: 100');
        });
    }
} 