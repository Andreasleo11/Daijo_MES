
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Daftar Asakai</h2>
            <a href="{{ route('asakai.create') }}" 
            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                + Tambah Asakai
            </a>
        </div>

        @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
        @endif

        @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
        @endif

        {{-- Filters --}}
        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <input type="text" wire:model.live="search" 
                        placeholder="Cari Part No / Issue / Customer..."
                        class="w-full border rounded px-3 py-2">
                </div>

                <div>
                    <select wire:model.live="statusFilter" class="w-full border rounded px-3 py-2">
                        <option value="">Semua Status</option>
                        <option value="draft">Draft</option>
                        <option value="submitted">Submitted</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>

                <div>
                    <select wire:model.live="customerFilter" class="w-full border rounded px-3 py-2">
                        <option value="">Semua Customer</option>
                        @foreach($customers as $customer)
                        <option value="{{ $customer }}">{{ $customer }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <select wire:model.live="shiftFilter" class="w-full border rounded px-3 py-2">
                        <option value="">Semua Shift</option>
                        <option value="Shift 1">Shift 1</option>
                        <option value="Shift 2">Shift 2</option>
                        <option value="Shift 3">Shift 3</option>
                    </select>
                </div>

                <div>
                    <input type="date" wire:model.live="dateFrom" 
                        placeholder="Dari Tanggal"
                        class="w-full border rounded px-3 py-2">
                </div>

                <div>
                    <input type="date" wire:model.live="dateTo" 
                        placeholder="Sampai Tanggal"
                        class="w-full border rounded px-3 py-2">
                </div>

                <div class="md:col-span-2">
                    <button wire:click="resetFilters" 
                            class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                        Reset Filter
                    </button>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Part No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Issue</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lot Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PIC</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($asakais as $asakai)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $asakai->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $asakai->customer }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $asakai->part_no }}</td>
                            <td class="px-6 py-4 text-sm">
                                <div class="max-w-xs truncate" title="{{ $asakai->issue }}">
                                    {{ Str::limit($asakai->issue, 50) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $asakai->quantity }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ $asakai->lot_date->format('d/m/Y') }}<br>
                                <span class="text-xs text-gray-500">{{ $asakai->lot_shift }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="max-w-xs truncate" title="{{ $asakai->pic_names }}">
                                    {{ Str::limit($asakai->pic_names, 30) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded
                                    @if($asakai->status === 'draft') bg-gray-200 text-gray-700
                                    @elseif($asakai->status === 'submitted') bg-yellow-200 text-yellow-800
                                    @else bg-green-200 text-green-800
                                    @endif">
                                    {{ ucfirst($asakai->status) }}
                                </span>
                                @if($asakai->is_overdue)
                                <span class="block text-xs text-red-500 mt-1">Overdue!</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex gap-2">
                                    <a href="{{ route('asakai.detail', $asakai->id) }}" 
                                    class="text-blue-600 hover:underline" title="Detail">
                                        👁️
                                    </a>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex gap-2">
                                            <a href="{{ route('asakai.detail', $asakai->id) }}" 
                                            class="text-blue-600 hover:underline" title="Detail">
                                                👁️
                                            </a>
                                            
                                            {{-- UBAH INI: Disable edit & delete kalau closed --}}
                                            @if($asakai->status !== 'closed')
                                                <a href="{{ route('asakai.edit', $asakai->id) }}" 
                                                class="text-green-600 hover:underline" title="Edit">
                                                    ✏️
                                                </a>
                                                <button wire:click="delete({{ $asakai->id }})" 
                                                        wire:confirm="Yakin ingin menghapus?"
                                                        class="text-red-600 hover:underline" title="Hapus">
                                                    🗑️
                                                </button>
                                            @else
                                                <span class="text-gray-400" title="Tidak dapat diedit (Status: Closed)">🔒</span>
                                            @endif
                                        </div>
                                    </td>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                                Tidak ada data ditemukan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4">
                {{ $asakais->links() }}
            </div>
        </div>
    </div>
