<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
        }
        .company-info {
            margin-bottom: 20px;
        }
        .order-info {
            margin-bottom: 20px;
        }
        .customer-info {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>SALES ORDER</h1>
        <p>Order #{{ $order->order_number }}</p>
    </div>
    
    <div class="company-info">
        <div class="section-title">Company Information</div>
        <p>
            <strong>{{ $company->name ?? 'Company Name' }}</strong><br>
            {{ $company->address ?? 'Company Address' }}<br>
            Phone: {{ $company->phone ?? 'Company Phone' }}<br>
            Email: {{ $company->email ?? 'Company Email' }}<br>
            {{ $company->tax_id ? 'Tax ID: ' . $company->tax_id : '' }}
        </p>
    </div>
    
    <div class="order-info">
        <div class="section-title">Order Information</div>
        <table>
            <tr>
                <td><strong>Order Number:</strong> {{ $order->order_number }}</td>
                <td><strong>Order Date:</strong> {{ $order->created_at->format('M d, Y') }}</td>
            </tr>
            <tr>
                <td><strong>Status:</strong> {{ ucfirst($order->status) }}</td>
                <td><strong>Payment Status:</strong> {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}</td>
            </tr>
            <tr>
                <td><strong>Salesperson:</strong> {{ $order->salesperson->name }}</td>
                <td>
                    @if($order->payment_method)
                        <strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}
                    @endif
                </td>
            </tr>
        </table>
    </div>
    
    <div class="customer-info">
        <div class="section-title">Customer Information</div>
        <table>
            <tr>
                <td><strong>Name:</strong> {{ $order->customer->name }}</td>
                <td><strong>Phone:</strong> {{ $order->customer->contact_number }}</td>
            </tr>
            <tr>
                <td><strong>Email:</strong> {{ $order->customer->email ?? 'N/A' }}</td>
                <td><strong>Member Level:</strong> {{ ucfirst($order->customer->member_level) }}</td>
            </tr>
            @if($order->customer->address)
                <tr>
                    <td colspan="2"><strong>Address:</strong> {{ $order->customer->address }}</td>
                </tr>
            @endif
        </table>
    </div>
    
    <div class="order-items">
        <div class="section-title">Order Items</div>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Discount</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>
                            {{ $item->product_name }}
                            @if($item->specifications)
                                <br>
                                <small>
                                    @foreach($item->specifications as $key => $value)
                                        {{ $key }}: {{ $value }}{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                </small>
                            @endif
                        </td>
                        <td>{{ $item->product_sku }}</td>
                        <td>{{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->discount_amount, 2) }}</td>
                        <td>{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right"><strong>Subtotal:</strong></td>
                    <td>{{ number_format($order->subtotal, 2) }}</td>
                </tr>
                @if($order->tax_amount > 0)
                    <tr>
                        <td colspan="5" class="text-right"><strong>Tax:</strong></td>
                        <td>{{ number_format($order->tax_amount, 2) }}</td>
                    </tr>
                @endif
                @if($order->discount_amount > 0)
                    <tr>
                        <td colspan="5" class="text-right"><strong>Discount:</strong></td>
                        <td>-{{ number_format($order->discount_amount, 2) }}</td>
                    </tr>
                @endif
                <tr>
                    <td colspan="5" class="text-right"><strong>Total:</strong></td>
                    <td><strong>{{ number_format($order->total_amount, 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
    
    @if($order->remarks)
        <div class="remarks">
            <div class="section-title">Remarks</div>
            <p>{{ $order->remarks }}</p>
        </div>
    @endif
    
    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>Generated on {{ now()->format('M d, Y h:i A') }}</p>
    </div>
</body>
</html> 