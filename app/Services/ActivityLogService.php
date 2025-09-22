<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Record activity logs
     */
    public function log(
        Model $subject,
        string $event,
        string $description,
        array $properties = []
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'subject_type' => get_class($subject),
            'subject_id' => $subject->getKey(),
            'event' => $event,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Record creation operation
     */
    public function logCreated(Model $subject, array $properties = []): ActivityLog
    {
        return $this->log(
            $subject,
            'created',
            sprintf('Created %s', class_basename($subject)),
            $properties
        );
    }

    /**
     * Record update operations
     */
    public function logUpdated(Model $subject, array $properties = []): ActivityLog
    {
        return $this->log(
            $subject,
            'updated',
            sprintf('Updated %s', class_basename($subject)),
            $properties
        );
    }

    /**
     * Record deletion operation
     */
    public function logDeleted(Model $subject, array $properties = []): ActivityLog
    {
        return $this->log(
            $subject,
            'deleted',
            sprintf('Deleted %s', class_basename($subject)),
            $properties
        );
    }

    /**
     * Record status changes
     */
    public function logStatusChanged(
        Model $subject,
        string $fromStatus,
        string $toStatus,
        array $properties = []
    ): ActivityLog {
        return $this->log(
            $subject,
            'status_changed',
            sprintf(
                'Will %s The status from %s Modified to %s',
                class_basename($subject),
                $fromStatus,
                $toStatus
            ),
            $properties
        );
    }
} 