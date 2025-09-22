<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Purchase;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PurchaseStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Purchase $purchase,
        private readonly string $oldStatus,
        private readonly string $newStatus,
        private readonly array $additionalData = []
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Purchase Order Status Update Notice')
            ->line("Purchase Order #{$this->purchase->id} Status updated")
            ->line("from {$this->oldStatus} Change to {$this->newStatus}")
            ->action('check the details', route('purchases.show', $this->purchase))
            ->line('Thank you for using our system!');
    }

    public function toArray($notifiable): array
    {
        return [
            'purchase_id' => $this->purchase->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'additional_data' => $this->additionalData,
        ];
    }
} 