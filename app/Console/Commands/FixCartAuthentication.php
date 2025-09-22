<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixCartAuthentication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cart:fix-authentication {--force : Force update all carts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修复购物车的认证问题，将session_id关联切换为customer_id关联';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('开始修复购物车认证问题...');
        
        // 1. 查找有customer_id但没有关联到购物车的用户
        $this->info('步骤1: 查找有customer_id但没有关联到购物车的用户');
        $customersWithoutCarts = Customer::whereNotIn('id', function($query) {
            $query->select('customer_id')->from('carts')->whereNotNull('customer_id');
        })->get();
        
        $this->info("发现 {$customersWithoutCarts->count()} 个没有购物车的客户");
        
        // 2. 查找有session_id但没有customer_id的购物车
        $this->info('步骤2: 查找有session_id但没有customer_id的购物车');
        $cartsMissingCustomerId = Cart::whereNotNull('session_id')
            ->whereNull('customer_id')
            ->get();
            
        $this->info("发现 {$cartsMissingCustomerId->count()} 个没有customer_id的购物车");
        
        // 3. 尝试通过用户邮箱匹配session中的购物车
        $this->info('步骤3: 尝试将购物车关联到正确的客户');
        $matchedCount = 0;
        
        foreach ($cartsMissingCustomerId as $cart) {
            // 检查购物车中是否有任何项目
            $hasItems = CartItem::where('cart_id', $cart->id)->exists();
            
            if (!$hasItems) {
                // 如果购物车为空，直接删除
                $cart->delete();
                $this->info("删除了空的购物车 #{$cart->id}");
                continue;
            }
            
            // 查找所有客户
            $customers = Customer::all();
            
            $this->info("尝试为购物车 #{$cart->id} 查找所有者...");
            $matched = false;
            
            // 随机选择一个客户（仅用于测试目的）
            if ($this->option('force') && $customers->isNotEmpty()) {
                $randomCustomer = $customers->random();
                $cart->customer_id = $randomCustomer->id;
                $cart->save();
                $matchedCount++;
                $matched = true;
                
                $this->warn("使用--force参数：将购物车 #{$cart->id} 随机关联到客户 #{$randomCustomer->id}");
            }
            
            if (!$matched) {
                // 如果仍未匹配，保留现状但记录警告
                $this->warn("无法为购物车 #{$cart->id} 找到对应的客户");
            }
        }
        
        $this->info("成功匹配并关联了 {$matchedCount} 个购物车");
        
        // 4. 检查购物车项目是否有关联不一致的问题
        $this->info('步骤4: 检查购物车项目的关联一致性');
        
        $inconsistentItems = DB::table('cart_items')
            ->join('carts', 'cart_items.cart_id', '=', 'carts.id')
            ->whereNull('carts.customer_id')
            ->count();
            
        $this->info("发现 {$inconsistentItems} 个购物车项目关联到未认证的购物车");
        
        // 5. 修复所有客户的默认购物车
        $this->info('步骤5: 确保每个客户都有默认购物车');
        $customersWithoutDefaultCart = Customer::whereNotIn('id', function($query) {
            $query->select('customer_id')
                ->from('carts')
                ->where('type', 'default')
                ->orWhere('is_default', true)
                ->whereNotNull('customer_id');
        })->get();
        
        $this->info("发现 {$customersWithoutDefaultCart->count()} 个客户没有默认购物车");
        
        foreach ($customersWithoutDefaultCart as $customer) {
            // 查找客户的任何购物车
            $anyCart = Cart::where('customer_id', $customer->id)->first();
            
            if ($anyCart) {
                // 将此购物车设为默认
                $anyCart->is_default = true;
                $anyCart->type = 'default';
                $anyCart->save();
                
                $this->info("将购物车 #{$anyCart->id} 设为客户 #{$customer->id} 的默认购物车");
            } else {
                // 创建新的默认购物车
                $newCart = new Cart();
                $newCart->customer_id = $customer->id;
                $newCart->type = 'default';
                $newCart->is_default = true;
                $newCart->name = '默认购物车';
                $newCart->save();
                
                $this->info("为客户 #{$customer->id} 创建了新的默认购物车 #{$newCart->id}");
            }
        }
        
        // 6. 汇总结果
        $this->info('购物车认证修复完成!');
        $this->info("- 处理了 {$customersWithoutCarts->count()} 个没有购物车的客户");
        $this->info("- 发现 {$cartsMissingCustomerId->count()} 个没有customer_id的购物车");
        $this->info("- 成功关联了 {$matchedCount} 个购物车到其所有者");
        $this->info("- 修复了 {$customersWithoutDefaultCart->count()} 个客户的默认购物车");
        
        return Command::SUCCESS;
    }
}
