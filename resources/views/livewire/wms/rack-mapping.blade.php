<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Header & Legenda -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Warehouse Mapping</h1>
                <p class="text-gray-500 text-sm">Monitoring Hunian Rak Gudang J06 (Highly Marelli)</p>
            </div>
            <div class="flex items-center gap-4 flex-wrap">
                <!-- Item / Pallet / SPK Search Input with Dropdown Suggestions -->
                <div class="flex items-center gap-2 bg-gray-50 p-2 rounded-xl border border-gray-200 min-w-[280px] relative" x-data="{ open: true }" @click.outside="open = false">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest pl-1">Search Item</span>
                    <div class="relative w-full">
                        <input type="text" wire:model.live.debounce.250ms="searchItem"
                               @focus="open = true"
                               @input="open = true"
                               placeholder="Cari Item, SPK, Pallet ID..."
                               class="bg-white border border-gray-200 rounded-lg text-xs font-bold px-2.5 py-1.5 w-full focus:ring-2 focus:ring-blue-500 outline-none">
                        @if(!empty($searchItem))
                            <button wire:click="$set('searchItem', '')" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 font-bold text-xs">✕</button>
                        @endif

                        {{-- Dropdown Suggestions --}}
                        @if(!empty($searchSuggestions) && count($searchSuggestions) > 0)
                            <div x-show="open" class="absolute left-0 right-0 top-full mt-1.5 bg-white border border-gray-200 rounded-xl shadow-xl z-50 overflow-hidden divide-y divide-gray-100 min-w-[320px]">
                                <div class="px-3 py-1.5 bg-gray-50 text-[9px] font-extrabold text-gray-400 uppercase tracking-wider flex justify-between">
                                    <span>Item di Gudang (Stok > 0)</span>
                                    <span>Posisi Rak</span>
                                </div>
                                @foreach($searchSuggestions as $s)
                                    <div wire:click="selectSearchSuggestion('{{ $s['part_no'] }}')"
                                         @click="open = false"
                                         class="p-2.5 hover:bg-blue-50 cursor-pointer transition flex items-center justify-between gap-3 group">
                                        <div class="overflow-hidden">
                                            <div class="text-xs font-extrabold text-gray-900 group-hover:text-blue-700 truncate">
                                                {{ $s['part_no'] }}
                                            </div>
                                            <div class="text-[10px] font-semibold text-gray-500 truncate">
                                                {{ $s['model_name'] }}
                                            </div>
                                            <div class="text-[10px] font-bold text-emerald-600 mt-0.5">
                                                Stok: {{ number_format($s['total_qty']) }} Pcs ({{ $s['pallet_count'] }} Pallet)
                                            </div>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <span class="text-[10px] font-black text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-200 block truncate max-w-[120px]">
                                                📍 {{ $s['positions'] ?: 'Non-Rak' }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @elseif(!empty(trim($searchItem)) && empty($searchSuggestions))
                            <div x-show="open" class="absolute left-0 right-0 top-full mt-1.5 bg-white border border-gray-200 rounded-xl shadow-xl z-50 p-3 text-center text-xs font-semibold text-gray-400 min-w-[280px]">
                                Tidak ada item dengan stok > 0 ditemukan di rak.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Customer Filter -->
                <div class="flex items-center gap-2 bg-gray-50 p-2 rounded-xl border border-gray-200">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest pl-1">Filter Customer</span>
                    <select wire:model.live="filterCustomer" class="bg-white border border-gray-200 rounded-lg text-[10px] font-bold px-2 py-1 focus:ring-0 focus:border-blue-500 outline-none">
                        <option value="">ALL CUSTOMERS</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->customer_code }}">{{ $c->customer_code }} - {{ $c->customer_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-4 bg-gray-50 p-2 rounded-xl border border-gray-200">
                    <div class="flex items-center px-3 py-1 bg-white rounded-lg border border-gray-200 text-[10px] font-bold text-gray-400">
                        <span class="w-2 h-2 bg-gray-300 rounded-full mr-2"></span> EMPTY
                    </div>
                    <div class="flex items-center px-3 py-1 bg-yellow-50 rounded-lg border border-yellow-200 text-[10px] font-bold text-yellow-600">
                        <span class="w-2 h-2 bg-yellow-400 rounded-full mr-2"></span> PARTIAL
                    </div>
                    <div class="flex items-center px-3 py-1 bg-red-50 rounded-lg border border-red-200 text-[10px] font-bold text-red-600">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span> FULL
                    </div>
                </div>
                <button wire:click="$set('showAddRackModal', true)" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-md">
                    + ADD RACK
                </button>
            </div>
        </div>

        @if (session()->has('success'))
            <div class="bg-green-100 border-l-4 border-green-500 p-4 rounded-r-xl shadow-sm animate-in fade-in slide-in-from-top duration-300">
                <p class="text-green-700 text-sm font-bold uppercase tracking-widest italic">{{ session('success') }}</p>
            </div>
        @endif

        @if (!empty(trim($searchItem)))
            <div class="bg-blue-50 border-l-4 border-blue-600 p-4 rounded-r-xl shadow-sm flex items-center justify-between animate-in fade-in duration-200">
                <div class="flex items-center gap-3">
                    <span class="text-xl">🔍</span>
                    <div>
                        <p class="text-blue-900 text-xs font-extrabold uppercase tracking-wide">
                            Pencarian Item / SPK / Pallet: "<span class="text-blue-700 underline">{{ $searchItem }}</span>"
                        </p>
                        <p class="text-blue-700 text-xs mt-0.5">
                            Ditemukan <span class="font-black text-blue-900">{{ count($matchingPositionIds) }} slot rak</span> yang menyimpan item ini. Slot terkait disorot dengan **animasi biru menyala**.
                        </p>
                    </div>
                </div>
                <button wire:click="$set('searchItem', '')" class="text-xs text-blue-600 font-bold hover:underline">
                    Reset Pencarian ✕
                </button>
            </div>
        @endif

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Grid Container -->
            <div class="flex-grow flex flex-wrap gap-6 items-start" id="mapping-grid">
                @foreach($racks as $rack)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-4 w-full md:w-[calc(50%-12px)] xl:w-[calc(33.333%-16px)]">
                        <div class="flex justify-between items-center border-b border-gray-50 pb-3">
                            <h3 class="text-lg font-black text-blue-600 italic tracking-tighter uppercase">Rack {{ $rack->rack_code }}</h3>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold text-gray-400 bg-gray-50 px-2 py-1 rounded-full">{{ count($rack->positions) }} Slots</span>
                                <button wire:click="deleteRack({{ $rack->id }})" onclick="return confirm('Anda yakin ingin menghapus rak ini beserta seluruh isinya? Data yang dihapus tidak dapat dikembalikan.')" class="p-1 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Hapus Rak">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Vertical Level Columns -->
                        <div class="flex flex-row gap-4 overflow-x-auto pb-2">
                            @foreach($rack->positions->groupBy('level_no')->sortKeys() as $level => $positions)
                                <div class="flex flex-col gap-2 flex-1 min-w-[60px]">
                                    <div class="text-[9px] font-black text-blue-500 text-center uppercase tracking-tighter border-b border-blue-50 mb-1 pb-1">
                                        LVL {{ $level }}
                                    </div>
                                    <div class="space-y-2">
                                        @foreach($positions as $pos)
                                            @php
                                                $statusColor = 'bg-gray-100 hover:bg-gray-200 border-gray-200';
                                                if($pos->status == 'PARTIAL') $statusColor = 'bg-yellow-100 hover:bg-yellow-200 border-yellow-300';
                                                if($pos->status == 'FULL') $statusColor = 'bg-red-100 hover:bg-red-200 border-red-300';

                                                $isMatchedBySearch = !empty(trim($searchItem)) && in_array($pos->id, $matchingPositionIds);

                                                $isHighlighted = true;
                                                if ($filterCustomer && $pos->customer_code !== $filterCustomer) {
                                                    $isHighlighted = false;
                                                }
                                                if (!empty(trim($searchItem)) && !$isMatchedBySearch) {
                                                    $isHighlighted = false;
                                                }

                                                $opacityClass = $isHighlighted ? 'opacity-100' : 'opacity-20 grayscale';
                                                $searchGlowClass = $isMatchedBySearch ? 'ring-4 ring-blue-500 scale-105 border-blue-600 bg-blue-100 shadow-lg z-10' : '';
                                            @endphp
                                            <button wire:click="selectPosition({{ $pos->id }})" 
                                                    class="w-full aspect-square border-2 {{ $statusColor }} {{ $opacityClass }} {{ $searchGlowClass }} rounded-lg p-1 transition-all group relative overflow-hidden flex flex-col justify-between items-center"
                                                    title="{{ $pos->position_code }} @if($pos->customer_code) (Customer: {{ $pos->customer_code }}) @endif">
                                                
                                                @if($isMatchedBySearch)
                                                    <div class="absolute inset-0 bg-blue-400/20 animate-pulse pointer-events-none rounded-lg"></div>
                                                    <div class="absolute top-0.5 left-0.5 bg-blue-600 text-white rounded-full px-1 text-[7px] font-black shadow-sm z-20">
                                                        MATCH
                                                    </div>
                                                @endif

                                                <div class="text-[8px] font-black text-gray-400 group-hover:text-gray-600 text-center uppercase leading-none">
                                                    S{{ $pos->slot_no }}
                                                </div>

                                                @if($pos->customer_code)
                                                    <div class="text-[7px] font-extrabold text-blue-600 group-hover:text-blue-800 bg-blue-50 px-1 py-0.5 rounded leading-none w-full truncate text-center">
                                                        {{ $pos->customer_code }}
                                                    </div>
                                                @endif
 
                                                @if($pos->pallet_forms_count > 0)
                                                    <div class="absolute top-1 right-1 z-20">
                                                        <span class="flex h-1.5 w-1.5">
                                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 {{ $pos->status == 'FULL' ? 'bg-red-400' : 'bg-yellow-400' }}"></span>
                                                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 {{ $pos->status == 'FULL' ? 'bg-red-500' : 'bg-yellow-500' }}"></span>
                                                        </span>
                                                    </div>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Detail Sidebar -->
            <div class="w-full lg:w-96 flex-shrink-0">
                @if($showDetail && $selectedPosData)
                    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden sticky top-6 animate-in slide-in-from-right duration-300">
                        <div class="bg-blue-600 p-8 text-white relative">
                            <div class="absolute top-0 right-0 p-4 opacity-10">
                                <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            </div>
                            <div class="relative z-10">
                                <h3 class="text-3xl font-black italic uppercase tracking-tighter">{{ $selectedPosData->position_code }}</h3>
                                <p class="text-blue-100 text-xs font-bold uppercase tracking-widest mt-1">Slot Identification Detail</p>
                                @if($selectedPosData->customer)
                                    <p class="text-blue-200 text-[10px] font-black uppercase tracking-wider mt-2 bg-blue-700/50 w-fit px-2 py-0.5 rounded border border-blue-500/30">
                                        Cust: {{ $selectedPosData->customer->customer_name }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="p-8 space-y-6">
                            <!-- Occupant Info -->
                            <div class="space-y-4">
                                <div class="flex justify-between items-center text-[10px] font-black uppercase text-gray-400 tracking-widest italic border-b border-gray-50 pb-2">
                                    <span>Inventory Status</span>
                                    <span class="px-2 py-0.5 rounded {{ $selectedPosData->status == 'EMPTY' ? 'bg-gray-100 text-gray-400' : ($selectedPosData->status == 'PARTIAL' ? 'bg-yellow-100 text-yellow-600' : 'bg-red-100 text-red-600') }}">
                                        {{ $selectedPosData->status }}
                                    </span>
                                </div>

                                @if($selectedPosData->pallet_forms_count > 0)
                                    <div class="bg-gray-50 border border-gray-100 p-6 rounded-2xl">
                                        <div class="text-[10px] text-gray-400 font-bold uppercase mb-1 text-center">Summary</div>
                                        <div class="text-2xl font-black text-gray-800 tracking-wider text-center">
                                            {{ $selectedPosData->last_item_code ?: 'MIXED / NO LABEL' }}
                                        </div>
                                        <div class="mt-2 text-center">
                                            <span class="text-3xl font-black text-blue-600">{{ $selectedPosData->pallet_forms_count }}</span>
                                            <span class="text-[10px] text-gray-400 uppercase font-black italic">Pallet(s)</span>
                                        </div>

                                        <!-- Pallet List with Full Mixed Item Breakdown -->
                                        <div class="mt-6 space-y-3">
                                            <div class="flex items-center justify-between text-[9px] font-black text-gray-400 uppercase tracking-widest italic border-b border-gray-100 pb-1">
                                                <span>Stored Pallets & Item Details</span>
                                                <span>{{ count($selectedPosData->palletForms->where('status', 'STORED')) }} Pallet(s)</span>
                                            </div>
                                            @foreach($selectedPosData->palletForms->where('status', 'STORED') as $pf)
                                                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-3 space-y-2 text-left">
                                                    <!-- Pallet Header Info -->
                                                    <div class="flex justify-between items-start border-b border-gray-100 pb-2">
                                                        <div>
                                                            <div class="font-black text-xs text-gray-900 flex items-center gap-1.5">
                                                                📦 {{ $pf->pallet_id }}
                                                                @if($pf->details->count() > 1)
                                                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-black bg-purple-100 text-purple-700">MIXED</span>
                                                                @endif
                                                            </div>
                                                            <div class="text-[10px] text-gray-500 font-semibold">
                                                                Lot: {{ $pf->lot_no ?: '-' }} | Deliv: {{ $pf->delivery_name ?: '-' }}
                                                            </div>
                                                        </div>
                                                        <div class="text-right shrink-0">
                                                            <span class="text-xs font-black text-blue-600 block">{{ number_format($pf->total_pallet_qty, 0) }} Pcs</span>
                                                            <a href="{{ route('wms.pallet-form.print', ['id' => $pf->pallet_id]) }}" target="_blank" class="inline-block p-1 text-gray-400 hover:text-blue-600 transition" title="Print Barcode">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                                            </a>
                                                        </div>
                                                    </div>

                                                    <!-- Itemized Box Details Breakdown -->
                                                    <div class="bg-gray-50 p-2 rounded-lg space-y-1.5 border border-gray-100">
                                                        <div class="text-[9px] font-black text-gray-400 uppercase tracking-wider flex justify-between">
                                                            <span>Rincian Item dalam Pallet</span>
                                                            <span>Qty</span>
                                                        </div>
                                                        @forelse($pf->details as $detail)
                                                            @php
                                                                $isMatchedDetail = !empty(trim($searchItem)) && (stripos($detail->part_no, trim($searchItem)) !== false || stripos($detail->model_name, trim($searchItem)) !== false || stripos($detail->spk_no, trim($searchItem)) !== false);
                                                            @endphp
                                                            <div class="flex justify-between items-center text-[11px] p-1.5 rounded border transition-all {{ $isMatchedDetail ? 'bg-amber-100 border-amber-400 ring-2 ring-amber-300 font-extrabold' : 'bg-white border-gray-100' }}">
                                                                <div class="truncate pr-2">
                                                                    <div class="font-extrabold text-gray-900 leading-tight flex items-center gap-1">
                                                                        {{ $detail->part_no }}
                                                                        @if($isMatchedDetail)
                                                                            <span class="bg-amber-500 text-white text-[7px] px-1 rounded font-black uppercase">SEARCH MATCH</span>
                                                                        @endif
                                                                    </div>
                                                                    <div class="text-[9px] text-gray-500 truncate">{{ $detail->model_name ?: 'No Model' }}</div>
                                                                    @if($detail->spk_no)
                                                                        <div class="text-[8px] font-semibold text-blue-600">SPK: {{ $detail->spk_no }}</div>
                                                                    @endif
                                                                </div>
                                                                <div class="text-right shrink-0">
                                                                    <span class="font-black text-xs text-gray-900 block">{{ number_format($detail->qty, 0) }}</span>
                                                                    <span class="text-[8px] text-gray-400 font-semibold uppercase">Pcs</span>
                                                                </div>
                                                            </div>
                                                        @empty
                                                            <div class="text-[10px] text-gray-400 italic text-center py-1">
                                                                Main Item: {{ $pf->part_no ?: 'NO LABEL' }} ({{ number_format($pf->total_pallet_qty) }} Pcs)
                                                            </div>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div class="py-12 text-center border-2 border-dashed border-gray-100 rounded-3xl">
                                        <svg class="w-12 h-12 mx-auto text-gray-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                        <p class="text-gray-300 text-sm italic font-bold uppercase">Slot is Empty</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Management Actions -->
                            <div class="space-y-4 pt-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-1">Capacity</label>
                                        <input type="number" wire:model.defer="editMaxCapacity" class="w-full bg-gray-50 border border-gray-100 rounded-xl px-4 py-2 text-sm font-bold focus:bg-white focus:border-blue-500 outline-none transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-1">Customer</label>
                                        <select wire:model.defer="editCustomerCode" class="w-full bg-gray-50 border border-gray-100 rounded-xl px-4 py-2 text-sm font-bold focus:bg-white focus:border-blue-500 outline-none transition-all uppercase">
                                            <option value="">-- NO CUSTOMER --</option>
                                            @foreach($customers as $c)
                                                <option value="{{ $c->customer_code }}">{{ $c->customer_code }} - {{ $c->customer_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="flex gap-2">
                                    <button wire:click="saveSettings" class="flex-grow py-3 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-xl text-[10px] uppercase tracking-widest shadow-lg shadow-blue-100 transition-all active:scale-95">
                                        Update Detail
                                    </button>
                                    <button wire:click="resetSlot" onclick="return confirm('Reset slot ini menjadi kosong?')" class="p-3 bg-gray-50 hover:bg-red-50 text-gray-400 hover:text-red-500 border border-gray-100 rounded-xl transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </div>

                            @if (isset($unassignedPallets) && count($unassignedPallets) > 0)
                                <div class="mt-6 p-4 bg-amber-50 border border-amber-200 rounded-2xl space-y-3">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-xs font-black text-amber-900 uppercase tracking-widest">
                                            📦 Delivery Pending Slot ({{ count($unassignedPallets) }})
                                        </h4>
                                        <span class="text-[9px] bg-amber-200 text-amber-950 px-2 py-0.5 rounded font-bold">Store Action</span>
                                    </div>
                                    <p class="text-[10px] text-amber-700">Pallet di bawah ini di-scan oleh Delivery dan siap di-assign ke slot <strong>{{ $selectedPosData->position_code }}</strong>:</p>
                                    <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                        @foreach ($unassignedPallets as $un)
                                            <div class="p-2.5 bg-white border border-amber-200 rounded-xl flex items-center justify-between text-xs shadow-2xs">
                                                <div>
                                                    <div class="font-mono font-black text-gray-900">{{ $un->pallet_id }}</div>
                                                    <div class="text-[10px] text-gray-500 font-semibold">{{ $un->part_no }} &bull; {{ number_format($un->total_pallet_qty, 0) }} pcs</div>
                                                </div>
                                                <button wire:click="assignPalletToSelectedSlot('{{ $un->pallet_id }}')" class="px-3 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-[10px] font-bold shadow-xs transition">
                                                    Assign ke Slot Ini
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="h-full flex items-center justify-center border-4 border-dashed border-gray-100 rounded-[3rem] p-12 grayscale opacity-40">
                        <div class="text-center space-y-4">
                            <svg class="w-20 h-20 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
                            <p class="text-gray-400 font-bold text-xs uppercase tracking-[0.3em]">No Selection</p>
                            <p class="text-gray-300 text-[10px] italic">Pilih salah satu slot rak <br> untuk melihat detail isi inventory</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>

    <!-- Add Rack Modal (Simpler Version) -->
    @if($showAddRackModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm animate-in fade-in duration-200">
            <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200 border border-gray-100">
                <div class="bg-blue-600 p-8 text-white text-center">
                    <h3 class="text-xl font-black italic uppercase tracking-tighter italic">Add New Rack</h3>
                    <div class="w-10 h-1 bg-white/20 mx-auto mt-2 rounded"></div>
                </div>
                
                <div class="p-8 space-y-6">
                    <div class="space-y-4 text-gray-600">
                        <div>
                            <label class="block text-[9px] font-black uppercase text-gray-400 tracking-widest mb-1 italic">Rack Identifier</label>
                            <input type="text" wire:model.defer="newRackCode" placeholder="Ex: R06" 
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 outline-none font-black text-xl text-center uppercase tracking-widest transition-all">
                            @error('newRackCode') <span class="text-[9px] text-red-500 font-bold uppercase mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-[9px] font-black uppercase text-gray-400 tracking-widest mb-1 italic">Pre-Assign Customer ID (Optional)</label>
                            <select wire:model.defer="newRackCustomer" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 outline-none font-black text-xs uppercase tracking-widest transition-all">
                                <option value="">-- NONE --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->customer_code }}">{{ $c->customer_code }} - {{ $c->customer_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[9px] font-black uppercase text-gray-400 tracking-widest mb-1 italic">Levels</label>
                                <input type="number" wire:model.defer="newLevels" 
                                    class="w-full px-3 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none font-bold text-center text-lg">
                            </div>
                            <div>
                                <label class="block text-[9px] font-black uppercase text-gray-400 tracking-widest mb-1 italic">Slots/LVL</label>
                                <input type="number" wire:model.defer="newSlotsPerLevel" 
                                    class="w-full px-3 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none font-bold text-center text-lg">
                            </div>
                            <div>
                                <label class="block text-[9px] font-black uppercase text-gray-400 tracking-widest mb-1 italic">Max/Slot</label>
                                <input type="number" wire:model.defer="newMaxCapacity" 
                                    class="w-full px-3 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none font-bold text-center text-lg">
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button wire:click="$set('showAddRackModal', false)" class="flex-1 py-4 bg-gray-50 hover:bg-gray-100 text-gray-400 font-black rounded-xl uppercase text-[9px] tracking-widest transition-all italic">Close</button>
                        <button wire:click="createNewRack" class="flex-1 py-4 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-xl uppercase text-[9px] tracking-widest transition-all shadow-lg shadow-blue-100">CREATE</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
