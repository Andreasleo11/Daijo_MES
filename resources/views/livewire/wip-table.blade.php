<div class="space-y-4">
    <!-- Search & Settings Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-4 rounded-lg shadow-sm">
        <div class="flex-1">
            <input 
                type="text" 
                placeholder="Search by SO, Customer, Item, WIP..." 
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
                <option value="SUCCESS">SUCCESS</option>
                <option value="DANGER">DANGER</option>
                <option value="WARNING">WARNING</option>
                <option value="MUTED">MUTED</option>
                <option value="INFO">INFO</option>
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
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Delivery Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Customer Code</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-gray-200 whitespace-nowrap"
                            wire:click="sortBy('customer_name')">
                            Customer Name
                            @if($sortField === 'customer_name')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Item Code</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Item Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Outstanding Del</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">WIP Code</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">WIP Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Departement</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">BOM Level</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">BOM Quantity</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Req Quantity</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Stock WIP</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Balance WIP</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Outstanding WIP</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Remark</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($wipData as $wip)
                         <tr
                            class="transition-colors duration-150
                            @if($wip->status === 'SUCCESS')
                                bg-green-200 hover:bg-green-100
                            @elseif($wip->status === 'DANGER')
                                bg-red-200 hover:bg-red-100
                            @elseif($wip->status === 'WARNING')
                                bg-yellow-200 hover:bg-yellow-100
                            @elseif($wip->status === 'INFO')
                                bg-blue-200 hover:bg-blue-100
                            @elseif($wip->status === 'MUTED')
                                bg-gray-100 hover:bg-gray-200
                            @else
                                hover:bg-gray-200
                            @endif
                        ">
                            <td class="px-4 py-3 text-sm whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium text-white
                                    @if($wip->status === 'SUCCESS')
                                        bg-green-500
                                    @elseif($wip->status === 'DANGER')
                                        bg-red-500
                                    @elseif($wip->status === 'WARNING')
                                        bg-yellow-500
                                    @elseif($wip->status === 'INFO')
                                        bg-blue-500
                                    @else
                                        bg-gray-500
                                    @endif
                                ">
                                    {{ $wip->status ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 font-medium whitespace-nowrap">{{ $wip->so_number ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ $wip->delivery_date ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ $wip->customer_code ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $wip->customer_name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ $wip->item_code ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $wip->item_name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 font-medium whitespace-nowrap">{{ $wip->outstanding_del ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ $wip->wip_code ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $wip->wip_name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ $wip->departement ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 font-medium whitespace-nowrap">{{ $wip->bom_level ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 font-medium whitespace-nowrap">{{ $wip->bom_quantity ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 font-medium whitespace-nowrap">{{ $wip->req_quantity ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 font-medium whitespace-nowrap">{{ $wip->stock_wip ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 font-medium whitespace-nowrap">{{ $wip->balance_wip ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 font-medium whitespace-nowrap">{{ $wip->outstanding_wip ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 font-medium whitespace-nowrap">{{ $wip->remark ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="17" class="px-4 py-8 text-center text-gray-500">
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
            Showing <span class="font-semibold">{{ $wipData->firstItem() ?? 0 }}</span> 
            to <span class="font-semibold">{{ $wipData->lastItem() ?? 0 }}</span> 
            of <span class="font-semibold">{{ $wipData->total() }}</span> entries
        </div>

        <!-- Pagination Links -->
        <div class="flex items-center gap-2 flex-wrap">
            {{ $wipData->links() }}
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