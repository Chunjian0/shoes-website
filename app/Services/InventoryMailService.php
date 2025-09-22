<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\InventoryNotification;
use App\Models\Product;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

class InventoryMailService
{
    private const ADMIN_EMAIL = 'ethankhoo09@gmail.com';

    public function sendLowStockNotification(Product $product): void
    {
        try {
            $subject = 'Inventory warning notice';
            $message = sprintf(
                "Dear administrator, the following inventory is below the warning value:\n\nProduct Name:%s\nCurrent inventory:%d\nMinimum inventory:%d\nProduct Category:%s\n\nPlease handle replenishment matters in a timely manner.",
                htmlspecialchars($product->name),
                $product->inventory_count,
                $product->min_stock,
                htmlspecialchars($product->category->name)
            );

            Mail::to(self::ADMIN_EMAIL)
                ->send(new InventoryNotification($subject, $message));

            Log::info('Inventory warning email was sent successfully', [
                'product_id' => $product->id,
                'product_name' => $product->name
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send inventory warning email:' . $e->getMessage());
            throw $e;
        }
    }

    public function sendOutOfStockNotification(Product $product): void
    {
        try {
            $subject = 'Urgent notice: Product inventory exhausted';
            $message = sprintf(
                "Urgent notice: Product inventory exhausted\n\nProduct Name:%s\nProduct Category:%s\nProduct Code:%s\n\nThis product has been automatically removed from the shelves. Please handle the replenishment matters as soon as possible.",
                htmlspecialchars($product->name),
                htmlspecialchars($product->category->name),
                htmlspecialchars($product->sku)
            );

            Mail::to(self::ADMIN_EMAIL)
                ->send(new InventoryNotification($subject, $message));

            Log::info('Inventory exhaustion email sent successfully', [
                'product_id' => $product->id,
                'product_name' => $product->name
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send an exhausted inventory email:' . $e->getMessage());
            throw $e;
        }
    }
} 