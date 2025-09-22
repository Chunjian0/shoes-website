<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('message_templates')->insert([
            'name' => 'low_stock_removal',
            'description' => 'Template for notifications when products with low stock are removed from homepage',
            'channel' => 'email',
            'type' => 'notification',
            'subject' => '[System Alert] Low Stock Products Removed From Homepage',
            'content' => $this->getDefaultTemplate(),
            'status' => 'active',
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('message_templates')
            ->where('name', 'low_stock_removal')
            ->delete();
    }
    
    /**
     * Get the default template content
     */
    private function getDefaultTemplate(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Low Stock Products Removed</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            border-bottom: 3px solid #dc3545;
            text-align: center;
        }
        .content {
            padding: 20px;
            background-color: #ffffff;
        }
        .footer {
            padding: 20px;
            background-color: #f8f9fa;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
        .alert {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
        }
        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .product-table th, .product-table td {
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: left;
        }
        .product-table th {
            background-color: #f0f0f0;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #0066cc;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .product-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Product Stock Alert</h2>
            <p>{{ count }} products with low stock have been removed from the homepage</p>
        </div>
        <div class="content">
            <p>Hello,</p>
            <p>This is an automated notification to inform you that {{ count }} products with stock levels below the threshold of <strong>{{ threshold }}</strong> have been automatically removed from the homepage.</p>
            
            <div class="alert">
                <p><strong>Important:</strong> These products will no longer be displayed on the homepage until their stock is replenished.</p>
            </div>
            
            <h3>Removed Products:</h3>
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Name</th>
                        <th>SKU</th>
                        <th>Current Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (removed_products as product)
                    <tr>
                        <td>{{ product.id }}</td>
                        <td>{{ product.name }}</td>
                        <td>{{ product.sku }}</td>
                        <td>{{ product.stock }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <p>Please take one of the following actions:</p>
            <ol>
                <li>Replenish the stock of these products</li>
                <li>Manually add these products back to the homepage if needed despite low stock</li>
                <li>Update product information in the inventory system</li>
            </ol>
            
            <a href="{{ url('/admin/homepage/low-stock-products') }}" class="btn">View Low Stock Products</a>
            
            <p>This action was taken automatically on {{ time }}.</p>
        </div>
        <div class="footer">
            <p>This is an automated message from your inventory management system. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
};
