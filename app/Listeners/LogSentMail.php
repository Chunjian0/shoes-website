<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSent;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Log;

class LogSentMail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        try {
            $message = $event->message;
            $data = $event->data;
            
            // Get recipients
            $to = $this->getRecipients($message);
            $subject = $message->getSubject();
            $content = $this->getMessageContent($message);
            $type = $data['type'] ?? 'system_alert';
            
            // For each recipient, create or update a notification log entry
            foreach ($to as $recipient) {
                // 检查是否已存在相同收件人和主题的记录（在最近5分钟内创建的）
                $existingLog = NotificationLog::where('recipient', $recipient)
                    ->where('subject', $subject)
                    ->where('created_at', '>=', now()->subMinutes(5))
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($existingLog) {
                    // 更新现有记录
                    $existingLog->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'content' => $content,
                        // 只有在现有记录类型为空或为system_alert时才更新类型
                        'type' => ($existingLog->type === 'system_alert' || empty($existingLog->type)) ? $type : $existingLog->type
                    ]);
                    
                    Log::info('Updated existing email notification log', [
                        'id' => $existingLog->id,
                        'subject' => $subject,
                        'recipient' => $recipient
                    ]);
                } else {
                    // 创建新记录
                    NotificationLog::create([
                        'type' => $type,
                        'recipient' => $recipient,
                        'subject' => $subject,
                        'content' => $content,
                        'status' => 'sent',
                        'sent_at' => now(),
                    ]);
                    
                    Log::info('Created new email notification log', [
                        'subject' => $subject,
                        'recipient' => $recipient
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to log email notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Extract recipients from the message
     */
    private function getRecipients($message): array
    {
        $to = [];
        
        // Get all recipients
        $recipients = $message->getTo();
        if (!empty($recipients)) {
            foreach ($recipients as $email => $name) {
                // 确保收件人是有效的电子邮件地址
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $to[] = $email;
                } else {
                    // 记录无效的收件人
                    Log::warning('Invalid email recipient found', [
                        'recipient' => $email
                    ]);
                }
            }
        }
        
        // 如果没有有效的收件人，记录警告
        if (empty($to)) {
            Log::warning('No valid recipients found for email', [
                'subject' => $message->getSubject() ?? 'No subject'
            ]);
        }
        
        return $to;
    }
    
    /**
     * Extract message content
     */
    private function getMessageContent($message): string
    {
        try {
            // Try to get HTML body
            $body = $message->getHtmlBody();
            
            // If no HTML body, try to get text body
            if (empty($body)) {
                $body = $message->getTextBody();
            }
            
            return $body ?: 'No content available';
        } catch (\Exception $e) {
            Log::warning('Could not extract email content', [
                'error' => $e->getMessage()
            ]);
            return 'Content extraction failed';
        }
    }
}
