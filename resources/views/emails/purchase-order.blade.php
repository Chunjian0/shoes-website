<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase order #{{ $purchase->purchase_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .content {
            margin-bottom: 30px;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #666;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Purchase order notice</h2>
    </div>

    <div class="content">
        <p>Dear supplier {{ $supplierItem->supplier->name }}:</p>
        
        <p>We are pleased to inform you of the purchase order #{{ $purchase->purchase_number }} Approval has been approved。The details are as follows:</p>

        <ul>
            <li>Purchase Order Number:{{ $purchase->purchase_number }}</li>
            <li>Purchase Date:{{ $purchase->purchase_date->format('Y-m-d') }}</li>
            <li>Estimated arrival date:{{ $purchase->expected_delivery_date ? $purchase->expected_delivery_date->format('Y-m-d') : 'not specified' }}</li>
            <li>Total purchase amount:RM {{ number_format($supplierItem->final_amount, 2) }}</li>
        </ul>

        <p>Please check the attachmentPDFDocumentation to learn more about orders。</p>

        <p>If you have any questions, please contact us in time。</p>
    </div>

    <div class="footer">
        <p>{{ $company->name }}</p>
        <p>{{ $company->address }}</p>
        <p>Telephone:{{ $company->phone }} | Mail:{{ $company->email }}</p>
    </div>
</body>
</html> 