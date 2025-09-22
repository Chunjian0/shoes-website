<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Media;

class MediaDebugController extends Controller
{
    /**
     * 测试媒体上传
     */
    public function testUpload(Request $request)
    {
        try {
            // 记录所有请求信息
            Log::channel('daily')->info('MediaDebugController::testUpload 方法被调用', [
                'request_method' => $request->method(),
                'request_url' => $request->fullUrl(),
                'request_ip' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'content_type' => $request->header('Content-Type'),
                'accept' => $request->header('Accept'),
                'csrf_token' => $request->header('X-CSRF-TOKEN'),
                'session_id' => $request->session()->getId(),
                'user_id' => auth()->id(),
                'all_headers' => $request->headers->all(),
                'all_input' => $request->except('file'),
                'has_file' => $request->hasFile('file'),
                'file_info' => $request->hasFile('file') ? [
                    'name' => $request->file('file')->getClientOriginalName(),
                    'size' => $request->file('file')->getSize(),
                    'mime' => $request->file('file')->getMimeType(),
                ] : null,
            ]);
            
            if (!$request->hasFile('file')) {
                return response()->json([
                    'success' => false,
                    'message' => '没有找到上传的文件'
                ], 400);
            }
            
            $file = $request->file('file');
            
            // 保存文件
            $path = 'debug/uploads';
            $fileName = Str::random(40) . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs($path, $fileName, 'public');
            
            Log::channel('daily')->info('文件已保存', [
                'file_path' => $filePath,
                'full_url' => asset('storage/' . $filePath),
            ]);
            
            // 创建媒体记录
            $media = new Media([
                'model_type' => $request->input('model_type', 'App\\Models\\Setting'),
                'collection_name' => $request->input('collection_name', 'debug'),
                'name' => $file->getClientOriginalName(),
                'file_name' => $fileName,
                'mime_type' => $file->getMimeType(),
                'disk' => 'public',
                'path' => $filePath,
                'size' => $file->getSize(),
            ]);
            
            $media->save();
            
            Log::channel('daily')->info('媒体记录已创建', [
                'media_id' => $media->id,
                'media_path' => $media->path,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => '文件上传成功',
                'id' => $media->id,
                'url' => asset('storage/' . $filePath),
                'name' => $media->name,
            ]);
            
        } catch (\Exception $e) {
            Log::channel('daily')->error('文件上传失败', [
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
     * 检查路由和CSRF
     */
    public function checkRouteAndCsrf(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => '路由和CSRF检查成功',
            'csrf_token' => csrf_token(),
            'session_token' => $request->session()->token(),
            'header_token' => $request->header('X-CSRF-TOKEN'),
            'route_info' => [
                'current_route' => $request->route()->getName(),
                'current_action' => $request->route()->getActionName(),
            ],
            'request_info' => [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ],
        ]);
    }
} 