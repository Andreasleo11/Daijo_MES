<x-app-layout>
    <div class="container mx-auto mt-10 px-10">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Waiting Purchase Orders</h1>
        <div class="flex justify-end mb-4">
            <a href="{{ route('waiting_purchase_orders.create') }}"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">Create New Order</a>
        </div>
        <div class="overflow-hidden shadow rounded-lg bg-white">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mold
                            Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Process</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Remark</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($orders as $order)
                        <tr class="hover:bg-gray-100">
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $order->mold_name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $order->process }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">${{ number_format($order->price) }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if ($order->status === 1)
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded bg-red-100 text-red-800 text-xs font-medium">Belum
                                        PO</span>
                                @elseif($order->status === 2)
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded bg-green-100 text-green-800 text-xs font-medium">Sudah
                                        PO</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $order->remark }}</td>
                            <td class="px-6 py-4 text-sm text-right">
                                <a href="{{ route('waiting_purchase_orders.show', $order->id) }}"
                                    class="text-blue-600 hover:underline">View</a>
                                <span class="text-gray-500 mx-2">|</span>
                                <a href="{{ route('waiting_purchase_orders.edit', $order->id) }}"
                                    class="text-green-600 hover:underline">Edit</a>
                                <span class="text-gray-500 mx-2">|</span>
                                <button onclick="confirmDelete({{ $order->id }})"
                                    class="text-red-600 hover:underline">Delete</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="hidden fixed inset-0 z-50 bg-gray-800 bg-opacity-50 flex justify-center items-center">
        <div class="bg-white rounded-lg shadow-lg p-6 w-96">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Confirm Delete</h2>
            <p class="text-gray-600 mb-6">Are you sure you want to delete this order? This action cannot be undone.</p>
            <div class="flex justify-end">
                <button onclick="closeModal()"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded mr-2">Cancel</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(orderId) {
            const modal = document.getElementById('deleteModal');
            const deleteForm = document.getElementById('deleteForm');
            deleteForm.action = `/waiting_purchase_orders/${orderId}`;
            modal.classList.remove('hidden');
        }

        function closeModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.add('hidden');
        }
    </script>
</x-app-layout>
