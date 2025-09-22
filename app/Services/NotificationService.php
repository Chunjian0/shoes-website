<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Purchase;
use App\Models\QualityInspection;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PurchaseStatusNotification;
use App\Notifications\PaymentStatusNotification;
use App\Notifications\PurchaseStatusChanged;
use App\Notifications\QualityInspectionCreated;
use App\Notifications\QualityInspectionStatusChanged;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Mail\Message;
use App\Models\MessageTemplate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Exception;
use App\Mail\TemplateEmail;

class NotificationService
{
    public function __construct(
        private readonly NotificationSettingService $notificationSettingService
    ) {}

    /**
     * Send a notice of change of purchase order status
     */
    public function sendPurchaseStatusNotification(
        Purchase $purchase,
        string $oldStatus,
        string $newStatus,
        array $additionalData = []
    ): void {
        try {
            // Check whether email notifications are enabled
            if (!$this->notificationSettingService->isMethodEnabled('email')) {
                Log::info('Email notification is not enabled, sending is skipped');
                return;
            }

            // Get users who need notifications
            $receivers = $this->notificationSettingService->getReceivers('purchase_status_changed');
            if (empty($receivers)) {
                Log::info('Notify recipient without configuration, skip send');
                return;
            }

            $users = User::whereIn('email', $receivers)->get();
            if ($users->isEmpty()) {
                Log::warning('No valid notification received user found');
                return;
            }

            // Send notification
            Notification::send($users, new PurchaseStatusNotification(
                $purchase,
                $oldStatus,
                $newStatus,
                $additionalData
            ));

            Log::info('The purchase order status change notification was sent successfully', [
                'purchase_id' => $purchase->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'receivers' => $receivers
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send a notice of change of purchase order status', [
                'error' => $e->getMessage(),
                'purchase_id' => $purchase->id
            ]);
        }
    }

    /**
     * Send a notification of payment status change
     */
    public function sendPaymentStatusNotification(
        Purchase $purchase,
        string $oldStatus,
        string $newStatus,
        float $totalPaid,
        array $additionalData = []
    ): void {
        try {
            // Check whether email notifications are enabled
            if (!$this->notificationSettingService->isMethodEnabled('email')) {
                Log::info('Email notification is not enabled, sending is skipped');
                return;
            }

            // Get users who need notifications
            $receivers = $this->notificationSettingService->getReceivers('payment_status_changed');
            if (empty($receivers)) {
                Log::info('Notify the recipient without configuration, skip sending');
                return;
            }

            $users = User::whereIn('email', $receivers)->get();
            if ($users->isEmpty()) {
                Log::warning('No valid notification received user found');
                return;
            }

            // Send notification
            Notification::send($users, new PaymentStatusNotification(
                $purchase,
                $oldStatus,
                $newStatus,
                $totalPaid,
                $additionalData
            ));

            Log::info('Payment status change notification was sent successfully', [
                'purchase_id' => $purchase->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'total_paid' => $totalPaid,
                'receivers' => $receivers
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send a notification to change payment status', [
                'error' => $e->getMessage(),
                'purchase_id' => $purchase->id
            ]);
        }
    }

    /**
     * Get users who need to receive notifications
     *
     * @return \Illuminate\Database\Eloquent\Collection<User>
     */
    private function getNotificationRecipients(Purchase $purchase)
    {
        return User::query()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['admin', 'purchaser', 'accountant']);
            })
            ->orWhere('id', $purchase->created_by)
            ->get();
    }

    /**
     * Get notification recipient
     *
     * @param string $notificationType Notification Type
     * @return array Notify recipient of email array
     */
    public function getNotificationReceivers(string $notificationType): array
    {
        try {
            $receiversData = Setting::where('key', 'notification_receivers')
                ->where('group', 'notifications')
                ->first();
            
            if (!$receiversData) {
                return [];
            }
            
            $receivers = json_decode($receiversData->value, true);
            
            return $receivers[$notificationType] ?? [];
        } catch (Exception $e) {
            Log::error('获取通知接收人失败', [
                'error' => $e->getMessage(),
                'type' => $notificationType
            ]);
            return [];
        }
    }

    /**
     * 发送邮件通知
     * 
     * @param string $subject 主题
     * @param string $templateName 模板名称
     * @param array $data 数据
     * @param string $notificationType 通知类型
     * @param int|null $supplierId 供应商ID
     * @return void
     */
    public function sendEmailNotification(
        string $subject,
        string $templateName,
        array $data = [],
        string $notificationType = 'system_alert',
        ?int $supplierId = null
    ): void {
        $receivers = $this->getNotificationReceivers($notificationType);

        if (empty($receivers)) {
            Log::warning('未找到通知接收人，邮件未发送', [
                'type' => $notificationType,
                'subject' => $subject
            ]);
            return;
        }

        try {
            // 获取模板内容，优先使用供应商特定模板
            $template = $this->getMessageTemplate($templateName, 'email', $supplierId);
            if (!$template) {
                throw new Exception("未找到模板: {$templateName}");
            }
            
            // 使用模板主题（如果设置了模板主题）
            if (!empty($template->subject)) {
                $subject = $this->parseTemplateContent($template->subject, $data);
            }
            
            // 解析模板内容
            $content = $this->parseTemplateContent($template->content, $data);
            
            // 发送邮件
            Mail::send('emails.template', ['content' => $content], function (Message $message) use ($receivers, $subject) {
                $message->to($receivers)
                    ->subject($subject);
            });

            Log::info('邮件通知发送成功', [
                'receivers' => $receivers,
                'subject' => $subject,
                'template' => $templateName,
                'supplier_id' => $supplierId
            ]);
        } catch (Exception $e) {
            Log::error('邮件通知发送失败', [
                'error' => $e->getMessage(),
                'receivers' => $receivers,
                'subject' => $subject,
                'template' => $templateName,
                'supplier_id' => $supplierId
            ]);
        }
    }

    /**
     * Send quality inspection creation notification
     */
    public function sendQualityInspectionCreatedNotification(QualityInspection $inspection): void
    {
        try {
            // Check whether email notifications are enabled
            if (!$this->notificationSettingService->isMethodEnabled('email')) {
                Log::info('Email notification is not enabled, sending is skipped');
                return;
            }

            // Get users who need notifications
            $receivers = $this->notificationSettingService->getReceivers('quality_inspection_created');
            if (empty($receivers)) {
                Log::info('Notify the recipient without configuration, skip sending');
                return;
            }

            // Get notifications from users
            $users = User::whereIn('email', $receivers)->get();
            if ($users->isEmpty()) {
                Log::warning('No valid notification received user found');
                return;
            }

            Notification::send($users, new QualityInspectionCreated($inspection));
            
            Log::info('Quality inspection creation notification was sent successfully', [
                'inspection_id' => $inspection->id,
                'receivers' => $receivers
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send quality inspection creation notification', [
                'error' => $e->getMessage(),
                'inspection_id' => $inspection->id
            ]);
        }
    }

    /**
     * Send a notification of quality inspection status change
     */
    public function sendQualityInspectionStatusChangedNotification(
        QualityInspection $inspection,
        string $oldStatus,
        string $newStatus
    ): void {
        try {
            // Check whether email notifications are enabled
            if (!$this->notificationSettingService->isMethodEnabled('email')) {
                Log::info('Email notification is not enabled, sending is skipped');
                return;
            }

            // Get users who need notifications
            $receivers = $this->notificationSettingService->getReceivers('quality_inspection_updated');
            if (empty($receivers)) {
                Log::info('Notify the recipient without configuration, skip sending');
                return;
            }

            // Get notifications from users
            $users = User::whereIn('email', $receivers)->get();
            if ($users->isEmpty()) {
                Log::warning('No valid notification received user found');
                return;
            }

            Notification::send($users, new QualityInspectionStatusChanged(
                $inspection,
                $oldStatus,
                $newStatus
            ));

            Log::info('The quality inspection status change notification was sent successfully', [
                'inspection_id' => $inspection->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'receivers' => $receivers
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send a notification for quality inspection status change', [
                'error' => $e->getMessage(),
                'inspection_id' => $inspection->id
            ]);
        }
    }

    /**
     * Send inventory warning notice
     */
    public function sendInventoryAlertNotification(string $subject, string $message): void
    {
        try {
            // Check whether email notifications are enabled
            if (!$this->notificationSettingService->isMethodEnabled('email')) {
                Log::info('Email notification is not enabled, sending is skipped');
                return;
            }

            // Get users who need notifications
            $receivers = $this->notificationSettingService->getReceivers('inventory_alert');
            if (empty($receivers)) {
                Log::info('Notify the recipient without configuration, skip sending');
                return;
            }

            // Send email notification
            Mail::raw($message, function (Message $mail) use ($subject, $receivers) {
                $mail->to($receivers)
                    ->subject($subject);
            });

            Log::info('Inventory warning notification was sent successfully', [
                'subject' => $subject,
                'receivers' => $receivers
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send inventory warning notification', [
                'error' => $e->getMessage(),
                'subject' => $subject
            ]);
        }
    }

    /**
     * 向用户发送通用通知
     *
     * @param mixed $users 用户或用户集合
     * @param mixed $notification 通知类
     * @return void
     */
    public function sendNotification($users, $notification): void
    {
        try {
            if (is_null($users)) {
                Log::warning('无效的通知接收者', [
                    'notification' => get_class($notification)
                ]);
                return;
            }
            
            // 发送通知
            Notification::send($users, $notification);
            
            // 记录日志
            $userCount = $users instanceof Collection ? $users->count() : 1;
            Log::info('成功发送通知', [
                'notification' => get_class($notification),
                'user_count' => $userCount
            ]);
        } catch (Exception $e) {
            Log::error('发送通知失败', [
                'error' => $e->getMessage(),
                'notification' => get_class($notification)
            ]);
        }
    }

    /**
     * 获取消息模板
     *
     * @param string $name 模板名称
     * @param string $channel 渠道类型 (email)
     * @param int|null $supplierId 供应商ID，如果提供则优先查找此供应商的模板
     * @return MessageTemplate|null
     */
    public function getMessageTemplate(string $name, string $channel = 'email', ?int $supplierId = null): ?MessageTemplate
    {
        $cacheKey = "message_template:{$name}:{$channel}" . ($supplierId ? ":{$supplierId}" : "");
        
        return Cache::remember($cacheKey, now()->addHour(), function () use ($name, $channel, $supplierId) {
            // 先查找特定供应商的模板
            if ($supplierId) {
                $template = MessageTemplate::active()
                    ->where('name', $name)
                    ->where('channel', $channel)
                    ->where('supplier_id', $supplierId)
                    ->first();
                
                if ($template) {
                    return $template;
                }
            }
            
            // 如果没有找到特定供应商的模板，查找全局模板
            $template = MessageTemplate::active()
                ->where('name', $name)
                ->where('channel', $channel)
                ->whereNull('supplier_id')
                ->first();
            
            if ($template) {
                return $template;
            }
            
            // 最后尝试获取默认模板
            return MessageTemplate::active()
                ->where('name', $name)
                ->where('channel', $channel)
                ->where('is_default', true)
                ->first();
        });
    }
    
    /**
     * 清除模板缓存
     *
     * @param string $name
     * @return void
     */
    public function clearTemplateCache(string $name): void
    {
        // 清除所有可能的渠道缓存
        $channels = ['email'];
        
        foreach ($channels as $channel) {
            // 清除无供应商的缓存
            Cache::forget("message_template:{$name}:{$channel}");
            
            // 清除有供应商ID的缓存 - 这里我们不知道具体有哪些供应商ID，
            // 所以使用模糊匹配方式删除
            $pattern = "message_template:{$name}:{$channel}:*";
            $this->clearCacheByPattern($pattern);
        }
    }
    
    /**
     * 根据模式清除缓存
     * 
     * @param string $pattern
     * @return void
     */
    private function clearCacheByPattern(string $pattern): void
    {
        // 由于Laravel不直接支持通过pattern删除缓存，这里我们假设使用Redis作为缓存驱动
        try {
            if (config('cache.default') === 'redis') {
                $redis = Cache::getRedis();
                $keys = $redis->keys(config('cache.prefix') . ':' . $pattern);
                
                foreach ($keys as $key) {
                    // 移除缓存前缀，获取实际的key
                    $actualKey = str_replace(config('cache.prefix') . ':', '', $key);
                    Cache::forget($actualKey);
                }
            } else {
                // 如果不是Redis，则简单记录无法按模式清除缓存
                Log::info('当前缓存驱动不支持按模式清除缓存，可能需要手动清除相关缓存');
            }
        } catch (\Exception $e) {
            Log::error('清除缓存模式失败', [
                'error' => $e->getMessage(),
                'pattern' => $pattern
            ]);
        }
    }
    
    /**
     * 解析模板内容，替换变量
     *
     * @param string $content 模板内容
     * @param array $data 替换数据
     * @return string 解析后的内容
     */
    private function parseTemplateContent(string $content, array $data): string
    {
        // 替换模板变量
        foreach ($data as $key => $value) {
            // 确保值是字符串类型
            $stringValue = is_null($value) ? '' : (string)$value;
            $content = str_replace('{' . $key . '}', $stringValue, $content);
        }
        
        return $content;
    }

    /**
     * Send a custom email notification without using a template
     *
     * @param string $recipient The email recipient
     * @param string $subject The email subject
     * @param string $content The email content (HTML)
     * @param array $attachments Optional attachments
     * @param bool $skipLogging Whether to skip creating notification log (useful when log is created elsewhere)
     * @param string $type The notification type (default: 'custom_email')
     * @return bool Whether the email was sent successfully
     */
    public function sendCustomEmail(
        string $recipient,
        string $subject,
        string $content,
        array $attachments = [],
        bool $skipLogging = false,
        string $type = 'custom_email'
    ): bool {
        try {
            // // Check if email notifications are enabled (Ignored as requested)
            // if (!$this->notificationSettingService->isMethodEnabled('email')) {
            //     Log::info('Email notification is not enabled, but sending anyway as requested.');
            //     // return false; // Original behavior was to return false
            // }

            // Send the email
            try {
                Mail::send('emails.template', ['content' => $content, 'type' => $type], function ($message) use ($recipient, $subject, $attachments) {
                    $message->to($recipient)
                            ->subject($subject);
                    
                    foreach ($attachments as $attachment) {
                        if (isset($attachment['path']) && file_exists($attachment['path'])) {
                            $message->attach($attachment['path'], [
                                'as' => $attachment['name'] ?? basename($attachment['path']),
                                'mime' => $attachment['mime'] ?? null,
                            ]);
                        }
                    }
                });
                
                // Log the success
                Log::info('Custom email sent successfully', [
                    'recipient' => $recipient,
                    'subject' => $subject,
                    'type' => $type
                ]);
                
                // Create notification log if not skipped
                if (!$skipLogging) {
                    try {
                        $notificationLog = new \App\Models\NotificationLog([
                            'type' => $type,
                            'recipient' => $recipient,
                            'subject' => $subject,
                            'content' => $content,
                            'status' => 'sent'
                        ]);
                        $notificationLog->save();
                    } catch (\Exception $logException) {
                        Log::error('Failed to create notification log', [
                            'error' => $logException->getMessage()
                        ]);
                    }
                }
                
                return true;
            } catch (\Exception $e) {
                Log::error('Failed to send custom email', [
                    'error' => $e->getMessage(),
                    'recipient' => $recipient,
                    'subject' => $subject,
                    'type' => $type
                ]);
                
                // Create failure log if not skipped
                if (!$skipLogging) {
                    try {
                        $notificationLog = new \App\Models\NotificationLog([
                            'type' => $type,
                            'recipient' => $recipient,
                            'subject' => $subject,
                            'content' => $content,
                            'status' => 'failed',
                            'error_message' => $e->getMessage()
                        ]);
                        $notificationLog->save();
                    } catch (\Exception $logException) {
                        Log::error('Failed to create notification failure log', [
                            'error' => $logException->getMessage()
                        ]);
                    }
                }
                
                return false;
            }
        } catch (\Exception $e) {
            // 这个catch块处理的是上面try块中的其他异常
            Log::error('Exception in sendCustomEmail', [
                'error' => $e->getMessage(),
                'recipient' => $recipient,
                'subject' => $subject,
                'type' => $type
            ]);
            
            return false;
        }
    }

    /**
     * Send an email notification using a MessageTemplate object directly
     *
     * @param string $recipient The email recipient
     * @param MessageTemplate $template The message template object
     * @param array $data The data to use for template variable replacement
     * @param array $attachments Optional attachments
     * @param string|null $overrideSubject Optional subject to override the template subject
     * @param bool $skipLogging Whether to skip creating notification log (useful when log is created elsewhere)
     * @param string|null $type The notification type (default: template name)
     * @return bool Whether the email was sent successfully
     */
    public function sendEmailWithTemplate(
        string $recipient,
        MessageTemplate $template,
        array $data = [],
        array $attachments = [],
        ?string $overrideSubject = null,
        bool $skipLogging = false,
        ?string $type = null
    ): bool {
        try {
            // // Check if email notifications are enabled (Ignored as requested)
            // if (!$this->notificationSettingService->isMethodEnabled('email')) {
            //     Log::info('Email notification is not enabled, but sending anyway as requested.');
            //     // return false; // Original behavior was to return false
            // }

            // Get the subject from template or override
            $subject = $overrideSubject ?? $template->subject;

            // Replace variables in subject
            $subject = $this->replaceVariables($subject, $data);

            // Get the content from template and replace variables
            $content = $this->replaceVariables($template->content, $data);
            
            // Use provided type or default to template name
            $notificationType = $type ?? $template->name;

            // Send the email
            try {
                Mail::to($recipient)->send(new TemplateEmail($subject, $content, $attachments, $notificationType));
                
                // Log the success
                Log::info('Email notification sent successfully', [
                    'recipient' => $recipient,
                    'template' => $template->name,
                    'subject' => $subject,
                    'type' => $notificationType
                ]);
                
                // Create notification log if not skipped
                if (!$skipLogging) {
                    try {
                        $notificationLog = new \App\Models\NotificationLog([
                            'type' => $notificationType,
                            'recipient' => $recipient,
                            'subject' => $subject,
                            'content' => $content,
                            'status' => 'sent',
                            'message_template_id' => $template->id
                        ]);
                        $notificationLog->save();
                    } catch (\Exception $logException) {
                        Log::error('Failed to create notification log', [
                            'error' => $logException->getMessage()
                        ]);
                    }
                }
                
                return true;
            } catch (\Exception $e) {
                Log::error('Failed to send email notification', [
                    'error' => $e->getMessage(),
                    'recipient' => $recipient,
                    'template' => $template->name ?? null,
                    'type' => $notificationType
                ]);
                
                // Create failure log if not skipped
                if (!$skipLogging) {
                    try {
                        $notificationLog = new \App\Models\NotificationLog([
                            'type' => $notificationType,
                            'recipient' => $recipient,
                            'subject' => $subject ?? $template->subject,
                            'content' => $content ?? $template->content,
                            'status' => 'failed',
                            'error_message' => $e->getMessage(),
                            'message_template_id' => $template->id
                        ]);
                        $notificationLog->save();
                    } catch (\Exception $logException) {
                        Log::error('Failed to create notification failure log', [
                            'error' => $logException->getMessage()
                        ]);
                    }
                }
                
                return false;
            }
        } catch (\Exception $e) {
            // 这个catch块处理的是上面try块中的其他异常，比如获取模板内容时的异常
            Log::error('Exception in sendEmailWithTemplate', [
                'error' => $e->getMessage(),
                'recipient' => $recipient,
                'template_id' => $template->id ?? null,
                'type' => $type ?? ($template->name ?? 'unknown')
            ]);
            
            return false;
        }
    }

    /**
     * Replace template variables with actual values
     *
     * @param string $content The template content with variables
     * @param array $data The data to replace variables with
     * @return string The content with replaced variables
     */
    public function replaceVariables(string $content, array $data): string
    {
        // 处理 {key} 格式的变量
        foreach ($data as $key => $value) {
            // 确保值是字符串类型
            $stringValue = is_null($value) ? '' : (string)$value;
            $content = str_replace('{' . $key . '}', $stringValue, $content);
        }
        
        // 处理 {{key}} 格式的变量
        $content = preg_replace_callback('/\{\{(.*?)\}\}/', function ($matches) use ($data) {
            $key = trim($matches[1]);
            if (isset($data[$key])) {
                // 确保值是字符串类型
                return is_null($data[$key]) ? '' : (string)$data[$key];
            }
            return $matches[0];
        }, $content);
        
        return $content;
    }

    /**
     * 发送产品创建通知
     */
    public function notifyProductCreated(\App\Models\Product $product): void
    {
        $receivers = \App\Models\Setting::where('key', 'notification_receivers_product_created')
            ->where('group', 'notifications')
            ->first();

        if ($receivers) {
            $emails = json_decode($receivers->value ?? '[]', true);
            $users = \App\Models\User::whereIn('email', $emails)->get();
            \Illuminate\Support\Facades\Notification::send($users, new \App\Notifications\ProductCreated($product));
        }
    }
} 