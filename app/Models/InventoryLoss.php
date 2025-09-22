<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryLoss extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'loss_number',
        'loss_date',
        'user_id',
        'status',
        'reason',
        'notes',
    ];

    protected $casts = [
        'loss_date' => 'date',
    ];

    // State constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    // Related damage details
    public function items(): HasMany
    {
        return $this->hasMany(InventoryLossItem::class);
    }

    // Related Users
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Generate a loss order number
    public static function generateLossNumber(): string
    {
        $prefix = 'IL';
        $date = date('Ymd');
        $sequence = str_pad((string) (static::whereDate('created_at', today())->count() + 1), 4, '0', STR_PAD_LEFT);
        
        return $prefix . $date . $sequence;
    }
} 