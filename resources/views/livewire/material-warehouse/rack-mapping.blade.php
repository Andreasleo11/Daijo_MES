<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Header & Legend -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-black rounded-full uppercase tracking-wider">Raw Material Storage</span>
                    <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Material Warehouse Mapping</h1>
                </div>
                <p class="text-gray-500 text-sm mt-1">Monitoring & Tata Letak Rak Bahan Baku / Material</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-4 bg-gray-50 p-2 rounded-xl border border-gray-200">
                    <div class="flex items-center px-3 py-1 bg-white rounded-lg border border-gray-200 text-[10px] font-bold text-gray-400">
                        <span class="w-2 h-2 bg-gray-300 rounded-full mr-2"></span> EMPTY
                    </div>
                    <div class="flex items-center px-3 py-1 bg-amber-50 rounded-lg border border-amber-200 text-[10px] font-bold text-amber-600">
                        <span class="w-2 h-2 bg-amber-400 rounded-full mr-2"></span> PARTIAL
                    </div>
                    <div class="flex items-center px-3 py-1 bg-emerald-50 rounded-lg border border-emerald-200 text-[10px] font-bold text-emerald-700">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full mr-2"></span> FULL
                    </div>
                </div>
                <button wire:click="$set('showAddRackModal', true)" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    ADD RACK MATERIAL
                </button>
            </div>
        </div>

        @if (session()->has('success'))
            <div class="bg-emerald-100 border-l-4 border-emerald-500 p-4 rounded-r-xl shadow-sm animate-in fade-in slide-in-from-top duration-300">
                <p class="text-emerald-800 text-sm font-bold uppercase tracking-widest italic">{{ session('success') }}</p>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 border-l-4 border-red-500 p-4 rounded-r-xl shadow-sm animate-in fade-in slide-in-from-top duration-300">
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
                                <div class="flex flex-col gap-2 flex-1 min-w-[70px]">
                                    <div class="text-[9px] font-black text-emerald-600 text-center uppercase tracking-tighter border-b border-emerald-100 mb-1 pb-1">
                                        LVL {{ $level }}
                                    </div>
                                    <div class="space-y-2">
                                        @foreach($positions as $pos)
                                            @php
                                                $statusColor = 'bg-gray-100 hover:bg-gray-200 border-gray-200 text-gray-700';
                                                if($pos->status == 'PARTIAL') $statusColor = 'bg-amber-100 hover:bg-amber-200 border-amber-300 text-amber-900';
                                                if($pos->status == 'FULL') $statusColor = 'bg-emerald-100 hover:bg-emerald-200 border-emerald-300 text-emerald-900';
                                                
                                                $isSelected = $selectedPositionId == $pos->id;
                                                $ringClass = $isSelected ? 'ring-2 ring-emerald-500 ring-offset-2' : '';
                                            @endphp
                                            <button wire:click="selectPosition({{ $pos->id }})" 
                                                    class="w-full aspect-square border-2 {{ $statusColor }} {{ $ringClass }} rounded-xl p-1.5 transition-all group relative overflow-hidden flex flex-col justify-between items-center shadow-xs"
                                                    title="{{ $pos->position_code }} ({{ $pos->slot_label ?? 'Slot' }})">
                                                
                                                <div class="text-[8px] font-black text-gray-500 group-hover:text-gray-800 text-center uppercase leading-none truncate w-full">
                                                    S{{ $pos->slot_no }}
                                                </div>

                                                <div class="text-[9px] font-black text-emerald-800 text-center uppercase leading-none my-0.5 truncate w-full">
                                                    {{ $pos->slot_label ?: $pos->position_code }}
                                                </div>

                                                @if($pos->last_item_code)
                                                    <div class="text-[7px] font-extrabold text-emerald-700 bg-emerald-50/80 px-1 py-0.5 rounded leading-none w-full truncate text-center">
                                                        {{ $pos->last_item_code }}
                                                    </div>
                                                @else
                                                    <div class="text-[7px] font-medium text-gray-400 leading-none">
                                                        -
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
            <div class="w-full lg:w-96 flex-shrink-0">
                @if($showDetail && $selectedPosData)
                    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden sticky top-6 animate-in slide-in-from-right duration-300">
                        <div class="bg-emerald-600 p-6 text-white relative">
                            <div class="absolute top-0 right-0 p-4 opacity-10">
                                <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            </div>
                            <div class="relative z-10">
                                <span class="text-[10px] font-black text-emerald-200 uppercase tracking-widest bg-emerald-700/60 px-2 py-0.5 rounded">Material Slot Config</span>
                                <h3 class="text-2xl font-black italic uppercase tracking-tighter mt-1">{{ $selectedPosData->position_code }}</h3>
                                <p class="text-emerald-100 text-xs font-medium mt-1">Level {{ $selectedPosData->level_no }} &bull; Slot {{ $selectedPosData->slot_no }}</p>
                            </div>
                        </div>

                        <div class="p-6 space-y-5">
                            <!-- Form Edit Slot -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Kode Slot / Position Code</label>
                                    <input type="text" wire:model="editPositionCode" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-sm font-bold focus:bg-white focus:border-emerald-500 outline-none uppercase transition-all">
                                    @error('editPositionCode') <span class="text-[10px] text-red-500 font-semibold">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Label Custom / Display Name</label>
                                    <input type="text" wire:model="editSlotLabel" placeholder="Misal: RESIN-A1, BAGGING-01" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-sm font-bold focus:bg-white focus:border-emerald-500 outline-none transition-all">
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
                                        <input type="number" step="0.01" wire:model="editMaxCapacity" placeholder="Ex: 500" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-bold focus:bg-white focus:border-emerald-500 outline-none transition-all">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Item / Material Code</label>
                                    <input type="text" wire:model="editLastItemCode" placeholder="Kode Material (Opsional)" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs font-bold focus:bg-white focus:border-emerald-500 outline-none uppercase transition-all">
                                </div>

                                <div class="pt-2 flex gap-2">
                                    <button wire:click="saveSettings" class="flex-grow py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl text-[10px] uppercase tracking-widest shadow-md transition-all active:scale-95">
                                        Simpan Perubahan
                                    </button>
                                    <button wire:click="resetSlot" onclick="return confirm('Reset status slot ini menjadi EMPTY?')" class="p-3 bg-gray-50 hover:bg-red-50 text-gray-400 hover:text-red-500 border border-gray-200 rounded-xl transition-all" title="Reset Status">
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
                            <p class="text-gray-400 text-[10px] italic">Klik salah satu slot pada rak material <br> untuk mengedit kode, label, dan kapasitas</p>
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
