<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\QualityInspection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QualityInspectionStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly QualityInspection $inspection,
        public readonly string $oldStatus,
        public readonly string $newStatus
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = match($this->newStatus) {
            'passed' => 'Quality inspection has been passed',
            'failed' => 'Failed to pass the quality inspection',
            default => 'Quality inspection status has been updated'
        };

        $message = (new MailMessage)
            ->subject($subject)
            ->line("Quality inspection number: {$this->inspection->inspection_number}")
            ->line("Purchase Order Number: {$this->inspection->purchase->purchase_number}")
            ->line("Status change: {$this->oldStatus} -> {$this->newStatus}");

        if ($this->newStatus === 'failed') {
            $failedItems = $this->inspection->items()
                ->where('failed_quantity', '>', 0)
                ->get();

            if ($failedItems->isNotEmpty()) {
                $message->line('Unqualified projects:');
                foreach ($failedItems as $item) {
                    $message->line("- {$item->purchaseItem->product->name}: {$item->failed_quantity} {$item->purchaseItem->product->unit}");
                    if ($item->defect_description) {
                        $message->line("  Defect description: {$item->defect_description}");
                    }
                }
            }
        }

        return $message->action('check the details', route('quality-inspections.show', $this->inspection));
    }
} 