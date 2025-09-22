<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class CustomerPage extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('customers.index', [], false);
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url())
                ->assertSee('Customer Management')
                ->assertSee('Manage customer information,Optometry data and consumption records');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@search-input' => 'input[name="search"]',
            '@member-level-select' => 'select[name="member_level"]',
            '@search-button' => 'button[type="submit"]',
            '@clear-filter' => '[dusk="clear-filter"]',
            '@add-customer' => 'a[href="' . route('customers.create', [], false) . '"]',
        ];
    }

    /**
     * Create a new customer
     */
    public function createCustomer(Browser $browser, array $customerData): void
    {
        $browser->click('@add-customer')
                ->waitForText('Create a new customer')
                ->type('name', $customerData['name'])
                ->type('ic_number', $customerData['ic_number'])
                ->type('contact_number', $customerData['contact_number'])
                ->type('email', $customerData['email'] ?? '')
                ->type('birthday', $customerData['birthday'] ?? '')
                ->select('member_level', $customerData['member_level'] ?? 'normal')
                ->type('address', $customerData['address'] ?? '')
                ->type('notes', $customerData['notes'] ?? '')
                ->press('keep')
                ->waitForText('Customer creation successfully');
    }

    /**
     * Search for customers
     */
    public function searchCustomer(Browser $browser, string $keyword): void
    {
        $browser->type('@search-input', $keyword)
                ->click('@search-button')
                ->pause(500); // Wait for the search results to load
    }

    /**
     * Filter by membership level
     */
    public function filterByMemberLevel(Browser $browser, string $level): void
    {
        $browser->select('@member-level-select', $level)
                ->click('@search-button')
                ->pause(500);
    }

    /**
     * Clear filter criteria
     */
    public function clearFilter(Browser $browser): void
    {
        $browser->click('@clear-filter')
                ->pause(500);
    }
} 