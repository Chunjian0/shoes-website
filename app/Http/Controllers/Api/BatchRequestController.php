<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BatchRequestController extends Controller
{
    /**
     * 处理批量API请求
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function process(Request $request)
    {
        // 验证请求格式
        $request->validate([
            '*' => 'required|array',
            '*.id' => 'required|string',
            '*.path' => 'required|string',
            '*.method' => 'required|string|in:GET,POST,PUT,DELETE,PATCH',
            '*.params' => 'nullable|array',
            '*.headers' => 'nullable|array',
        ]);

        $batchRequests = $request->all();
        $results = [];

        // 限制批处理的最大请求数量
        $maxRequests = config('api.batch_max_requests', 10);
        if (count($batchRequests) > $maxRequests) {
            return response()->json([
                'success' => false,
                'message' => "Batch request exceeds maximum allowed requests ($maxRequests)",
            ], 400);
        }

        foreach ($batchRequests as $batchRequest) {
            $results[] = $this->processSingleRequest($batchRequest);
        }

        // 记录批处理性能指标
        $this->logBatchPerformance($results);

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    /**
     * 处理单个请求
     *
     * @param array $batchRequest
     * @return array
     */
    protected function processSingleRequest(array $batchRequest)
    {
        $id = $batchRequest['id'];
        $path = $batchRequest['path'];
        $method = strtoupper($batchRequest['method']);
        $params = $batchRequest['params'] ?? [];
        $headers = $batchRequest['headers'] ?? [];

        // 确保路径是绝对的并以/api开头
        if (!Str::startsWith($path, '/')) {
            $path = '/' . $path;
        }

        // 安全检查：只允许处理API请求
        if (!Str::startsWith($path, '/api/') && $path !== '/api') {
            return [
                'id' => $id,
                'statusCode' => 403,
                'success' => false,
                'error' => [
                    'message' => 'Only API endpoints are allowed in batch requests',
                    'code' => 'FORBIDDEN_ENDPOINT',
                ],
            ];
        }

        // 禁止递归批处理
        if (Str::contains($path, '/api/batch')) {
            return [
                'id' => $id,
                'statusCode' => 403,
                'success' => false,
                'error' => [
                    'message' => 'Recursive batch requests are not allowed',
                    'code' => 'RECURSIVE_BATCH',
                ],
            ];
        }

        try {
            // 记录请求开始时间
            $startTime = microtime(true);

            // 创建内部请求
            $request = Request::create(
                $path, 
                $method, 
                $method === 'GET' ? $params : [], 
                [], // cookies
                [], // files
                $this->prepareServerVariables($headers),
                $method !== 'GET' ? json_encode($params) : null
            );

            // 设置请求头
            foreach ($headers as $key => $value) {
                $request->headers->set($key, $value);
            }

            // 从当前请求复制认证信息
            $this->copyAuthenticationFromCurrentRequest($request);

            // 执行请求
            $response = app()->handle($request);
            $statusCode = $response->getStatusCode();
            
            // 解析响应内容
            $content = $response->getContent();
            $data = null;
            
            if ($content && $this->isJsonResponse($response)) {
                $data = json_decode($content, true);
            } else {
                $data = [
                    'content' => $content,
                    'format' => 'text',
                ];
            }

            // 记录请求结束时间和时长
            $endTime = microtime(true);
            $duration = ($endTime - $startTime) * 1000; // 毫秒

            // 构建结果
            $result = [
                'id' => $id,
                'statusCode' => $statusCode,
                'success' => $statusCode >= 200 && $statusCode < 300,
                'duration' => round($duration, 2),
            ];

            if ($result['success']) {
                $result['data'] = $data;
            } else {
                $result['error'] = [
                    'message' => $data['message'] ?? 'Request failed with status code ' . $statusCode,
                    'details' => $data,
                ];
            }

            return $result;
        } catch (\Exception $e) {
            // 处理异常
            if ($e instanceof HttpException) {
                $statusCode = $e->getStatusCode();
            } else {
                $statusCode = 500;
            }

            return [
                'id' => $id,
                'statusCode' => $statusCode,
                'success' => false,
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'details' => app()->environment('production') ? null : [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ],
                ],
            ];
        }
    }

    /**
     * 准备服务器变量
     *
     * @param array $headers
     * @return array
     */
    protected function prepareServerVariables(array $headers = [])
    {
        $server = [];

        foreach ($headers as $key => $value) {
            $key = strtoupper(str_replace('-', '_', $key));
            $server["HTTP_$key"] = $value;
        }

        return $server;
    }

    /**
     * 从当前请求复制认证信息
     *
     * @param Request $request
     * @return void
     */
    protected function copyAuthenticationFromCurrentRequest(Request $request)
    {
        $currentRequest = request();

        // 复制授权令牌
        if ($currentRequest->bearerToken()) {
            $request->headers->set('Authorization', 'Bearer ' . $currentRequest->bearerToken());
        }

        // 复制会话信息
        if ($currentRequest->hasSession()) {
            $request->setLaravelSession($currentRequest->session());
        }

        // 复制用户信息
        if ($currentRequest->user()) {
            app('auth')->setUser($currentRequest->user());
        }
    }

    /**
     * 检查响应是否为JSON
     *
     * @param Response $response
     * @return bool
     */
    protected function isJsonResponse(Response $response)
    {
        $contentType = $response->headers->get('Content-Type');
        return $contentType && Str::contains($contentType, ['/json', '+json']);
    }

    /**
     * 记录批处理性能指标
     *
     * @param array $results
     * @return void
     */
    protected function logBatchPerformance(array $results)
    {
        if (!config('app.debug')) {
            return;
        }

        $totalRequests = count($results);
        $successfulRequests = count(array_filter($results, fn($r) => $r['success'] ?? false));
        $totalDuration = array_sum(array_column($results, 'duration'));
        $averageDuration = $totalRequests > 0 ? $totalDuration / $totalRequests : 0;

        Log::debug('Batch request performance', [
            'total_requests' => $totalRequests,
            'successful_requests' => $successfulRequests,
            'failed_requests' => $totalRequests - $successfulRequests,
            'total_duration_ms' => round($totalDuration, 2),
            'average_duration_ms' => round($averageDuration, 2),
        ]);
    }
} 