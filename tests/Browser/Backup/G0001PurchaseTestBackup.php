<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\User;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Product;
use App\Enums\PurchaseStatus;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

class G0001PurchaseTest extends DuskTestCase
{
    protected User $user;
    protected Supplier $supplier;
    protected Warehouse $warehouse;
    protected Product $product;

    public function setUp(): void
    {
        parent::setUp();

        // Create a test user and authorize it
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'purchase_test_' . uniqid() . '@example.com',
            'employee_id' => 'EMP' . uniqid()
        ]);
        $this->user->assignRole('super-admin');

        // Obtain an existing supplier (by E0001SupplierTest create)
        $this->supplier = Supplier::where('name', 'Glory(China)Co., Ltd.')->firstOrFail();

        // Get an existing repository (by F0001WarehouseTest create)
        $this->warehouse = Warehouse::where('status', true)->first();

        // Get existing test products (by D0001ProductTest create)
        $this->product = Product::where('name', 'Zeiss1.67Non -spherical lens')->firstOrFail();
    }

    /**
     * Test to create a purchase order
     */
    public function test_create_purchase(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/purchases/create')
                    ->waitForText('Create a new purchase order')
                    
                    // Select a warehouse
                    ->select('select[name="warehouse_id"]', $this->warehouse->id)
                    ->pause(2000)
                    ->assertInputValue('#delivery_address', 'Test Store Address')
                    
                    // Click the Add Product button
                    ->click('.inline-flex.items-center.px-3.py-2.border.border-transparent')
                    ->waitFor('#productModal')
                    ->waitForText('Select a product')
                    
                    // Search for products
                    ->type('#productSearch', 'Zeiss')
                    ->pause(1000)
                    ->waitForText('Zeiss1.67Non -spherical lens')
                    
                    // Select a product
                    ->click('button[type="button"].text-indigo-600')
                    ->pause(2000) // Wait for modal box to close and form update
                    
                    // Wait for the product form to load
                    ->waitFor('table')
                    ->waitForText('MERCHANDISE')
                    ->waitForText('Zeiss1.67Non -spherical lens')
                    ->waitForText('Minimum order quantity: 30')
                    
                    // Fill in the quantity of products
                    ->waitFor('input[name="items[0][quantity]"]')
                    ->clear('input[name="items[0][quantity]"]')
                    ->keys('input[name="items[0][quantity]"]', '100')
                    ->pause(2000) // Wait for the subtotal amount to be updated
                    
                    // Wait for supplier summary information to load
                    ->waitFor('#supplierSummaryContainer')
                    
                    // Set up supplier freight and notes
                    ->waitFor("input[name='supplier_shipping_fee[{$this->supplier->id}]']")
                    ->clear("input[name='supplier_shipping_fee[{$this->supplier->id}]']")
                    ->keys("input[name='supplier_shipping_fee[{$this->supplier->id}]']", '10')
                    
                    
                    // Add purchase order notes
                    ->clear('textarea[name="notes"]')
                    ->keys('textarea[name="notes"]', 'Test purchase order notes')
                    
                    // Save the purchase order
                    ->press('keep')
                    ->waitForText('Confirm Submission')
                    ->press('Sure')
                    ->waitForText('Purchase Order Details')
                    ->assertSee('Purchase order creation successfully');
        });
    }

    /**
     * Test to view purchase order list
     */
    public function test_view_purchases(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/purchases')
                    ->waitForText('Purchasing Order Management')
                    ->assertSee('Purchasing Order Management')
                    ->waitFor('table')
                    ->assertSee('Glory(China)Co., Ltd.')
                    
                    // Test to view details
                    ->click('a.text-indigo-600')
                    ->waitForText('Purchase Order Details')
                    ->assertSee('Product list')
                    ->assertSee('Zeiss1.67Non -spherical lens')
                    ->assertSee('Glory(China)Co., Ltd.');
        });
    }

    /**
     * Test approval purchase form
     */
    public function test_approve_purchase(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/purchases')
                    ->waitForText('Purchasing Order Management')
                    ->click('a.text-indigo-600')
                    ->waitForText('Purchase Order Details')
                    ->click('button.inline-flex.items-center.px-4.py-2.border.border-transparent.bg-green-600')
                    ->waitForText('Purchase order has been approved')
                    ->assertSee(PurchaseStatus::APPROVED->label());
        });
    }

    /**
     * Test confirmation of receipt
     */
    public function test_confirm_received(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/purchases')
                    ->waitForText('Purchasing Order Management')
                    ->click('a.text-indigo-600')
                    ->waitForText('Purchase Order Details')
                    ->pause(1000)
                    ->click('button[onclick="showReceiveModal()"]')
                    // Wait for the modal to be visible
                    ->waitFor('#receiveModal')
                    ->pause(500)
                    ->waitForText('Confirm the receipt of goods')
                    // Select multiple items
                    ->check('selected_items[]')
                    ->pause(500)
                    // Fill in received quantities
                    ->type('input[name="received_quantities[1]"]', '100')
                    // Click the Confirm Received Button
                    ->press('Confirm the receipt of goods')
                    // Wait for confirmation pop-up window
                    ->waitForText('Are you sure you want to submit the delivery information?')
                    // Click the OK button
                    ->press('Sure')
                    // Waiting for successful news
                    ->waitForText('Purchase order has been confirmed and received')
                    // Verification status updated
                    ->assertSee(PurchaseStatus::RECEIVED->label());
        });
    }

    /**
     * Test Search Purchase Order
     */
    public function test_search_purchase(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/purchases')
                    ->waitForText('Purchasing Order Management')
                    ->type('input[placeholder*="Search for purchase order"]', 'PO')
                    ->click('button[type="submit"]')
                    ->waitFor('table')
                    ->assertSee('PO');
        });
    }

    /**
     * Test export purchase orderPDF
     */
    public function test_export_purchase_pdf(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/purchases')
                    ->waitForText('Purchasing Order Management')
                    ->click('a.text-indigo-600')
                    ->waitForText('Purchase Order Details')
                    ->click('a.inline-flex.items-center.px-4.py-2.border.border-transparent.bg-blue-600')
                    ->pause(3000); // Wait for the download to begin
        });
    }

    /**
     * Test send purchase order email
     */
    public function test_send_purchase_email(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/purchases')
                    ->waitForText('Purchasing Order Management')
                    ->click('a.text-indigo-600')
                    ->waitForText('Purchase Order Details')
                    ->click('button.inline-flex.items-center.px-4.py-2.border.border-transparent.bg-blue-600')
                    ->waitForText('Purchase order has been sent to supplier')
                    ->assertSee('Purchase order has been sent to supplier');
        });
    }
}
