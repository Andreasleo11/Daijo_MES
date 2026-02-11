<div class="space-y-4">
    <!-- Search & Settings Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-4 rounded-lg shadow-sm">
        <div class="flex-1">
            <input 
                type="text" 
                placeholder="Search by SO, Customer, Item..." 
                wire:model.live="search"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
        </div>

        <div class="flex items-center gap-2">
            <label class="text-sm font-medium text-gray-700">Status:</label>
            <select 
                wire:model.live="statusFilter"
                class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option value="">All Status</option>
                <option value="Finish">Finish</option>
                <option value="Danger">Danger</option>
                <option value="Warning">Warning</option>
            </select>
        </div>
        
        <div class="flex items-center gap-2">
            <label class="text-sm font-medium text-gray-700">Show:</label>
            <select 
                wire:model.live="perPage"
                class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <span class="text-sm text-gray-600 ml-2">entries</span>
        </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-300">
        <div style="max-height: 70vh; overflow-y: auto; position: relative;">
            <table class="w-full border-collapse text-sm">
                <thead style="position: sticky; top: 0; background-color: #f3f4f6; z-index: 10;">
                    <tr class="border-b border-gray-300">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-gray-200 whitespace-nowrap"
                            wire:click="sortBy('status')">
                            Status
                            @if($sortField === 'status')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-gray-200 whitespace-nowrap"
                            wire:click="sortBy('so_number')">
                            SO Number
                            @if($sortField === 'so_number')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-gray-200 whitespace-nowrap"
                            wire:click="sortBy('delivery_date')">
                            Delivery Date
                            @if($sortField === 'delivery_date')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Customer Code</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-gray-200 whitespace-nowrap"
                            wire:click="sortBy('customer_name')">
                            Customer Name
                            @if($sortField === 'customer_name')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Item Code</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-gray-200 whitespace-nowrap"
                            wire:click="sortBy('item_name')">
                            Item Name
                            @if($sortField === 'item_name')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Departement</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Delivery Qty</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Delivered</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Outstanding</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Stock</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Balance</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Outstanding Stk</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Packaging Code</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Standar Pack</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Packaging Qty</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Doc Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Remark</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($deliveries as $delivery)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 py-3 text-sm whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium text-white
                                    @if($delivery->status === 'Finish')
                                        bg-green-500
                                    @elseif($delivery->status === 'Danger')
                                        bg-red-500
                                    @elseif($delivery->status === 'Warning')
                                        bg-yellow-500
                                    @else
                                        bg-gray-500
                                    @endif
                                ">
                                    {{ $delivery->status ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 font-medium whitespace-nowrap">{{ $delivery->so_number ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ $delivery->delivery_date ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ $delivery->customer_code ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $delivery->customer_name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ $delivery->item_code ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $delivery->item_name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ $delivery->departement ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 font-medium whitespace-nowrap">{{ $delivery->delivery_qty ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 font-medium whitespace-nowrap">{{ $delivery->delivered ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 font-medium whitespace-nowrap">{{ $delivery->outstanding ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 font-medium whitespace-nowrap">{{ $delivery->stock ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 font-medium whitespace-nowrap">{{ $delivery->balance ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 font-medium whitespace-nowrap">{{ $delivery->outstanding_stk ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ $delivery->packaging_code ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 font-medium whitespace-nowrap">{{ $delivery->standar_pack ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 font-medium whitespace-nowrap">{{ $delivery->packaging_qty ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ $delivery->doc_status ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $delivery->remark ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="19" class="px-4 py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    No data found
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination & Info -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-4 rounded-lg shadow-sm">
        <div class="text-sm text-gray-600">
            Showing <span class="font-semibold">{{ $deliveries->firstItem() ?? 0 }}</span> 
            to <span class="font-semibold">{{ $deliveries->lastItem() ?? 0 }}</span> 
            of <span class="font-semibold">{{ $deliveries->total() }}</span> entries
        </div>

        <!-- Pagination Links -->
        <div class="flex items-center gap-2 flex-wrap">
            {{ $deliveries->links() }}
        </div>
    </div>

    <style>
        /* Custom Pagination Styling untuk Livewire */
        .pagination {
            display: flex;
            gap: 4px;
            list-style: none;
            padding: 0;
            margin: 0;
            flex-wrap: wrap;
        }

        .pagination li {
            display: inline-block;
        }

        .pagination a,
        .pagination button,
        .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 8px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background-color: #f3f4f6;
            color: #374151;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .pagination a:hover,
        .pagination button:hover {
            background-color: #e5e7eb;
            border-color: #9ca3af;
            color: #111827;
        }

        .pagination .active span {
            background-color: #3b82f6;
            color: #ffffff;
            border-color: #3b82f6;
            font-weight: 600;
            cursor: default;
        }

        .pagination .disabled span,
        .pagination li:has(span.relative.inline-flex.items-center.justify-center.px-4.py-2.mx-1.text-sm.font-medium.text-gray-500.cursor-not-allowed.bg-white.border.border-gray-300.rounded.leading-5) {
            background-color: #f9fafb;
            color: #d1d5db;
            border-color: #e5e7eb;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .pagination li.disabled a {
            background-color: #f9fafb;
            border-color: #e5e7eb;
            pointer-events: none;
        }
    </style>
</div>