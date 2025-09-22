<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class NotificationSettingService
{
    private const CACHE_KEY_PREFIX = 'notification_settings:';
    private const CACHE_TTL = 3600; // 1Hourly cache

    /**
     * Get all notification types
     *
     * @return array<string, string>
     */
    public function getNotificationTypes(): array
    {
        return [
            'product_created' => 'Product Created',
            'product_updated' => 'Product Updated',
            'product_deleted' => 'Product Deleted',
            'quality_inspection_created' => 'Quality Inspection Created',
            'quality_inspection_updated' => 'Quality Inspection Status Updated',
            'purchase_created' => 'Purchase Order Created',
            'purchase_status_changed' => 'Purchase Order Status Updated',
            'inventory_alert' => 'Inventory Alert',
            'payment_status_changed' => 'Payment Status Updated',
            'system_alert' => 'System Alert',
            'purchase_order_generated' => 'Auto Purchase Order Generated',
            'payment_overdue' => 'Payment Overdue Reminder',
            'supplier_order_notification' => 'Supplier Order Notification',
            'low_stock_removal' => 'Low Stock Products Removed From Homepage',
            'test_mail' => 'Test Mail'
        ];
    }

    /**
     * Get notification settings
     */
    public function getSettings(): array
    {
        try {
            return Cache::remember(self::CACHE_KEY_PREFIX . 'all', self::CACHE_TTL, function () {
                $settings = Setting::where('group', 'notification')->get();
                $result = [];

                foreach ($settings as $setting) {
                    $result[$setting->key] = $this->parseSettingValue($setting);
                }

                return $result;
            });
        } catch (\Exception $e) {
            Log::error('Failed to get notification settings', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get the notification recipient of the specified type
     */
    public function getReceivers(string $type): array
    {
        try {
            return Cache::remember(
                self::CACHE_KEY_PREFIX . 'receivers:' . $type,
                self::CACHE_TTL,
                function () use ($type) {
                    $settings = Setting::where('key', 'notification_receivers')->first();
                    if (!$settings) {
                        return [];
                    }

                    $receivers = json_decode($settings->value, true);
                    return $receivers[$type] ?? [];
                }
            );
        } catch (\Exception $e) {
            Log::error('Failed to get notification recipient', [
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Update notification recipient settings
     */
    public function updateReceivers(array $receivers): bool
    {
        try {
            $setting = Setting::updateOrCreate(
                ['key' => 'notification_receivers'],
                [
                    'value' => json_encode($receivers),
                    'type' => 'json',
                    'group' => 'notification',
                    'label' => 'Notify the recipient',
                    'description' => 'Settings of recipients of various notifications in the system'
                ]
            );

            // Clear cache
            $this->clearReceiversCache();

            Log::info('Notify the recipient that the settings have been updated', [
                'setting_id' => $setting->id,
                'receivers' => $receivers
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Update notification recipient settings failed', [
                'error' => $e->getMessage(),
                'receivers' => $receivers
            ]);
            return false;
        }
    }

    /**
     * Update notification method settings
     */
    public function updateNotificationMethod(string $method, bool $enabled): bool
    {
        try {
            if (!in_array($method, ['email'])) {
                throw new \InvalidArgumentException('Invalid notification method');
            }

            $key = "{$method}_notifications_enabled";
            
            $setting = Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $enabled ? 'true' : 'false',
                    'type' => 'boolean',
                    'group' => 'notification',
                    'label' => "Enable{$method}notify",
                    'description' => "Whether to enable{$method}Notification function"
                ]
            );

            // Clear cache
            Cache::forget(self::CACHE_KEY_PREFIX . $key);

            Log::info('Notification method settings have been updated', [
                'method' => $method,
                'enabled' => $enabled
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Update notification method setting failed', [
                'method' => $method,
                'enabled' => $enabled,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check whether the notification method is enabled
     */
    public function isMethodEnabled(string $method, bool $forceEnable = false): bool
    {
        // If forceEnable is true, bypass the check and return true
        if ($forceEnable) {
            return true;
        }
        
        try {
            return Cache::remember(
                self::CACHE_KEY_PREFIX . "{$method}_notifications_enabled",
                self::CACHE_TTL,
                function () use ($method) {
                    // 检查两种可能的键名
                    $settingPlural = Setting::where('key', "{$method}_notifications_enabled")->first();
                    $settingSingular = Setting::where('key', "{$method}_notification_enabled")->first();
                    
                    // 如果任一设置为true，则返回true
                    if ($settingPlural && $settingPlural->value === 'true') {
                        return true;
                    }
                    
                    if ($settingSingular && $settingSingular->value === 'true') {
                        return true;
                    }
                    
                    return false;
                }
            );
        } catch (\Exception $e) {
            Log::error('Failed to check notification mode status', [
                'method' => $method,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get an optional list of notification recipients
     */
    public function getAvailableReceivers(): \Illuminate\Database\Eloquent\Collection
    {
        try {
            $users = Cache::remember(
                self::CACHE_KEY_PREFIX . 'available_receivers',
                self::CACHE_TTL,
                function () {
                    return User::select('id', 'name', 'email')
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get()
                        ->toArray();
                }
            );

            // Convert cached array data backCollection
            return new \Illuminate\Database\Eloquent\Collection(
                array_map(function ($userData) {
                    return new User($userData);
                }, $users)
            );
        } catch (\Exception $e) {
            Log::error('Failed to get available notification recipient', [
                'error' => $e->getMessage()
            ]);
            return new \Illuminate\Database\Eloquent\Collection();
        }
    }

    /**
     * Clear all notification settings related caches
     */
    private function clearReceiversCache(): void
    {
        try {
            $types = array_keys($this->getNotificationTypes());
            foreach ($types as $type) {
                Cache::forget(self::CACHE_KEY_PREFIX . 'receivers:' . $type);
            }
            Cache::forget(self::CACHE_KEY_PREFIX . 'all');
        } catch (\Exception $e) {
            Log::error('Failed to clear notification settings cache', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Resolve the setting value
     */
    private function parseSettingValue(Setting $setting): mixed
    {
        return match($setting->type) {
            'boolean' => $setting->value === 'true',
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }
} 