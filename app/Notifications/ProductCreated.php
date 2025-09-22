<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Product $product
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New product has been created')
            ->markdown('emails.products.created', [
                'product' => $this->product,
                'notifiable' => $notifiable
            ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'name' => $this->product->name,
            'type' => 'product_created',
            'message' => "New Products {$this->product->name} Created"
        ];
    }
} 