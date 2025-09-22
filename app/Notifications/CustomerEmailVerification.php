<?php

namespace App\Notifications;

use App\Models\VerificationCode;
use App\Models\MessageTemplate;
use App\Services\NotificationService;
use App\Mail\TemplateEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CustomerEmailVerification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * 验证码
     */
    protected $verificationCode;
    
    /**
     * 通知服务
     */
    protected $notificationService;

    /**
     * Create a new notification instance.
     */
    public function __construct($email = null)
    {
        $this->notificationService = app(NotificationService::class);
        
        if ($email) {
            // 为指定邮箱生成验证码
            $this->verificationCode = VerificationCode::generateFor($email);
        }
    }

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
        // 如果构造函数没有传入邮箱，则使用notifiable对象的邮箱生成验证码
        if (!$this->verificationCode) {
            $email = $notifiable->email ?? $notifiable->getEmailForVerification();
            $this->verificationCode = VerificationCode::generateFor($email);
        }
        
        try {
            // 尝试获取自定义模板
            $template = $this->notificationService->getMessageTemplate('email_verification_code', 'email');
            
            if ($template) {
                // 准备模板变量
                $data = [
                    'verification_code' => $this->verificationCode->code,
                    'email' => $this->verificationCode->email,
                    'expires_in_minutes' => 30,
                    'app_name' => config('app.name', 'Laravel'),
                    'app_url' => config('app.url', ''),
                    'current_date' => now()->format('Y-m-d'),
                    'current_time' => now()->format('H:i'),
                ];
                
                // 创建邮件消息
                $mailMessage = new MailMessage;
                
                // 解析模板内容和主题
                $subject = $this->notificationService->replaceVariables($template->subject, $data);
                $content = $this->notificationService->replaceVariables($template->content, $data);
                
                $mailMessage->subject($subject)
                            ->view('emails.template', ['content' => $content]);
                
                return $mailMessage;
            }
        } catch (\Exception $e) {
            Log::error('Failed to get email verification code template, using default template', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        // 如果没有找到模板或出错，使用默认模板
        return (new MailMessage)
            ->subject('Email Verification Code')
            ->greeting('Hello!')
            ->line('You are in the process of email verification and your verification code is:')
            ->view('emails.verification-code', ['code' => $this->verificationCode->code]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'code' => $this->verificationCode->code,
            'expires_at' => $this->verificationCode->expires_at
        ];
    }
}
