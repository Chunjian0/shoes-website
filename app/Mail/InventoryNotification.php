<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InventoryNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance
     */
    public function __construct(
        protected string $emailSubject,
        protected string $emailContent
    ) {
        $this->subject = $emailSubject;
    }

    /**
     * Build the message
     */
    public function build(): self
    {
        return $this->view('emails.inventory-notification')
                    ->with([
                        'content' => $this->emailContent
                    ]);
    }
} 