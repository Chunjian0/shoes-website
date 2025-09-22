<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestSettingsController extends Controller
{
    /**
     * 显示所有设置
     */
    public function index()
    {
        $settings = DB::table('settings')
            ->orderBy('group')
            ->orderBy('key')
            ->get();
        
        return response()->json([
            'success' => true,
            'settings' => $settings
        ]);
    }
    
    /**
     * 显示特定组的设置
     */
    public function getGroup($group)
    {
        $settings = DB::table('settings')
            ->where('group', $group)
            ->orderBy('key')
            ->get()
            ->keyBy('key')
            ->map(function($item) {
                return $item->value;
            })
            ->toArray();
        
        return response()->json([
            'success' => true,
            'group' => $group,
            'settings' => $settings
        ]);
    }
    
    /**
     * 保存测试设置
     */
    public function saveTest(Request $request)
    {
        try {
            $key = $request->input('key');
            $value = $request->input('value');
            $group = $request->input('group', 'test');
            
            if (!$key) {
                return response()->json([
                    'success' => false,
                    'message' => 'Key is required'
                ], 400);
            }
            
            // 记录原始值
            Log::info('Saving test setting', [
                'key' => $key,
                'value' => $value,
                'value_type' => gettype($value),
                'group' => $group
            ]);
            
            // 检查设置是否已存在
            $existingSetting = DB::table('settings')
                ->where('group', $group)
                ->where('key', $key)
                ->first();
            
            if ($existingSetting) {
                DB::table('settings')
                    ->where('group', $group)
                    ->where('key', $key)
                    ->update([
                        'value' => $value,
                        'updated_at' => now()
                    ]);
            } else {
                DB::table('settings')->insert([
                    'group' => $group,
                    'key' => $key,
                    'value' => $value,
                    'type' => 'string',
                    'label' => ucwords(str_replace('_', ' ', $key)),
                    'description' => 'Test setting for ' . $key,
                    'options' => null,
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            // 读取设置验证是否正确保存
            $updatedSetting = DB::table('settings')
                ->where('group', $group)
                ->where('key', $key)
                ->first();
            
            return response()->json([
                'success' => true,
                'message' => 'Setting saved successfully',
                'setting' => $updatedSetting
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save test setting: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save setting: ' . $e->getMessage()
            ], 500);
        }
    }
} 