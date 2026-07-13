<div class="max-w-7xl mx-auto space-y-6" x-data="{ 
    selectedBoxCid: @entangle('selectedBoxCid'),
    selectedBoxLabel: @entangle('selectedBoxLabel'),
    selectedBoxSourcePalletId: @entangle('selectedBoxSourcePalletId')
}">
    {{-- Header --}}
    <div class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Pallet Sorting & Consolidation</h1>
            <p class="text-gray-500 text-sm">Pindahkan box antar-pallet secara visual untuk merapikan gudang (1 Pallet = 1 Jenis Item).</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('wms.pallet-form.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-semibold transition-all">
                KEMBALI KE RIWAYAT
            </a>
        </div>
    </div>

    {{-- Control Panel --}}
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
        {{-- Load Pallet Form --}}
        <form wire:submit.prevent="addPallet" class="space-y-1">
            <label class="block text-xs font-bold text-gray-500 uppercase">Muat Pallet ke Workspace</label>
            <div class="flex space-x-2">
                <input type="text" wire:model="palletSearchInput" placeholder="Scan / Input Pallet ID..."
                    class="flex-1 px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm font-bold uppercase">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-sm transition-all">
                    LOAD
                </button>
            </div>
        </form>

        {{-- Scan Box Form --}}
        <form wire:submit.prevent="scanBox" class="space-y-1">
            <label class="block text-xs font-bold text-gray-500 uppercase">Cari & Pilih Box via Scan</label>
            <div class="flex space-x-2">
                <input type="text" wire:model="boxScanInput" placeholder="Scan Barcode Box..."
                    class="flex-1 px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm font-mono font-bold uppercase">
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-black text-white font-bold rounded-xl text-sm transition-all">
                    CARI
                </button>
            </div>
        </form>

        {{-- Add Target Pallet --}}
        <div class="flex justify-end">
            <button type="button" wire:click="addNewTargetPallet" 
                class="w-full md:w-auto px-5 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl text-sm transition-all flex items-center justify-center space-x-2 shadow-md shadow-green-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>BUAT PALLET TARGET BARU</span>
            </button>
        </div>
    </div>

    {{-- Status Alerts --}}
    @if (session()->has('error'))
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-xl flex items-start space-x-3">
            <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <p class="text-red-700 font-medium">{{ session('error') }}</p>
        </div>
    @endif
    @if (session()->has('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-xl flex items-start space-x-3">
            <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <p class="text-green-700 font-medium">{{ session('success') }}</p>
        </div>
    @endif

    {{-- Highlight Box Info Bar --}}
    <div x-show="selectedBoxCid" class="bg-amber-50 border border-amber-200 p-4 rounded-2xl flex items-center justify-between animate-in slide-in-from-top-2 duration-200">
        <div class="flex items-center space-x-3">
            <span class="flex h-3 w-3 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
            </span>
            <p class="text-sm font-semibold text-amber-800">
                Box Terpilih: <span class="font-mono font-bold" x-text="selectedBoxLabel"></span> 
                (dari Pallet: <span class="font-bold" x-text="selectedBoxSourcePalletId"></span>). 
                Klik tombol <span class="font-bold">"MASUKKAN"</span> pada pallet tujuan di bawah.
            </p>
        </div>
        <button @click="selectedBoxCid = ''; selectedBoxLabel = ''; selectedBoxSourcePalletId = '';" 
            class="text-xs font-bold text-amber-600 hover:text-amber-800 underline">
            BATALKAN
        </button>
    </div>

    {{-- Workspace Workspace Lanes --}}
    @if(count($workspacePallets) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($workspacePallets as $pIdx => $pallet)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col min-h-[500px]">
                    {{-- Lane Header --}}
                    <div class="p-4 border-b border-gray-100 flex justify-between items-start bg-gray-50/50">
                        <div class="space-y-1">
                            <div class="flex items-center space-x-2">
                                <span class="font-mono font-black text-gray-800 text-base">{{ $pallet['pallet_id'] }}</span>
                                @if($pallet['is_new'])
                                    <span class="px-2 py-0.5 bg-green-100 text-green-800 text-[10px] font-black rounded-lg uppercase">NEW</span>
                                @else
                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-800 text-[10px] font-black rounded-lg uppercase">STORED</span>
                                @endif
                            </div>
                            <div class="text-[9px] text-gray-400 font-bold uppercase mt-1">POSISI RAK:</div>
                            <div class="relative inline-block mt-0.5">
                                <select wire:change="changePalletPosition('{{ $pallet['pallet_id'] }}', $event.target.value)"
                                    class="text-xs font-black text-blue-600 bg-blue-50/50 hover:bg-blue-50 border border-blue-200 rounded-xl px-2 py-1.5 outline-none cursor-pointer transition-all">
                                    <option value="">-- Pilih Posisi Rak --</option>
                                    @foreach($this->availablePositions as $pos)
                                        <option value="{{ $pos->id }}" {{ $pallet['position_id'] == $pos->id ? 'selected' : '' }}>
                                            {{ $pos->position_code }} ({{ $pos->status }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button type="button" wire:click="removePalletFromWorkspace('{{ $pallet['pallet_id'] }}')"
                            class="text-gray-400 hover:text-red-500 transition-colors p-1 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    {{-- Lane Active Dropzone Indicator --}}
                    @if(!empty($selectedBoxCid) && $selectedBoxSourcePalletId !== $pallet['pallet_id'])
                        <div wire:click="moveBox('{{ $selectedBoxCid }}', '{{ $pallet['pallet_id'] }}')"
                            class="m-3 p-3 bg-amber-50 hover:bg-amber-100 border-2 border-dashed border-amber-300 hover:border-amber-400 rounded-xl text-center cursor-pointer transition-all animate-pulse">
                            <span class="text-xs font-bold text-amber-700 uppercase">👉 MASUKKAN KE PALLET INI</span>
                        </div>
                    @endif

                    {{-- Lane Content (Boxes) --}}
                    <div class="p-3 flex-1 overflow-y-auto max-h-[450px] space-y-3 custom-scrollbar">
                        @forelse($pallet['boxes'] as $box)
                            @php
                                $isThisBoxSelected = ($selectedBoxCid === $box['cid']);
                                $isThisBoxHighlighted = ($highlightedBoxCid === $box['cid']);
                            @endphp
                            <div class="p-3 border rounded-xl transition-all relative flex flex-col space-y-2 
                                {{ $isThisBoxSelected ? 'border-amber-400 bg-amber-50/20 shadow-md ring-2 ring-amber-400/50' : 'border-gray-100 bg-white hover:border-gray-200' }}
                                {{ $isThisBoxHighlighted ? 'ring-4 ring-blue-500/30' : '' }}"
                                id="box_card_{{ $box['cid'] }}">
                                
                                {{-- Card Content --}}
                                <div class="flex justify-between items-start">
                                    <div class="space-y-0.5">
                                        <div class="flex items-center space-x-1.5">
                                            @if($box['is_no_label'])
                                                <span class="text-orange-600 font-bold italic text-xs">🚫 NO LABEL</span>
                                            @else
                                                <span class="font-mono text-gray-700 text-xs font-bold">{{ $box['label'] }}</span>
                                            @endif
                                        </div>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase">SPK: <span class="font-mono text-gray-700">{{ $box['spk_no'] ?: 'MANUAL' }}</span></p>
                                        <div class="text-[10px] font-black text-gray-800 tracking-tight mt-1">{{ $box['part_no'] }}</div>
                                        <div class="text-[9px] text-gray-400 truncate max-w-[180px]">{{ $box['model_name'] }}</div>
                                    </div>
                                    <div class="text-right">
                                        <span class="px-2 py-0.5 bg-blue-50 text-blue-700 text-[10px] font-black rounded-lg">{{ $box['warehouse'] }}</span>
                                        <div class="text-base font-black text-blue-600 mt-1">{{ number_format($box['qty'], 0) }}</div>
                                    </div>
                                </div>

                                {{-- Card Actions --}}
                                <div class="pt-2 border-t border-gray-50 flex justify-between items-center text-xs">
                                    {{-- Group move dropdown helper --}}
                                    <div>
                                        @if($box['spk_no'])
                                            <button type="button" @click="let el = document.getElementById('gp_dropdown_{{ $pallet['pallet_id'] }}_{{ $loop->index }}'); el.classList.toggle('hidden')"
                                                class="text-gray-400 hover:text-blue-600 font-semibold flex items-center">
                                                <span>Grup SPK 🔽</span>
                                            </button>
                                            <div id="gp_dropdown_{{ $pallet['pallet_id'] }}_{{ $loop->index }}" class="hidden absolute left-3 bottom-10 bg-white border border-gray-200 rounded-xl shadow-xl z-50 p-2 min-w-[200px]">
                                                <div class="text-[9px] font-black text-gray-400 uppercase p-1.5 border-b border-gray-100 mb-1">Pindahkan Semua Box SPK ini ke:</div>
                                                @foreach($workspacePallets as $targetPallet)
                                                    @if($targetPallet['pallet_id'] !== $pallet['pallet_id'])
                                                        <button type="button" wire:click="moveAllBoxesBySpk('{{ $pallet['pallet_id'] }}', '{{ $box['spk_no'] }}', '{{ $targetPallet['pallet_id'] }}')"
                                                            class="w-full text-left px-2 py-1.5 text-xs font-bold text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors">
                                                            ➡️ {{ $targetPallet['pallet_id'] }}
                                                        </button>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Quick select to move --}}
                                    @if(!$isThisBoxSelected)
                                        <button type="button" wire:click="selectBoxManual('{{ $box['cid'] }}', '{{ $box['label'] ?: 'No Label' }}', '{{ $pallet['pallet_id'] }}')"
                                            class="px-2 py-1 bg-gray-50 hover:bg-amber-100 text-gray-600 hover:text-amber-700 font-bold rounded-lg border transition-all">
                                            PILIH BOX
                                        </button>
                                    @else
                                        <button type="button" wire:click="clearSelection"
                                            class="px-2 py-1 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-lg transition-all">
                                            TERPILIH
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="py-12 text-center text-gray-400 italic text-xs border border-dashed border-gray-200 rounded-2xl bg-gray-50/20">
                                Pallet kosong. Pindahkan box ke sini.
                            </div>
                        @endforelse
                    </div>

                    {{-- Lane Footer --}}
                    <div class="p-4 border-t border-gray-100 bg-gray-50/30 flex justify-between items-center text-xs">
                        <div class="font-bold text-gray-500">Box Count: <span class="text-gray-800 font-black">{{ count($pallet['boxes']) }}</span></div>
                        <div class="font-bold text-gray-500">Total Qty: <span class="text-blue-600 font-black">{{ number_format(array_sum(array_column($pallet['boxes'], 'qty')), 0) }}</span></div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center text-gray-400 italic">
            Workspace kosong. Muat satu atau beberapa pallet untuk memulai proses pemilahan (sorting).
        </div>
    @endif

    {{-- Apply Changes Controls --}}
    @if(count($workspacePallets) > 0)
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex justify-end space-x-3">
            <button type="button" onclick="confirm('Apakah Anda yakin ingin membatalkan semua sorting?') && @this.set('workspacePallets', [])"
                class="px-5 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-sm transition-all">
                BATALKAN SEMUA
            </button>
            <button type="button" wire:click="applySorting" 
                class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-sm transition-all shadow-lg shadow-blue-200 flex items-center space-x-2">
                <span>TERAPKAN KONSOLIDASI & SIMPAN</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </button>
        </div>
    @endif
</div>
