<?php

namespace App\Events;

use App\Models\Stock;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockChangedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $stockEntry;
    public $oldQuantity;
    public $newQuantity;
    public $productId;
    public $changeType; // 'increase', 'decrease', 'adjustment'

    /**
     * Create a new event instance.
     * 
     * @param Stock $stockEntry 库存记录
     * @param int $oldQuantity 变更前数量
     * @param int $newQuantity 变更后数量
     * @param string $changeType 变更类型 (increase, decrease, adjustment)
     */
    public function __construct(Stock $stockEntry, int $oldQuantity, int $newQuantity, string $changeType)
    {
        $this->stockEntry = $stockEntry;
        $this->oldQuantity = $oldQuantity;
        $this->newQuantity = $newQuantity;
        $this->productId = $stockEntry->product_id;
        $this->changeType = $changeType;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
