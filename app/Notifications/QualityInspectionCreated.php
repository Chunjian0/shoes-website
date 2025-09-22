<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\QualityInspection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QualityInspectionCreated extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly QualityInspection $inspection
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
        return (new MailMessage)
            ->subject('New quality inspection record has been created')
            ->line("Quality inspection number: {$this->inspection->inspection_number}")
            ->line("Purchase Order Number: {$this->inspection->purchase->purchase_number}")
            ->line("Inspection date: {$this->inspection->inspection_date->format('Y-m-d')}")
            ->action('check the details', route('quality-inspections.show', $this->inspection));
    }
} 