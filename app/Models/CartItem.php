<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class CartItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'specifications',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'specifications' => 'array',
        'quantity' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'subtotal',
        'unit_price',
        'image_url',
        'parameter_group',
    ];

    /**
     * Get the cart that owns the item
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the product for this cart item
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product template if available
     */
    public function productTemplate()
    {
        if (!$this->product) {
            return null;
        }
        
        return ProductTemplate::whereHas('linkedProducts', function($query) {
            $query->where('products.id', $this->product_id);
        })->first();
    }

    /**
     * Get the parameter group from specifications
     */
    public function getParameterGroupAttribute(): ?string
    {
        // 如果specifications中包含template_id，可以用来查找模板
        if (isset($this->specifications['template_id'])) {
            $templateId = $this->specifications['template_id'];
            $template = ProductTemplate::find($templateId);
            
            if ($template) {
                // 查找此产品在模板中的parameter_group
                $pivot = DB::table('product_template_product')
                    ->where('product_template_id', $templateId)
                    ->where('product_id', $this->product_id)
                    ->first();
                
                if ($pivot && $pivot->parameter_group) {
                    return $pivot->parameter_group;
                }
            }
        }
        
        // 从specifications中构建parameter_group
        if (!empty($this->specifications)) {
            $parts = [];
            foreach ($this->specifications as $key => $value) {
                // 跳过template_id和其他非参数字段
                if ($key === 'template_id' || $value === null) {
                    continue;
                }
                
                // 修复键名中可能存在的空格
                $cleanKey = trim($key);
                $cleanValue = is_string($value) ? trim($value) : $value;
                
                if ($cleanKey && $cleanValue) {
                    $parts[] = $cleanKey . '=' . $cleanValue;
                }
            }
            
            if (!empty($parts)) {
                return implode(';', $parts);
            }
        }
        
        return null;
    }

    /**
     * Calculate the subtotal for this item
     */
    public function getSubtotalAttribute(): float
    {
        // 从product中获取价格
        $price = $this->getUnitPriceAttribute();
        return $price * $this->quantity;
    }
    
    /**
     * Get the unit price for this item
     */
    public function getUnitPriceAttribute(): float
    {
        if (!$this->product) {
            return 0;
        }
        
        // 优先使用selling_price作为最终销售价
        if ($this->product->selling_price > 0) {
            return (float)$this->product->selling_price;
        }
        
        // 如果无selling_price，则检查是否有折扣
        if ($this->product->hasValidDiscount()) {
            return (float)$this->product->getDiscountedPrice();
        }
        
        // 最后使用原价
        return (float)$this->product->price;
    }
    
    /**
     * Get the image URL for display
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->product) {
            return null;
        }
        
        // 优先使用产品的getAllImages方法
        if (method_exists($this->product, 'getAllImages')) {
            $images = $this->product->getAllImages();
            if (!empty($images)) {
                return $images[0];
            }
        }
        
        // 回退到检查media关系
        if ($this->product->relationLoaded('media') && $this->product->media->isNotEmpty()) {
            $media = $this->product->media->first();
            return asset('storage/' . $media->path);
        }
        
        // 回退到image_url字段
        if ($this->product->image_url) {
            return asset('storage/' . $this->product->image_url);
        }
        
        return asset('images/placeholder.png');
    }
} 