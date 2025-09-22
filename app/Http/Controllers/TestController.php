<?php

namespace App\Http\Controllers;

use App\Mail\TestMail;
use App\Models\MessageTemplate;
use App\Models\NotificationLog;
use App\Services\NotificationService;
use App\Services\NotificationSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    protected $notificationService;
    protected $notificationSettingService;
    
    public function __construct(
        NotificationService $notificationService,
        NotificationSettingService $notificationSettingService
    )
    {
        $this->notificationService = $notificationService;
        $this->notificationSettingService = $notificationSettingService;
    }
    
    /**
     * Send a test email and log it to the notification_logs table
     */
    public function sendTestEmail(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'recipient' => 'required|email',
                'type' => 'nullable|string',
                'subject' => 'nullable|string',
                'content' => 'nullable|string',
                'message_template_id' => 'nullable|exists:message_templates,id',
                'test_data' => 'nullable|json',
                'log_id' => 'nullable|integer|exists:notification_logs,id',
                'user_id' => 'nullable|integer|exists:users,id',
            ]);
            
            $recipient = $validated['recipient'];
            $type = $validated['type'] ?? 'test_mail';
            $subject = $validated['subject'] ?? 'Test Email';
            $content = $validated['content'] ?? 'This is a test email message from the system.';
            $testData = json_decode($validated['test_data'] ?? '{}', true);
            $logId = $validated['log_id'] ?? null;
            $userId = $validated['user_id'] ?? null;
            
            // 测试邮件不需要检查电子邮件通知是否启用，应该总是可以发送
            
            // Check if a message template was selected
            if (!empty($validated['message_template_id'])) {
                $template = MessageTemplate::findOrFail($validated['message_template_id']);
                
                // Send email through NotificationService with template
                $sent = $this->notificationService->sendEmailWithTemplate(
                    $recipient,
                    $template,
                    $testData,
                    [], // Empty array for attachments
                    $subject, // Use the subject from request
                    true, // Skip logging as we're handling it separately
                    $type // 传递类型参数
                );
            } else {
                // No template selected, send direct test email
                $sent = $this->notificationService->sendCustomEmail(
                    $recipient,
                    $subject,
                    $content,
                    [], // Empty array for attachments
                    true,  // Skip logging as we're handling it separately
                    $type // 传递类型参数
                );
            }
            
            if ($sent) {
                Log::info('Test email sent', [
                    'recipient' => $recipient,
                    'type' => $type,
                    'template_id' => $validated['message_template_id'] ?? null,
                    'log_id' => $logId
                ]);
                
                // Update notification log if log_id is provided
                if ($logId) {
                    $log = NotificationLog::find($logId);
                    if ($log) {
                        $log->status = 'sent';
                        $log->sent_at = now();
                        $log->save();
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Test email sent successfully',
                    'log_id' => $logId
                ]);
            } else {
                throw new \Exception('Email sending failed');
            }
        } catch (\Exception $e) {
            Log::error('Failed to send test email', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'log_id' => $request->input('log_id')
            ]);
            
            // Update existing log if log_id is provided
            if ($request->has('log_id')) {
                try {
                    $log = NotificationLog::find($request->input('log_id'));
                    if ($log) {
                        $log->status = 'failed';
                        $log->error_message = $e->getMessage();
                        $log->save();
                    }
                } catch (\Exception $logException) {
                    Log::error('Failed to update notification log', [
                        'error' => $logException->getMessage(),
                        'log_id' => $request->input('log_id')
                    ]);
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage(),
                'log_id' => $request->input('log_id')
            ], 500);
        }
    }
}