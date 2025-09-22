<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "saved" event.
     * Triggered when a product (potentially linked to a template) is updated.
     */
    public function saved(Product $product): void
    {
        // Check if this product is part of any homepage template before clearing cache
        // This check depends on how products are linked/identified in templates.
        // For now, assume any product change *could* affect the homepage.
        $this->clearHomepageCache('saved', $product);
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        $this->clearHomepageCache('deleted', $product);
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        $this->clearHomepageCache('restored', $product);
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        $this->clearHomepageCache('forceDeleted', $product);
    }
    
    /**
     * Clear relevant homepage cache keys.
     */
    private function clearHomepageCache(string $event, Product $model): void
    {
        // We clear the main homepage data cache as product changes might affect
        // the linked_products within the featured/new/sale templates.
        Log::info('[Observer Product] ' . $event . ' event triggered. Clearing homepage cache.', ['id' => $model->id]);
        Cache::forget('homepage_api_data'); 
    }
}
