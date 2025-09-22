<?php

namespace App\Observers;

use App\Models\Banner;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BannerObserver
{
    /**
     * Handle the Banner "created" event.
     */
    public function created(Banner $banner): void
    {
        //
    }

    /**
     * Handle the Banner "updated" event.
     */
    public function updated(Banner $banner): void
    {
        //
    }

    /**
     * Handle the Banner "saved" event.
     */
    public function saved(Banner $banner): void
    {
        $this->clearHomepageCache('saved', $banner);
    }

    /**
     * Handle the Banner "deleted" event.
     */
    public function deleted(Banner $banner): void
    {
        $this->clearHomepageCache('deleted', $banner);
    }

    /**
     * Handle the Banner "restored" event.
     */
    public function restored(Banner $banner): void
    {
        $this->clearHomepageCache('restored', $banner);
    }

    /**
     * Handle the Banner "force deleted" event.
     */
    public function forceDeleted(Banner $banner): void
    {
        $this->clearHomepageCache('forceDeleted', $banner);
    }
    
    /**
     * Clear relevant homepage cache keys.
     */
    private function clearHomepageCache(string $event, Banner $model): void
    {
        Log::info('[Observer Banner] ' . $event . ' event triggered. Clearing homepage cache.', ['id' => $model->id]);
        // Clear the main data cache as banners are part of it
        Cache::forget('homepage_api_data');
        // Banners don't affect settings cache
    }
}
