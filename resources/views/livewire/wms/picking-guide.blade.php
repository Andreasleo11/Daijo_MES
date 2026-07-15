<div class="max-w-7xl mx-auto space-y-6" x-data="{ 
    printGuide() {
        window.print();
    },
    activeTab: 'create'
}">
    {{-- Header --}}
    <div class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100 print:hidden">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">WMS Delivery Picking Guide (FIFO)</h1>
            <p class="text-gray-500 text-sm font-medium">Buat dokumen pengambilan barang terurut FIFO dan pantau progress pemindahan dari rak secara real-time.</p>
        </div>
        <div class="flex space-x-2">
            @if($this->activePickingHeader)
                <button @click="printGuide()" class="px-4 py-2 bg-slate-800 hover:bg-black text-white rounded-xl text-sm font-semibold transition-all flex items-center space-x-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>CETAK DOKUMEN</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Session Alerts --}}
    @if (session()->has('error'))
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-xl flex items-start space-x-3 print:hidden">
            <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <p class="text-red-700 font-medium text-sm">{{ session('error') }}</p>
        </div>
    @endif
    @if (session()->has('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-xl flex items-start space-x-3 print:hidden">
            <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <p class="text-green-700 font-medium text-sm">{{ session('success') }}</p>
        </div>
    @endif

    {{-- Main Area when NO active picking list is selected --}}
    @if(!$activePickingId)
        {{-- Navigation Tabs --}}
        <div class="flex space-x-2 border-b border-gray-200 pb-px print:hidden">
            <button @click="activeTab = 'create'" 
                :class="activeTab === 'create' ? 'border-blue-500 text-blue-600 font-bold border-b-2' : 'text-gray-500 hover:text-gray-700 font-medium'"
                class="px-4 py-2.5 text-sm transition-all focus:outline-none">
                Buat Lembar Picking Baru
            </button>
            <button @click="activeTab = 'active_sheets'" 
                :class="activeTab === 'active_sheets' ? 'border-blue-500 text-blue-600 font-bold border-b-2' : 'text-gray-500 hover:text-gray-700 font-medium'"
                class="px-4 py-2.5 text-sm transition-all focus:outline-none flex items-center space-x-1.5">
                <span>Dokumen Aktif</span>
                @if(count($this->activePickingSheets) > 0)
                    <span class="px-2 py-0.5 bg-blue-100 text-blue-800 text-[10px] rounded-full font-black">
                        {{ count($this->activePickingSheets) }}
                    </span>
                @endif
            </button>
            <button @click="activeTab = 'history'" 
                :class="activeTab === 'history' ? 'border-blue-500 text-blue-600 font-bold border-b-2' : 'text-gray-500 hover:text-gray-700 font-medium'"
                class="px-4 py-2.5 text-sm transition-all focus:outline-none">
                Riwayat & Arsip
            </button>
        </div>

        {{-- TAB 1: CREATE NEW --}}
        <div x-show="activeTab === 'create'" class="space-y-6">
            {{-- Import DO / Document Panel --}}
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Pilihan Awal: Load Otomatis dari Dokumen DO / SO</label>
                <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3 items-end">
                    <div class="flex-1 space-y-1">
                        <input list="doc_nums_list" wire:model="docNumSearch" placeholder="Ketik atau Pilih Nomor Dokumen (doc_num)..."
                            class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm font-bold uppercase">
                        <datalist id="doc_nums_list">
                            @foreach($this->availableDocNums as $num)
                                <option value="{{ $num }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <button type="button" wire:click="loadFromDocNum" 
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-sm transition-all shadow-md shadow-blue-100">
                        LOAD DATA DOKUMEN
                    </button>
                </div>
            </div>

            {{-- Request Item List Form --}}
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
                <div class="flex justify-between items-center">
                    <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Daftar Kebutuhan Barang (Request List)</h2>
                    <button type="button" wire:click="addItemRow" class="text-xs font-bold text-blue-600 hover:text-blue-700 flex items-center space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>Tambah Item Manual</span>
                    </button>
                </div>

                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-xs font-bold text-gray-400 uppercase">
                                <th class="py-3 px-2">Part Number (Item Code)</th>
                                <th class="py-3 px-2">Nama Model (Opsional)</th>
                                <th class="py-3 px-2 w-32">Kuantitas (PCS)</th>
                                <th class="py-3 px-2 w-16 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-sm">
                            @foreach($requestItems as $index => $item)
                                <tr>
                                    <td class="py-2.5 px-2">
                                        <input list="items_list_{{ $index }}" wire:model.defer="requestItems.{{ $index }}.item_code" placeholder="Ketik / Pilih Item..."
                                            class="w-full px-2 py-1.5 border border-gray-200 rounded-lg text-xs font-mono font-bold uppercase focus:ring-1 focus:ring-blue-500">
                                        <datalist id="items_list_{{ $index }}">
                                            @foreach($this->availableItems as $opt)
                                                <option value="{{ $opt['item_code'] }}">{{ $opt['item_name'] }}</option>
                                            @endforeach
                                        </datalist>
                                    </td>
                                    <td class="py-2.5 px-2">
                                        <input type="text" wire:model.defer="requestItems.{{ $index }}.item_name" placeholder="Nama model..."
                                            class="w-full px-2 py-1.5 border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-blue-500">
                                    </td>
                                    <td class="py-2.5 px-2">
                                        <input type="number" wire:model.defer="requestItems.{{ $index }}.quantity" placeholder="Qty..."
                                            class="w-full px-2 py-1.5 border border-gray-200 rounded-lg text-xs font-bold focus:ring-1 focus:ring-blue-500 text-right">
                                    </td>
                                    <td class="py-2.5 px-2 text-center">
                                        <button type="button" wire:click="removeItemRow({{ $index }})" 
                                            class="text-gray-400 hover:text-red-500 p-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile List View --}}
                <div class="block md:hidden space-y-4">
                    @foreach($requestItems as $index => $item)
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-3 relative">
                            <button type="button" wire:click="removeItemRow({{ $index }})" 
                                class="absolute top-3 right-3 text-gray-400 hover:text-red-500 p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Part Number</label>
                                <input list="items_list_mob_{{ $index }}" wire:model.defer="requestItems.{{ $index }}.item_code" placeholder="Ketik / Pilih Item..."
                                    class="w-full px-2 py-1.5 border border-gray-200 rounded-lg text-xs font-mono font-bold uppercase focus:ring-1 focus:ring-blue-500">
                                <datalist id="items_list_mob_{{ $index }}">
                                    @foreach($this->availableItems as $opt)
                                        <option value="{{ $opt['item_code'] }}">{{ $opt['item_name'] }}</option>
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Nama Model (Opsional)</label>
                                <input type="text" wire:model.defer="requestItems.{{ $index }}.item_name" placeholder="Nama model..."
                                    class="w-full px-2 py-1.5 border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Kuantitas (PCS)</label>
                                <input type="number" wire:model.defer="requestItems.{{ $index }}.quantity" placeholder="Qty..."
                                    class="w-full px-2 py-1.5 border border-gray-200 rounded-lg text-xs font-bold focus:ring-1 focus:ring-blue-500">
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-end space-x-2 pt-2 border-t border-gray-50">
                    <button type="button" wire:click="clear" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-xs transition-all">
                        RESET FORM
                    </button>
                    <button type="button" wire:click="createPickingSheet" 
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs transition-all shadow-md shadow-blue-100">
                        BUAT DOKUMEN PICKING (FIFO)
                    </button>
                </div>
            </div>
        </div>

        {{-- TAB 2: ACTIVE SHEETS LIST --}}
        <div x-show="activeTab === 'active_sheets'" class="space-y-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Dokumen Picking Sedang Berjalan (Active)</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-xs font-bold text-gray-400 bg-gray-50/20 uppercase">
                                <th class="py-3.5 px-6">No. Picking</th>
                                <th class="py-3.5 px-6">Asal DO/SO</th>
                                <th class="py-3.5 px-6">Tanggal Pembuatan</th>
                                <th class="py-3.5 px-6">Status</th>
                                <th class="py-3.5 px-6 text-center w-48">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-sm">
                            @forelse($this->activePickingSheets as $sheet)
                                <tr>
                                    <td class="py-3 px-6 font-mono font-black text-blue-600">{{ $sheet->picking_no }}</td>
                                    <td class="py-3 px-6 font-bold text-gray-700">{{ $sheet->doc_num ?: 'MANUAL INPUT' }}</td>
                                    <td class="py-3 px-6 text-gray-500 text-xs">{{ $sheet->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td class="py-3 px-6">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-black {{ $sheet->status === 'PENDING' ? 'bg-yellow-50 text-yellow-700' : 'bg-blue-50 text-blue-700' }} uppercase">
                                            {{ $sheet->status }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-6 text-center flex justify-center space-x-2">
                                        <button type="button" wire:click="selectPickingSheet({{ $sheet->id }})"
                                            class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-bold transition-all">
                                            BUKA
                                        </button>
                                        <button type="button" wire:click="cancelPickingSheet({{ $sheet->id }})"
                                            class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 rounded-lg text-xs font-bold transition-all">
                                            BATAL
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-400 font-medium">Tidak ada dokumen picking yang aktif saat ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 3: HISTORY ARCHIVE --}}
        <div x-show="activeTab === 'history'" class="space-y-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Arsip Dokumen Selesai / Batal</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-xs font-bold text-gray-400 bg-gray-50/20 uppercase">
                                <th class="py-3.5 px-6">No. Picking</th>
                                <th class="py-3.5 px-6">Asal DO/SO</th>
                                <th class="py-3.5 px-6">Tanggal Diperbarui</th>
                                <th class="py-3.5 px-6">Status Akhir</th>
                                <th class="py-3.5 px-6 text-center w-32">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-sm">
                            @forelse($this->completedPickingSheets as $sheet)
                                <tr>
                                    <td class="py-3 px-6 font-mono font-bold text-gray-600">{{ $sheet->picking_no }}</td>
                                    <td class="py-3 px-6 text-gray-700">{{ $sheet->doc_num ?: 'MANUAL INPUT' }}</td>
                                    <td class="py-3 px-6 text-gray-500 text-xs">{{ $sheet->updated_at->format('Y-m-d H:i:s') }}</td>
                                    <td class="py-3 px-6">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-black {{ $sheet->status === 'COMPLETED' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }} uppercase">
                                            {{ $sheet->status }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-6 text-center">
                                        <button type="button" wire:click="selectPickingSheet({{ $sheet->id }})"
                                            class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition-all">
                                            LIHAT
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-400 font-medium">Belum ada riwayat dokumen tersimpan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Detail View of Selected Active / Completed Picking List Document --}}
    @if($activePickingId && $this->activePickingHeader)
        @php
            $header = $this->activePickingHeader;
            $details = $header->details;
            
            // Calculate progress bar statistics
            $totalItems = $details->where('status', '!=', 'OUT_OF_STOCK')->count();
            $pickedCount = $details->where('status', '!=', 'OUT_OF_STOCK')->where('is_picked', true)->count();
            $progressPct = $totalItems > 0 ? round(($pickedCount / $totalItems) * 100) : 0;
        @endphp

        <div class="space-y-6">
            {{-- Print Header (Only visible on paper print) --}}
            <div class="hidden print:block border-b-2 border-black pb-4 mb-6">
                <h1 class="text-2xl font-black uppercase text-center">WMS Picking Sheet (FIFO)</h1>
                <div class="grid grid-cols-2 gap-4 text-xs font-bold mt-4">
                    <div>No. Picking: <span class="font-black text-sm">{{ $header->picking_no }}</span></div>
                    <div>No. Dokumen SO/DO: <span class="font-black">{{ $header->doc_num ?: 'MANUAL LIST' }}</span></div>
                    <div>Status: <span class="font-black">{{ $header->status }}</span></div>
                    <div class="text-right">Tanggal Cetak: {{ now()->format('Y-m-d H:i:s') }}</div>
                </div>
            </div>

            {{-- Document Summary Header --}}
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4 print:hidden">
                <div class="space-y-1">
                    <div class="flex items-center space-x-2">
                        <span class="text-xs font-bold text-gray-400 uppercase">Instansi Aktif</span>
                        <span class="px-2 py-0.5 text-[10px] font-black rounded-lg uppercase {{ $header->status === 'COMPLETED' ? 'bg-green-50 text-green-700' : 'bg-blue-50 text-blue-700' }}">
                            {{ $header->status }}
                        </span>
                    </div>
                    <h2 class="text-xl font-black text-gray-800 font-mono">{{ $header->picking_no }}</h2>
                    <p class="text-gray-400 text-xs font-medium">Diambil dari DO: <span class="font-bold text-gray-600">{{ $header->doc_num ?: 'Manual Input' }}</span> | Tanggal: {{ $header->created_at->format('Y-m-d H:i') }}</p>
                </div>
                
                {{-- Progress Bar --}}
                <div class="w-full md:w-80 space-y-2">
                    <div class="flex justify-between text-xs font-bold">
                        <span class="text-gray-500 uppercase">Progress Pengambilan</span>
                        <span class="text-blue-600">{{ $pickedCount }} / {{ $totalItems }} Box ({{ $progressPct }}%)</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ $progressPct }}%"></div>
                    </div>
                </div>

                {{-- Action controls --}}
                <div class="flex space-x-2">
                    <button type="button" wire:click="closeActiveSheet" 
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-xs transition-all">
                        TUTUP DOKUMEN
                    </button>
                </div>
            </div>

            {{-- Pick List Instructions Table --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50 print:hidden">
                    <h2 class="text-base font-bold text-gray-800">Rute Lokasi Rak (Terurut Posisi)</h2>
                    <span class="px-3 py-1 bg-slate-800 text-white text-[10px] font-black rounded-lg uppercase">
                        {{ $details->count() }} Baris Alokasi
                    </span>
                </div>

                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-xs font-bold text-gray-400 uppercase bg-gray-50/20">
                                <th class="py-3 px-4 w-12 print:hidden">Check</th>
                                <th class="py-3 px-4">Posisi Rak</th>
                                <th class="py-3 px-4">Pallet ID</th>
                                <th class="py-3 px-4">Part Number & Model</th>
                                <th class="py-3 px-4">No. SPK / Box Label</th>
                                <th class="py-3 px-4 text-right w-36">Jumlah Ambil (PCS)</th>
                                <th class="py-3 px-4">Keterangan Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-xs">
                            @foreach($details as $idx => $inst)
                                @php
                                    $isChecked = $inst->is_picked;
                                    $isCompleted = ($header->status === 'COMPLETED' || $header->status === 'CANCELLED');
                                @endphp
                                <tr class="transition-colors {{ $isChecked ? 'bg-gray-50 text-gray-400 line-through' : '' }}">
                                    {{-- Checkbox --}}
                                    <td class="py-3 px-4 print:hidden">
                                        @if($inst->pallet_id && !$isCompleted)
                                            <input type="checkbox" 
                                                wire:click="toggleDetailPicked({{ $inst->id }}, $event.target.checked)"
                                                {{ $isChecked ? 'checked' : '' }}
                                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                        @elseif($isChecked)
                                            <span class="text-green-500 font-bold">✔</span>
                                        @endif
                                    </td>
                                    {{-- Position --}}
                                    <td class="py-3 px-4 font-black">
                                        @if(!$inst->pallet_id)
                                            <span class="text-red-500">🚫 KOSONG</span>
                                        @else
                                            <span class="text-blue-600 text-sm tracking-wider">{{ $inst->position_code }}</span>
                                        @endif
                                    </td>
                                    {{-- Pallet ID --}}
                                    <td class="py-3 px-4 font-mono font-bold text-[11px]">{{ $inst->pallet_id ?: 'N/A' }}</td>
                                    {{-- Part No --}}
                                    <td class="py-3 px-4">
                                        <div class="font-black text-gray-800">{{ $inst->item_code }}</div>
                                        <div class="text-[10px] text-gray-400">{{ $inst->model_name }}</div>
                                    </td>
                                    {{-- SPK & Label --}}
                                    <td class="py-3 px-4">
                                        @if($inst->pallet_id)
                                            <div class="font-bold text-gray-600">SPK: <span class="font-mono">{{ $inst->spk_no ?? '-' }}</span></div>
                                            <div class="text-[10px] text-gray-400">Label: <span class="font-mono font-bold">{{ $inst->label }}</span></div>
                                        @else
                                            <span>-</span>
                                        @endif
                                    </td>
                                    {{-- Pick Qty --}}
                                    <td class="py-3 px-4 text-right font-black text-sm text-blue-600">
                                        {{ number_format($inst->qty_to_pick, 0) }} PCS
                                    </td>
                                    {{-- Status / Alerts --}}
                                    <td class="py-3 px-4">
                                        @if($inst->status === 'AVAILABLE')
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-black bg-green-50 text-green-700 uppercase">
                                                    Tersedia
                                                </span>
                                                @if($inst->fifo_seq)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-black bg-purple-100 text-purple-800 uppercase">
                                                        FIFO #{{ $inst->fifo_seq }}{{ $inst->fifo_seq == 1 ? ' (TERLAMA)' : '' }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($inst->created_at)
                                                <div class="text-[8px] text-gray-400 mt-0.5">Stok: {{ $inst->created_at->format('Y-m-d H:i') }}</div>
                                            @endif
                                        @elseif($inst->status === 'STOCK_SHORTAGE')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-black bg-amber-50 text-amber-700 uppercase">
                                                Kurang
                                            </span>
                                            <div class="text-[9px] text-amber-600 font-medium mt-0.5">{{ $inst->notes }}</div>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-black bg-red-50 text-red-700 uppercase">
                                                Kosong
                                            </span>
                                            <div class="text-[9px] text-red-600 font-medium mt-0.5">{{ $inst->notes }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile List Cards View --}}
                <div class="block md:hidden space-y-3 p-4 print:hidden">
                    @foreach($details as $idx => $inst)
                        @php
                            $isChecked = $inst->is_picked;
                            $isCompleted = ($header->status === 'COMPLETED' || $header->status === 'CANCELLED');
                        @endphp
                        <div class="p-4 bg-white rounded-xl border {{ $isChecked ? 'border-gray-200 bg-gray-50 opacity-60' : 'border-gray-200 shadow-sm' }} space-y-3">
                            <div class="flex justify-between items-start">
                                <div class="space-y-1">
                                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider block">POSISI RAK</span>
                                    @if(!$inst->pallet_id)
                                        <span class="text-red-500 font-black text-sm">🚫 KOSONG</span>
                                    @else
                                        <span class="text-blue-600 font-black text-base tracking-wider block">{{ $inst->position_code }}</span>
                                        <span class="text-[10px] font-bold text-gray-500 font-mono block">Pallet: {{ $inst->pallet_id }}</span>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider block">Qty Ambil</span>
                                    <span class="text-blue-600 font-black text-base block">{{ number_format($inst->qty_to_pick, 0) }} PCS</span>
                                </div>
                            </div>

                            <hr class="border-gray-100">

                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div>
                                    <span class="text-[9px] font-bold text-gray-400 uppercase block">Part & Model</span>
                                    <span class="font-black text-gray-800 block">{{ $inst->item_code }}</span>
                                    <span class="text-[9px] text-gray-500 block leading-tight">{{ $inst->model_name }}</span>
                                </div>
                                <div>
                                    <span class="text-[9px] font-bold text-gray-400 uppercase block">SPK & Label</span>
                                    @if($inst->pallet_id)
                                        <span class="font-bold text-gray-700 block font-mono">SPK: {{ $inst->spk_no }}</span>
                                        <span class="text-[9px] text-gray-500 block font-mono">LBL: {{ $inst->label }}</span>
                                    @else
                                        <span class="text-gray-400 block">-</span>
                                    @endif
                                </div>
                            </div>

                            <hr class="border-gray-100">

                            <div class="flex justify-between items-center text-xs">
                                <div>
                                    @if($inst->status === 'AVAILABLE')
                                        <div class="flex space-x-1 items-center">
                                            <span class="px-1.5 py-0.5 text-[8px] font-black bg-green-50 text-green-700 rounded uppercase">Tersedia</span>
                                            @if($inst->fifo_seq)
                                                <span class="px-1.5 py-0.5 text-[8px] font-black bg-purple-100 text-purple-800 rounded uppercase">FIFO #{{ $inst->fifo_seq }}</span>
                                            @endif
                                        </div>
                                    @elseif($inst->status === 'STOCK_SHORTAGE')
                                        <span class="px-1.5 py-0.5 text-[8px] font-black bg-amber-50 text-amber-700 rounded uppercase">Kurang</span>
                                    @else
                                        <span class="px-1.5 py-0.5 text-[8px] font-black bg-red-50 text-red-700 rounded uppercase">Kosong</span>
                                    @endif
                                </div>
                                <div class="text-[9px] text-gray-400">
                                    {{ $inst->created_at ? $inst->created_at->format('Y-m-d H:i') : '' }}
                                </div>
                            </div>

                            @if($inst->pallet_id && !$isCompleted)
                                <label class="flex items-center justify-center space-x-2 w-full py-2 bg-blue-50 border border-blue-200 rounded-lg text-blue-700 font-bold text-xs cursor-pointer select-none active:bg-blue-100 transition-colors">
                                    <input type="checkbox" 
                                        wire:click="toggleDetailPicked({{ $inst->id }}, $event.target.checked)"
                                        {{ $isChecked ? 'checked' : '' }}
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span>{{ $isChecked ? 'Batal Centang' : 'Tandai Sudah Diambil' }}</span>
                                </label>
                            @elseif($isChecked)
                                <div class="text-center py-2 bg-green-50 text-green-700 font-bold text-xs rounded-lg">
                                    ✔ SUDAH DIAMBIL
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
