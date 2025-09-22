<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Purchase Order #{{ $purchase->purchase_number }}</title>
    <style>
        body {
            font-family: 'times';
            font-size: 11pt;
            line-height: 1.6;
            color: #2d3748;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px double #2d3748;
            padding-bottom: 20px;
        }
        .header h1 {
            font-family: 'times';
            font-size: 28pt;
            font-weight: bold;
            margin: 0;
            color: #1a365d;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .header h2 {
            font-family: 'times';
            font-size: 18pt;
            margin: 10px 0 0;
            color: #4a5568;
        }
        .company-info {
            margin-bottom: 40px;
            padding: 20px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }
        .company-info h3 {
            font-family: 'times';
            margin: 0 0 15px;
            color: #1a365d;
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .company-info p {
            margin: 8px 0;
            color: #4a5568;
            font-size: 11pt;
        }
        .order-info {
            margin-bottom: 40px;
        }
        .order-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .order-info td {
            padding: 15px;
            vertical-align: top;
            border: 1px solid #e2e8f0;
        }
        .order-info strong {
            color: #1a365d;
            display: block;
            margin-bottom: 10px;
            font-size: 12pt;
            text-transform: uppercase;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }
        .items-table th,
        .items-table td {
            border: 1px solid #e2e8f0;
            padding: 15px;
            text-align: left;
        }
        .items-table th {
            background-color: #1a365d;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11pt;
        }
        .items-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .items-table td {
            font-size: 11pt;
        }
        .total-info {
            text-align: right;
            margin-top: 40px;
            padding: 20px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }
        .total-info p {
            margin: 8px 0;
            color: #4a5568;
            font-size: 11pt;
        }
        .total-info p:last-child {
            color: #1a365d;
            font-weight: bold;
            font-size: 14pt;
            margin-top: 15px;
            border-top: 2px solid #e2e8f0;
            padding-top: 15px;
        }
        .notes {
            margin: 40px 0;
            padding: 20px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }
        .notes h4 {
            margin: 0 0 15px;
            color: #1a365d;
            font-size: 12pt;
            text-transform: uppercase;
        }
        .signature-area {
            margin-top: 60px;
            border-top: 2px solid #e2e8f0;
            padding-top: 40px;
        }
        .signature-area table {
            width: 100%;
        }
        .signature-area td {
            padding: 15px;
            text-align: center;
            vertical-align: bottom;
        }
        .signature-area p {
            margin: 8px 0;
            color: #4a5568;
        }
        .signature-area .line {
            display: inline-block;
            width: 200px;
            border-bottom: 1px solid #4a5568;
            margin-top: 40px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9pt;
            color: #718096;
            padding: 15px 0;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Purchase Order</h1>
        <h2>#{{ $purchase->purchase_number }}</h2>
    </div>

    <div class="company-info">
        @if(isset($company))
            <h3>{{ $company->company_name }}</h3>
            <p>{{ $company->address }}</p>
            <p>Tel: {{ $company->phone }}</p>
            <p>Email: {{ $company->email }}</p>
            @if(isset($company->registration_number))
                <p>Registration No: {{ $company->registration_number }}</p>
            @endif
            @if(isset($company->tax_number))
                <p>Tax No: {{ $company->tax_number }}</p>
            @endif
        @else
            <h3>Company Information Not Set</h3>
        @endif
    </div>

    <div class="order-info">
        <table>
            <tr>
                <td width="50%">
                    <strong>Supplier Information</strong>
                    {{ $supplierItem->supplier->name }}<br>
                    {{ $supplierItem->supplier->address }}<br>
                    Tel: {{ $supplierItem->supplier->phone }}<br>
                    Email: {{ $supplierItem->supplier->email }}
                </td>
                <td width="50%">
                    <strong>Order Information</strong>
                    Purchase Date: {{ $purchase->purchase_date->format('d/m/Y') }}<br>
                    Order Status: {{ $purchase->purchase_status->label() }}<br>
                    Payment Status: {{ $purchase->payment_status->label() }}
                </td>
            </tr>
        </table>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>Item Description</th>
                <th>Quantity</th>
                <th>Unit Price (RM)</th>
                <th>Tax Rate</th>
                <th>Tax (RM)</th>
                <th>Subtotal (RM)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($supplierItem->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ number_format($item->quantity) }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ number_format($item->tax_rate, 1) }}%</td>
                    <td>{{ number_format($item->tax_amount, 2) }}</td>
                    <td>{{ number_format($item->total_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-info">
        <p>Subtotal: RM {{ number_format($supplierItem->total_amount, 2) }}</p>
        <p>Tax Amount: RM {{ number_format($supplierItem->tax_amount, 2) }}</p>
        <p>Shipping Fee: RM {{ number_format($supplierItem->shipping_fee, 2) }}</p>
        <p>Total Amount: RM {{ number_format($supplierItem->final_amount, 2) }}</p>
    </div>

    @if($supplierItem->notes)
        <div class="notes">
            <h4>Notes</h4>
            <p>{{ $supplierItem->notes }}</p>
        </div>
    @endif

    <div class="signature-area">
        <table>
            <tr>
                <td width="50%">
                    <p>Authorized By</p>
                    <div class="line"></div>
                    <p>Date: _________________</p>
                </td>
                <td width="50%">
                    <p>Supplier Confirmation</p>
                    <div class="line"></div>
                    <p>Date: _________________</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>{{ $company->company_name }} | {{ $company->address }} | Tel: {{ $company->phone }}</p>
    </div>
</body>
</html> 