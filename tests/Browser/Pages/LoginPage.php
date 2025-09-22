<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;
use App\Models\User;

class LoginPage extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('login', [], false);
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url());
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@email' => 'input[name="email"]',
            '@password' => 'input[name="password"]',
            '@login-button' => 'button[type="submit"]',
        ];
    }

    /**
     * Log in as an administrator
     */
    public function loginAsAdmin(Browser $browser): void
    {
        // Create an administrator user(If it does not exist)
        $admin = User::firstOrCreate(
            ['email' => 'ethankhoo09@gmail.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'employee_id' => 'EMP001',
                'is_active' => true,
            ]
        );

        // Ensure that users havesuper-adminRole
        if (!$admin->hasRole('super-admin')) {
            $admin->assignRole('super-admin');
        }

        // Use directly loginAs Method Login
        $browser->loginAs($admin);
    }

    /**
     * Log in as a specified user
     */
    public function loginAsUser(Browser $browser, User $user): void
    {
        $browser->loginAs($user);
    }
} 