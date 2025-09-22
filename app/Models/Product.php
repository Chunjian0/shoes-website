<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    /**
     * 允许批量赋值的属性
     */
    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'barcode',
        'brand',
        'model',
        'cost_price',
        'selling_price',
        'min_stock',
        'stock',
        'description',
        'images',
        'parameters',
        'is_active',
        'is_featured',
        'is_new',
        'is_sale',
        'discount_percentage',
        'template_id',
        'variant_options',
        'price_adjustment',
        // 首页管理相关字段
        'featured_order',
        'featured_image_index',
        'is_new_arrival',
        'show_in_new_arrivals',
        'new_arrival_image_index',
        'discount_start_date',
        'discount_end_date',
        'min_quantity_for_discount',
        'max_quantity_for_discount',
        'show_in_sale',
        'sale_image_index',
        'additional_images',
        'default_image_index',
        'new_until_date',
        'sale_until_date'
    ];

    /**
     * 类型转换
     */
    protected $casts = [
        'images' => 'array',
        'parameters' => 'array',
        'variant_options' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_new' => 'boolean',
        'is_sale' => 'boolean',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'price_adjustment' => 'decimal:2',
        'discount_start_date' => 'datetime',
        'discount_end_date' => 'datetime',
        'new_until_date' => 'datetime',
        'sale_until_date' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'supplier_products')
            ->withPivot([
                'supplier_product_code',
                'purchase_price',
                'tax_rate',
                'min_order_quantity',
                'lead_time',
                'is_preferred',
                'remarks'
            ]);
    }

    /**
     * Get the price agreement for the product
     */
    public function priceAgreements(): HasMany
    {
        return $this->hasMany(SupplierPriceAgreement::class);
    }

    /**
     * Calculate the latest cost price
     * 
     * rule:
     * 1. If there is a price agreement, use the latest price agreement price
     * 2. If there is no price agreement, use the lowest purchase price in the supplier's product
     * 3. If none are available, return to the current cost price
     */
    public function calculateLatestCostPrice(): float
    {
        try {
            // Get the current valid price agreement
            $latestAgreement = $this->suppliers()
                ->whereHas('priceAgreements', function ($query) {
                    $query->where('product_id', $this->id)
                        ->where('start_date', '<=', now())
                        ->where(function ($q) {
                            $q->where('end_date', '>=', now())
                                ->orWhereNull('end_date');
                        });
                })
                ->with(['priceAgreements' => function ($query) {
                    $query->where('product_id', $this->id)
                        ->where('start_date', '<=', now())
                        ->where(function ($q) {
                            $q->where('end_date', '>=', now())
                                ->orWhereNull('end_date');
                        })
                        ->orderBy('price')
                        ->first();
                }])
                ->first();

            if ($latestAgreement && $latestAgreement->priceAgreements->isNotEmpty()) {
                // Use the lowest price in the price agreement
                $agreement = $latestAgreement->priceAgreements->first();
                return $agreement->getDiscountedPrice();
            }

            // If there is no valid price agreement, use the lowest purchase price in the supplier's product
            $lowestPurchasePrice = $this->suppliers()
                ->orderBy('supplier_products.purchase_price')
                ->first();

            if ($lowestPurchasePrice) {
                return (float) $lowestPurchasePrice->pivot->purchase_price;
            }

            // If none are available, return to the current cost price
            return (float) $this->cost_price;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to calculate the cost price of the product', [
                'product_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return (float) $this->cost_price;
        }
    }

    /**
     * Update cost price
     */
    public function updateCostPrice(): bool
    {
        try {
            $newCostPrice = $this->calculateLatestCostPrice();
            if ($newCostPrice != $this->cost_price) {
                $this->update(['cost_price' => $newCostPrice]);
                \Illuminate\Support\Facades\Log::info('Product cost price has been updated', [
                    'product_id' => $this->id,
                    'old_price' => $this->cost_price,
                    'new_price' => $newCostPrice
                ]);
            }
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to update the product cost price', [
                'product_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Obtain product inventory records
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    /**
     * 获取产品的总库存数量（从stock表中汇总）
     * 
     * @return int
     */
    public function getTotalStock(): int
    {
        try {
            // 获取所有库存记录并汇总数量
            $totalQuantity = $this->stocks()->sum('quantity');
            
            // 如果没有库存记录，则使用product表中的stock字段
            if ($totalQuantity <= 0) {
                return (int)$this->stock;
            }
            
            return (int)$totalQuantity;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to get total stock for product', [
                'product_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            
            // 出错时返回product表中的stock字段
            return (int)$this->stock;
        }
    }

    /**
     * 获取当前有效折扣价格
     *
     * @param int $quantity 购买数量
     * @return float|null
     */
    public function getDiscountedPrice($quantity = 1)
    {
        if (!$this->hasValidDiscount($quantity)) {
            return null;
        }
        
        $discountMultiplier = (100 - $this->discount_percentage) / 100;
        return round($this->price * $discountMultiplier, 2);
    }
    
    /**
     * 判断当前产品是否有有效折扣
     *
     * @param int $quantity 购买数量
     * @return bool
     */
    public function hasValidDiscount($quantity = 1)
    {
        // 检查折扣百分比
        if ($this->discount_percentage <= 0) {
            return false;
        }
        
        // 检查折扣时间
        $now = now();
        if ($this->discount_start_date && $now->lt($this->discount_start_date)) {
            return false;
        }
        
        if ($this->discount_end_date && $now->gt($this->discount_end_date)) {
            return false;
        }
        
        // 检查数量限制
        if ($this->min_quantity_for_discount > 0 && $quantity < $this->min_quantity_for_discount) {
            return false;
        }
        
        if ($this->max_quantity_for_discount && $quantity > $this->max_quantity_for_discount) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 判断产品是否属于新品
     *
     * @return bool
     */
    public function isNewArrival()
    {
        // 如果手动设置为新品
        if ($this->is_new_arrival) {
            return true;
        }
        
        // 检查创建时间是否在新品时间范围内
        $newProductDays = 30; // 默认30天
        
        // 从设置中获取天数
        $settingDays = DB::table('settings')
            ->where('group', 'homepage')
            ->where('key', 'new_products_days')
            ->value('value');
            
        if ($settingDays) {
            $newProductDays = (int)$settingDays;
        }
        
        $cutoffDate = now()->subDays($newProductDays);
        
        return $this->created_at->gt($cutoffDate);
    }
    
    /**
     * 获取用于展示的图片URL
     *
     * @param string $type 展示类型：featured, sale 或 null
     * @return string
     */
    public function getDisplayImageUrl($type = null)
    {
        $imageIndex = 0;
        
        if ($type === 'featured' && $this->featured_image_index > 0) {
            $imageIndex = $this->featured_image_index;
        } elseif ($type === 'sale' && $this->sale_image_index > 0) {
            $imageIndex = $this->sale_image_index;
        }
        
        $images = $this->images ?? [];
        
        // 如果指定索引的图片存在，使用该图片
        if (isset($images[$imageIndex])) {
            return asset('storage/' . $images[$imageIndex]);
        }
        
        // 否则使用主图片
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        
        // 都不存在时使用默认图片
        return asset('images/placeholder.png');
    }

    /**
     * 获取产品的所有图片URL
     *
     * @return array
     */
    public function getAllImages()
    {
        try {
            $images = [];
            
            // 从media关联获取图片
            $mediaImages = $this->media->map(function ($mediaItem) {
                if (strpos($mediaItem->path, 'http') === 0) {
                    return $mediaItem->path; // 已经是完整URL
                } else {
                    return asset('storage/' . $mediaItem->path);
                }
            })->toArray();
            
            if (!empty($mediaImages)) {
                return $mediaImages;
            }
            
            // 如果没有media关联图片，尝试使用legacy方式处理
            if (!empty($this->images) && is_array($this->images)) {
                foreach ($this->images as $image) {
                    if (is_string($image)) {
                        if (strpos($image, 'http') === 0) {
                            $images[] = $image;
                        } else {
                            $images[] = asset('storage/' . $image);
                        }
                    }
                }
                return $images;
            }
            
            // 如果都没有，返回默认图片
            return [asset('images/placeholder.png')];
        } catch (\Exception $e) {
            Log::error('Error getting product images', [
                'product_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return [asset('images/placeholder.png')];
        }
    }
    
    /**
     * 获取产品的主图片URL
     * 
     * @return string
     */
    public function getMainImage()
    {
        $images = $this->getAllImages();
        return $images[0] ?? asset('images/placeholder.png');
    }
    
    /**
     * 根据类型获取产品图片
     * 
     * @param string $type 图片类型 (featured, new, sale)
     * @return string 图片URL
     */
    public function getImageForType(string $type = 'featured'): string
    {
        $images = $this->getAllImages();
        
        if (empty($images)) {
            return asset('images/placeholder.png');
        }
        
        $index = 0;
        
        switch ($type) {
            case 'featured':
                $index = $this->featured_image_index ?? 0;
                break;
            case 'new':
                $index = $this->new_image_index ?? 0;
                break;
            case 'sale':
                $index = $this->sale_image_index ?? 0;
                break;
            default:
                $index = 0;
        }
        
        // 确保索引有效
        if ($index >= count($images)) {
            $index = 0;
        }
        
        return $images[$index];
    }

    /**
     * 获取包含此产品的首页部分
     */
    public function homepageSections()
    {
        return $this->belongsToMany(HomepageSection::class, 'homepage_section_product')
            ->withPivot('position')
            ->orderBy('position');
    }

    public function template()
    {
        return $this->belongsTo(ProductTemplate::class);
    }

    public function getFinalPriceAttribute()
    {
        if ($this->template) {
            return $this->template->base_price + $this->price_adjustment;
        }
        return $this->price;
    }

    public function getActualSellingPriceAttribute()
    {
        if ($this->selling_price > 0) {
            return $this->selling_price;
        }
        
        $price = $this->getFinalPriceAttribute();
        if ($this->is_sale && $this->discount_percentage > 0) {
            $price = $price * (1 - $this->discount_percentage / 100);
        }
        return round($price, 2);
    }
} 