<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Pallet Form History</h1>
                <p class="text-gray-500">Lihat dan cetak ulang pallet form yang sudah dibuat.</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('wms.pallet-form.lookup') }}" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl shadow-lg shadow-amber-100 transition-all flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    PALLET LOOKUP
                </a>
                <a href="{{ route('wms.pallet-form.create') }}" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-200 transition-all flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    CREATE NEW PALLET
                </a>
            </div>
        </div>

        <!-- Filter & Search Section -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="relative w-full md:w-96">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari ID Palet, Part No, atau Delivery..." 
                    class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm font-semibold">
            </div>

            <div class="flex items-center space-x-3 w-full md:w-auto">
                <label class="text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Filter Slot:</label>
                <select wire:model.live="filterSlot" class="py-2.5 px-4 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="ALL">Semua Pallet</option>
                    <option value="UNASSIGNED">⚠️ Belum Assign Slot (Delivery)</option>
                    <option value="ASSIGNED">Sudah Assign Slot</option>
                </select>
            </div>
        </div>

        <!-- Table Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-gray-500 text-xs font-bold uppercase tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">Pallet ID</th>
                            <th class="px-6 py-4">Date & Delivery</th>
                            <th class="px-6 py-4">Part No / Model</th>
                            <th class="px-6 py-4">Customer</th>
                            <th class="px-6 py-4">Qty</th>
                            <th class="px-6 py-4">Rack Position (Store)</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($palletForms as $form)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <div class="font-bold text-gray-800">{{ $form->pallet_id }}</div>
                                        @if($form->status === 'OUT')
                                            <span class="px-2 py-0.5 bg-red-100 text-red-800 text-[10px] font-bold rounded">OUT</span>
                                        @else
                                            <span class="px-2 py-0.5 bg-green-100 text-green-800 text-[10px] font-bold rounded">STORED</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-400">#{{ $form->lot_no ?: 'No Lot' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <div class="font-bold text-gray-800">{{ $form->created_at->timezone('Asia/Jakarta')->format('d M Y') }}</div>
                                    <div class="text-xs text-gray-400 font-mono">{{ $form->delivery_name ?: 'Delivery' }} (Shift {{ $form->delivery_shift ?: '-' }})</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-800">{{ $form->part_no }}</div>
                                    <div class="text-xs text-gray-500">{{ $form->model_name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $customers = $form->details->map(fn($d) => $d->item?->customer)->filter()->unique('customer_code');
                                    @endphp
                                    @if($customers->isNotEmpty())
                                        <div class="flex flex-col gap-1">
                                            @foreach($customers as $cust)
                                                <div class="text-xs font-bold text-gray-800" title="{{ $cust->customer_code }}">
                                                    {{ $cust->customer_name }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">No Customer</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-blue-600">{{ number_format($form->total_pallet_qty, 0) }} pcs</div>
                                    <div class="text-xs text-gray-400">{{ $form->box_qty }} Boxes</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($form->position)
                                        <div class="flex items-center space-x-2">
                                            <span class="px-3 py-1 bg-gray-800 text-white text-xs font-bold rounded-lg uppercase tracking-tight">
                                                {{ $form->position->position_code }}
                                            </span>
                                            <button wire:click="openAssignModal('{{ $form->pallet_id }}')" class="text-gray-400 hover:text-blue-600 p-1" title="Ubah Slot Rak">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                        </div>
                                    @else
                                        <div class="flex items-center space-x-2">
                                            <span class="px-2.5 py-1 bg-amber-100 text-amber-900 border border-amber-300 text-[10px] font-black rounded-lg uppercase tracking-tight animate-pulse">
                                                ⚠️ BELUM ASSIGN SLOT
                                            </span>
                                            <button wire:click="openAssignModal('{{ $form->pallet_id }}')" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-bold rounded-lg shadow-sm transition-all">
                                                Assign Slot
                                            </button>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right flex justify-end space-x-2">
                                    <a href="{{ route('wms.pallet-form.print', ['id' => $form->pallet_id]) }}" target="_blank"
                                        class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white rounded-lg text-xs font-bold transition-all">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        REPRINT
                                    </a>
                                    <button wire:click="deletePallet('{{ $form->pallet_id }}')" 
                                        wire:confirm="Apakah Anda yakin ingin menghapus pallet {{ $form->pallet_id }}? Data ini tidak bisa dikembalikan."
                                        class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded-lg text-xs font-bold transition-all">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        DELETE
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-20 text-center text-gray-400 italic">
                                    Belum ada data pallet form yang tersimpan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($palletForms->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    {{ $palletForms->links() }}
                </div>
            @endif
        </div>

    </div>

    <!-- Store Assign Slot Modal -->
    @if ($showAssignModal)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl space-y-5 border border-gray-100">
                <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                    <h3 class="text-base font-black text-gray-900 uppercase tracking-tight">Assign Slot Rak FG (Store)</h3>
                    <button wire:click="$set('showAssignModal', false)" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
                </div>
                <div class="space-y-4">
                    <div class="p-3.5 bg-blue-50 border border-blue-200 rounded-2xl text-xs text-blue-900 space-y-1">
                        <div class="flex justify-between">
                            <span>Pallet ID:</span>
                            <strong class="font-mono text-blue-950 font-black">{{ $assignPalletId }}</strong>
                        </div>
                        <p class="text-[10px] text-blue-700">Silakan pilih slot rak posisi di Gudang FG untuk pallet ini.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Pilih Slot Rak WMS:</label>
                        <select wire:model="assignPositionId" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="">-- Pilih Slot Rak --</option>
                            @foreach ($availablePositions as $pos)
                                <option value="{{ $pos->id }}">
                                    {{ $pos->position_code }} (Customer: {{ $pos->customer_code ?: 'ALL' }}, Status: {{ $pos->status }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end space-x-2 pt-3 border-t border-gray-100">
                    <button wire:click="$set('showAssignModal', false)" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-xs">Batal</button>
                    <button wire:click="saveAssignSlot" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs shadow-md">Simpan Assignment</button>
                </div>
            </div>
        </div>
    @endif
</div>
