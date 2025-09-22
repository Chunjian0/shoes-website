<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class MediaController extends Controller
{
    /**
     * Allowed file types
     */
    protected array $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    /**
     * Allowed file extensions
     */
    protected array $allowedExtensions = [
        'jpg', 'jpeg', 'png', 'gif', 'webp'
    ];

    /**
     * Maximum file size (5MB)
     */
    protected int $maxFileSize = 5 * 1024 * 1024;

    /**
     * Upload file
     */
    public function store(Request $request)
    {
        try {
            // 添加详细的调试日志
            Log::channel('daily')->info('MediaController::store 方法被调用', [
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
                'route_name' => $request->route() ? $request->route()->getName() : 'unknown',
            ]);
            
            Log::info('Start processing file upload request', [
                'request_data' => $request->except('file'),
                'has_file' => $request->hasFile('file'),
                'request_ip' => $request->ip(),
                'user_id' => auth()->id(),
            ]);
            
            // Verification request
            $validated = $request->validate([
                'file' => 'required|file|max:5120', // 5MB
                'temp_id' => 'nullable|string',
                'model_id' => 'nullable|integer',
                'model_type' => 'required|string',
                'collection_name' => 'nullable|string',
            ]);

            $file = $request->file('file');
            
            Log::info('File information', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'extension' => $file->getClientOriginalExtension(),
            ]);

            // Verify file type
            if (!in_array($file->getMimeType(), $this->allowedMimeTypes)) {
                Log::warning('File type not allowed', [
                    'mime_type' => $file->getMimeType(),
                    'file_name' => $file->getClientOriginalName(),
                ]);
                return response()->json([
                    'message' => 'Unsupported file types. Only JPG, PNG, GIF and WEBP formats are supported.',
                ], 422);
            }

            // Verify file extension
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, $this->allowedExtensions)) {
                Log::warning('File extension not allowed', [
                    'extension' => $extension,
                    'file_name' => $file->getClientOriginalName(),
                ]);
                return response()->json([
                    'message' => 'Unsupported file extension. Only JPG, PNG, GIF and WEBP formats are supported.',
                ], 422);
            }

            // Verify file size
            if ($file->getSize() > $this->maxFileSize) {
                Log::warning('File size exceeds limit', [
                    'size' => $file->getSize(),
                    'file_name' => $file->getClientOriginalName(),
                ]);
                return response()->json([
                    'message' => 'File size cannot exceed 5MB',
                ], 422);
            }

            // Verify file content (check if it's really an image)
            try {
                // 使用更简单的方式验证图片内容
                $image = Image::make($file->getRealPath());
                $width = $image->width();
                $height = $image->height();
                
                Log::info('Image properties', [
                    'width' => $width,
                    'height' => $height,
                    'mime' => $image->mime(),
                ]);
            } catch (\Exception $e) {
                Log::error('Invalid image content', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(), 
                    'file_name' => $file->getClientOriginalName(),
                ]);
                return response()->json([
                    'message' => 'The uploaded file is not a valid image.',
                    'debug_info' => $e->getMessage()
                ], 422);
            }

            // Determine the storage path
            $path = $this->getStoragePath($validated);
            
            Log::info('Storage path information', [
                'base_path' => $path,
                'full_storage_path' => storage_path('app/public/' . $path),
            ]);

            // Create directory if it doesn't exist
            if (!Storage::disk('public')->exists($path)) {
                Storage::disk('public')->makeDirectory($path);
                Log::info('Created directory', ['path' => $path]);
            }

            // Generate random file names
            $fileName = Str::random(40) . '.' . $extension;

            // 简化图片保存逻辑，直接保存原文件
            $filePath = $path . '/' . $fileName;
            Storage::disk('public')->put($filePath, file_get_contents($file->getRealPath()));
            
            Log::info('File saved', [
                'file_path' => $filePath,
                'exists' => Storage::disk('public')->exists($filePath),
                'full_url' => asset('storage/' . $filePath),
            ]);

            // Ensure storage link exists
            if (!file_exists(public_path('storage'))) {
                Log::warning('Storage symlink missing, attempting to create');
                try {
                    \Illuminate\Support\Facades\Artisan::call('storage:link');
                    Log::info('Storage symlink created successfully');
                } catch (\Exception $e) {
                    Log::error('Failed to create storage symlink', ['error' => $e->getMessage()]);
                }
            }

            // Create media records
            $media = new Media([
                'temp_id' => $validated['temp_id'] ?? null,
                'model_type' => $validated['model_type'],
                'model_id' => $validated['model_id'] ?? null,
                'collection_name' => $validated['collection_name'] ?? 'default',
                'name' => $file->getClientOriginalName(),
                'file_name' => $fileName,
                'mime_type' => $file->getMimeType(),
                'disk' => 'public',
                'path' => $filePath,
                'size' => filesize(storage_path('app/public/' . $filePath)),
            ]);

            $media->save();

            Log::info('File upload successfully', [
                'media_id' => $media->id,
                'file_name' => $media->name,
                'path' => $media->path,
                'url' => asset('storage/' . $filePath),
            ]);

            return response()->json([
                'id' => $media->id,
                'url' => asset('storage/' . $filePath),
                'name' => $media->name,
            ]);

        } catch (\Exception $e) {
            Log::error('File upload failed', [
                'error' => $e->getMessage(),
                'file' => $request->file('file')?->getClientOriginalName(),
            ]);

            return response()->json([
                'message' => 'File upload failed',
            ], 500);
        }
    }

    /**
     * Associate temporary files to model
     */
    public function associate(Request $request)
    {
        try {
            $validated = $request->validate([
                'temp_id' => 'required|string',
                'model_type' => 'required|string',
                'model_id' => 'required|integer',
            ]);

            // Find temporary files
            $media = Media::where('temp_id', $validated['temp_id'])
                ->where('model_type', $validated['model_type'])
                ->whereNull('model_id')
                ->get();

            if ($media->isEmpty()) {
                Log::info('No temporary files found to associate', [
                    'temp_id' => $validated['temp_id'],
                    'model_type' => $validated['model_type'],
                ]);
                return response()->json([
                    'message' => 'No file to be associated was found',
                ], 404);
            }

            // 计算目标目录
            $modelType = str_replace('\\', '', $validated['model_type']);
            $modelType = Str::snake($modelType);
            
            $targetPath = '';
            // 检查是否为产品模板类型
            if (strpos($validated['model_type'], 'ProductTemplate') !== false) {
                $targetPath = 'product_templates/' . $validated['model_id'];
            } else {
                $targetPath = $modelType . 's/' . $validated['model_id'];
            }
            
            // 确保目标目录存在
            if (!Storage::disk('public')->exists($targetPath)) {
                Storage::disk('public')->makeDirectory($targetPath);
            }
            
            if (!Storage::disk('public')->exists($targetPath . '/images')) {
                Storage::disk('public')->makeDirectory($targetPath . '/images');
            }

            // Move the file to a new location
            foreach ($media as $item) {
                $sourcePath = $item->path;
                
                // 确保使用的是正确的路径替换方式
                $newPath = '';
                
                if (strpos($sourcePath, 'temp/' . $validated['temp_id']) !== false) {
                    // 使用str_replace处理标准替换
                    $newPath = str_replace('temp/' . $validated['temp_id'], $targetPath, $sourcePath);
                } else {
                    // 如果找不到预期的模式，尝试直接构建新路径
                    $fileName = basename($sourcePath);
                    $newPath = $targetPath . '/images/' . $fileName;
                }
                
                if (Storage::disk($item->disk)->exists($item->path)) {
                    // 确保目标目录存在
                    $targetDir = dirname($newPath);
                    if (!Storage::disk($item->disk)->exists($targetDir)) {
                        Storage::disk($item->disk)->makeDirectory($targetDir);
                    }
                    
                    // 使用copy而不是move，避免权限问题
                    if (Storage::disk($item->disk)->copy($item->path, $newPath)) {
                        Storage::disk($item->disk)->delete($item->path);
                    }
                    
                    // Update records
                    $item->update([
                        'model_id' => $validated['model_id'],
                        'temp_id' => null,
                        'path' => $newPath,
                    ]);
                } else {
                    Log::warning('Source file does not exist', [
                        'path' => $item->path,
                        'media_id' => $item->id,
                    ]);
                }
            }

            return response()->json([
                'message' => 'File association was successful',
                'count' => $media->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('File association failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'temp_id' => $request->input('temp_id'),
            ]);

            return response()->json([
                'message' => 'File association failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete media files
     */
    public function destroy(Media $media)
    {
        try {
            // 记录删除操作开始
            Log::info('开始删除媒体文件', [
                'media_id' => $media->id,
                'path' => $media->path,
                'model_type' => $media->model_type,
                'model_id' => $media->model_id,
                'user_id' => auth()->id(),
            ]);

            $filePath = $media->path;
            $fullStoragePath = storage_path('app/public/' . $filePath);
            $fullPublicPath = public_path('storage/' . $filePath);
            
            // Check if the file exists before attempting to delete
            $storageExists = Storage::disk('public')->exists($filePath);
            $fileExists = file_exists($fullStoragePath);
            $publicExists = file_exists($fullPublicPath);
            
            Log::info('文件存在性检查', [
                'storage_exists' => $storageExists,
                'file_exists' => $fileExists,
                'public_exists' => $publicExists,
                'full_storage_path' => $fullStoragePath,
                'full_public_path' => $fullPublicPath,
            ]);

            // 尝试从各种位置删除文件
            $errors = [];
            
            // 从storage删除
            try {
                if ($storageExists) {
                    Storage::disk('public')->delete($filePath);
                    Log::info('从storage disk成功删除文件', ['path' => $filePath]);
                } else {
                    Log::warning('storage disk中找不到文件', ['path' => $filePath]);
                }
            } catch (\Exception $e) {
                $errors[] = 'Storage删除错误: ' . $e->getMessage();
                Log::error('Storage删除错误', ['error' => $e->getMessage()]);
            }
            
            // 从文件系统删除
            try {
                if ($fileExists) {
                    unlink($fullStoragePath);
                    Log::info('从文件系统成功删除文件', ['path' => $fullStoragePath]);
                }
            } catch (\Exception $e) {
                $errors[] = 'Filesystem删除错误: ' . $e->getMessage();
                Log::error('Filesystem删除错误', ['error' => $e->getMessage()]);
            }
            
            // 从public路径删除
            try {
                if ($publicExists) {
                    unlink($fullPublicPath);
                    Log::info('从public路径成功删除文件', ['path' => $fullPublicPath]);
                }
            } catch (\Exception $e) {
                $errors[] = 'Public路径删除错误: ' . $e->getMessage();
                Log::error('Public路径删除错误', ['error' => $e->getMessage()]);
            }

            // 即使文件删除出错，我们仍然删除数据库记录
            try {
                // 删除数据库记录
                $media->delete();
                Log::info('成功删除媒体记录', ['media_id' => $media->id]);
            } catch (\Exception $e) {
                $errors[] = '数据库记录删除错误: ' . $e->getMessage();
                Log::error('数据库记录删除错误', ['error' => $e->getMessage()]);
                throw $e; // 如果数据库删除失败，这是严重错误，需要抛出
            }
            
            // 如果有错误但仍然成功删除了数据库记录，返回警告信息
            if (!empty($errors)) {
                return response()->json([
                    'message' => '数据库记录已删除，但文件删除过程中有警告',
                    'warnings' => $errors
                ], 200);
            }

            return response()->json([
                'message' => '媒体文件删除成功'
            ]);

        } catch (\Exception $e) {
            Log::error('删除媒体文件失败', [
                'media_id' => $media->id ?? '未知',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => '删除媒体文件错误: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the storage path
     */
    protected function getStoragePath(array $validated): string
    {
        // Remove backslashes from namespace
        $modelType = str_replace('\\', '', $validated['model_type']);
        $modelType = Str::snake($modelType);
        
        $path = '';
        if (isset($validated['model_id'])) {
            // 检查是否为产品模板类型
            if (strpos($validated['model_type'], 'ProductTemplate') !== false) {
                $path = 'product_templates/' . $validated['model_id'] . '/images';
            } else {
                $path = $modelType . 's/' . $validated['model_id'] . '/images';
            }
        } else {
            $path = 'temp/' . ($validated['temp_id'] ?? '') . '/images';
        }
        
        // 确保路径不包含双斜杠
        $cleanedPath = str_replace('//', '/', $path);
        
        return $cleanedPath;
    }
} 