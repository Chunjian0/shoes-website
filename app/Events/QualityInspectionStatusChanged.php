<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\QualityInspection;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QualityInspectionStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly QualityInspection $inspection,
        public readonly string $oldStatus,
        public readonly string $newStatus
    ) {}
} 