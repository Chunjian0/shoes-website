<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class SettingsService
{
    /**
     * 获取多个设置项的值
     *
     * @param array $keys 设置项的键名数组
     * @return array 设置项的键值对数组
     */
    public function getSettings(array $keys): array
    {
        try {
            $settings = Setting::whereIn('key', $keys)
                ->get()
                ->pluck('value', 'key')
                ->toArray();
            
            return $settings;
        } catch (Exception $e) {
            Log::error('获取设置失败: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * 获取单个设置项的值
     *
     * @param string $key 设置项的键名
     * @param mixed $default 默认值
     * @return mixed 设置项的值或默认值
     */
    public function getSetting(string $key, $default = null)
    {
        try {
            // 先从缓存中获取
            if (Cache::has('setting:'.$key)) {
                return Cache::get('setting:'.$key);
            }
            
            // 缓存中不存在，从数据库获取
            $setting = Setting::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }
            
            // 将结果存入缓存，有效期1小时
            Cache::put('setting:'.$key, $setting->value, 3600);
            
            return $setting->value;
        } catch (Exception $e) {
            Log::error('获取设置项失败: ' . $e->getMessage());
            return $default;
        }
    }
    
    /**
     * 更新设置项的值
     *
     * @param string $key 设置项的键名
     * @param mixed $value 设置项的新值
     * @param string $group 设置组，默认为'system'
     * @return bool 是否更新成功
     */
    public function updateSetting(string $key, $value, string $group = 'system'): bool
    {
        try {
            // 更新或创建设置
            Setting::updateOrCreate(
                ['key' => $key], 
                [
                    'value' => is_bool($value) ? ($value ? 'true' : 'false') : $value,
                    'group' => $group
                ]
            );
            
            // 清除缓存
            Cache::forget('setting:'.$key);
            
            return true;
        } catch (Exception $e) {
            Log::error('更新设置项失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 批量更新设置项
     *
     * @param array $settings 设置项的键值对数组
     * @param string $group 设置组，默认为'system'
     * @return bool 是否全部更新成功
     */
    public function updateSettings(array $settings, string $group = 'system'): bool
    {
        try {
            foreach ($settings as $key => $value) {
                $this->updateSetting($key, $value, $group);
            }
            
            return true;
        } catch (Exception $e) {
            Log::error('批量更新设置失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 删除设置项
     *
     * @param string $key 设置项的键名
     * @return bool 是否删除成功
     */
    public function deleteSetting(string $key): bool
    {
        try {
            Setting::where('key', $key)->delete();
            
            // 清除缓存
            Cache::forget('setting:'.$key);
            
            return true;
        } catch (Exception $e) {
            Log::error('删除设置项失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 检查设置项是否存在
     *
     * @param string $key 设置项的键名
     * @return bool 是否存在
     */
    public function hasSetting(string $key): bool
    {
        return Setting::where('key', $key)->exists();
    }
    
    /**
     * 检查设置项是否为true
     * 
     * @param string $key 设置项的键名
     * @param bool $default 默认值
     * @return bool 设置值是否为true
     */
    public function isEnabled(string $key, bool $default = false): bool
    {
        $value = $this->getSetting($key);
        
        if ($value === null) {
            return $default;
        }
        
        return in_array(strtolower($value), ['true', '1', 'yes', 'on'], true);
    }
    
    /**
     * 获取自动采购设置
     * 
     * @return array 自动采购设置的键值对数组
     */
    public function getAutoPurchaseSettings(): array
    {
        try {
            $settings = Setting::where('key', 'like', 'auto_purchase_%')
                ->get()
                ->pluck('value', 'key')
                ->toArray();
            
            return $settings;
        } catch (Exception $e) {
            Log::error('获取自动采购设置失败: ' . $e->getMessage());
            return [];
        }
    }
} 