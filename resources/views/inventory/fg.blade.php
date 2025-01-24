<x-app-layout>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FG Inventory</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

    <div class="container mx-auto bg-white p-6 rounded-lg shadow-lg">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">FG Inventory</h1>

        <!-- Filter Form -->
        <!-- <form method="GET" class="mb-6 flex space-x-4">
            <input type="text" name="item_code" placeholder="Filter by Item Code" value="{{ request('item_code') }}" class="px-4 py-2 border rounded-lg w-1/3">
            <input type="text" name="item_name" placeholder="Filter by Item Name" value="{{ request('item_name') }}" class="px-4 py-2 border rounded-lg w-1/3">
            <input type="number" name="item_group" placeholder="Filter by Item Group" value="{{ request('item_group') }}" class="px-4 py-2 border rounded-lg w-1/3">
            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg">Filter</button>
        </form> -->

        <!-- Inventory Table -->
        <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-md">
            <thead>
                <tr class="bg-gray-200 text-gray-600">
                    <th class="py-2 px-4 text-left">Item Code</th>
                    <th class="py-2 px-4 text-left">Item Name</th>
                    <th class="py-2 px-4 text-left">Item Group</th>
                    <th class="py-2 px-4 text-left">Safety Stock</th>
                    <th class="py-2 px-4 text-left">Stock</th>
                    <th class="py-2 px-4 text-left">Warehouse</th>
                    <th class="py-2 px-4 text-left">Production Min. Qty</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($fgInventories as $inventory)
                    <tr class="border-b hover:bg-gray-100">
                        <td class="py-2 px-4">{{ $inventory->item_code }}</td>
                        <td class="py-2 px-4">{{ $inventory->item_name }}</td>
                        <td class="py-2 px-4">{{ $inventory->item_group }}</td>
                        <td class="py-2 px-4">{{ $inventory->safety_stock }}</td>
                        <td class="py-2 px-4">{{ $inventory->stock }}</td>
                        <td class="py-2 px-4">{{ $inventory->warehouse }}</td>
                        <td class="py-2 px-4">{{ $inventory->production_min_qty }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 px-4">No records found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</body>
</html>


</x-app-layout>