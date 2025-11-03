<x-app-layout>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MTR Inventory</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

    <div class="container mx-auto bg-white p-6 rounded-lg shadow-lg">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">MTR Inventory</h1>

        <!-- Filter Form -->
        <!-- <form method="GET" class="mb-6 flex space-x-4">
            <input type="text" name="fg_code" placeholder="Filter by FG Code" value="{{ request('fg_code') }}" class="px-4 py-2 border rounded-lg w-1/3">
            <input type="text" name="material_code" placeholder="Filter by Material Code" value="{{ request('material_code') }}" class="px-4 py-2 border rounded-lg w-1/3">
            <input type="text" name="material_name" placeholder="Filter by Material Name" value="{{ request('material_name') }}" class="px-4 py-2 border rounded-lg w-1/3">
            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg">Filter</button>
        </form> -->

        <!-- Inventory Table -->
        <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-md">
            <thead>
                <tr class="bg-gray-200 text-gray-600">
                    <th class="py-2 px-4 text-left">FG Code</th>
                    <th class="py-2 px-4 text-left">Material Code</th>
                    <th class="py-2 px-4 text-left">Material Name</th>
                    <th class="py-2 px-4 text-left">Bom Quantity</th>
                    <th class="py-2 px-4 text-left">In Stock</th>
                    <th class="py-2 px-4 text-left">Item Group</th>
                    <th class="py-2 px-4 text-left">Vendor Code</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($mtrInventories as $inventory)
                    <tr class="border-b hover:bg-gray-100">
                        <td class="py-2 px-4">{{ $inventory->fg_code }}</td>
                        <td class="py-2 px-4">{{ $inventory->material_code }}</td>
                        <td class="py-2 px-4">{{ $inventory->material_name }}</td>
                        <td class="py-2 px-4">{{ $inventory->bom_quantity }}</td>
                        <td class="py-2 px-4">{{ $inventory->in_stock }}</td>
                        <td class="py-2 px-4">{{ $inventory->item_group }}</td>
                        <td class="py-2 px-4">{{ $inventory->vendor_code }}</td>
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