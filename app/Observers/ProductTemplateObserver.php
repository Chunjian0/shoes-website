<?php

namespace App\Observers;

use App\Models\ProductTemplate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProductTemplateObserver
{
    /**
     * Handle the ProductTemplate "created" event.
     */
    public function created(ProductTemplate $productTemplate): void
    {
        //
    }

    /**
     * Handle the ProductTemplate "updated" event.
     */
    public function updated(ProductTemplate $productTemplate): void
    {
        //
    }

    /**
     * Handle the ProductTemplate "saved" event.
     */
    public function saved(ProductTemplate $productTemplate): void
    {
        $this->clearHomepageCache('saved', $productTemplate);
    }

    /**
     * Handle the ProductTemplate "deleted" event.
     */
    public function deleted(ProductTemplate $productTemplate): void
    {
        $this->clearHomepageCache('deleted', $productTemplate);
    }

    /**
     * Handle the ProductTemplate "restored" event.
     */
    public function restored(ProductTemplate $productTemplate): void
    {
        $this->clearHomepageCache('restored', $productTemplate);
    }

    /**
     * Handle the ProductTemplate "force deleted" event.
     */
    public function forceDeleted(ProductTemplate $productTemplate): void
    {
        $this->clearHomepageCache('forceDeleted', $productTemplate);
    }

    /**
     * Clear relevant homepage cache keys.
     */
    private function clearHomepageCache(string $event, ProductTemplate $model): void
    {
        Log::info('[Observer ProductTemplate] ' . $event . ' event triggered. Clearing homepage cache.', ['id' => $model->id]);
        // Clear the main data cache
        Cache::forget('homepage_api_data');
        // Clear the settings cache (might be redundant, but safe)
        Cache::forget('homepage_settings_api');
        // Consider clearing individual template caches if they exist
    }
}
