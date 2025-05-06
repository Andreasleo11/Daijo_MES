<x-dashboard-layout>
    <div class="overflow-x-auto">
        <table class="min-w-full table-auto border border-gray-300 text-sm text-left text-gray-700 shadow-md rounded-lg">
            <thead class="bg-gray-100 text-gray-700 uppercase tracking-wider">
                <tr>
                    <th class="px-4 py-3 border-b border-gray-300">Interval</th>
                    <th class="px-4 py-3 border-b border-gray-300">SPK Code</th>
                    <th class="px-4 py-3 border-b border-gray-300">Item Code</th>
                    <th class="px-4 py-3 border-b border-gray-300">Quantity</th>
                    <th class="px-4 py-3 border-b border-gray-300">Box</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach (collect($data)->sortBy('interval') as $item)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-2 whitespace-nowrap">{{ $item['interval'] }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $item['spk_code'] }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $item['item_code'] }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $item['total_quantity'] }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $item['numbox'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-dashboard-layout>
