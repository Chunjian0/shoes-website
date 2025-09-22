<?php

namespace App\Observers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SettingObserver
{
    // Define the keys that affect the homepage settings API cache
    private $homepageSettingKeys = [
        'show_promotion', 'site_title', 'site_description', 'layout', 'show_brands',
        'featured_products_title', 'featured_products_subtitle', 'featured_products_button_text', 'featured_products_button_link',
        'new_products_title', 'new_products_subtitle', 'new_products_button_text', 'new_products_button_link',
        'sale_products_title', 'sale_products_subtitle', 'sale_products_button_text', 'sale_products_button_link',
        // Add other relevant keys from getHomepageSettings if needed
    ];

    /**
     * Handle the Setting "created" event.
     */
    public function created(Setting $setting): void
    {
        //
    }

    /**
     * Handle the Setting "updated" event.
     */
    public function updated(Setting $setting): void
    {
        //
    }

    /**
     * Handle the Setting "saved" event.
     */
    public function saved(Setting $setting): void
    {
        $this->clearRelevantCache('saved', $setting);
    }

    /**
     * Handle the Setting "deleted" event.
     */
    public function deleted(Setting $setting): void
    {
        $this->clearRelevantCache('deleted', $setting);
    }

    /**
     * Handle the Setting "restored" event.
     */
    public function restored(Setting $setting): void
    {
        $this->clearRelevantCache('restored', $setting);
    }

    /**
     * Handle the Setting "force deleted" event.
     */
    public function forceDeleted(Setting $setting): void
    {
        $this->clearRelevantCache('forceDeleted', $setting);
    }
    
    /**
     * Clear relevant cache keys based on the setting key.
     */
    private function clearRelevantCache(string $event, Setting $model): void
    {
        Log::info('[Observer Setting] ' . $event . ' event triggered for key: ' . $model->key, ['id' => $model->id]);
        
        // Clear the specific homepage settings cache if the key matches
        if (in_array($model->key, $this->homepageSettingKeys)) {
            Log::info('[Observer Setting] Clearing homepage settings cache (homepage_settings_api).');
            Cache::forget('homepage_settings_api');
            // Also clear the main data cache as it contains settings
            Log::info('[Observer Setting] Clearing main homepage data cache (homepage_api_data).');
            Cache::forget('homepage_api_data'); 
        }
        
        // Add logic here to clear other caches if this setting affects them
        // e.g., if (str_starts_with($model->key, 'product_list_')) { Cache::forget(...); }
    }
}
