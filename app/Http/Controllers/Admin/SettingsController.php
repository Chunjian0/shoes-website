<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    /**
     * 显示设置页面
     */
    public function index()
    {
        // 获取所有设置
        $allSettings = Setting::all();
        $settings = [];
        
        // 将设置转换为键值对
        foreach ($allSettings as $setting) {
            $settings[$setting->key] = $setting->value;
        }
        
        return view('admin.settings.index', compact('settings'));
    }
    
    /**
     * 更新设置
     */
    public function update(Request $request)
    {
        try {
            $settingsData = $request->input('settings', []);
            
            foreach ($settingsData as $key => $value) {
                // 获取现有设置
                $setting = Setting::where('key', $key)->first();
                
                if ($setting) {
                    // 更新现有设置
                    $setting->value = $value;
                    $setting->save();
                } else {
                    // 创建新设置
                    Setting::create([
                        'key' => $key,
                        'value' => $value,
                        'group' => $this->getGroupByKey($key)
                    ]);
                }
            }
            
            return redirect()->route('admin.settings.index')
                ->with('success', '设置已成功更新');
        } catch (\Exception $e) {
            Log::error('更新设置失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.settings.index')
                ->with('error', '设置更新失败：' . $e->getMessage());
        }
    }
    
    /**
     * 根据键名确定设置组
     */
    private function getGroupByKey($key)
    {
        $prefixes = [
            'company_' => 'company',
            'homepage_' => 'homepage',
            'email_' => 'email',
            'auto_' => 'homepage',
            'new_products_' => 'homepage'
        ];
        
        foreach ($prefixes as $prefix => $group) {
            if (strpos($key, $prefix) === 0) {
                return $group;
            }
        }
        
        return 'general';
    }
} 