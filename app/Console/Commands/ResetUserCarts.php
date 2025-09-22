<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResetUserCarts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cart:reset {user_email? : 用户邮箱，如不提供则交互式询问}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重置用户的购物车状态，用于调试登录问题';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('购物车重置工具');
        $this->line('此工具将重置用户的购物车状态，用于调试。');
        $this->line('');
        
        // 获取用户邮箱
        $email = $this->argument('user_email');
        
        if (!$email) {
            // 如果没有提供邮箱，列出所有客户
            $customers = Customer::orderBy('id', 'asc')->get();
            
            $this->info('系统中的客户列表:');
            foreach ($customers as $index => $customer) {
                $cartsCount = Cart::where('customer_id', $customer->id)->count();
                $this->line(sprintf("%d) %s (%s) - %d个购物车", 
                    $index + 1, 
                    $customer->name, 
                    $customer->email,
                    $cartsCount
                ));
            }
            
            // 让用户选择
            $selection = $this->ask('选择要重置购物车的客户 (输入数字)');
            
            if (is_numeric($selection) && $selection > 0 && $selection <= $customers->count()) {
                $customer = $customers[$selection - 1];
                $email = $customer->email;
            } else {
                $this->error('无效的选择');
                return Command::FAILURE;
            }
        } else {
            // 验证提供的邮箱是否存在
            $customer = Customer::where('email', $email)->first();
            
            if (!$customer) {
                $this->error("找不到邮箱为 {$email} 的客户");
                return Command::FAILURE;
            }
        }
        
        // 确认操作
        $this->info("准备重置客户 {$customer->name} ({$email}) 的购物车");
        
        if (!$this->confirm('确定要继续吗？此操作将删除该用户的所有购物车数据！')) {
            $this->info('操作已取消');
            return Command::SUCCESS;
        }
        
        // 备份当前数据
        $carts = Cart::where('customer_id', $customer->id)->get();
        $cartIds = $carts->pluck('id')->toArray();
        $cartItems = CartItem::whereIn('cart_id', $cartIds)->get();
        
        $this->info("找到 {$carts->count()} 个购物车，共 {$cartItems->count()} 个商品");
        
        // 创建备份数据
        $backup = [
            'timestamp' => now()->toDateTimeString(),
            'customer' => $customer->toArray(),
            'carts' => $carts->toArray(),
            'cart_items' => $cartItems->toArray()
        ];
        
        $backupFile = storage_path('cart_backups/' . $customer->id . '_' . now()->format('Y-m-d_H-i-s') . '.json');
        
        // 确保目录存在
        if (!file_exists(dirname($backupFile))) {
            mkdir(dirname($backupFile), 0755, true);
        }
        
        file_put_contents($backupFile, json_encode($backup, JSON_PRETTY_PRINT));
        
        $this->info("已创建备份: " . basename($backupFile));
        
        // 删除购物车项目
        CartItem::whereIn('cart_id', $cartIds)->delete();
        $this->info("已删除 {$cartItems->count()} 个购物车项目");
        
        // 删除购物车
        Cart::where('customer_id', $customer->id)->delete();
        $this->info("已删除 {$carts->count()} 个购物车");
        
        // 创建新的默认购物车
        $newCart = new Cart();
        $newCart->customer_id = $customer->id;
        $newCart->type = 'default';
        $newCart->is_default = true;
        $newCart->name = '默认购物车';
        $newCart->session_id = null; // 不设置session_id，只使用customer_id
        $newCart->save();
        
        $this->info("已创建新的默认购物车 #{$newCart->id}");
        
        // 完成
        $this->newLine();
        $this->info("操作完成！客户 {$customer->name} 的购物车已重置");
        $this->line("备份文件: " . basename($backupFile));
        $this->line("该客户现在有一个空的默认购物车，可以重新测试登录功能");
        
        return Command::SUCCESS;
    }
}
