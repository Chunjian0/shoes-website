<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\NotificationSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class NotificationSettingController extends Controller
{
    public function __construct(
        private readonly NotificationSettingService $notificationSettingService
    ) {
        $this->middleware(['auth', 'permission:manage notification settings']);
    }

    /**
     * Show notification settings page
     */
    public function index()
    {
        try {
            // Get all optional users
            $users = $this->notificationSettingService->getAvailableReceivers();

            // Get a list of notification types
            $notificationTypes = $this->notificationSettingService->getNotificationTypes();

            // Get the current notification settings
            $settings = $this->notificationSettingService->getSettings();
            $receivers = $settings['notification_receivers'] ?? [];

            return view('settings.notifications', [
                'users' => $users,
                'receivers' => $receivers,
                'notificationTypes' => $notificationTypes,
                'emailEnabled' => $settings['email_notifications_enabled'] ?? true,
            ]);
        } catch (\Exception $e) {
            Log::error('Loading notification settings page failed', [
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Loading notification settings page failed:' . $e->getMessage());
        }
    }

    /**
     * Update notification settings
     */
    public function update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'receivers' => 'required|array',
                'receivers.*' => 'array',
                'receivers.*.*' => 'email'
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            // Update settings
            $success = $this->notificationSettingService->updateReceivers($request->receivers);

            if (!$success) {
                return back()->with('error', 'Update notification settings failed, please try again.');
            }

            Log::info('Notification settings are updated successfully', [
                'updated_by' => auth()->id(),
                'receivers' => $request->receivers
            ]);

            return back()->with('success', 'Notification settings updated successfully!');
        } catch (\Exception $e) {
            Log::error('Update notification settings failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            return back()->with('error', 'Update notification settings failed:' . $e->getMessage());
        }
    }

    /**
     * Switch email notification status
     */
    public function toggleEmail(Request $request)
    {
        try {
            $enabled = $request->boolean('enabled');
            $success = $this->notificationSettingService->updateNotificationMethod('email', $enabled);

            if (!$success) {
                return response()->json(['error' => 'Operation failed'], 500);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Failed to switch email notification status:' . $e->getMessage());
            return response()->json(['error' => 'Operation failed'], 500);
        }
    }
} 