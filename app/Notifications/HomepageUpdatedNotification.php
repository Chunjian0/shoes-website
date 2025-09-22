<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HomepageUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * 更新类型
     *
     * @var string
     */
    protected $updateType;
    
    /**
     * 更新人
     *
     * @var string
     */
    protected $updatedBy;
    
    /**
     * 更新数据
     *
     * @var array
     */
    protected $data;

    /**
     * 创建通知实例
     *
     * @param string $updateType
     * @param string $updatedBy
     * @param array $data
     * @return void
     */
    public function __construct(string $updateType, string $updatedBy, array $data = [])
    {
        $this->updateType = $updateType;
        $this->updatedBy = $updatedBy;
        $this->data = $data;
    }

    /**
     * 获取通知发送频道
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * 获取邮件通知
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject('Homepage Update Notification')
            ->greeting('Hello!');
            
        switch ($this->updateType) {
            case 'featured_products':
                $message->line('Featured products on the homepage have been updated.')
                        ->line('Updated by: ' . $this->updatedBy);
                
                if (isset($this->data['count'])) {
                    $message->line('Number of products: ' . $this->data['count']);
                }
                break;
                
            case 'new_products':
                $message->line('New arrival products on the homepage have been updated.')
                        ->line('Updated by: ' . $this->updatedBy);
                
                if (isset($this->data['count'])) {
                    $message->line('Number of products: ' . $this->data['count']);
                }
                break;
                
            case 'sale_products':
                $message->line('Sale products on the homepage have been updated.')
                        ->line('Updated by: ' . $this->updatedBy);
                
                if (isset($this->data['count'])) {
                    $message->line('Number of products: ' . $this->data['count']);
                }
                break;
                
            case 'section_created':
                $message->line('A new section has been created on the homepage.')
                        ->line('Created by: ' . $this->updatedBy);
                
                if (isset($this->data['title'])) {
                    $message->line('Section title: ' . $this->data['title']);
                }
                if (isset($this->data['type'])) {
                    $message->line('Section type: ' . $this->data['type']);
                }
                break;
                
            case 'section_updated':
                $message->line('A section has been updated on the homepage.')
                        ->line('Updated by: ' . $this->updatedBy);
                
                if (isset($this->data['title'])) {
                    $message->line('Section title: ' . $this->data['title']);
                }
                if (isset($this->data['type'])) {
                    $message->line('Section type: ' . $this->data['type']);
                }
                break;
                
            case 'section_deleted':
                $message->line('A section has been deleted from the homepage.')
                        ->line('Deleted by: ' . $this->updatedBy);
                
                if (isset($this->data['title'])) {
                    $message->line('Section title: ' . $this->data['title']);
                }
                break;
                
            case 'sections_reordered':
                $message->line('Homepage sections have been reordered.')
                        ->line('Updated by: ' . $this->updatedBy);
                break;
                
            case 'settings_updated':
                $message->line('Homepage settings have been updated.')
                        ->line('Updated by: ' . $this->updatedBy);
                break;
                
            case 'low_stock_products':
                $message->line('Low stock products have been removed from the homepage.')
                        ->line('System automated action');
                
                if (isset($this->data['count'])) {
                    $message->line('Number of products removed: ' . $this->data['count']);
                }
                break;
                
            default:
                $message->line('The homepage has been updated.')
                        ->line('Updated by: ' . $this->updatedBy);
                break;
        }
        
        return $message->line('Thank you for using our application!')
                       ->action('View Homepage', url('/'));
    }

    /**
     * 获取数据库通知
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        $title = 'Homepage Update';
        $description = 'The homepage has been updated.';
        
        switch ($this->updateType) {
            case 'featured_products':
                $title = 'Featured Products Updated';
                $description = 'Featured products on the homepage have been updated by ' . $this->updatedBy;
                break;
                
            case 'new_products':
                $title = 'New Arrival Products Updated';
                $description = 'New arrival products on the homepage have been updated by ' . $this->updatedBy;
                break;
                
            case 'sale_products':
                $title = 'Sale Products Updated';
                $description = 'Sale products on the homepage have been updated by ' . $this->updatedBy;
                break;
                
            case 'section_created':
                $title = 'Homepage Section Created';
                $description = 'A new section has been created on the homepage by ' . $this->updatedBy;
                break;
                
            case 'section_updated':
                $title = 'Homepage Section Updated';
                $description = 'A section has been updated on the homepage by ' . $this->updatedBy;
                break;
                
            case 'section_deleted':
                $title = 'Homepage Section Deleted';
                $description = 'A section has been deleted from the homepage by ' . $this->updatedBy;
                break;
                
            case 'sections_reordered':
                $title = 'Homepage Sections Reordered';
                $description = 'Homepage sections have been reordered by ' . $this->updatedBy;
                break;
                
            case 'settings_updated':
                $title = 'Homepage Settings Updated';
                $description = 'Homepage settings have been updated by ' . $this->updatedBy;
                break;
                
            case 'low_stock_products':
                $title = 'Low Stock Products Removed';
                $description = 'Low stock products have been automatically removed from the homepage';
                break;
        }
        
        return [
            'title' => $title,
            'description' => $description,
            'update_type' => $this->updateType,
            'updated_by' => $this->updatedBy,
            'data' => $this->data,
        ];
    }

    /**
     * 获取数组表示
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'update_type' => $this->updateType,
            'updated_by' => $this->updatedBy,
            'data' => $this->data,
        ];
    }
} 