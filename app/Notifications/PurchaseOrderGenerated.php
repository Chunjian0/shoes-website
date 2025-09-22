<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseOrderGenerated extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * 生成的采购单列表
     *
     * @var array
     */
    protected array $purchases;

    /**
     * 创建新的通知实例
     *
     * @param array $purchases
     */
    public function __construct(array $purchases)
    {
        $this->purchases = $purchases;
    }

    /**
     * 获取通知发送的通道
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * 获取邮件形式的通知
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $count = count($this->purchases);
        $mailMessage = (new MailMessage)
            ->subject("[系统通知] 自动采购单生成")
            ->greeting("您好!")
            ->line("系统自动生成了 {$count} 个采购单，请及时审核。");

        // 添加每个采购单的详情
        foreach ($this->purchases as $purchase) {
            $supplier = $purchase->supplierItems->first()->supplier->name ?? '未知供应商';
            $itemCount = $purchase->items->count();
            $totalAmount = number_format($purchase->final_amount, 2);
            
            $mailMessage->line("- 采购单号: {$purchase->purchase_number}, 供应商: {$supplier}, 商品数量: {$itemCount}, 总金额: {$totalAmount} 元");
        }

        return $mailMessage
            ->action('查看详情', url('/purchases'))
            ->line('感谢您使用我们的系统!');
    }

    /**
     * 获取数据库形式的通知
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toDatabase($notifiable): array
    {
        $count = count($this->purchases);
        $purchaseNumbers = collect($this->purchases)->pluck('purchase_number')->implode(', ');
        
        return [
            'message' => "系统自动生成了 {$count} 个采购单，请及时审核。",
            'purchase_count' => $count,
            'purchase_numbers' => $purchaseNumbers,
            'purchase_ids' => collect($this->purchases)->pluck('id')->toArray(),
            'url' => '/purchases',
        ];
    }

    /**
     * 获取数组形式的通知
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable): array
    {
        return [
            'purchase_count' => count($this->purchases),
            'purchase_ids' => collect($this->purchases)->pluck('id')->toArray(),
        ];
    }
} 