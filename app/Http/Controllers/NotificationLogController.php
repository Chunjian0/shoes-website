<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationLogController extends Controller
{
    /**
     * 获取通知日志列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = NotificationLog::query()->with('user');
            
            // 应用筛选条件
            if ($request->has('type') && !empty($request->type)) {
                $query->where('type', $request->type);
            }
            
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }
            
            if ($request->has('recipient') && !empty($request->recipient)) {
                $query->where('recipient', $request->recipient);
            }
            
            if ($request->has('user_id') && !empty($request->user_id)) {
                $query->where('user_id', $request->user_id);
            }
            
            // 排序
            $query->orderBy('created_at', 'desc');
            
            // 分页
            $perPage = $request->input('per_page', 10);
            $logs = $query->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $logs
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch notification logs', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notification logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 创建通知日志条目
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'subject' => 'required|string',
                'type' => 'required|string',
                'content' => 'required|string',
                'status' => 'required|string|in:pending,sending,sent,failed',
                'error_message' => 'nullable|string',
                'message_template_id' => 'nullable|exists:message_templates,id',
            ]);
            
            // 获取用户的邮箱，用于记录
            $user = User::find($validated['user_id']);
            $validated['recipient'] = $user ? $user->email : 'unknown';
            
            $log = NotificationLog::create($validated);
            
            Log::info('Email notification logged successfully', [
                'subject' => $validated['subject'],
                'recipients' => [$validated['user_id']]
            ]);
            
            return response()->json([
                'success' => true,
                'id' => $log->id,
                'log_id' => $log->id,
                'message' => 'Notification log entry created successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create notification log entry', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create notification log entry: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 更新通知日志条目状态
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|string|in:pending,sending,sent,failed',
                'error_message' => 'nullable|string',
            ]);
            
            $log = NotificationLog::findOrFail($id);
            $log->update($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Notification log entry updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update notification log entry', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'log_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update notification log entry: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取单个通知日志详情
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $log = NotificationLog::with('user')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $log
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch notification log details', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'log_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notification log details: ' . $e->getMessage()
            ], 500);
        }
    }
} 