<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HomepageUpdatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var string 更新类型
     */
    public $updateType;
    
    /**
     * @var string 更新人
     */
    public $updatedBy;
    
    /**
     * @var array 更新数据
     */
    public $data;

    /**
     * 创建一个新的事件实例
     *
     * @param string $updateType 更新类型（例如：featured_products, new_products, sale_products, section_created等）
     * @param string $updatedBy 更新人（通常是邮箱地址）
     * @param array $data 附加数据
     * @return void
     */
    public function __construct(string $updateType, string $updatedBy, array $data = [])
    {
        $this->updateType = $updateType;
        $this->updatedBy = $updatedBy;
        $this->data = $data;
    }
} 