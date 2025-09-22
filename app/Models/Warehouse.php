<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'address',
        'contact_person',
        'contact_phone',
        'status',
        'notes',
        'is_store',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_store' => 'boolean',
    ];

    /**
     * Get inventory movement records
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
} 