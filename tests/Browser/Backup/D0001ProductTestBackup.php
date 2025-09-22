<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\User;
use App\Models\Product;
use App\Models\ProductCategory;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Browser\Traits\CleanupTestData;

class D0001ProductTest extends DuskTestCase
{
    use CleanupTestData;

    protected User $user;
    protected ProductCategory $category;

    public function setUp(): void
    {
        parent::setUp();

        // Create a test user and give authorization permissions
        $this->user = User::factory()->create([
            'email' => 'product_test_' . uniqid() . '@example.com',
            'employee_id' => 'EMP' . uniqid()
        ]);
        $this->user->assignRole('super-admin');

        // Ensure the classification of lens exists
        $this->category = ProductCategory::where('name', 'Lens')->first();
        if (!$this->category) {
            throw new \RuntimeException('Test needs "Lens" classification exists, please run the classification test first');
        }
    }

    /**
     * Test creating product
     */
    public function test_create_product(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/products/create')
                    ->assertSee('Create a product')
                    ->waitFor('#category_id')
                    ->select('#category_id', $this->category->id)
                    ->pause(2000) // Wait for parameters to load
                    
                    ->waitFor('.bg-gray-50') // Wait for parameter card to display
                    
                    ->type('#name', 'Zeiss 1.67 Non - spherical lens')
                    ->type('#brand', 'Zeiss')
                    ->type('#sku', 'ZEISS-167-ASP-' . uniqid())
                    ->type('#barcode', '123456789')
                    ->type('#description', 'High quality aspherical lens with 1.67 refractive index')
                    ->waitFor('input[name^="parameters[spherical-degree-"]')
                    ->type('input[name^="parameters[spherical-degree-"]', '1.00')
                    ->waitFor('input[name^="parameters[pillar-"]')
                    ->type('input[name^="parameters[pillar-"]', '0.50')
                    ->waitFor('select[name^="parameters[refractive-rate-"]')
                    ->select('select[name^="parameters[refractive-rate-"]', '1.67')
                    ->waitFor('select[name^="parameters[lens-type-"]')
                    ->select('select[name^="parameters[lens-type-"]', 'Advance')
                    ->type('#selling_price', '299.99')
                    ->type('#min_stock', '10')
                    ->pause(1000) // Wait for form to fully load
                    ->click('.fixed.bottom-0 button[type="submit"]') // Use more precise selector
                    ->waitForText('Product creation successfully')
                    ->assertSee('Product creation successfully');
        });
    }

    /**
     * Test viewing and searching products
     */
    public function test_view_and_search_products(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/products')
                    ->waitForText('Product list')
                    ->assertSee('Zeiss 1.67 Non - spherical lens')
                    ->type('search', 'Zeiss')
                    ->press('search')
                    ->waitUntilMissing('.opacity-75')
                    ->assertSee('Zeiss 1.67 Non - spherical lens');
        });
    }

    /**
     * Test editing product
     */
    public function test_edit_product(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/products')
                    ->waitFor('.bg-white.divide-y.divide-gray-200')
                    ->assertSee('Zeiss 1.67 Non - spherical lens')
                    ->click('a[href*="products"][href*="edit"]')
                    ->waitForText('Edit Product')
                    ->assertSelected('category_id', (string)$this->category->id)
                    ->type('name', 'Zeiss 1.67 Non - spherical lens - Upgraded version')
                    ->pause(1000) // Wait for form to fully load
                    ->click('.fixed.bottom-0 button[type="submit"]') // Use more precise selector
                    ->waitForText('The product update is successful!')
                    ->assertPathBeginsWith('/products/')  // Verify that you are redirecting to the product details page
                    ->assertSee('Zeiss 1.67 Non - spherical lens - Upgraded version'); // Verify that the updated name appears
        });
    }

    /**
     * Test deleting product
     */
    public function test_delete_product(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/products')
                    ->waitFor('.bg-white.divide-y.divide-gray-200')
                    ->assertSee('Zeiss 1.67 Non - spherical lens - Upgraded version')
                    ->press('delete')
                    ->waitForDialog()
                    ->acceptDialog()
                    ->waitForText('The product is successfully deleted!')
                    ->assertDontSee('Zeiss 1.67 Non - spherical lens - Upgraded version');
        });
    }

    /**
     * Create a product for supplier test
     */
    public function test_create_product_for_supplier_test(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/products/create')
                    ->assertSee('Create a product')
                    ->waitFor('#category_id')
                    ->select('#category_id', $this->category->id)
                    ->pause(2000) // Wait for parameters to load
                    
                    ->waitFor('.bg-gray-50') // Wait for parameter card to display
                    
                    ->type('#name', 'Zeiss1.67Non -spherical lens')
                    ->type('#brand', 'Zeiss')
                    ->type('#sku', 'ZEISS-167-ASP-' . uniqid())
                    ->type('#barcode', '123456789')
                    ->type('#description', 'High quality aspherical lens with 1.67 refractive index')
                    ->waitFor('input[name^="parameters[spherical-degree-"]')
                    ->type('input[name^="parameters[spherical-degree-"]', '1.00')
                    ->waitFor('input[name^="parameters[pillar-"]')
                    ->type('input[name^="parameters[pillar-"]', '0.50')
                    ->waitFor('select[name^="parameters[refractive-rate-"]')
                    ->select('select[name^="parameters[refractive-rate-"]', '1.67')
                    ->waitFor('select[name^="parameters[lens-type-"]')
                    ->select('select[name^="parameters[lens-type-"]', 'Advance')
                    ->type('#selling_price', '299.99')
                    ->type('#min_stock', '10')
                    ->pause(1000) // Wait for form to fully load
                    ->click('.fixed.bottom-0 button[type="submit"]') // Use more precise selector
                    ->waitForText('Product creation successfully')
                    ->assertSee('Product creation successfully');
        });
    }
} 