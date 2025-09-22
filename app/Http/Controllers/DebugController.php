<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DebugController extends Controller
{
    /**
     * 显示测试上传页面
     */
    public function testUpload()
    {
        return view('debug.test-upload');
    }
    
    /**
     * 处理测试上传请求
     */
    public function handleUpload(Request $request)
    {
        try {
            Log::info('Debug upload request received', [
                'has_file' => $request->hasFile('file'),
                'all_data' => $request->all(),
                'headers' => $request->header(),
            ]);
            
            if (!$request->hasFile('file')) {
                return response()->json([
                    'success' => false,
                    'message' => '没有找到上传的文件'
                ], 400);
            }
            
            $file = $request->file('file');
            
            // 记录文件信息
            Log::info('Debug file information', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'extension' => $file->getClientOriginalExtension(),
            ]);
            
            // 保存文件
            $path = 'debug/uploads';
            $fileName = Str::random(40) . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs($path, $fileName, 'public');
            
            Log::info('Debug file saved', [
                'file_path' => $filePath,
                'full_url' => asset('storage/' . $filePath),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => '文件上传成功',
                'id' => Str::random(10),
                'url' => asset('storage/' . $filePath),
                'name' => $file->getClientOriginalName(),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Debug upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => '上传失败: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    
    /**
     * 检查CSRF令牌
     */
    public function checkCsrf(Request $request)
    {
        return response()->json([
            'csrf_token' => csrf_token(),
            'session_token' => $request->session()->token(),
            'header_token' => $request->header('X-CSRF-TOKEN'),
            'request_data' => $request->all(),
        ]);
    }
} 