<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'image',
        'featured_on_homepage',
        'position',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'featured_on_homepage' => 'boolean',
        'position' => 'integer',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function parameters(): HasMany
    {
        return $this->hasMany(CategoryParameter::class, 'category_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }
} 