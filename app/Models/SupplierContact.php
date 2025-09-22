<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SupplierContact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'name',
        'position',
        'phone',
        'email',
        'is_primary',
        'remarks',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    // Related Suppliers
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    // Set as primary contact
    public function setAsPrimary(): void
    {
        // Set all contacts as non-primary contacts first
        $this->supplier->contacts()
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        // Set the current contact as the primary contact
        $this->update(['is_primary' => true]);
    }
} 