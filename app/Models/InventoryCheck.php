<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryCheck extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'check_number',
        'check_date',
        'user_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'check_date' => 'date',
    ];

    // State constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    // Related inventory personnel
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Related inventory details
    public function items(): HasMany
    {
        return $this->hasMany(InventoryCheckItem::class);
    }

    // Generate inventory order numbers
    public static function generateCheckNumber(): string
    {
        $prefix = 'IC';
        $date = date('Ymd');
        $sequence = str_pad((string) (static::whereDate('created_at', today())->count() + 1), 4, '0', STR_PAD_LEFT);
        
        return $prefix . $date . $sequence;
    }
} 