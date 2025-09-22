<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Api\CartController;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductTemplate;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TestCartController extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:cart';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '测试购物车控制器功能';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('开始测试购物车控制器...');
        
        // 清理测试数据
        $this->cleanUpTestData();
        
        // 测试添加商品到购物车
        $this->testAddToCart();
        
        // 测试更新购物车
        $this->testUpdateCart();
        
        // 测试获取购物车
        $this->testGetCart();
        
        // 测试删除购物车项
        $this->testRemoveFromCart();
        
        $this->info('购物车控制器测试完成。');
        
        return 0;
    }
    
    /**
     * 清理测试数据
     */
    private function cleanUpTestData()
    {
        $this->info('清理测试数据...');
        
        // 删除测试会话的购物车及项目
        $sessionId = 'test-session-' . Str::random(10);
        
        $carts = Cart::where('session_id', 'like', 'test-session-%')->get();
        foreach ($carts as $cart) {
            CartItem::where('cart_id', $cart->id)->delete();
            $cart->delete();
        }
        
        $this->info('测试数据已清理。');
        
        return $sessionId;
    }
    
    /**
     * 测试添加商品到购物车
     */
    private function testAddToCart()
    {
        $this->info('测试添加商品到购物车...');
        
        // 获取一个产品用于测试
        $product = Product::first();
        if (!$product) {
            $this->error('未找到产品，无法进行测试。');
            return;
        }
        
        // 获取关联的模板（如果有）
        $template = ProductTemplate::whereHas('linkedProducts', function($query) use ($product) {
            $query->where('products.id', $product->id);
        })->first();
        
        $templateId = $template ? $template->id : null;
        $parameterGroup = null;
        
        if ($template) {
            // 获取该产品的参数组
            $pivot = DB::table('product_template_product')
                ->where('product_template_id', $template->id)
                ->where('product_id', $product->id)
                ->first();
                
            $parameterGroup = $pivot ? $pivot->parameter_group : null;
        }
        
        $this->info("测试产品ID: {$product->id}, 名称: {$product->name}");
        $this->info("关联模板ID: " . ($templateId ?? 'none'));
        $this->info("参数组: " . ($parameterGroup ?? 'none'));
        
        // 创建模拟请求
        $request = new Request([
            'product_id' => $product->id,
            'quantity' => 1,
            'cart_type' => 'default',
        ]);
        
        if ($templateId) {
            $request->merge(['template_id' => $templateId]);
        }
        
        if ($parameterGroup) {
            $request->merge(['parameter_group' => $parameterGroup]);
        }
        
        // 添加会话ID
        $sessionId = 'test-session-' . Str::random(10);
        $request->cookies->set('cart_session_id', $sessionId);
        
        $controller = new CartController();
        $response = $controller->store($request);
        
        // 解析响应
        $content = json_decode($response->getContent(), true);
        
        if ($content['success']) {
            $this->info('商品添加到购物车成功。');
            $this->info("购物车ID: {$content['data']['cart_id']}");
            $this->info("商品数量: {$content['data']['item_count']}");
            $this->info("购物车总额: {$content['data']['total']}");
        } else {
            $this->error('添加商品到购物车失败: ' . $content['message']);
        }
    }
    
    /**
     * 测试更新购物车
     */
    private function testUpdateCart()
    {
        $this->info('测试更新购物车...');
        
        // 获取测试会话的购物车
        $sessionId = 'test-session-' . Str::random(10);
        $cart = Cart::where('session_id', 'like', 'test-session-%')->first();
        
        if (!$cart) {
            $this->error('未找到测试购物车，无法进行更新测试。');
            return;
        }
        
        // 获取购物车中的第一个项目
        $cartItem = CartItem::where('cart_id', $cart->id)->first();
        
        if (!$cartItem) {
            $this->error('未找到购物车项目，无法进行更新测试。');
            return;
        }
        
        $this->info("测试购物车项ID: {$cartItem->id}, 当前数量: {$cartItem->quantity}");
        
        // 创建模拟请求 
        $request = new Request([
            'quantity' => $cartItem->quantity + 1,
            'cart_id' => $cart->id
        ]);
        
        // 添加会话ID
        $request->cookies->set('cart_session_id', $sessionId);
        
        $controller = new CartController();
        $response = $controller->update($request, $cartItem->id);
        
        // 解析响应
        $content = json_decode($response->getContent(), true);
        
        if ($content['success']) {
            $this->info('购物车更新成功。');
            
            // 重新查询购物车项目，确认数量已更新
            $updatedItem = CartItem::find($cartItem->id);
            $this->info("更新后数量: {$updatedItem->quantity}");
        } else {
            $this->error('更新购物车失败: ' . $content['message']);
        }
    }
    
    /**
     * 测试获取购物车内容
     */
    private function testGetCart()
    {
        $this->info('测试获取购物车...');
        
        // 获取测试会话的购物车
        $sessionId = 'test-session-' . Str::random(10);
        $cart = Cart::where('session_id', 'like', 'test-session-%')->first();
        
        if (!$cart) {
            $this->error('未找到测试购物车，无法获取内容。');
            return;
        }
        
        // 创建模拟请求
        $request = new Request([
            'cart_id' => $cart->id
        ]);
        
        // 添加会话ID
        $request->cookies->set('cart_session_id', $sessionId);
        
        $controller = new CartController();
        $response = $controller->index($request);
        
        // 解析响应
        $content = json_decode($response->getContent(), true);
        
        if ($content['success']) {
            $this->info('购物车内容获取成功。');
            $this->info("购物车数量: " . count($content['data']['carts']));
            
            if (!empty($content['data']['carts'])) {
                $firstCart = $content['data']['carts'][0];
                $this->info("购物车ID: {$firstCart['id']}");
                $this->info("商品数量: " . count($firstCart['items']));
                
                if (!empty($firstCart['items'])) {
                    $firstItem = $firstCart['items'][0];
                    $this->info("第一个商品: {$firstItem['name']}");
                    $this->info("数量: {$firstItem['quantity']}");
                    $this->info("单价: {$firstItem['price']}");
                    $this->info("小计: {$firstItem['subtotal']}");
                    
                    // 检查是否有模板信息
                    if (isset($firstItem['template'])) {
                        $this->info("关联模板: {$firstItem['template']['name']}");
                    }
                    
                    // 检查是否有参数组信息
                    if (isset($firstItem['parameter_group'])) {
                        $this->info("参数组: {$firstItem['parameter_group']}");
                    }
                }
            }
        } else {
            $this->error('获取购物车内容失败: ' . $content['message']);
        }
    }
    
    /**
     * 测试从购物车中删除商品
     */
    private function testRemoveFromCart()
    {
        $this->info('测试从购物车中删除商品...');
        
        // 获取测试会话的购物车
        $sessionId = 'test-session-' . Str::random(10);
        $cart = Cart::where('session_id', 'like', 'test-session-%')->first();
        
        if (!$cart) {
            $this->error('未找到测试购物车，无法进行删除测试。');
            return;
        }
        
        // 获取购物车中的第一个项目
        $cartItem = CartItem::where('cart_id', $cart->id)->first();
        
        if (!$cartItem) {
            $this->error('未找到购物车项目，无法进行删除测试。');
            return;
        }
        
        $this->info("要删除的购物车项ID: {$cartItem->id}");
        
        // 创建模拟请求
        $request = new Request([
            'cart_id' => $cart->id
        ]);
        
        // 添加会话ID
        $request->cookies->set('cart_session_id', $sessionId);
        
        $controller = new CartController();
        $response = $controller->destroy($request, $cartItem->id);
        
        // 解析响应
        $content = json_decode($response->getContent(), true);
        
        if ($content['success']) {
            $this->info('商品从购物车中删除成功。');
            
            // 确认项目已被删除
            $deletedItem = CartItem::find($cartItem->id);
            if (!$deletedItem) {
                $this->info('购物车项已成功删除。');
            } else {
                $this->error('购物车项删除失败，仍然存在。');
            }
        } else {
            $this->error('从购物车中删除商品失败: ' . $content['message']);
        }
    }
}
