<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

class F0001WarehouseTest extends DuskTestCase
{
    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        // Create a test user and give authorization permissions
        $this->user = User::factory()->create([
            'email' => 'warehouse_test_' . uniqid() . '@example.com',
            'employee_id' => 'EMP' . uniqid()
        ]);
        $this->user->assignRole('super-admin');
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Test the warehouse (for procurement test)
     */
    public function test_create_warehouse_for_purchase(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/warehouses/create')
                    ->waitForText('New warehouse')
                    ->type('code', 'WH-TEST')
                    ->type('name', 'Test warehouse')
                    ->type('address', 'Test address')
                    ->type('contact_person', 'Test contact')
                    ->type('contact_phone', '13800138000')
                    ->select('status', '1')
                    ->type('notes', 'Warehouse for procurement testing')
                    ->press('keep')
                    ->waitForText('Warehouse creation successfully');
        });
    }

    /**
     * Test the creation warehouse (for deleting test)
     */
    public function test_create_warehouse_for_delete(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/warehouses/create')
                    ->waitForText('New warehouse')
                    ->type('code', 'WH-DEL-' . uniqid())
                    ->type('name', 'To be deleted warehouse')
                    ->type('address', 'Huaxia Road, Zhujiang New City, Tianhe District, Guangzhou10Number')
                    ->type('contact_person', 'Zhang San')
                    ->type('contact_phone', '020-12345678')
                    ->select('status', '1')
                    ->type('notes', 'Warehouse for testing and deleting functions')
                    ->press('keep')
                    ->waitForText('Warehouse creation successfully');
        });
    }

    /**
     * Test the warehouse list
     */
    public function test_view_warehouses(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/warehouses')
                    ->waitForText('Warehouse management')
                    ->assertSee('Warehouse management')
                    // Verify the existence of both warehouses
                    ->waitFor('.divide-y.divide-gray-200')
                    ->assertSee('Test warehouse')
                    ->assertSee('To be deleted warehouse');
        });
    }

    /**
     * Test edit warehouse
     */
    public function test_edit_warehouse(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/warehouses')
                    ->waitFor('.divide-y.divide-gray-200')
                    ->assertSee('To be deleted warehouse')
                    ->clickLink('edit')
                    ->waitForText('Edit warehouse')
                    ->type('name', 'To be deleted warehouse-Edited')
                    ->press('keep')
                    ->waitForText('Warehouse update successfully')
                    ->assertSee('To be deleted warehouse-Edited');
        });
    }

    /**
     * Test delete warehouse
     */
    public function test_delete_warehouse(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/warehouses')
                    ->waitFor('.divide-y.divide-gray-200')
                    ->assertSee('To be deleted warehouse-Edited')
                    ->press('delete')
                    ->waitForDialog()
                    ->acceptDialog()
                    ->waitForText('Warehouse deletion successfully')
                    ->refresh()
                    ->waitFor('.divide-y.divide-gray-200')
                    ->assertDontSee('To be deleted warehouse-Edited')
                    // Ensure that the test warehouse still exists
                    ->assertSee('Test warehouse');
        });
    }
} 