<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockProductRemovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * 被移除的产品数据
     *
     * @var array
     */
    protected $removedProducts;

    /**
     * 库存阈值
     *
     * @var int
     */
    protected $threshold;

    /**
     * 创建新的通知实例
     *
     * @param array $removedProducts
     * @param int $threshold
     * @return void
     */
    public function __construct(array $removedProducts, int $threshold)
    {
        $this->removedProducts = $removedProducts;
        $this->threshold = $threshold;
    }

    /**
     * 获取通知的发送通道
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * 获取邮件通知的表现形式
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $count = count($this->removedProducts);
        $mailMessage = (new MailMessage)
            ->subject("【系统通知】{$count}个低库存产品已从首页移除")
            ->greeting('库存预警通知')
            ->line("系统已自动将{$count}个库存低于{$this->threshold}的产品从首页移除。")
            ->line('被移除的产品列表:');

        // 添加被移除产品的详细信息
        foreach ($this->removedProducts as $product) {
            $mailMessage->line("• {$product['name']} (SKU: {$product['sku']}) - 当前库存: {$product['stock']}");
        }

        return $mailMessage
            ->action('查看详情', url('/admin/homepage/low-stock-products'))
            ->line('请及时处理这些产品的库存情况，或手动将其添加回首页。');
    }

    /**
     * 获取数据库通知的数组表示
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'message' => count($this->removedProducts) . '个低库存产品已从首页移除',
            'threshold' => $this->threshold,
            'removed_products' => $this->removedProducts,
            'type' => 'low_stock_removal'
        ];
    }
} 