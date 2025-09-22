<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TemplateEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected string $emailSubject;
    protected string $emailContent;
    protected array $emailAttachments;
    protected string $emailType;

    /**
     * Create a new message instance.
     *
     * @param string $subject The email subject
     * @param string $content The email content
     * @param array $attachments Optional attachments
     * @param string $type The notification type
     */
    public function __construct(string $subject, string $content, array $attachments = [], string $type = 'custom_email')
    {
        $this->emailSubject = $subject;
        $this->emailContent = $content;
        $this->emailAttachments = $attachments;
        $this->emailType = $type;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->subject($this->emailSubject)
            ->view('emails.template')
            ->with([
                'content' => $this->emailContent,
                'type' => $this->emailType
            ]);

        // Add attachments if any
        foreach ($this->emailAttachments as $attachment) {
            if (isset($attachment['path'])) {
                $mail->attach($attachment['path'], $attachment['options'] ?? []);
            } elseif (isset($attachment['data'])) {
                $mail->attachData(
                    $attachment['data'],
                    $attachment['name'],
                    $attachment['options'] ?? []
                );
            }
        }

        return $mail;
    }
} 