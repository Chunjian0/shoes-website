<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockProductsRemovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * 已移除的产品
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
     * 创建通知实例
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
     * 获取通知发送渠道
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * 获取邮件通知的表示方式
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $mailMessage = (new MailMessage)
            ->subject('首页低库存产品已自动移除')
            ->greeting('您好！')
            ->line('系统自动从首页移除了以下低库存产品：')
            ->line('库存阈值设置为: ' . $this->threshold);

        // 添加移除的产品列表
        foreach ($this->removedProducts as $index => $product) {
            if ($index < 10) {  // 限制展示数量，避免邮件过长
                $mailMessage->line("{$product['name']} (SKU: {$product['sku']}) - 当前库存: {$product['stock']}");
            }
        }

        // 如果产品数量超过10个，添加提示
        if (count($this->removedProducts) > 10) {
            $remaining = count($this->removedProducts) - 10;
            $mailMessage->line("... 以及其他 {$remaining} 个产品");
        }

        return $mailMessage
            ->action('查看首页管理', url('/admin/homepage'))
            ->line('感谢您使用我们的系统！');
    }

    /**
     * 获取数据库通知的表示方式
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'title' => '首页低库存产品已自动移除',
            'message' => '系统已移除 ' . count($this->removedProducts) . ' 个低库存产品',
            'threshold' => $this->threshold,
            'removed_count' => count($this->removedProducts),
            'action_url' => '/admin/homepage',
        ];
    }

    /**
     * 获取通知的数组表示形式
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'removed_products' => $this->removedProducts,
            'threshold' => $this->threshold,
        ];
    }
} 