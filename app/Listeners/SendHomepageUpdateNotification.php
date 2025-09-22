<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\HomepageUpdatedEvent;
use App\Notifications\HomepageUpdatedNotification;
use App\Services\NotificationSettingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Models\User;

class SendHomepageUpdateNotification implements ShouldQueue
{
    /**
     * 通知设置服务
     */
    protected $notificationSettingService;
    
    /**
     * 创建监听器实例
     *
     * @param NotificationSettingService $notificationSettingService
     * @return void
     */
    public function __construct(NotificationSettingService $notificationSettingService)
    {
        $this->notificationSettingService = $notificationSettingService;
    }

    /**
     * 处理事件
     *
     * @param HomepageUpdatedEvent $event
     * @return void
     */
    public function handle(HomepageUpdatedEvent $event)
    {
        try {
            // 根据通知类型获取接收人
            $receivers = $this->getReceivers($event->updateType);
            
            if (empty($receivers)) {
                Log::info('No recipients configured for homepage update notification', [
                    'type' => $event->updateType
                ]);
                return;
            }
            
            // 发送通知
            Notification::route('mail', $receivers)
                ->notify(new HomepageUpdatedNotification(
                    $event->updateType,
                    $event->updatedBy,
                    $event->data
                ));
            
            Log::info('Homepage update notification sent successfully', [
                'type' => $event->updateType,
                'receivers' => $receivers
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send homepage update notification', [
                'error' => $e->getMessage(),
                'type' => $event->updateType
            ]);
        }
    }
    
    /**
     * 获取通知接收人
     *
     * @param string $updateType
     * @return array
     */
    protected function getReceivers(string $updateType): array
    {
        // 进行类型映射，将事件类型映射到通知设置中的类型
        $mappedType = $this->mapEventTypeToNotificationType($updateType);
        return $this->notificationSettingService->getReceivers($mappedType);
    }
    
    /**
     * 将事件类型映射到通知设置中的类型
     *
     * @param string $eventType
     * @return string
     */
    protected function mapEventTypeToNotificationType(string $eventType): string
    {
        $typeMap = [
            'featured_products' => 'homepage_featured_products_updated',
            'new_products' => 'homepage_new_products_updated',
            'sale_products' => 'homepage_sale_products_updated',
            'section_created' => 'homepage_section_created',
            'section_updated' => 'homepage_section_updated',
            'section_deleted' => 'homepage_section_deleted',
            'sections_reordered' => 'homepage_sections_reordered',
            'settings_updated' => 'homepage_settings_updated',
            'low_stock_products' => 'low_stock_removal'
        ];
        
        return $typeMap[$eventType] ?? 'homepage_updated';
    }
} 