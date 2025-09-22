<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Customer;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Pages\LoginPage;
use Illuminate\Support\Facades\Artisan;

class A0001CustomerTest extends DuskTestCase
{
    /**
     * Test to create a store
     */
    public function test_create_store(): void
    {
        Artisan::call('migrate:refresh');
        Artisan::call('db:seed');

        $this->browse(function (Browser $browser) {
            $browser->visit(new LoginPage)
                    ->loginAsAdmin()
                    ->visit('/customers/create')
                    ->waitForText('Create a store')
                    ->assertSee('Create a store')
                    ->type('code', 'STORE001')
                    ->type('name', 'Test Store')
                    ->type('address', 'Test Store Address')
                    ->type('contact_person', 'Store Manager')
                    ->type('contact_phone', '0123456789')
                    ->press('Create a store')
                    ->assertPathIs('/dashboard');
        });
    }

    /**
     * Test Creation Customer
     */
    public function test_create_customer(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/customers/create')
                    ->waitForText('New customer')
                    ->assertSee('New customer')
                    ->type('name', 'Zhang San')
                    ->type('ic_number', '123456789012')
                    ->type('contact_number', '0123456789')
                    ->type('email', 'zhangsan@example.com')
                    ->type('birthday', '01011990')
                    ->select('member_level', 'normal')
                    
                    ->type('notes', 'Test remark')
                    ->press('keep')
                    ->assertPathIs('/customers')
                    ->assertSee('Customer creation is successful!');
        });
        $this->browse(function (Browser $browser) {
            $browser->visit('/customers/create')
                    ->waitForText('New customer')
                    ->assertSee('New customer')
                    ->type('name', 'Li Si')
                    ->type('ic_number', '098765431210')
                    ->type('contact_number', '0987654321')
                    ->type('email', 'lisi@example.com')
                    ->type('birthday', '111991212')
                    ->select('member_level', 'gold')
                    
                    ->type('notes', 'Test remark')
                    ->press('keep')
                    ->waitForText('Customer creation is successful!')
                    ->assertPathIs('/customers')
                    ->assertSee('Customer creation is successful!');
        });
    }

    /**
     * Test the customer list and search function
     */
    public function test_view_and_search_customers(): void
    {

        $this->browse(function (Browser $browser)  {
            $browser->visit('/customers')
                    ->assertSee('Customer management')
                    ->waitForText('Zhang San',10000)
                    ->assertSee('Li Si')
                    // Test search function
                    ->type('#search', 'Zhang San')
                    ->keys('#search', '{enter}')
                    ->assertSee('Zhang San')
                    ->assertDontSee('Li Si')
                    // Test member level screening
                    ->type('#search', ' ')
                    ->select('member_level', 'gold')
                    ->keys('#search', '{enter}')
                    ->assertDontSee('Zhang San')
                    ->assertSee('Li Si') 
                    // Clear and screening
                    ->select('member_level', '')
                    ->type('#search', ' ')
                    ->keys('#search', '{enter}')
                    ->waitForText('Zhang San',10000)
                    ->assertSee('Li Si');
        });
    }

    /**
     * Test edit customer
     */
    public function test_edit_customer(): void
    {
        $customer = Customer::create([
            'name' => 'King five',
            'ic_number' => '456789123456',
            'contact_number' => '4567891234',
            'member_level' => 'normal',
            'address' => 'Original address',
            'store_id' => 1
        ]);

        $this->browse(function (Browser $browser) use ($customer) {
            $browser->visit('/customers/' . $customer->id . '/edit')
                    ->waitForText('Edit Customer')
                    ->assertSee('Edit Customer')
                    ->type('ic_number', '123456789010')
                    ->select('member_level', 'gold')
                    ->press('keep')
                    ->assertPathIs('/customers')
                    ->assertSee('Customer information updated successfully!');
        });
    }

    /**
     * Test delete customers
     */
    public function test_delete_customer(): void
    {
        $customer = Customer::create([
            'name' => 'Zhao Liu',
            'ic_number' => '789123456789',
            'contact_number' => '7891234567',
            'member_level' => 'normal',
            'store_id' => 1
        ]);

        // Record the number of initial customers
        $initialCount = Customer::count();

        $this->browse(function (Browser $browser) use ($customer) {
            $browser->visit('/customers')
                    ->assertSee('Zhao Liu')
                    ->click('.text-red-600')  // Click to delete button
                    ->waitForDialog()  // Waiting for the browser's confirmation dialog box
                    ->acceptDialog()  // Confirm delete
                    ->waitForText('Customer deletion successfully!')
                    ->assertPathIs('/customers');
                    
        });

        // Verify the number of customers in the database decreased1
        $this->assertEquals($initialCount - 1, Customer::count());
    }
}
