<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon; // Import Carbon for date handling

class OrderController extends Controller
{
    /**
     * Display a listing of the orders for the authenticated customer.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        /** @var \App\Models\Customer|null $customer */
        $customer = Auth::guard('customer')->user();

        if (!$customer) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        try {
            $orders = SalesOrder::where('customer_id', $customer->id)
                            ->with([
                                // Load only the first item and its necessary relations for summary
                                'items' => function ($query) {
                                    $query->limit(1);
                                },
                                'items.product',
                                'items.product.media'
                            ])
                            ->orderBy('created_at', 'desc') // Show newest orders first
                            ->paginate($request->input('per_page', 10)); // Add pagination

            // Format the orders for the frontend
            $formattedOrders = $orders->getCollection()->transform(function ($order) {
                $firstItem = $order->items->first();
                $firstItemImageUrl = asset('images/placeholder.png'); // Default

                if ($firstItem && $firstItem->product) {
                    // --- Use the same image logic as in show() ---
                     $images = [];
                     if (method_exists($firstItem->product, 'getAllImages')) {
                        $images = $firstItem->product->getAllImages();
                     } elseif ($firstItem->product->relationLoaded('media') && $firstItem->product->media->isNotEmpty()) {
                         $images = $firstItem->product->media->map(function($media) {
                             $path = $media->path ?? null;
                             if ($path && !Str::startsWith($path, 'storage/')) { return asset('storage/' . $path); }
                             elseif ($path) { return asset($path); }
                             return null;
                         })->filter()->toArray();
                     } elseif (!empty($firstItem->product->images) && is_array($firstItem->product->images)) {
                         $images = array_map(function($imagePath) {
                             if ($imagePath && !Str::startsWith($imagePath, 'storage/')) { return asset('storage/' . $imagePath); }
                             elseif ($imagePath) { return asset($imagePath); }
                             return null;
                         }, $firstItem->product->images);
                         $images = array_filter($images);
                     }
                     $firstItemImageUrl = $images[0] ?? asset('images/placeholder.png');
                    // --- End image logic ---
                }
                
                // Get the actual total item count for the order (if needed)
                // This requires a separate query or loading all items initially
                // For now, we just indicate if there are items based on the loaded first one
                $totalItemCount = $order->items_count ?? ($firstItem ? 1 : 0); // Use items_count if preloaded, otherwise check firstItem
                // If you need the exact count without loading all items, consider adding a count relationship
                // E.g., in SalesOrder model: public function itemsCount() { return $this->hasMany(SalesOrderItem::class)->selectRaw('sales_order_id, count(*) as count')->groupBy('sales_order_id'); }
                // Then load with: ->withCount('items')

                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'order_date' => Carbon::parse($order->order_date)->isoFormat('LL'), // Formatted date
                    'order_date_raw' => $order->order_date->toIso8601String(), // Raw date
                    'total_amount' => number_format((float)$order->total_amount, 2),
                    'shipping_status' => $order->shipping_status,
                    'estimated_arrival_date' => $order->estimated_arrival_date ? Carbon::parse($order->estimated_arrival_date)->isoFormat('LL') : null,
                    'estimated_arrival_date_raw' => $order->estimated_arrival_date ? $order->estimated_arrival_date->toDateString() : null, // Raw estimated date (YYYY-MM-DD)
                    'first_item_image_url' => $firstItemImageUrl,
                    'item_count' => $totalItemCount,
                ];
            });

             return response()->json([
                'status' => 'success',
                'data' => $formattedOrders,
                 'pagination' => [ // Include pagination info
                    'total' => $orders->total(),
                    'per_page' => $orders->perPage(),
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'from' => $orders->firstItem(),
                    'to' => $orders->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching order history', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['status' => 'error', 'message' => 'Could not retrieve order history.'], 500);
        }
    }
    
    /**
     * Display the specified order for the authenticated customer.
     *
     * @param  string $orderId The ID or order_number of the order.
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, string $orderId)
    {
        /** @var \App\Models\Customer|null $customer */
        $customer = Auth::guard('customer')->user();

        if (!$customer) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        try {
            // 首先尝试查找临时订单（立即购买流程创建的状态为pending_checkout的订单）
            // 对于临时订单，我们允许用户访问，因为这是从"立即购买"流程过来的
            $query = SalesOrder::where(function($query) use ($orderId, $customer) {
                $query->where('id', $orderId)
                      ->orWhere('order_number', $orderId);
            });
            
            // 先查找临时订单 
            $tempOrder = (clone $query)
                        ->where('status', 'pending_checkout')
                        ->where('customer_id', $customer->id)
                        ->with(['items', 'items.product', 'items.product.media'])
                        ->first();
                        
            if ($tempOrder) {
                $order = $tempOrder; // 使用临时订单
                Log::info('找到临时订单', ['order_id' => $order->id, 'customer_id' => $customer->id]);
            } else {
                // 如果不是临时订单，则必须是用户自己的订单
                $order = $query->where('customer_id', $customer->id)
                             ->with(['items', 'items.product', 'items.product.media'])
                             ->first();
                             
                if (!$order) {
                    Log::warning('订单未找到或访问被拒绝', ['order_id_or_number' => $orderId, 'customer_id' => $customer->id]);
                    return response()->json(['status' => 'error', 'message' => '订单未找到或访问被拒绝。'], 404);
                }
            }
            
            // Format the order items to include necessary product details like image URL
            $order->items->transform(function ($item) {
                $imageUrl = null;
                if ($item->product) { // Check if product exists first

                    // --- 使用 CartController 的图片获取逻辑 ---
                    $images = [];
            
                    // 优先使用getAllImages方法
                    if (method_exists($item->product, 'getAllImages')) {
                        $images = $item->product->getAllImages(); // 假设返回 URL 数组
                    } 
                    // 回退到media关系
                    elseif ($item->product->relationLoaded('media') && $item->product->media->isNotEmpty()) {
                        $images = $item->product->media->map(function($media) {
                             // 假设 $media->path 是相对于 storage/app/public 的路径
                             // 如果 $media->path 包含 'storage/' 前缀，则可能需要移除
                             $path = $media->path ?? null;
                             if ($path && !Str::startsWith($path, 'storage/')) {
                                return asset('storage/' . $path);
                             } elseif ($path) {
                                 // 如果路径已经是 'storage/...', asset() 应该能处理
                                return asset($path);
                             }
                             return null; // 无法获取路径
                        })->filter()->toArray(); // filter() 移除 null 值
                    }
                    // 回退到images数组
                    elseif (!empty($item->product->images) && is_array($item->product->images)) {
                        $images = array_map(function($imagePath) {
                             // 处理可能已包含 'storage/' 的情况
                             if ($imagePath && !Str::startsWith($imagePath, 'storage/')) {
                                 return asset('storage/' . $imagePath);
                             } elseif ($imagePath) {
                                 return asset($imagePath);
                             }
                             return null;
                        }, $item->product->images);
                         $images = array_filter($images); // 移除 null 值
                    }
            
                    if (empty($images)) {
                        $images[] = asset('images/placeholder.png');
                    }
                    // --- 结束 CartController 逻辑 ---
            
                    // OrderController 需要 image_url (单个), 取 CartController 结果的第一张图
                    $imageUrl = $images[0] ?? asset('images/placeholder.png'); // Fallback in case array is empty after all

                } else {
                     // 如果 $item->product 不存在，也设置占位图
                     $imageUrl = asset('images/placeholder.png');
                }

                // Add image URL directly to the item for easier frontend access
                $item->image_url = $imageUrl;
                // Ensure specifications is an object or null, not an empty array from DB potentially
                $item->specifications = $item->specifications ? (object)$item->specifications : null;
                return $item;
            });

            // No need to transform the main order object here usually,
            // but we add the raw dates if they aren't automatically included
            // The $casts in the model might handle this, but let's ensure they are present.
            $orderData = $order->toArray(); // Convert model to array
            $orderData['order_date_raw'] = $order->order_date->toIso8601String();
            $orderData['estimated_arrival_date_raw'] = $order->estimated_arrival_date ? $order->estimated_arrival_date->toDateString() : null;
            // Ensure items also have raw dates if needed (though unlikely needed in detail view?)
            // Re-apply item transformation from before to ensure image_url is correct
            $orderData['items'] = $order->items->map(function ($item) {
                 $transformedItem = $item->toArray(); // Convert item to array
                 $imageUrl = null;
                 if ($item->product) {
                     // --- Use the same image logic as established --- 
                    $images = [];
                    if (method_exists($item->product, 'getAllImages')) {
                        $images = $item->product->getAllImages();
                    } elseif ($item->product->relationLoaded('media') && $item->product->media->isNotEmpty()) {
                        $images = $item->product->media->map(function($media) {
                            $path = $media->path ?? null;
                            if ($path && !Str::startsWith($path, 'storage/')) { return asset('storage/' . $path); }
                            elseif ($path) { return asset($path); }
                            return null;
                        })->filter()->toArray();
                    } elseif (!empty($item->product->images) && is_array($item->product->images)) {
                        $images = array_map(function($imagePath) {
                            if ($imagePath && !Str::startsWith($imagePath, 'storage/')) { return asset('storage/' . $imagePath); }
                            elseif ($imagePath) { return asset($imagePath); }
                            return null;
                        }, $item->product->images);
                        $images = array_filter($images);
                    }
                    $imageUrl = $images[0] ?? asset('images/placeholder.png');
                 } else {
                    $imageUrl = asset('images/placeholder.png');
                 }
                 $transformedItem['image_url'] = $imageUrl;
                 $transformedItem['specifications'] = $item->specifications ? (object)$item->specifications : null;
                 // Remove potentially large objects we don't need in the response
                 unset($transformedItem['product']); 
                 return $transformedItem;
            })->toArray();

            Log::info('Order details retrieved successfully', ['order_id' => $order->id, 'customer_id' => $customer->id]);

            // Return the modified order data array
            return response()->json([
                'status' => 'success', 
                'data' => $orderData // Return the processed array
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching order details', [
                'order_id_or_number' => $orderId,
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['status' => 'error', 'message' => 'Could not retrieve order details.'], 500);
        }
    }

    // Add other methods like index (for order history) later if needed
} 