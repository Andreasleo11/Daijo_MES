<div class="p-6 bg-gray-50 min-h-screen">
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
                <a href="{{ route('wms.pallet-form.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-semibold flex items-center transition-all">
                    VIEW HISTORY
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

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Remarks</label>
                            <textarea wire:model="remarks" rows="2" class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm resize-none"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Scan + List --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Scan Panel --}}
                <div class="{{ $label_mode === 'NO_LABEL' ? 'bg-orange-500' : 'bg-blue-600' }} p-6 rounded-2xl shadow-lg transition-colors duration-300">
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
                            <div>
                                <label class="block text-xs font-bold text-white/70 uppercase mb-1">SPK Code</label>
                                <input type="text" wire:model.live.debounce.500ms="scan_spk" id="scan_spk"
                                    class="w-full px-4 py-3 bg-white/20 border-2 border-white/30 rounded-xl text-white font-bold focus:bg-white focus:text-gray-800 outline-none transition-all placeholder-white/50"
                                    placeholder="Scan SPK...">
                            </div>
                            {{-- Auto-filled item info --}}
                            <div>
                                <label class="block text-xs font-bold text-white/70 uppercase mb-1">Item (Auto)</label>
                                <div class="w-full px-4 py-3 bg-white/10 border-2 border-white/20 rounded-xl text-white/90 text-sm min-h-[50px]">
                                    @if($scan_part_no)
                                        <div class="font-bold leading-tight">{{ $scan_part_no }}</div>
                                        <div class="text-xs text-white/60 truncate">{{ $scan_model_name }}</div>
                                    @else
                                        <span class="text-white/40 italic text-xs">Scan SPK dulu...</span>
                                    @endif
                                </div>
                            </div>
                            {{-- Qty --}}
                            <div>
                                <label class="block text-xs font-bold text-white/70 uppercase mb-1">Quantity</label>
                                <input type="number" wire:model="scan_qty" id="scan_qty"
                                    class="w-full px-4 py-3 bg-white/20 border-2 border-white/30 rounded-xl text-white font-bold focus:bg-white focus:text-gray-800 outline-none transition-all placeholder-white/50"
                                    placeholder="Qty">
                            </div>
                            {{-- Whse --}}
                            <div>
                                <label class="block text-xs font-bold text-white/70 uppercase mb-1">Warehouse</label>
                                <input type="text" wire:model="scan_whse" id="scan_whse"
                                    class="w-full px-4 py-3 bg-white/20 border-2 border-white/30 rounded-xl text-white font-bold focus:bg-white focus:text-gray-800 outline-none transition-all placeholder-white/50"
                                    placeholder="Whse">
                            </div>
                        </div>

                        {{-- Label row --}}
                        <div class="mt-4 flex items-end gap-4">
                            <div class="flex-1">
                                <label class="block text-xs font-bold text-white/70 uppercase mb-1">Label Barcode</label>
                                <input type="text" wire:model="scan_label" id="scan_label"
                                    class="w-full px-4 py-3 bg-white/20 border-2 border-white/30 rounded-xl text-white font-bold focus:bg-white focus:text-gray-800 outline-none transition-all placeholder-white/50"
                                    placeholder="Scan label box...">
                            </div>
                            <button wire:click="addItem" type="button"
                                class="px-6 py-3 bg-white text-blue-700 font-black rounded-xl hover:bg-blue-50 transition-all active:scale-95 shadow-lg whitespace-nowrap">
                                + ADD BOX
                            </button>
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

                        <div class="mt-4 flex justify-end">
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
                            <thead class="bg-gray-50 text-gray-500 text-xs font-bold uppercase tracking-wider sticky top-0">
                                <tr>
                                    <th class="px-4 py-3">No</th>
                                    <th class="px-4 py-3">Part No</th>
                                    <th class="px-4 py-3">SPK</th>
                                    <th class="px-4 py-3 text-right">Qty</th>
                                    <th class="px-4 py-3">Whse</th>
                                    <th class="px-4 py-3">Label</th>
                                    <th class="px-4 py-3 text-right">Hapus</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse ($scanned_items as $index => $item)
                                    <tr class="{{ $item['is_no_label'] ? 'bg-orange-50 hover:bg-orange-100' : 'hover:bg-gray-50' }} transition-colors">
                                        <td class="px-4 py-3 text-gray-400 font-mono">#{{ $loop->iteration }}</td>
                                        <td class="px-4 py-3">
                                            @if($item['is_no_label'])
                                                <span class="text-orange-500 italic text-xs">—</span>
                                            @else
                                                <div class="font-bold text-gray-800 text-xs">{{ $item['part_no'] }}</div>
                                                <div class="text-gray-400 text-xs truncate max-w-[120px]">{{ $item['model_name'] }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 font-mono text-xs text-gray-700">
                                            {{ $item['spk_no'] ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-bold text-blue-600">
                                            {{ number_format($item['qty'], 0) }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $item['warehouse'] ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            @if($item['is_no_label'])
                                                <span class="inline-flex items-center px-2 py-1 bg-orange-100 text-orange-700 text-xs font-bold rounded-lg">
                                                    🚫 TANPA LABEL
                                                    @if($item['no_label_reason'])
                                                        <span class="ml-1 text-orange-500 font-normal">({{ $item['no_label_reason'] }})</span>
                                                    @endif
                                                </span>
                                            @else
                                                <span class="font-mono text-xs text-gray-600">{{ $item['label'] }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <button wire:click="removeItem({{ $index }})" type="button"
                                                class="text-red-400 hover:text-red-600 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-gray-400 italic">
                                            Belum ada box yang di-scan. Gunakan panel scan di atas.
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
                            class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 transition-all active:scale-95 flex items-center">
                            <span>GENERATE PALLET FORM</span>
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
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
        });

        // Enter key navigation: SPK → Qty → Whse → Label → addItem
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

        // Auto-submit 1s after label stops changing
        let autoTimer;
        document.addEventListener('input', (e) => {
            const label = document.getElementById('scan_label');
            if (!label || document.activeElement !== label) return;
            clearTimeout(autoTimer);
            if (label.value.trim() !== '') {
                autoTimer = setTimeout(() => { @this.addItem(); }, 1000);
            }
        });
    </script>

    <style>
        .bg-blue-600 input:focus, .bg-orange-500 input:focus {
            box-shadow: 0 0 0 3px rgba(255,255,255,0.35);
        }
    </style>
</div>
