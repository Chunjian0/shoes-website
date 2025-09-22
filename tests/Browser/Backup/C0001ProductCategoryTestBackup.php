<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\User;
use App\Models\ProductCategory;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;

class C0001ProductCategoryTest extends DuskTestCase
{
    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        // Get or create test users
        $this->user = User::where('email', 'ethankhoo09@gmail.com')->first();
        if (!$this->user) {
            $this->user = User::factory()->create([
                'name' => 'Admin',
                'email' => 'ethankhoo09@gmail.com',
                'password' => bcrypt('password'),
            ]);
            $this->user->assignRole('super-admin');
        }
    }

    /**
     * Test and create product classification
     */
    public function test_create_product_category(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/product-categories/create')
                    ->waitForText('Add product classification')
                    ->type('name', 'lens')
                    ->type('description', 'Various types of glasses lenses, including single light,Progressive')
                    ->check('is_active')
                    // Use the correct selector to click the parameter button
                    ->click('button[type="button"].bg-indigo-600')
                    ->click('button[type="button"].bg-indigo-600')
                    ->click('button[type="button"].bg-indigo-600')
                    ->waitFor('input[name="parameters[0][name]"]')
                    ->type('parameters[0][name]', 'Spherical degree')
                    ->select('parameters[0][type]', 'number')
                    ->check('parameters[0][is_required]')
                    ->type('parameters[0][min_length]', '0')
                    ->type('parameters[0][max_length]', '2000')
                    // Add the second parameter
                    ->click('button[type="button"].bg-indigo-600')
                    ->waitFor('input[name="parameters[1][name]"]')
                    ->type('parameters[1][name]', 'Pillar')
                    ->select('parameters[1][type]', 'number')
                    ->check('parameters[1][is_required]')
                    ->type('parameters[1][min_length]', '0')
                    ->type('parameters[1][max_length]', '2000')
                    // Add the third parameter
                    
                    ->tap(function ($browser) {
                        $browser->driver->executeScript("window.scrollTo(0, document.body.scrollHeight)");
                    })
                    
                    ->waitFor('input[name="parameters[2][name]"]')
                    ->type('parameters[2][name]', 'Refractive rate')
                    ->select('parameters[2][type]', 'select')
                    ->check('parameters[2][is_required]')
                    ->pause(1000) // wait select Change trigger display
                    ->waitFor('#option-input-2')
                    ->type('#option-input-2', '1.56')
                    ->click('#option-input-2 + button')
                    ->type('#option-input-2', '1.60')
                    ->click('#option-input-2 + button')
                    ->type('#option-input-2', '1.67')
                    ->click('#option-input-2 + button')
                    ->type('#option-input-2', '1.74')
                    ->click('#option-input-2 + button')
                    ->tap(function ($browser) {
                        $browser->driver->executeScript("window.scrollTo(0, document.body.scrollHeight)");
                    })
                    ->waitFor('input[name="parameters[3][name]"]')
                    ->type('parameters[3][name]', 'Lens type')
                    ->select('parameters[3][type]', 'select')
                    ->check('parameters[3][is_required]')
                    ->pause(1000) // wait select Change trigger display
                    ->waitFor('#option-input-3')
                    ->type('#option-input-3', 'Single light')
                    ->click('#option-input-3 + button')
                    ->type('#option-input-3', 'Advance')
                    ->click('#option-input-3 + button')
                    ->type('#option-input-3', 'Dual light')
                    ->click('#option-input-3 + button')
                    ->tap(function ($browser) {
                        $browser->driver->executeScript("window.scrollTo(0, document.body.scrollHeight)");
                    })
                    ->press('keep')
                    ->waitForText('The creation of product classification is successful!')
                    ->assertPathIs('/product-categories')
                    ->waitForText('Product Classification List')
                    // Create test classification
                    ->visit('/product-categories/create')
                    ->waitForText('Add product classification')
                    ->type('name', 'Test classification')
                    ->type('description', 'This is a classification for testing')
                    ->check('is_active')
                    ->press('keep')
                    ->waitForText('The creation of product classification is successful!')
                    ->assertPathIs('/product-categories');
        });
    }

    /**
     * Test and search for product classification
     */
    public function test_view_and_search_product_categories(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/product-categories')
                    ->waitForText('Product Classification List')
                    ->assertSee('Product Classification List')
                    // Verify the classification of just created
                    ->waitFor('.bg-white.divide-y.divide-gray-200')
                    ->assertSee('lens')
                    ->assertSee('Various types of glasses lenses, including single light,Progressive')
                    // Test search function
                    ->waitFor('#search')
                    ->type('search', 'lens')
                    ->press('search')
                    ->waitUntilMissing('.opacity-75')
                    ->assertSee('lens')
                    // Test screening function
                    ->waitFor('#type')
                    ->select('type', 'lens')
                    ->press('search')
                    ->waitUntilMissing('.opacity-75')
                    ->assertSee('lens')
                    ->waitFor('#status')
                    ->select('status', '1')
                    ->press('search')
                    ->waitUntilMissing('.opacity-75')
                    ->assertSee('lens');
        });
    }

    /**
     * Test Edit Product Classification
     */
    public function test_edit_product_category(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/product-categories')
                    ->waitForText('Product Classification List')
                    ->assertSee('lens')
                    ->waitFor('a[href*="edit"]')
                    ->click('a[href*="edit"]')
                    ->waitForText('Edit Product Classification')
                    ->waitFor('input[name="name"]')
                    ->type('name', 'Lens')
                    ->type('description', 'Updated lens classification description')
                    ->check('is_active')
                    ->press('keep')
                    ->waitForText('Product classification updates successfully!')
                    ->assertPathIs('/product-categories')
                    ->waitFor('.bg-white.divide-y.divide-gray-200')
                    ->assertSee('Lens')
                    ->assertSee('Updated lens classification description');
        });
    }

    /**
     * Test delete product classification
     */
    public function test_delete_product_category(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/product-categories')
                    ->waitForText('Product Classification List')
                    // Search Test Classification
                    ->waitFor('#search')
                    ->type('search', 'Test classification')
                    ->press('search')
                    ->waitUntilMissing('.opacity-75')
                    ->assertSee('Test classification')
                    ->waitFor('.text-red-600')
                    ->click('.text-red-600')
                    ->waitForDialog()
                    ->acceptDialog()
                    ->waitForText('The deletion of product classification is successful!')
                    ->assertDontSee('Test classification')
                    // Verification lens classification is still
                    ->type('search', ' ') // Empty search
                    ->waitUntilMissing('.opacity-75')
                    ->assertSee('lens');
        });
    }
} 
