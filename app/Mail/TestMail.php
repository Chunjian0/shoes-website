<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The notification type.
     */
    public string $type;

    /**
     * The message content.
     */
    public string $messageContent;

    /**
     * Create a new message instance.
     */
    public function __construct(string $type = 'test_mail', string $messageContent = 'This is a test email message.')
    {
        $this->type = $type;
        $this->messageContent = $messageContent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Test Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.test',
            with: [
                'content' => $this->messageContent,
                'type' => $this->type,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Add type to the data array for the LogSentMail listener
        $this->withSymfonyMessage(function ($message) {
            $message->getHeaders()->addTextHeader('X-Notification-Type', $this->type);
        });

        return $this;
    }
}
