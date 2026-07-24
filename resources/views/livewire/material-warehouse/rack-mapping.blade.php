<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Header, Search Bar & Legend -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-black rounded-full uppercase tracking-wider">Raw Material Storage</span>
                    <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Material Warehouse Mapping</h1>
                </div>
                <p class="text-gray-500 text-sm mt-1">Monitoring & Tata Letak Rak Bahan Baku / Material</p>
            </div>

            <!-- Search Bar & Actions -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
                <!-- Search Input -->
                <div class="relative flex-grow sm:w-80">
                    <input type="text" 
                           wire:model.live.debounce.250ms="searchTerm" 
                           placeholder="Cari Part Code, Pallet ID, Lot, Rak..." 
                           class="w-full pl-10 pr-8 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    <div class="absolute left-3 top-3 text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    @if ($searchTerm)
                        <button wire:click="$set('searchTerm', '')" class="absolute right-2.5 top-2.5 text-gray-400 hover:text-gray-600 p-0.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    @endif
                </div>

                <!-- Add Rack Button -->
                <button wire:click="$set('showAddRackModal', true)" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center justify-center gap-1.5 whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>ADD RACK MATERIAL</span>
                </button>
            </div>
        </div>

        <!-- Legend & Search Result Counter -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 bg-white px-5 py-3 rounded-xl border border-gray-100 shadow-xs">
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Status Slot:</span>
                <div class="flex items-center px-2.5 py-1 bg-gray-50 rounded-lg border border-gray-200 text-[10px] font-bold text-gray-500">
                    <span class="w-2 h-2 bg-gray-300 rounded-full mr-2"></span> EMPTY
                </div>
                <div class="flex items-center px-2.5 py-1 bg-amber-50 rounded-lg border border-amber-200 text-[10px] font-bold text-amber-700">
                    <span class="w-2 h-2 bg-amber-400 rounded-full mr-2"></span> PARTIAL
                </div>
                <div class="flex items-center px-2.5 py-1 bg-emerald-50 rounded-lg border border-emerald-200 text-[10px] font-bold text-emerald-700">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full mr-2"></span> FULL
                </div>
                <div class="flex items-center px-2.5 py-1 bg-yellow-100 rounded-lg border border-yellow-400 text-[10px] font-black text-amber-900 shadow-xs">
                    <span class="w-2 h-2 bg-amber-500 rounded-full mr-2 animate-ping"></span> MATCH SEARCH
                </div>
            </div>

            @if ($searchTerm)
                <div class="text-xs font-bold text-emerald-800 bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-200">
                    Ditemukan <strong class="text-emerald-900 font-black">{{ count($matchingPositionIds) }}</strong> slot yang sesuai dengan "<span class="italic">{{ $searchTerm }}</span>"
                </div>
            @endif
        </div>

        <!-- Session Alert -->
        @if (session()->has('success'))
            <div class="bg-emerald-100 border-l-4 border-emerald-500 p-4 rounded-r-xl shadow-sm">
                <p class="text-emerald-800 text-sm font-bold uppercase tracking-widest italic">{{ session('success') }}</p>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 border-l-4 border-red-500 p-4 rounded-r-xl shadow-sm">
                <p class="text-red-700 text-sm font-bold uppercase tracking-widest italic">{{ session('error') }}</p>
            </div>
        @endif

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Grid Container -->
            <div class="flex-grow flex flex-wrap gap-6 items-start" id="mapping-grid">
                @forelse($racks as $rack)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-4 w-full md:w-[calc(50%-12px)] xl:w-[calc(33.333%-16px)]">
                        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                            <div>
                                <h3 class="text-lg font-black text-emerald-700 italic tracking-tighter uppercase">Rack {{ $rack->rack_code }}</h3>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold text-gray-400 bg-gray-50 px-2 py-1 rounded-full">{{ count($rack->positions) }} Slots</span>
                                <button wire:click="deleteRack({{ $rack->id }})" onclick="return confirm('Anda yakin ingin menghapus rak material ini beserta seluruh isinya?')" class="p-1 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Hapus Rak">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Vertical Level Columns -->
                        <div class="flex flex-row gap-4 overflow-x-auto pb-2">
                            @foreach($rack->positions->groupBy('level_no')->sortKeys() as $level => $positions)
                                <div class="flex flex-col gap-2 flex-1 min-w-[75px]">
                                    <div class="text-[9px] font-black text-emerald-600 text-center uppercase tracking-tighter border-b border-emerald-100 mb-1 pb-1">
                                        LVL {{ $level }}
                                    </div>
                                    <div class="space-y-2">
                                        @foreach($positions as $pos)
                                            @php
                                                $isMatched = in_array($pos->id, $matchingPositionIds);
                                                $isSelected = $selectedPositionId == $pos->id;

                                                $statusColor = 'bg-gray-100 hover:bg-gray-200 border-gray-200 text-gray-700';
                                                if($pos->status == 'PARTIAL') $statusColor = 'bg-amber-100 hover:bg-amber-200 border-amber-300 text-amber-900';
                                                if($pos->status == 'FULL') $statusColor = 'bg-emerald-100 hover:bg-emerald-200 border-emerald-300 text-emerald-900';
                                                
                                                if ($isMatched) {
                                                    $statusColor = 'bg-yellow-200 hover:bg-yellow-300 border-amber-500 text-amber-950 font-black shadow-md ring-4 ring-yellow-400/80 animate-pulse';
                                                }

                                                $ringClass = $isSelected ? 'ring-4 ring-emerald-500 ring-offset-2 scale-105 z-10' : '';
                                                $palletCount = $pos->pallets ? count($pos->pallets) : 0;
                                            @endphp
                                            <button wire:click="selectPosition({{ $pos->id }})" 
                                                    class="w-full aspect-square border-2 {{ $statusColor }} {{ $ringClass }} rounded-xl p-1.5 transition-all group relative overflow-hidden flex flex-col justify-between items-center shadow-xs"
                                                    title="{{ $pos->position_code }} ({{ $pos->slot_label ?? 'Slot' }})">
                                                
                                                @if ($isMatched)
                                                    <span class="absolute top-0.5 right-0.5 px-1 py-0.2 bg-amber-500 text-white rounded text-[7px] font-black uppercase tracking-tighter shadow-xs">
                                                        MATCH
                                                    </span>
                                                @endif

                                                <div class="text-[8px] font-black text-gray-500 group-hover:text-gray-800 text-center uppercase leading-none truncate w-full">
                                                    S{{ $pos->slot_no }}
                                                </div>

                                                <div class="text-[9px] font-black text-emerald-800 text-center uppercase leading-none my-0.5 truncate w-full">
                                                    {{ $pos->slot_label ?: $pos->position_code }}
                                                </div>

                                                @if($palletCount > 0)
                                                    <div class="text-[7px] font-extrabold text-emerald-900 bg-white/90 px-1 py-0.5 rounded leading-none w-full truncate text-center shadow-2xs">
                                                        {{ $palletCount }} Pallet ({{ number_format($pos->pallets->sum('current_qty'), 0) }} KG)
                                                    </div>
                                                @elseif($pos->last_item_code)
                                                    <div class="text-[7px] font-extrabold text-emerald-700 bg-emerald-50/80 px-1 py-0.5 rounded leading-none w-full truncate text-center">
                                                        {{ $pos->last_item_code }}
                                                    </div>
                                                @else
                                                    <div class="text-[7px] font-medium text-gray-400 leading-none">
                                                        Kosong
                                                    </div>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="w-full bg-white p-12 rounded-2xl border border-gray-100 text-center space-y-4">
                        <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        <h3 class="text-gray-600 font-bold text-base">Belum Ada Rak Material</h3>
                        <p class="text-gray-400 text-xs max-w-sm mx-auto">Klik tombol "+ ADD RACK MATERIAL" di atas untuk membuat tata letak rak material baru.</p>
                    </div>
                @endforelse
            </div>

            <!-- Detail Sidebar -->
            <div id="slot-detail-panel" class="w-full lg:w-96 flex-shrink-0">
                @if($showDetail && $selectedPosData)
                    <div x-init="$el.scrollIntoView({ behavior: 'smooth', block: 'nearest' })" class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden sticky top-6 animate-in slide-in-from-right duration-300">
                        <!-- Header Slot Info -->
                        @php
                            $totalQtyInSlot = $selectedPosData->pallets ? $selectedPosData->pallets->sum('current_qty') : 0;
                            $maxCap = max(1, $selectedPosData->max_capacity);
                            $capPct = round(($totalQtyInSlot / $maxCap) * 100);
                            $isOverCap = $totalQtyInSlot > $maxCap;
                            $barWidth = min(100, $capPct);
                        @endphp

                        <div class="bg-gradient-to-r {{ $isOverCap ? 'from-rose-700 to-red-800' : 'from-emerald-600 to-teal-700' }} p-6 text-white relative">
                            <button wire:click="$set('showDetail', false)" class="absolute top-4 right-4 text-white/70 hover:text-white transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>

                            <div class="relative z-10 space-y-1">
                                <div class="flex items-center space-x-2">
                                    <span class="text-[10px] font-black uppercase tracking-widest bg-black/20 px-2 py-0.5 rounded">
                                        Rak {{ $selectedPosData->rack ? $selectedPosData->rack->rack_code : '-' }}
                                    </span>
                                    @if ($isOverCap)
                                        <span class="text-[10px] font-black uppercase tracking-widest bg-rose-900 text-white px-2 py-0.5 rounded animate-pulse">
                                            ⚠️ OVER CAPACITY ({{ $capPct }}%)
                                        </span>
                                    @else
                                        <span class="text-[10px] font-black uppercase tracking-widest bg-white/20 px-2 py-0.5 rounded">
                                            {{ $selectedPosData->status }}
                                        </span>
                                    @endif
                                </div>

                                <h3 class="text-2xl font-black italic uppercase tracking-tighter">{{ $selectedPosData->position_code }}</h3>
                                <p class="text-white/80 text-xs font-medium">Level {{ $selectedPosData->level_no }} &bull; Slot {{ $selectedPosData->slot_no }} ({{ $selectedPosData->slot_label ?: 'Slot' }})</p>

                                <!-- Capacity Progress Bar -->
                                <div class="pt-3 space-y-1">
                                    <div class="flex justify-between text-[11px] font-bold">
                                        <span>Kapasitas Terisi:</span>
                                        <span>{{ number_format($totalQtyInSlot, 2) }} / {{ number_format($maxCap) }} KG ({{ $capPct }}%)</span>
                                    </div>
                                    <div class="w-full bg-black/30 rounded-full h-2 overflow-hidden">
                                        <div class="{{ $isOverCap ? 'bg-amber-300 animate-pulse' : 'bg-emerald-300' }} h-full rounded-full transition-all duration-500" style="width: {{ $barWidth }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-5 space-y-6 max-h-[calc(100vh-220px)] overflow-y-auto">
                            <!-- Section 1: Daftar Pallet ID yang tersimpan di Slot ini -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                    <h4 class="text-xs font-black text-gray-800 uppercase tracking-widest flex items-center space-x-1.5">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        <span>Isi Material Dalam Slot Ini</span>
                                    </h4>
                                    <div class="flex items-center space-x-2">
                                        <button type="button" wire:click="toggleAddMaterialForm" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-lg text-[10px] font-bold transition flex items-center space-x-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            <span>{{ $showAddMaterialForm ? 'Tutup Form' : '+ Input Material' }}</span>
                                        </button>
                                        <span class="text-[10px] bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full font-bold">
                                            {{ count($selectedPosData->pallets ?: []) }} Pallet
                                        </span>
                                    </div>
                                </div>

                                @if ($showAddMaterialForm)
                                    <form wire:submit.prevent="storeMaterialToSlot" class="p-4 bg-emerald-50/70 border border-emerald-200 rounded-2xl space-y-3 animate-in fade-in duration-200">
                                        <div class="flex justify-between items-center border-b border-emerald-200/60 pb-1.5">
                                            <span class="text-[10px] font-black text-emerald-900 uppercase tracking-widest">Input Material ke Slot {{ $selectedPosData->position_code }}</span>
                                            <span class="text-[9px] text-emerald-700 font-bold">Penyesuaian Actual</span>
                                        </div>

                                        <!-- Part Code Autocomplete -->
                                        <div class="relative">
                                            <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1">Part Code Material *</label>
                                            <input type="text" wire:model.live="new_item_code" placeholder="Cari Part Code / Nama..." class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-xl text-xs font-mono font-bold uppercase focus:ring-2 focus:ring-emerald-500">
                                            
                                            @if (!empty($newMaterialSearchResults))
                                                <div class="absolute left-0 right-0 top-full mt-1 bg-white rounded-xl shadow-xl border border-gray-100 z-30 max-h-40 overflow-y-auto">
                                                    @foreach ($newMaterialSearchResults as $res)
                                                        <button type="button" wire:click="selectNewMaterial('{{ $res['item_code'] }}', '{{ addslashes($res['item_description'] ?? '') }}')" class="w-full text-left px-3 py-1.5 hover:bg-emerald-50 transition border-b border-gray-50 flex flex-col">
                                                            <span class="text-xs font-bold font-mono text-gray-900">{{ $res['item_code'] }}</span>
                                                            <span class="text-[9px] text-gray-500 truncate">{{ $res['item_description'] }}</span>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            @endif
                                            @error('new_item_code') <span class="text-[9px] text-rose-500 font-bold block mt-0.5">{{ $message }}</span> @enderror
                                        </div>

                                        @if ($new_item_description)
                                            <div class="p-2 bg-white/80 rounded-lg text-[10px] text-gray-700 border border-emerald-100 truncate">
                                                <span class="font-bold">Deskripsi:</span> {{ $new_item_description }}
                                            </div>
                                        @endif

                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1">Qty (KG) *</label>
                                                <input type="number" step="0.01" wire:model="new_qty" placeholder="Ex: 500" class="w-full px-2.5 py-1.5 bg-white border border-gray-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-emerald-500">
                                                @error('new_qty') <span class="text-[9px] text-rose-500 font-bold block mt-0.5">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1">Lot / Batch No</label>
                                                <input type="text" wire:model="new_lot_no" placeholder="Ex: LOT-01" class="w-full px-2.5 py-1.5 bg-white border border-gray-200 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-emerald-500">
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1">Supplier (Opsional)</label>
                                                <input type="text" wire:model="new_supplier_name" placeholder="Nama Supplier" class="w-full px-2.5 py-1.5 bg-white border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500">
                                            </div>
                                            <div>
                                                <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1">No. PO (Opsional)</label>
                                                <input type="text" wire:model="new_po_number" placeholder="PO-12345" class="w-full px-2.5 py-1.5 bg-white border border-gray-200 rounded-xl text-xs font-mono focus:ring-2 focus:ring-emerald-500">
                                            </div>
                                        </div>

                                        <div class="flex justify-end space-x-2 pt-1">
                                            <button type="button" wire:click="toggleAddMaterialForm" class="px-3 py-1.5 bg-white border border-gray-200 text-gray-600 rounded-lg font-bold text-[10px]">
                                                Batal
                                            </button>
                                            <button type="submit" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-[10px] shadow-sm transition">
                                                + Simpan Material ke Slot
                                            </button>
                                        </div>
                                    </form>
                                @endif

                                @if ($selectedPosData->pallets && count($selectedPosData->pallets) > 0)
                                    <div class="space-y-3">
                                        @foreach ($selectedPosData->pallets as $pal)
                                            <div class="p-3.5 bg-gray-50 rounded-2xl border border-gray-200 hover:border-emerald-300 transition space-y-2">
                                                <div class="flex justify-between items-center">
                                                    <span class="font-mono font-black text-xs text-emerald-800">{{ $pal->pallet_id }}</span>
                                                    <span class="px-2 py-0.5 {{ $pal->status === 'STORED' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }} rounded-full text-[9px] font-bold">
                                                        {{ $pal->status }}
                                                    </span>
                                                </div>

                                                <div class="space-y-1">
                                                    <div class="font-mono font-bold text-xs text-gray-900">{{ $pal->item_code }}</div>
                                                    <div class="text-[11px] text-gray-600 leading-tight">{{ $pal->material ? $pal->material->item_description : '-' }}</div>
                                                </div>

                                                <div class="grid grid-cols-2 gap-2 pt-1 border-t border-gray-200/60 text-[11px]">
                                                    <div>
                                                        <span class="text-gray-400 text-[9px] uppercase tracking-wider block">Sisa Stok:</span>
                                                        <strong class="text-emerald-700 font-bold text-xs">{{ number_format($pal->current_qty, 2) }} KG</strong>
                                                        <span class="text-gray-400 text-[10px]"> (Awal: {{ number_format($pal->initial_qty, 2) }} KG)</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-400 text-[9px] uppercase tracking-wider block">Lot / Supplier:</span>
                                                        <span class="font-mono text-gray-800 font-bold">{{ $pal->lot_no ?: '-' }}</span>
                                                        <div class="text-[10px] text-gray-500 truncate">{{ $pal->incomingHeader ? ($pal->incomingHeader->supplier_name ?: '-') : '-' }}</div>
                                                    </div>
                                                </div>

                                                <div class="pt-1 flex justify-end gap-2">
                                                    <a href="{{ route('mwh.pallet.print', $pal->pallet_id) }}" target="_blank" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[10px] font-bold transition flex items-center space-x-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                                        <span>Print QR</span>
                                                    </a>
                                                    <a href="{{ route('mwh.outgoing.create', ['selected_item_code' => $pal->item_code]) }}" class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-[10px] font-bold transition flex items-center space-x-1">
                                                        <span>Picking Outgoing</span>
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="p-6 text-center bg-gray-50 border border-dashed border-gray-200 rounded-2xl text-gray-400 space-y-1">
                                        <svg class="w-8 h-8 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                        <p class="text-xs font-bold text-gray-500">Slot ini kosong</p>
                                        <p class="text-[10px] text-gray-400">Belum ada pallet material yang disimpan di slot ini.</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Section 2: Form Edit Configuration Slot -->
                            <div class="border-t border-gray-100 pt-4 space-y-4">
                                <h4 class="text-xs font-black text-gray-800 uppercase tracking-widest">Pengaturan & Edit Slot</h4>

                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Kode Slot / Position Code</label>
                                    <input type="text" wire:model="editPositionCode" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs font-bold focus:bg-white focus:border-emerald-500 outline-none uppercase transition-all">
                                    @error('editPositionCode') <span class="text-[10px] text-red-500 font-semibold">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Label Custom / Display Name</label>
                                    <input type="text" wire:model="editSlotLabel" placeholder="Misal: RESIN-A1, BAGGING-01" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs font-bold focus:bg-white focus:border-emerald-500 outline-none transition-all">
                                    @error('editSlotLabel') <span class="text-[10px] text-red-500 font-semibold">{{ $message }}</span> @enderror
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Status Slot</label>
                                        <select wire:model="editStatus" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-bold focus:bg-white focus:border-emerald-500 outline-none transition-all">
                                            <option value="EMPTY">EMPTY</option>
                                            <option value="PARTIAL">PARTIAL</option>
                                            <option value="FULL">FULL</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Max Capacity (KG)</label>
                                        <input type="number" step="0.01" wire:model="editMaxCapacity" placeholder="Ex: 1000" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-bold focus:bg-white focus:border-emerald-500 outline-none transition-all">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Last Item Code (Quick Note)</label>
                                    <input type="text" wire:model="editLastItemCode" placeholder="Kode Material (Opsional)" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs font-bold focus:bg-white focus:border-emerald-500 outline-none uppercase transition-all">
                                </div>

                                <div class="pt-2 flex gap-2">
                                    <button wire:click="saveSettings" class="flex-grow py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl text-[10px] uppercase tracking-widest shadow-md transition-all active:scale-95">
                                        Simpan Perubahan
                                    </button>
                                    <button wire:click="resetSlot" onclick="return confirm('Reset status slot ini menjadi EMPTY dan kosongkan pallet?')" class="p-2.5 bg-gray-50 hover:bg-red-50 text-gray-400 hover:text-red-500 border border-gray-200 rounded-xl transition-all" title="Reset Status">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="h-full flex items-center justify-center border-4 border-dashed border-gray-200 rounded-[3rem] p-12 grayscale opacity-40 min-h-[350px]">
                        <div class="text-center space-y-3">
                            <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
                            <p class="text-gray-400 font-bold text-xs uppercase tracking-[0.3em]">Pilih Slot Rak</p>
                            <p class="text-gray-400 text-[10px] italic">Klik salah satu slot pada rak material <br> untuk melihat detail pallet & mengedit slot</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>

    <!-- Modal Add Rack -->
    @if($showAddRackModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm animate-in fade-in duration-200">
            <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl overflow-hidden border border-gray-100 animate-in zoom-in-95 duration-200">
                <div class="bg-emerald-600 p-6 text-white text-center">
                    <h3 class="text-xl font-black italic uppercase tracking-tighter">Add Material Rack</h3>
                    <p class="text-emerald-100 text-xs font-medium">Buat tata letak rak material baru</p>
                </div>
                
                <div class="p-6 space-y-5">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[9px] font-black uppercase text-gray-400 tracking-widest mb-1">Kode Rak Material</label>
                            <input type="text" wire:model="newRackCode" placeholder="Ex: MTR-A, RAK-01" 
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-emerald-500 outline-none font-black text-xl text-center uppercase tracking-widest transition-all">
                            @error('newRackCode') <span class="text-[10px] text-red-500 font-bold uppercase mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[9px] font-black uppercase text-gray-400 tracking-widest mb-1 text-center">Levels</label>
                                <input type="number" wire:model="newLevels" min="1"
                                    class="w-full px-2 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none font-bold text-center text-base focus:bg-white focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block text-[9px] font-black uppercase text-gray-400 tracking-widest mb-1 text-center">Slots/LVL</label>
                                <input type="number" wire:model="newSlotsPerLevel" min="1"
                                    class="w-full px-2 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none font-bold text-center text-base focus:bg-white focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block text-[9px] font-black uppercase text-gray-400 tracking-widest mb-1 text-center">Max (KG)</label>
                                <input type="number" step="0.01" wire:model="newMaxCapacity" min="1"
                                    class="w-full px-2 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none font-bold text-center text-base focus:bg-white focus:border-emerald-500">
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button wire:click="$set('showAddRackModal', false)" class="flex-1 py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 font-black rounded-xl uppercase text-[10px] tracking-widest transition-all">Batal</button>
                        <button wire:click="createNewRack" class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl uppercase text-[10px] tracking-widest shadow-md transition-all">BUAT RAK</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
