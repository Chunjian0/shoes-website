<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ProductTemplate extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $table = 'product_templates';

    protected $fillable = [
        'name',
        'description',
        'images',
        'parameters',
        'is_active',
        'category_id',
        'store_id',
        'is_featured',
        'is_new_arrival',
        'is_sale',
        'parameter_options',
    ];

    protected $casts = [
        'images' => 'array',
        'parameters' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_new_arrival' => 'boolean',
        'is_sale' => 'boolean',
        'parameter_options' => 'array',
        'template_gallery' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'template_id');
    }

    /**
     * 获取直接关联到模板的产品（通过中间表）
     */
    public function linkedProducts()
    {
        return $this->belongsToMany(Product::class, 'product_template_product')
                    ->withPivot('parameter_group')
                    ->withTimestamps();
    }

    /**
     * 获取指定参数组的关联产品
     */
    public function getProductsByParameterGroup($group)
    {
        return $this->linkedProducts()
                    ->wherePivot('parameter_group', $group);
    }

    /**
     * 获取parameters属性
     *
     * @param  string|null  $value
     * @return array
     */
    public function getParametersAttribute($value)
    {
        if (is_null($value)) {
            return [];
        }
        
        if (is_array($value)) {
            return $value;
        }
        
        try {
            $decoded = json_decode($value, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('Failed to decode parameters JSON: ' . json_last_error_msg(), [
                    'template_id' => $this->id,
                    'raw_value' => $value
                ]);
                return [];
            }
            
            return $decoded;
        } catch (\Exception $e) {
            Log::warning('Exception when decoding parameters', [
                'template_id' => $this->id,
                'error' => $e->getMessage(),
                'raw_value' => $value
            ]);
            return [];
        }
    }
    
    /**
     * 设置parameters属性
     *
     * @param  array|string  $value
     * @return void
     */
    public function setParametersAttribute($value)
    {
        if (is_null($value)) {
            $this->attributes['parameters'] = json_encode([]);
            return;
        }
        
        if (is_string($value)) {
            try {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $this->attributes['parameters'] = json_encode($decoded);
                    return;
                }
            } catch (\Exception $e) {
                // 继续处理
            }
        }
        
        if (is_array($value)) {
            $this->attributes['parameters'] = json_encode($value);
            return;
        }
        
        $this->attributes['parameters'] = json_encode([]);
    }

    /**
     * 获取所有图片数据
     *
     * @return array
     */
    public function getAllImages()
    {
        $images = [];
        
        // 首先尝试检查是否有媒体关联
        if ($this->relationLoaded('media') && $this->media->count() > 0) {
            $images = $this->media->map(function($media) {
                return [
                    'id' => $media->id,
                    'url' => $media->path,
                    'thumbnail' => $media->path,
                    'name' => $media->name,
                    'path' => $media->path
                ];
            })->toArray();
        }
        
        // 如果没有关联媒体，但有images字段(JSON)
        if (empty($images) && !empty($this->images) && is_array($this->images)) {
            // 标准化images数组中的每个元素，确保统一格式
            foreach ($this->images as $image) {
                if (is_array($image)) {
                    // 处理数组格式的图片
                    $imageData = [
                        'id' => $image['id'] ?? null,
                        'name' => $image['name'] ?? ('Image ' . (count($images) + 1)),
                    ];
                    
                    // 处理URL路径
                    if (isset($image['url'])) {
                        $imageData['url'] = $image['url'];
                        $imageData['path'] = $image['url'];
                        $imageData['thumbnail'] = $image['thumbnail'] ?? $image['url'];
                    } elseif (isset($image['path'])) {
                        $imageData['url'] = $image['path'];
                        $imageData['path'] = $image['path'];
                        $imageData['thumbnail'] = $image['thumbnail'] ?? $image['path'];
                    }
                    
                    $images[] = $imageData;
                } elseif (is_string($image)) {
                    // 处理字符串格式的图片URL
                    $images[] = [
                        'id' => null,
                        'url' => $image,
                        'path' => $image,
                        'thumbnail' => $image,
                        'name' => 'Image ' . (count($images) + 1)
                    ];
                }
            }
        }
        
        // 记录图片处理结果
        if (empty($images)) {
            Log::info('No images found for product template', ['id' => $this->id]);
        } else {
            Log::info('Found images for product template', [
                'id' => $this->id, 
                'image_count' => count($images)
            ]);
        }
        
        return $images;
    }
    
    /**
     * 获取主图像
     *
     * @return string|null
     */
    public function getMainImage()
    {
        $images = $this->getAllImages();
        
        if (!empty($images)) {
            $firstImage = $images[0];
            if (isset($firstImage['url'])) {
                // 检查URL是否已经是完整URL
                if (strpos($firstImage['url'], 'http') === 0) {
                    return $firstImage['url'];
                }
                return Storage::url($firstImage['url']);
            }
        }
        
        return asset('images/no-image.png');
    }
    
    /**
     * 获取图片URL，用于在前端统一展示
     * 
     * @return string
     */
    public function getImageUrl()
    {
        // 优先使用媒体关联
        if ($this->relationLoaded('media') && $this->media->isNotEmpty()) {
            $media = $this->media->first();
            return Storage::url($media->path);
        }
        
        // 使用主图像方法
        return $this->getMainImage();
    }
}
