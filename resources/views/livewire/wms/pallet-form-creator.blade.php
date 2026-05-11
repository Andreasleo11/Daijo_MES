<div class="p-6 bg-gray-50 min-h-screen" 
     x-data="{ 
        pendingScans: [],
        addPending(data) {
            this.pendingScans.unshift({
                label: data.label,
                spk_no: data.spk,
                qty: data.qty,
                warehouse: data.whse,
                is_no_label: false,
                part_no: '---',
                model_name: 'Syncing...'
            });
        }
     }"
     x-on:add-pending.window="addPending($event.detail)"
     x-on:scan-success.window="pendingScans = []"
     x-on:scan-error.window="pendingScans = []"
     @if($showSuccessModal && $sapSyncStatus === 'pending') wire:poll.2s="checkSapSyncStatus" @endif>
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- Header --}}
        <div class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Generate Pallet Form</h1>
                <p class="text-gray-500 text-sm">Scan box dan lengkapi detail palet. Mendukung multi-item per pallet.</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('wms.outbound') }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-semibold flex items-center transition-all shadow-lg shadow-red-100">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    OUTBOUND SCAN
                </a>
                <a href="{{ route('wms.mapping') }}" class="px-4 py-2 bg-slate-800 hover:bg-black text-white rounded-xl text-sm font-semibold flex items-center transition-all">
                    <svg class="w-4 h-4 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                    WAREHOUSE MAPPING
                </a>
                <span class="px-4 py-2 bg-blue-50 text-blue-700 rounded-full text-sm font-semibold">Gudang J06</span>
            </div>
        </div>

        @if (session()->has('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-xl flex items-start space-x-3 mb-4">
                <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="text-green-700 font-bold">{{ session('success') }}</p>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-xl flex items-start space-x-3">
                <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="text-red-700 font-medium">{{ session('error') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left: Header Form --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h2 class="text-lg font-semibold mb-5 flex items-center text-gray-800">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Pallet Details
                    </h2>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Prod Date</label>
                                <input type="date" wire:model="prod_date"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                                @error('prod_date') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Shift</label>
                                <select wire:model="delivery_shift" class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                                    <option value="">Pilih Shift</option>
                                    <option value="1">Shift 1</option>
                                    <option value="2">Shift 2</option>
                                    <option value="3">Shift 3</option>
                                </select>
                                @error('delivery_shift') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Lot No. / MO</label>
                            <input type="text" wire:model="lot_no" placeholder="Opsional"
                                class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Delivery Name</label>
                            <input type="text" wire:model="delivery_name" placeholder="Nama pengirim..."
                                class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                            @error('delivery_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Remarks</label>
                            <textarea wire:model="remarks" placeholder="Catatan tambahan (opsional)..." rows="2"
                                class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm"></textarea>
                        </div>

                        {{-- Auto-calculated totals --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-3 bg-blue-50 rounded-xl border border-blue-100 text-center">
                                <div class="text-xs text-blue-500 font-semibold uppercase mb-1">Total Box</div>
                                <div class="text-2xl font-black text-blue-800">{{ $total_box }}</div>
                            </div>
                            <div class="p-3 bg-green-50 rounded-xl border border-green-100 text-center">
                                <div class="text-xs text-green-500 font-semibold uppercase mb-1">Total Qty</div>
                                <div class="text-2xl font-black text-green-800">{{ number_format($total_pallet_qty, 0) }}</div>
                            </div>
                        </div>

                        {{-- Recommended Slot Display --}}
                        @if($recommendedSlot)
                            <div class="p-4 bg-slate-800 rounded-2xl border-2 border-slate-700 shadow-inner group overflow-hidden relative">
                                <div class="absolute top-0 right-0 p-2 opacity-5 group-hover:opacity-10 transition-opacity">
                                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                </div>
                                <div class="text-[10px] font-black text-blue-400 uppercase tracking-[0.2em] mb-1">RECOMMENDED SLOT</div>
                                <div class="text-3xl font-black text-white italic tracking-tighter">{{ $recommendedSlot }}</div>
                            </div>
                        @endif

                        {{-- Multi-item indicator --}}
                        @php
                            $uniqueParts = collect($scanned_items)->pluck('part_no')->filter()->unique()->values();
                        @endphp
                        @if($uniqueParts->count() > 1)
                            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl">
                                <div class="text-xs font-bold text-amber-700 uppercase mb-1">⚡ Multi-Item Pallet</div>
                                <div class="text-xs text-amber-600">{{ $uniqueParts->implode(', ') }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right: Scan + List --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Scan Panel --}}
                <div class="{{ $label_mode === 'NO_LABEL' ? 'bg-orange-500' : 'bg-blue-600' }} p-6 rounded-2xl shadow-lg transition-colors duration-300 relative overflow-hidden">
                    {{-- Processing Overlay (Hanya untuk Generate Form, bukan scan) --}}
                    <div wire:loading wire:target="generateForm" 
                         class="absolute inset-0 bg-black/40 backdrop-blur-[2px] z-50 flex items-center justify-center">
                        <div class="bg-white px-5 py-3 rounded-2xl shadow-2xl flex items-center space-x-3 border-2 border-blue-600">
                            <div class="w-5 h-5 border-3 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                            <span class="text-sm font-black text-gray-800 uppercase tracking-widest italic">PROCESSING...</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-white text-lg font-semibold flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                            @if($label_mode === 'NO_LABEL')
                                📭 Mode: Tanpa Label
                            @else
                                Scanning Session (Per Box)
                            @endif
                        </h2>

                        {{-- Toggle button --}}
                        @if($label_mode === 'SCAN')
                            <button wire:click="toggleNoLabel" type="button"
                                class="px-4 py-2 bg-white/20 hover:bg-white/30 border border-white/40 text-white text-sm font-bold rounded-xl transition-all flex items-center space-x-2">
                                <span>📭</span>
                                <span>TANPA LABEL</span>
                            </button>
                        @else
                            <button wire:click="toggleNoLabel" type="button"
                                class="px-4 py-2 bg-white text-orange-600 hover:bg-orange-50 text-sm font-bold rounded-xl transition-all flex items-center space-x-2">
                                <span>🔙</span>
                                <span>KEMBALI SCAN LABEL</span>
                            </button>
                        @endif
                    </div>

                    @if($label_mode === 'SCAN')
                        {{-- Normal Scan Mode --}}
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            {{-- SPK --}}
                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-white/70 uppercase mb-1">SPK Code</label>
                                <input type="text" wire:model="scan_spk" id="scan_spk"
                                    wire:loading.attr="disabled"
                                    class="w-full px-4 py-3 bg-white/20 border-2 border-white/30 rounded-xl text-white font-bold focus:bg-white focus:text-gray-800 outline-none transition-all placeholder-white/50 disabled:opacity-50 disabled:cursor-not-allowed"
                                    placeholder="Scan SPK...">
                            </div>
                            {{-- Qty --}}
                            <div>
                                <label class="block text-xs font-bold text-white/70 uppercase mb-1">Quantity</label>
                                <input type="number" wire:model="scan_qty" id="scan_qty"
                                    wire:loading.attr="disabled"
                                    class="w-full px-4 py-3 bg-white/20 border-2 border-white/30 rounded-xl text-white font-bold focus:bg-white focus:text-gray-800 outline-none transition-all placeholder-white/50 disabled:opacity-50 disabled:cursor-not-allowed"
                                    placeholder="Qty">
                            </div>
                            {{-- Whse --}}
                            <div>
                                <label class="block text-xs font-bold text-white/70 uppercase mb-1">Warehouse</label>
                                <input type="text" wire:model="scan_whse" id="scan_whse"
                                    wire:loading.attr="disabled"
                                    class="w-full px-4 py-3 bg-white/20 border-2 border-white/30 rounded-xl text-white font-bold focus:bg-white focus:text-gray-800 outline-none transition-all placeholder-white/50 disabled:opacity-50 disabled:cursor-not-allowed"
                                    placeholder="Whse">
                            </div>
                        </div>

                        {{-- Label row --}}
                        <div class="mt-4 flex items-end gap-4">
                            <div class="flex-1">
                                <label class="block text-xs font-bold text-white/70 uppercase mb-1">Label Barcode</label>
                                <input type="text" id="scan_label"
                                    class="w-full px-4 py-3 bg-white/20 border-2 border-white/30 rounded-xl text-white font-bold focus:bg-white focus:text-gray-800 outline-none transition-all placeholder-white/50"
                                    placeholder="Scan label box..."
                                    autocomplete="off">
                            </div>
                            <div class="flex space-x-2">
                                <button wire:click="resetScanner" type="button"
                                    class="px-6 py-3 bg-white/20 hover:bg-white/30 border border-white/40 text-white font-bold rounded-xl transition-all active:scale-95 whitespace-nowrap">
                                    RESET
                                </button>
                                <button wire:click="addItem" type="button"
                                    class="px-6 py-3 bg-white text-blue-700 font-black rounded-xl hover:bg-blue-50 transition-all active:scale-95 shadow-lg whitespace-nowrap">
                                    + ADD BOX
                                </button>
                            </div>
                        </div>

                    @else
                        {{-- No-Label Mode --}}
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-white/70 uppercase mb-1">Jumlah Box <span class="text-red-300">*</span></label>
                                <input type="number" wire:model="scan_box_count"
                                    class="w-full px-4 py-3 bg-white/20 border-2 border-white/40 rounded-xl text-white font-bold focus:bg-white focus:text-gray-800 outline-none transition-all"
                                    placeholder="Berapa box?">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-white/70 uppercase mb-1">Qty per Box <span class="text-red-300">*</span></label>
                                <input type="number" wire:model="scan_qty" id="scan_qty"
                                    class="w-full px-4 py-3 bg-white/20 border-2 border-white/40 rounded-xl text-white font-bold focus:bg-white focus:text-gray-800 outline-none transition-all"
                                    placeholder="Pcs per box">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-white/70 uppercase mb-1">Warehouse</label>
                                <input type="text" wire:model="scan_whse" id="scan_whse_nl"
                                    class="w-full px-4 py-3 bg-white/20 border-2 border-white/30 rounded-xl text-white font-bold focus:bg-white focus:text-gray-800 outline-none transition-all placeholder-white/50"
                                    placeholder="Whse (opsional)">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-white/70 uppercase mb-1">Alasan</label>
                                <select wire:model="no_label_reason"
                                    class="w-full px-4 py-3 bg-white/20 border-2 border-white/30 rounded-xl text-white font-bold focus:bg-white focus:text-gray-800 outline-none transition-all">
                                    <option value="" class="text-gray-800">Pilih alasan...</option>
                                    <option value="Box Lama" class="text-gray-800">Box Lama</option>
                                    <option value="Relabel Pending" class="text-gray-800">Relabel Pending</option>
                                    <option value="Box Rework" class="text-gray-800">Box Rework</option>
                                    <option value="Lainnya" class="text-gray-800">Lainnya</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4 p-3 bg-white/10 rounded-xl border border-white/20 text-white/80 text-sm flex items-center space-x-2">
                            <span>ℹ️</span>
                            <span>Sistem akan menambahkan entry box sebanyak jumlah yang diinput dengan Qty masing-masing.</span>
                        </div>

                        <div class="mt-4 flex justify-end space-x-2">
                            <button wire:click="resetScanner" type="button"
                                class="px-6 py-3 bg-white/20 hover:bg-white/30 border border-white/40 text-white font-bold rounded-xl transition-all active:scale-95">
                                RESET
                            </button>
                            <button wire:click="addItem" type="button"
                                class="px-6 py-3 bg-white text-orange-600 font-black rounded-xl hover:bg-orange-50 transition-all active:scale-95 shadow-lg">
                                + ADD BOX TANPA LABEL
                            </button>
                        </div>
                    @endif

                    @if (session()->has('scan_error'))
                        <div class="mt-3 p-3 bg-red-500/30 border border-red-300/40 rounded-xl text-red-100 text-sm font-semibold flex items-center space-x-2">
                            <span>⚠️</span>
                            <span>{{ session('scan_error') }}</span>
                        </div>
                    @endif
                </div>

                {{-- Scanned List Table --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-800">List of Scanned Boxes</h2>
                        <div class="text-2xl font-black text-blue-600">
                            {{ $total_box }} <span class="text-sm text-gray-400 font-normal">Boxes</span>
                        </div>
                    </div>

                    <div class="max-h-[420px] overflow-y-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-100 text-gray-600 text-xs font-bold uppercase tracking-wider sticky top-0">
                                <tr>
                                    <th class="px-4 py-3 w-10 text-center">No</th>
                                    <th class="px-4 py-3 text-left">Production Details (SPK & Part)</th>
                                    <th class="px-4 py-3 text-center">Total Box</th>
                                    <th class="px-4 py-3 text-right">Total Quantity</th>
                                    <th class="px-4 py-3 text-center">Whse</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200" x-data="{ expandedSpk: null }">
                                {{-- Alpine Pending Scans (Instant UI) --}}
                                <template x-for="(item, index) in pendingScans" :key="'pending-'+index">
                                    <tr class="bg-blue-50/30 animate-pulse border-l-4 border-blue-400">
                                        <td class="px-4 py-3 text-center text-blue-400 font-bold">NEW</td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center space-x-2">
                                                <span class="px-2 py-0.5 bg-blue-100 text-blue-600 rounded text-[10px] font-bold" x-text="item.spk_no"></span>
                                                <span class="font-bold text-blue-500 text-xs" x-text="item.part_no"></span>
                                            </div>
                                            <div class="text-blue-300 text-[10px] italic" x-text="item.model_name"></div>
                                        </td>
                                        <td class="px-4 py-3 text-center font-bold text-blue-400">1</td>
                                        <td class="px-4 py-3 text-right font-black text-blue-500" x-text="item.qty"></td>
                                        <td class="px-4 py-3 text-center text-blue-300 text-xs" x-text="item.warehouse"></td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="w-4 h-4 border-2 border-blue-400 border-t-transparent rounded-full animate-spin ml-auto"></div>
                                        </td>
                                    </tr>
                                </template>

                                @php
                                    $groupedItems = collect($scanned_items)->map(function($item, $key) {
                                        $item['original_index'] = $key;
                                        return $item;
                                    })->groupBy('spk_no');
                                @endphp

                                @forelse ($groupedItems as $spk_no => $items)
                                    @php 
                                        $first = $items->first();
                                        $totalQty = $items->sum('qty');
                                        $boxCount = $items->count();
                                    @endphp
                                    {{-- Group Header Row --}}
                                    <tr class="hover:bg-gray-50 transition-colors cursor-pointer border-l-4 border-blue-600/20" 
                                        @click="expandedSpk === '{{ $spk_no }}' ? expandedSpk = null : expandedSpk = '{{ $spk_no }}'">
                                        <td class="px-4 py-4 text-center text-gray-400 font-mono text-xs">#{{ $loop->iteration }}</td>
                                        <td class="px-4 py-4">
                                            <div class="flex items-center space-x-3">
                                                <div class="flex flex-col">
                                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-[10px] font-bold w-fit mb-1">{{ $spk_no }}</span>
                                                    <span class="font-black text-gray-800 text-sm tracking-tight leading-none">{{ $first['part_no'] ?? '—' }}</span>
                                                    <span class="text-gray-400 text-[10px] truncate max-w-[200px] mt-1">{{ $first['model_name'] ?? '—' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full font-bold text-xs border border-blue-100">
                                                {{ $boxCount }} BOXES
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <div class="font-black text-blue-600 text-base leading-none">{{ number_format($totalQty, 0) }}</div>
                                            <div class="text-gray-300 text-[10px] font-bold uppercase mt-1">TOTAL PCS</div>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <span class="px-2 py-1 bg-gray-100 text-gray-500 rounded font-bold text-[10px]">{{ $first['warehouse'] ?? '—' }}</span>
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <div class="flex items-center justify-end space-x-2">
                                                <button type="button" class="p-2 bg-gray-50 text-gray-400 hover:text-blue-600 rounded-lg transition-colors border border-transparent hover:border-blue-100">
                                                    <svg class="w-5 h-5 transform transition-transform duration-200" :class="expandedSpk === '{{ $spk_no }}' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- Group Detail Row (Accordion) --}}
                                    <tr x-show="expandedSpk === '{{ $spk_no }}'" x-collapse x-cloak class="bg-gray-50/50">
                                        <td colspan="6" class="p-0">
                                            <div class="px-12 py-4 bg-white/50 border-y border-gray-100">
                                                <table class="w-full text-xs">
                                                    <thead>
                                                        <tr class="text-gray-400 font-bold border-b border-gray-100">
                                                            <th class="py-2 text-left">#</th>
                                                            <th class="py-2 text-left">Label Barcode</th>
                                                            <th class="py-2 text-right">Qty</th>
                                                            <th class="py-2 text-center">Type</th>
                                                            <th class="py-2 text-right">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-gray-50">
                                                        @foreach($items as $subIndex => $subItem)
                                                            <tr class="hover:bg-blue-50/30 transition-colors group">
                                                                <td class="py-2 text-gray-300 font-mono">{{ $loop->iteration }}</td>
                                                                <td class="py-2">
                                                                    @if($subItem['is_no_label'])
                                                                        <span class="text-orange-500 font-bold italic">🚫 No Label ({{ $subItem['no_label_reason'] ?? 'Manual' }})</span>
                                                                    @else
                                                                        <span class="font-mono text-gray-600 group-hover:text-blue-600 transition-colors">{{ $subItem['label'] }}</span>
                                                                    @endif
                                                                </td>
                                                                <td class="py-2 text-right font-bold text-gray-700">{{ number_format($subItem['qty'], 0) }}</td>
                                                                <td class="py-2 text-center">
                                                                    <span class="px-2 py-0.5 {{ $subItem['is_no_label'] ? 'bg-orange-100 text-orange-600' : 'bg-green-100 text-green-600' }} rounded-full text-[9px] font-bold uppercase">
                                                                        {{ $subItem['is_no_label'] ? 'Manual' : 'Scanned' }}
                                                                    </span>
                                                                </td>
                                                                <td class="py-2 text-right">
                                                                    <button wire:click="removeItem({{ $subItem['original_index'] }})" type="button"
                                                                        class="text-red-300 hover:text-red-500 transition-colors opacity-0 group-hover:opacity-100">
                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic bg-white">
                                            <div class="flex flex-col items-center justify-center">
                                                <svg class="w-12 h-12 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 00-2 2H6a2 2 0 00-2 2V13m16 0h-1v-4a1 1 0 00-1-1h-2a1 1 0 00-1 1v4h-1m-6 0h-1v-4a1 1 0 00-1-1H6a1 1 0 00-1 1v4h-1"></path></svg>
                                                <span>Belum ada box yang di-scan. Gunakan panel scan di atas.</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="p-5 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                        <div class="text-sm text-gray-500">
                            @php $noLabelCount = collect($scanned_items)->where('is_no_label', true)->count(); @endphp
                            @if($noLabelCount > 0)
                                <span class="inline-flex items-center px-2 py-1 bg-orange-100 text-orange-700 text-xs font-semibold rounded-lg">
                                    📭 {{ $noLabelCount }} box tanpa label
                                </span>
                            @endif
                        </div>
                        <button wire:click="generateForm" type="button"
                            wire:loading.attr="disabled"
                            wire:target="generateForm"
                            @if($isProcessing) disabled @endif
                            class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 transition-all active:scale-95 flex items-center disabled:bg-gray-400 disabled:shadow-none">
                            <span wire:loading.remove wire:target="generateForm">GENERATE PALLET FORM</span>
                            <span wire:loading wire:target="generateForm">PROCESSING...</span>
                            <svg wire:loading.remove wire:target="generateForm" class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        </button>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('focus-spk', () => {
                setTimeout(() => {
                    const el = document.getElementById('scan_spk');
                    if (el) { el.focus(); el.select(); }
                }, 60);
            });
            Livewire.on('focus-qty', () => {
                setTimeout(() => {
                    const el = document.getElementById('scan_qty');
                    if (el) el.focus();
                }, 60);
            });
            Livewire.on('focus-whse', () => {
                setTimeout(() => {
                    const el = document.getElementById('scan_whse');
                    if (el) el.focus();
                }, 60);
            });
            Livewire.on('focus-label', () => {
                setTimeout(() => {
                    const el = document.getElementById('scan_label');
                    if (el) el.focus();
                }, 60);
            });

            // Audio Feedback Logic
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            
            function playTone(frequency, duration, type = 'sine', volume = 0.1) {
                if (audioCtx.state === 'suspended') audioCtx.resume();
                const oscillator = audioCtx.createOscillator();
                const gainNode = audioCtx.createGain();

                oscillator.type = type;
                oscillator.frequency.setValueAtTime(frequency, audioCtx.currentTime);
                
                gainNode.gain.setValueAtTime(volume, audioCtx.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + duration/1000);

                oscillator.connect(gainNode);
                gainNode.connect(audioCtx.destination);

                oscillator.start();
                oscillator.stop(audioCtx.currentTime + duration/1000);
            }

            Livewire.on('scan-success', () => {
                window.isScanningInternal = false;
                playTone(1200, 200, 'sawtooth', 0.4); 
                setTimeout(() => playTone(1500, 100, 'sawtooth', 0.3), 50); 
            });

            Livewire.on('scan-error', () => {
                window.isScanningInternal = false;
                playTone(100, 400, 'square', 0.6); // Louder low buzz
                setTimeout(() => playTone(100, 400, 'square', 0.6), 500);

                // Balikin fokus ke SPK Code
                setTimeout(() => {
                    const el = document.getElementById('scan_spk');
                    if (el) { el.focus(); el.select(); }
                }, 100);
            });

        // Enter key navigation: SPK → Qty → Whse → Label
        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Enter') return;

            const spk   = document.getElementById('scan_spk');
            const qty   = document.getElementById('scan_qty');
            const whse  = document.getElementById('scan_whse');
            const label = document.getElementById('scan_label');
            const active = document.activeElement;

            if (active === spk)   { e.preventDefault(); qty?.focus(); }
            else if (active === qty)  { e.preventDefault(); whse?.focus(); }
            else if (active === whse) { e.preventDefault(); label ? label.focus() : null; }
        });

        // Turbo Scan Handler for Label
        const labelInput = document.getElementById('scan_label');
        let autoTimer;
        let lastScannedVal = '';
        let lastScanTime = 0;

        if (labelInput) {
            const processScan = (val) => {
                const now = Date.now();
                // Anti-Double: Jangan kirim kalau nilainya sama dan jaraknya kurang dari 1 detik
                if (val === lastScannedVal && (now - lastScanTime) < 1000) return;
                
                lastScannedVal = val;
                lastScanTime = now;

                // Instant UI Update
                const currentSpk = document.getElementById('scan_spk')?.value;
                const currentQty = document.getElementById('scan_qty')?.value;
                const currentWhse = document.getElementById('scan_whse')?.value;
                
                window.dispatchEvent(new CustomEvent('add-pending', { 
                    detail: { label: val, spk: currentSpk, qty: currentQty, whse: currentWhse } 
                }));

                @this.addItem(val, currentSpk, currentQty, currentWhse);
            };

            // Jalur Cepat (Tombol Enter)
            labelInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(autoTimer);
                    const val = this.value.trim();
                    if (val) {
                        this.value = ''; 
                        processScan(val);
                    }
                }
            });

            // Jalur Cadangan (Timer)
            labelInput.addEventListener('input', function(e) {
                clearTimeout(autoTimer);
                if (this.value.trim() !== '') {
                    autoTimer = setTimeout(() => {
                        const val = this.value.trim();
                        if (val) {
                            this.value = ''; 
                            processScan(val);
                        }
                    }, 450); 
                }
            });
        }

        Livewire.on('scan-success', () => {
            window.isScanningInternal = false;
            playTone(1200, 200, 'sawtooth', 0.4); 
            setTimeout(() => playTone(1500, 100, 'sawtooth', 0.3), 50); 
        });

        Livewire.on('scan-error', () => {
            window.isScanningInternal = false;
            playTone(100, 400, 'square', 0.6); 
            setTimeout(() => playTone(100, 400, 'square', 0.6), 500);

            // Balikin fokus ke SPK Code
            setTimeout(() => {
                const el = document.getElementById('scan_spk');
                if (el) { el.focus(); el.select(); }
            }, 100);
        });
    });
    </script>

    <style>
        .bg-blue-600 input:focus, .bg-orange-500 input:focus {
            box-shadow: 0 0 0 3px rgba(255,255,255,0.35);
        }
    </style>

    {{-- Success Modal --}}
    @if($showSuccessModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-300">
            <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden animate-in zoom-in-95 duration-300 flex flex-col max-h-[90vh]">
                <div class="p-8 text-center flex-1 overflow-y-auto custom-scrollbar">
                    <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h3 class="text-2xl font-black text-gray-800 mb-2 tracking-tighter italic">PALLET CREATED!</h3>
                    <p class="text-gray-500 mb-6 text-sm">Pallet ID: <span class="font-black text-blue-600 tracking-tight">{{ $lastGeneratedPalletId }}</span></p>
                    
                    {{-- SAP Sync Status Section --}}
                    <div class="mb-8 p-4 rounded-2xl border-2 {{ $sapSyncStatus === 'pending' ? 'bg-blue-50 border-blue-100' : (empty($failedSapItems) ? 'bg-green-50 border-green-100' : 'bg-red-50 border-red-100') }} transition-colors">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[10px] font-black uppercase tracking-widest {{ $sapSyncStatus === 'pending' ? 'text-blue-500' : (empty($failedSapItems) ? 'text-green-600' : 'text-red-600') }}">
                                SAP Integration Status
                            </span>
                            @if($sapSyncStatus === 'pending')
                                <div class="flex items-center space-x-1">
                                    <div class="w-1 h-1 bg-blue-400 rounded-full animate-bounce"></div>
                                    <div class="w-1 h-1 bg-blue-400 rounded-full animate-bounce [animation-delay:-0.3s]"></div>
                                    <div class="w-1 h-1 bg-blue-400 rounded-full animate-bounce [animation-delay:-0.5s]"></div>
                                </div>
                            @endif
                        </div>

                        @if($sapSyncStatus === 'pending')
                            <p class="text-xs font-bold text-blue-700 italic">Sinkronisasi ke SAP sedang berjalan di background...</p>
                        @elseif(empty($failedSapItems))
                            <p class="text-xs font-bold text-green-700">✅ Berhasil! Semua data terkirim ke SAP tanpa kendala.</p>
                        @else
                            <div class="space-y-3">
                                <p class="text-xs font-black text-red-700 uppercase tracking-tighter">⚠️ Ada {{ count($failedSapItems) }} SPK yang gagal terkirim ke SAP:</p>
                                <div class="overflow-hidden border border-red-200 rounded-xl">
                                    <table class="w-full text-left text-[10px]">
                                        <thead class="bg-red-100 text-red-700 font-black uppercase tracking-widest">
                                            <tr>
                                                <th class="px-3 py-2">SPK No</th>
                                                <th class="px-3 py-2">Error Message</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-red-100 bg-white">
                                            @foreach($failedSapItems as $failed)
                                                <tr>
                                                    <td class="px-3 py-2 font-black text-gray-800">{{ $failed['spk_no'] }}</td>
                                                    <td class="px-3 py-2 font-bold text-red-600 italic break-words">{{ $failed['sap_error_msg'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <button wire:click="retrySapSync" class="w-full py-2 bg-red-600 hover:bg-red-700 text-white text-[10px] font-black uppercase tracking-widest rounded-xl transition-all shadow-lg shadow-red-200">
                                    RETRY SAP SYNC
                                </button>
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <a href="{{ route('wms.pallet-form.print', $lastGeneratedPalletId) }}" target="_blank" 
                            class="block py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 transition-all active:scale-95 text-sm uppercase">
                            Print Label
                        </a>
                        <button wire:click="resetWholeForm" type="button"
                            class="block py-4 bg-slate-800 hover:bg-black text-white font-bold rounded-2xl transition-all active:scale-95 text-sm uppercase">
                            Next Scan
                        </button>
                    </div>
                </div>
                <div class="bg-gray-50 px-8 py-4 text-center">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">SAP Batch Processing v2.0</span>
                </div>
            </div>
        </div>
    @endif
</div>
