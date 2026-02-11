
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">📅 Daily Report</h2>
            <div class="flex gap-2">
                <button wire:click="export('excel')" 
                        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    📊 Export Excel
                </button>
                <button wire:click="export('pdf')" 
                        class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                    📄 Export PDF
                </button>
            </div>
        </div>

        {{-- Date Filter --}}
        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Tanggal</label>
                    <input type="date" wire:model.live="date" 
                        class="w-full border rounded px-3 py-2">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Customer</label>
                    <select wire:model.live="customerFilter" class="w-full border rounded px-3 py-2">
                        <option value="">Semua Customer</option>
                        @foreach($customers as $customer)
                        <option value="{{ $customer }}">{{ $customer }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Part No</label>
                    <input type="text" wire:model.live="partFilter" 
                        placeholder="Cari Part No..."
                        class="w-full border rounded px-3 py-2">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Shift</label>
                    <select wire:model.live="shiftFilter" class="w-full border rounded px-3 py-2">
                        <option value="">Semua Shift</option>
                        <option value="Shift 1">Shift 1</option>
                        <option value="Shift 2">Shift 2</option>
                        <option value="Shift 3">Shift 3</option>
                    </select>
                </div>
            </div>

            <div class="mt-3">
                <button wire:click="resetFilters" 
                        class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                    Reset Filter
                </button>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-500 text-white rounded-lg shadow p-4">
                <div class="text-sm opacity-80">Total Issues</div>
                <div class="text-3xl font-bold">{{ $total }}</div>
            </div>

            <div class="bg-green-500 text-white rounded-lg shadow p-4">
                <div class="text-sm opacity-80">Total Quantity</div>
                <div class="text-3xl font-bold">{{ number_format($totalQuantity) }}</div>
            </div>

            <div class="bg-yellow-500 text-white rounded-lg shadow p-4">
                <div class="text-sm opacity-80">Customers</div>
                <div class="text-3xl font-bold">{{ $byCustomer->count() }}</div>
            </div>

            <div class="bg-purple-500 text-white rounded-lg shadow p-4">
                <div class="text-sm opacity-80">Shifts</div>
                <div class="text-3xl font-bold">{{ $byShift->count() }}</div>
            </div>
        </div>

        {{-- Breakdown by Shift --}}
        @if($byShift->count() > 0)
        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <h3 class="font-bold mb-3">Breakdown by Shift</h3>
            <div class="grid grid-cols-3 gap-4">
                @foreach($byShift as $shift => $items)
                <div class="border rounded p-3">
                    <div class="text-sm text-gray-600">{{ $shift }}</div>
                    <div class="text-2xl font-bold text-blue-600">{{ $items->count() }} issues</div>
                    <div class="text-sm text-gray-500">Qty: {{ $items->sum('quantity') }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Data Table --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Part No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Issue</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shift</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">PIC</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($asakais as $asakai)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm">{{ $asakai->id }}</td>
                            <td class="px-4 py-3 text-sm">{{ $asakai->customer }}</td>
                            <td class="px-4 py-3 text-sm">{{ $asakai->part_no }}</td>
                            <td class="px-4 py-3 text-sm">
                                <div class="max-w-xs truncate" title="{{ $asakai->issue }}">
                                    {{ Str::limit($asakai->issue, 50) }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm">{{ $asakai->quantity }}</td>
                            <td class="px-4 py-3 text-sm">{{ $asakai->lot_shift }}</td>
                            <td class="px-4 py-3 text-sm">
                                <div class="max-w-xs truncate" title="{{ $asakai->pic_names }}">
                                    {{ Str::limit($asakai->pic_names, 30) }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded
                                    @if($asakai->status === 'draft') bg-gray-200 text-gray-700
                                    @elseif($asakai->status === 'submitted') bg-yellow-200 text-yellow-800
                                    @else bg-green-200 text-green-800
                                    @endif">
                                    {{ ucfirst($asakai->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                Tidak ada data untuk tanggal {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
