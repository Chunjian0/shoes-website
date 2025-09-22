<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'subtitle',
        'image',
        'button_text',
        'button_link',
        'order',
        'is_active',
        'media_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the media that owns the banner.
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    /**
     * Get the image URL.
     *
     * @return string|null
     */
    public function getImageUrl()
    {
        // 如果存在media关联，优先使用它
        if ($this->media) {
            return asset('storage/' . $this->media->path);
        }

        // 向后兼容: 检查旧的image字段
        if (!empty($this->image)) {
            // 检查图片是否为完整URL
            if (filter_var($this->image, FILTER_VALIDATE_URL)) {
                return $this->image;
            }

            // 检查图片是否以/storage/开头
            if (strpos($this->image, '/storage/') === 0) {
                return asset($this->image);
            }

            // 默认从storage目录获取
            return asset('storage/' . $this->image);
        }

        // 无图片
        return null;
    }

    /**
     * Get active banners ordered by their display order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('order', 'asc');
    }

    /**
     * Reorder banners after a banner is deleted or added.
     *
     * @return void
     */
    public static function reorder(): void
    {
        $banners = self::orderBy('order')->get();
        
        foreach ($banners as $index => $banner) {
            $banner->order = $index + 1;
            $banner->save();
        }
    }
} 