<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QualityInspectionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class QualityInspection extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $fillable = [
        'purchase_id',
        'inspector_id',
        'inspection_number',
        'inspection_date',
        'status',
        'is_partial',
        'remarks',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'status' => QualityInspectionStatus::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get associated purchase orders
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Obtain the inspector
     */
    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    /**
     * Obtain inspection items
     */
    public function items(): HasMany
    {
        return $this->hasMany(QualityInspectionItem::class);
    }

    /**
     * Check if it can be edited
     */
    public function isEditable(): bool
    {
        return $this->status === QualityInspectionStatus::PENDING;
    }

    /**
     * Check whether it can be reviewed
     */
    public function isReviewable(): bool
    {
        return $this->status === QualityInspectionStatus::PENDING;
    }

    /**
     * Get the total number of inspections
     */
    public function getTotalInspectedQuantityAttribute(): float
    {
        return $this->items->sum('inspected_quantity');
    }

    /**
     * Obtain the total pass count
     */
    public function getTotalPassedQuantityAttribute(): float
    {
        return $this->items->sum('passed_quantity');
    }

    /**
     * Get total unqualified quantity
     */
    public function getTotalFailedQuantityAttribute(): float
    {
        return $this->items->sum('failed_quantity');
    }

    /**
     * Obtain pass rate
     */
    public function getPassRateAttribute(): float
    {
        if ($this->total_inspected_quantity === 0) {
            return 0;
        }
        return round($this->total_passed_quantity / $this->total_inspected_quantity * 100, 2);
    }

    /**
     * Get all media for this quality inspection
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'model');
    }
} 