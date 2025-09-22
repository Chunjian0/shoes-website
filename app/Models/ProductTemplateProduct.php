<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTemplateProduct extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_template_product';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_template_id',
        'product_id',
        'parameter_group'
    ];

    /**
     * Get the product template that owns the record.
     */
    public function productTemplate()
    {
        return $this->belongsTo(ProductTemplate::class);
    }

    /**
     * Get the product that owns the record.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
} 