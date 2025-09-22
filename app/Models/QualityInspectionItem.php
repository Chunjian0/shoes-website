<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityInspectionItem extends Model
{
    protected $fillable = [
        'quality_inspection_id',
        'purchase_item_id',
        'inspected_quantity',
        'passed_quantity',
        'failed_quantity',
        'defect_description',
    ];

    protected $casts = [
        'inspected_quantity' => 'integer',
        'passed_quantity' => 'integer',
        'failed_quantity' => 'integer',
    ];

    /**
     * Obtain associated quality inspection
     */
    public function qualityInspection(): BelongsTo
    {
        return $this->belongsTo(QualityInspection::class);
    }

    /**
     * Get associated procurement items
     */
    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class);
    }
} 