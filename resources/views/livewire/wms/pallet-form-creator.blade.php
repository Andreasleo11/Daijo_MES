<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Header Section -->
        <div class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Generate Pallet Form</h1>
                <p class="text-gray-500">Scan box dan lengkapi detail palet untuk Highly Marelli (HM)</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('wms.outbound') }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-semibold flex items-center transition-all shadow-lg shadow-red-100">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    OUTBOUND SCAN
                </a>
                <a href="{{ route('wms.mapping') }}" class="px-4 py-2 bg-slate-800 hover:bg-black text-white rounded-xl text-sm font-semibold flex items-center transition-all shadow-lg shadow-gray-200">
                    <svg class="w-4 h-4 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                    WAREHOUSE MAPPING
                </a>
                <a href="{{ route('wms.pallet-form.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-semibold flex items-center transition-all">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    VIEW HISTORY
                </a>
                <span class="px-4 py-2 bg-blue-50 text-blue-700 rounded-full text-sm font-semibold flex items-center">Gudang J06</span>
            </div>
        </div>

        @if (session()->has('error'))
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-md">
                <p class="text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Column: Manual Form -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h2 class="text-lg font-semibold mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Pallet Details
                    </h2>
                    
                    <div class="space-y-4">
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Part No</label>
                            <input type="text" wire:model.live.debounce.300ms="part_no" placeholder="Cari Part No atau Nama Item..." 
                                class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                            @error('part_no') <span class="text-xs text-red-500">{{ $message }}</span> @enderror

                            @if($showDropdown)
                                <div class="absolute z-50 w-full mt-1 bg-white border border-gray-100 rounded-xl shadow-xl max-h-60 overflow-y-auto">
                                    @foreach($searchResults as $result)
                                        <div wire:click="selectPartNo('{{ $result['item_code'] }}', '{{ $result['item_name'] }}')" 
                                            class="px-4 py-3 hover:bg-blue-50 cursor-pointer border-b border-gray-50 last:border-none transition-colors">
                                            <div class="font-bold text-gray-800">{{ $result['item_code'] }}</div>
                                            <div class="text-xs text-gray-500">{{ $result['item_name'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Model / Name</label>
                            <input type="text" wire:model="model_name" readonly 
                                class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-gray-500 italic outline-none">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Prod Date</label>
                                <input type="date" wire:model="prod_date" 
                                    class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                                @error('prod_date') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Shift</label>
                                <select wire:model="delivery_shift" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                                    <option value="">Pilih Shift</option>
                                    <option value="1">Shift 1</option>
                                    <option value="2">Shift 2</option>
                                    <option value="3">Shift 3</option>
                                </select>
                                @error('delivery_shift') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Lot No. / MO</label>
                            <input type="text" wire:model="lot_no" placeholder="Nullable" 
                                class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                            @error('lot_no') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Name</label>
                            <input type="text" wire:model="delivery_name" placeholder="Nama pengirim..." 
                                class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                            @error('delivery_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="p-4 bg-blue-50 rounded-2xl border border-blue-100">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm text-blue-600 font-semibold">Auto Calculated Qty</span>
                            </div>
                            <div class="text-2xl font-black text-blue-800">
                                {{ number_format($total_pallet_qty, 0) }} <span class="text-sm font-normal">pcs</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                            <textarea wire:model="remarks" rows="2" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Scanning & List -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Scanning Bar -->
                <div class="bg-blue-600 p-6 rounded-2xl shadow-lg shadow-blue-200">
                    <h2 class="text-white text-lg font-semibold mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 00-1 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                        Scanning Session (Per Box)
                    </h2>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-white/70 uppercase mb-1">SPK Code</label>
                            <input type="text" wire:model="scan_spk" id="scan_spk" 
                                class="w-full px-4 py-3 bg-white/20 border-2 border-white/30 rounded-xl text-white font-bold focus:bg-white focus:text-gray-800 outline-none transition-all"
                                placeholder="Scan..."
                                wire:keydown.enter="$emit('focus-qty')">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-white/70 uppercase mb-1">Quantity</label>
                            <input type="number" wire:model="scan_qty" id="scan_qty" 
                                class="w-full px-4 py-3 bg-white/20 border-2 border-white/30 rounded-xl text-white font-bold focus:bg-white focus:text-gray-800 outline-none transition-all"
                                placeholder="Qty"
                                wire:keydown.enter="$emit('focus-whse')">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-white/70 uppercase mb-1">Warehouse</label>
                            <input type="text" wire:model="scan_whse" id="scan_whse" 
                                class="w-full px-4 py-3 bg-white/20 border-2 border-white/30 rounded-xl text-white font-bold focus:bg-white focus:text-gray-800 outline-none transition-all"
                                placeholder="Whse"
                                wire:keydown.enter="$emit('focus-label')">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-white/70 uppercase mb-1">Label</label>
                            <input type="text" wire:model="scan_label" id="scan_label" 
                                class="w-full px-4 py-3 bg-white/20 border-2 border-white/30 rounded-xl text-white font-bold focus:bg-white focus:text-gray-800 outline-none transition-all"
                                placeholder="Label"
                                wire:keydown.enter="addItem">
                        </div>
                    </div>

                    @if (session()->has('scan_error'))
                        <div class="mt-3 text-red-200 text-sm font-semibold italic">{{ session('scan_error') }}</div>
                    @endif
                </div>

                <!-- Scanned List -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-50 flex justify-between items-center">
                        <h2 class="text-lg font-semibold">List of Scanned Boxes</h2>
                        <div class="text-2xl font-black text-blue-600">{{ $total_box }} <span class="text-sm text-gray-400 font-normal">Boxes</span></div>
                    </div>
                    
                    <div class="max-h-[500px] overflow-y-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 text-gray-500 text-xs font-bold uppercase tracking-wider">
                                <tr>
                                    <th class="px-6 py-4">No</th>
                                    <th class="px-6 py-4">SPK</th>
                                    <th class="px-6 py-4">Qty</th>
                                    <th class="px-6 py-4">Warehouse</th>
                                    <th class="px-6 py-4">Label</th>
                                    <th class="px-6 py-4 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse ($scanned_items as $index => $item)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 text-sm text-gray-400">#{{ $loop->iteration }}</td>
                                        <td class="px-6 py-4 font-mono font-bold text-gray-800">{{ $item['spk_no'] }}</td>
                                        <td class="px-6 py-4 font-bold text-blue-600">{{ number_format($item['qty'], 0) }}</td>
                                        <td class="px-6 py-4 text-gray-600">{{ $item['warehouse'] }}</td>
                                        <td class="px-6 py-4 text-gray-600">{{ $item['label'] }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <button wire:click="removeItem({{ $index }})" class="text-red-400 hover:text-red-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">
                                            Belum ada box yang di-scan. Masukkan data melalui panel biru di atas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Final Action -->
                    <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end">
                        <button wire:click="generateForm" class="px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 transition-all active:scale-95 flex items-center">
                            <span>GENERATE PALLET FORM</span>
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2v4h10z"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        let autoSubmitTimer;

        document.addEventListener('livewire:init', () => {
            Livewire.on('focus-spk', () => {
                setTimeout(() => {
                    const spkInput = document.getElementById('scan_spk');
                    if (spkInput) {
                        spkInput.focus();
                        spkInput.select(); // Tambahan: blok text agar jika ada sisa langsung tertimpa
                    }
                }, 50); // Delay kecil untuk memastikan DOM sudah siap
            });

            Livewire.on('focus-qty', () => {
                document.getElementById('scan_qty').focus();
            });

            Livewire.on('focus-whse', () => {
                document.getElementById('scan_whse').focus();
            });

            Livewire.on('focus-label', () => {
                document.getElementById('scan_label').focus();
            });
        });

        // Smart Scanning Logic
        document.addEventListener('keydown', (e) => {
            const spk = document.getElementById('scan_spk');
            const qty = document.getElementById('scan_qty');
            const whse = document.getElementById('scan_whse');
            const label = document.getElementById('scan_label');

            if (e.key === 'Enter') {
                if (document.activeElement === spk) {
                    e.preventDefault();
                    qty.focus();
                } else if (document.activeElement === qty) {
                    e.preventDefault();
                    whse.focus();
                } else if (document.activeElement === whse) {
                    e.preventDefault();
                    label.focus();
                }
            }
        });

        // Auto-Submit Timer for Label Field
        document.addEventListener('input', (e) => {
            const label = document.getElementById('scan_label');
            
            if (document.activeElement === label) {
                clearTimeout(autoSubmitTimer);
                
                // If label has content, start 1s timer to auto-submit
                if (label.value.trim() !== '') {
                    autoSubmitTimer = setTimeout(() => {
                        @this.addItem();
                    }, 1000); // 1-second delay
                }
            }
        });
    </script>

    <style>
        input:focus {
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.4);
        }
    </style>
</div>
