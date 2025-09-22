<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class Media extends Model
{
    protected $fillable = [
        'name',
        'file_name',
        'mime_type',
        'size',
        'path',
        'disk',
        'model_type',
        'model_id',
        'collection_name',
        'temp_id'
    ];

    protected $casts = [
        'custom_properties' => 'array',
        'size' => 'integer',
    ];

    protected $appends = [
        'url',
        'full_path',
    ];

    /**
     * Get the associated model
     */
    public function model(): MorphTo
    {
        Log::info('MediaModel association query', [
            'media_id' => $this->id,
            'model_type' => $this->model_type,
            'model_id' => $this->model_id
        ]);
        
        return $this->morphTo('model', 'model_type', 'model_id');
    }

    /**
     * Get the complete fileURL
     */
    public function getUrlAttribute(): string
    {
        // 基础URL
        $url = asset('storage/' . $this->path);
        
        // 可能的文件路径
        $storagePath = storage_path('app/public/' . $this->path);
        $publicPath = public_path('storage/' . $this->path);
        $storageExists = file_exists($storagePath);
        $publicExists = file_exists($publicPath);
        $diskExists = Storage::disk('public')->exists($this->path);
        
        // 记录调试信息
        Log::info('Media URL generation', [
            'media_id' => $this->id,
            'path' => $this->path,
            'full_url' => $url,
            'storage_path_exists' => $storageExists,
            'public_path_exists' => $publicExists,
            'storage_disk_exists' => $diskExists,
            'storage_path' => $storagePath,
            'public_path' => $publicPath,
            'absolute_url' => config('app.url') . '/storage/' . $this->path,
        ]);
        
        // 添加调试信息到URL
        $debugInfo = [
            'id' => $this->id,
            'exists_storage' => $storageExists ? 1 : 0,
            'exists_public' => $publicExists ? 1 : 0,
            'exists_disk' => $diskExists ? 1 : 0,
        ];
        
        // 返回包含调试信息的URL
        return $url . '?' . http_build_query($debugInfo);
    }

    /**
     * Get the full path to the file
     */
    public function getFullPathAttribute(): string
    {
        return storage_path('app/public/' . $this->path);
    }

    /**
     * Delete files
     */
    public function delete(): bool
    {
        if (Storage::disk($this->disk)->exists($this->path)) {
            Storage::disk($this->disk)->delete($this->path);
        }
        
        return parent::delete();
    }

    /**
     * Move the file to a new location
     */
    public function moveTo(string $newPath): bool
    {
        if (Storage::disk($this->disk)->exists($this->path)) {
            if (Storage::disk($this->disk)->move($this->path, $newPath)) {
                $this->path = $newPath;
                return $this->save();
            }
        }
        return false;
    }
} 