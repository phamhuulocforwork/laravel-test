<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table thead {
            background-color: #4a5568;
            color: white;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        table tbody tr:nth-child(even) {
            background-color: #f7fafc;
        }

        table tbody tr:hover {
            background-color: #edf2f7;
        }

        .status-active {
            color: #48bb78;
            font-weight: bold;
        }

        .status-inactive {
            color: #f56565;
            font-weight: bold;
        }

        .price {
            text-align: right;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: #718096;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <h1>Products List</h1>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Stock</th>
                <th>SKU</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td>{{ $product->id }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->description ?? 'N/A' }}</td>
                    <td class="price">${{ number_format($product->price, 2) }}</td>
                    <td>{{ $product->stock }}</td>
                    <td>{{ $product->sku }}</td>
                    <td>
                        <span class="{{ $product->is_active ? 'status-active' : 'status-inactive' }}">
                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">No products found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on {{ date('Y-m-d H:i:s') }}</p>
        <p>Total Products: {{ count($products) }}</p>
    </div>
</body>

</html>