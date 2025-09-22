<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Insufficient inventory notice</title>
</head>
<body>
    <h1>Insufficient inventory notice</h1>
    
    <p>Dear administrator:</p>
    
    <p>The system has detected that the following goods are in stock and there is no available inventory in other warehouses. Please deal with it in time:</p>
    
    <table border="1" cellpadding="10" style="border-collapse: collapse;">
        <tr>
            <th align="left">Product Information</th>
            <td>{{ $product->name }} ({{ $product->code }})</td>
        </tr>
        <tr>
            <th align="left">The warehouse</th>
            <td>{{ $warehouse->name }} ({{ $warehouse->code }})</td>
        </tr>
        <tr>
            <th align="left">Current inventory</th>
            <td>{{ $product->getStockInWarehouse($warehouse->id) }}</td>
        </tr>
        <tr>
            <th align="left">Minimum inventory</th>
            <td>{{ $product->min_stock }}</td>
        </tr>
    </table>
    
    <p>Please replenish inventory as soon as possible to avoid affecting normal sales.</p>
    
    <p>Sincerely</p>
    <p>Automatic sending of the system</p>
</body>
</html> 