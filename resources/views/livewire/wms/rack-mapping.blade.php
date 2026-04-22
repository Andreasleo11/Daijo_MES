<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Header & Legenda -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Warehouse Mapping</h1>
                <p class="text-gray-500 text-sm">Monitoring Hunian Rak Gudang J06 (Highly Marelli)</p>
            </div>
            <div class="flex items-center gap-4">
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

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Grid Container -->
            <div class="flex-grow flex flex-wrap gap-6 items-start" id="mapping-grid">
                @foreach($racks as $rack)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-4 w-full md:w-[calc(50%-12px)] xl:w-[calc(33.333%-16px)]">
                        <div class="flex justify-between items-center border-b border-gray-50 pb-3">
                            <h3 class="text-lg font-black text-blue-600 italic tracking-tighter uppercase">Rack {{ $rack->rack_code }}</h3>
                            <span class="text-[10px] font-bold text-gray-400 bg-gray-50 px-2 py-1 rounded-full">{{ count($rack->positions) }} Slots</span>
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
                                            @endphp
                                            <button wire:click="selectPosition({{ $pos->id }})" 
                                                    class="w-full aspect-square border-2 {{ $statusColor }} rounded-lg p-1 transition-all group relative overflow-hidden"
                                                    title="{{ $pos->position_code }}">
                                                
                                                <div class="text-[8px] font-black text-gray-400 group-hover:text-gray-600 text-center uppercase leading-none">
                                                    S{{ $pos->slot_no }}
                                                </div>

                                                @if($pos->pallet_forms_count > 0)
                                                    <div class="absolute bottom-1 right-1">
                                                        <span class="flex h-2 w-2">
                                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 {{ $pos->status == 'FULL' ? 'bg-red-400' : 'bg-yellow-400' }}"></span>
                                                            <span class="relative inline-flex rounded-full h-2 w-2 {{ $pos->status == 'FULL' ? 'bg-red-500' : 'bg-yellow-500' }}"></span>
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

                                @if($selectedPosData->last_item_code)
                                    <div class="bg-gray-50 border border-gray-100 p-6 rounded-2xl text-center">
                                        <div class="text-[10px] text-gray-400 font-bold uppercase mb-1">Current Part No</div>
                                        <div class="text-2xl font-black text-gray-800 tracking-wider">{{ $selectedPosData->last_item_code }}</div>
                                        <div class="mt-4 text-3xl font-black text-blue-600">
                                            {{ $selectedPosData->pallet_forms_count }} <span class="text-[10px] text-gray-400 uppercase font-black italic">Pallet(s)</span>
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
                                        <input type="text" wire:model.defer="editCustomerCode" placeholder="CUST ID" class="w-full bg-gray-50 border border-gray-100 rounded-xl px-4 py-2 text-sm font-bold focus:bg-white focus:border-blue-500 outline-none transition-all uppercase">
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
                            <input type="text" wire:model.defer="newRackCustomer" placeholder="Ex: MARELLI" 
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 outline-none font-black text-xs text-center uppercase tracking-widest transition-all">
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
