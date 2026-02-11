
    <div class="max-w-4xl mx-auto p-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold mb-6">
                {{ $isEdit ? 'Edit Asakai' : 'Tambah Asakai Baru' }}
            </h2>

            <form wire:submit="save">
                {{-- Main Information --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium mb-2">Customer *</label>
                        <input type="text" wire:model="customer" 
                            class="w-full border rounded px-3 py-2 @error('customer') border-red-500 @enderror">
                        @error('customer') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Part No *</label>
                        <input type="text" wire:model="part_no" 
                            class="w-full border rounded px-3 py-2 @error('part_no') border-red-500 @enderror">
                        @error('part_no') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2">Issue *</label>
                        <textarea wire:model="issue" rows="3"
                                class="w-full border rounded px-3 py-2 @error('issue') border-red-500 @enderror"></textarea>
                        @error('issue') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Quantity *</label>
                        <input type="number" wire:model="quantity" 
                            class="w-full border rounded px-3 py-2 @error('quantity') border-red-500 @enderror">
                        @error('quantity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Lot Shift *</label>
                        <select wire:model="lot_shift" class="w-full border rounded px-3 py-2">
                            <option value="">- Pilih Shift -</option>
                            <option value="Shift 1">Shift 1</option>
                            <option value="Shift 2">Shift 2</option>
                            <option value="Shift 3">Shift 3</option>
                        </select>
                        @error('lot_shift') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Lot Date *</label>
                        <input type="date" wire:model="lot_date" 
                            class="w-full border rounded px-3 py-2 @error('lot_date') border-red-500 @enderror">
                        @error('lot_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Date Issue *</label>
                        <input type="date" wire:model="date_issue" 
                            class="w-full border rounded px-3 py-2 @error('date_issue') border-red-500 @enderror">
                        @error('date_issue') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- PIC Section --}}
                <div class="mb-6 p-4 bg-gray-50 rounded">
                    <div class="flex justify-between items-center mb-3">
                        <label class="block text-sm font-bold">PIC * (Person In Charge)</label>
                        <button type="button" wire:click="addPic" 
                                class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                            + Tambah PIC
                        </button>
                    </div>
                    @error('pics') <span class="text-red-500 text-sm block mb-2">{{ $message }}</span> @enderror
                    
                    @foreach($pics as $index => $pic)
                    <div class="flex gap-2 mb-2">
                        <input type="text" wire:model="pics.{{ $index }}" 
                            placeholder="Nama PIC {{ $index + 1 }}"
                            class="flex-1 border rounded px-3 py-2">
                        @if(count($pics) > 1)
                        <button type="button" wire:click="removePic({{ $index }})" 
                                class="bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600">
                            ×
                        </button>
                        @endif
                    </div>
                    @endforeach
                </div>

                {{-- RCA Section --}}
                <div class="mb-6 p-4 bg-gray-50 rounded">
                    <label class="block text-sm font-bold mb-3">Root Cause Analysis (RCA) * (Min 3 Why)</label>
                    @error('rcas') <span class="text-red-500 text-sm block mb-2">{{ $message }}</span> @enderror
                    
                    @foreach($rcas as $index => $rca)
                    <div class="mb-3">
                        <label class="block text-sm mb-1">
                            Why {{ $rca['why_level'] }} 
                            @if($index < 3) <span class="text-red-500">*</span> @endif
                        </label>
                        <textarea wire:model="rcas.{{ $index }}.description" 
                                rows="2" placeholder="Deskripsi Why {{ $rca['why_level'] }}"
                                class="w-full border rounded px-3 py-2"></textarea>
                    </div>
                    @endforeach
                </div>

                {{-- Corrective Action Section --}}
                <div class="mb-6 p-4 bg-gray-50 rounded">
                    <div class="flex justify-between items-center mb-3">
                        <label class="block text-sm font-bold">Corrective Action * (1-5 actions)</label>
                        @if(count($corrective_actions) < 5)
                        <button type="button" wire:click="addCorrectiveAction" 
                                class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                            + Tambah Action
                        </button>
                        @endif
                    </div>
                    @error('corrective_actions') <span class="text-red-500 text-sm block mb-2">{{ $message }}</span> @enderror
                    
                    @foreach($corrective_actions as $index => $action)
                    <div class="flex gap-2 mb-2">
                        <textarea wire:model="corrective_actions.{{ $index }}" 
                                rows="2" placeholder="Corrective Action {{ $index + 1 }}"
                                class="flex-1 border rounded px-3 py-2"></textarea>
                        @if(count($corrective_actions) > 1)
                        <button type="button" wire:click="removeCorrectiveAction({{ $index }})" 
                                class="bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600">
                            ×
                        </button>
                        @endif
                    </div>
                    @endforeach
                </div>

                {{-- Additional Fields --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2">Pokayoke</label>
                        <textarea wire:model="pokayoke" rows="2"
                                class="w-full border rounded px-3 py-2"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Audit Date</label>
                        <input type="date" wire:model="audit_date" class="w-full border rounded px-3 py-2">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Reply Date</label>
                        <input type="date" wire:model="reply_date" class="w-full border rounded px-3 py-2">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2">Verify</label>
                        <textarea wire:model="verify" rows="2"
                                class="w-full border rounded px-3 py-2"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">FMEA/CP</label>
                        <input type="text" wire:model="fmea_cp" class="w-full border rounded px-3 py-2">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Std Work</label>
                        <input type="text" wire:model="std_work" class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2">Remark</label>
                        <textarea wire:model="remark" rows="3"
                                placeholder="Catatan tambahan..."
                                class="w-full border rounded px-3 py-2"></textarea>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3 justify-end">
                    <a href="{{ route('asakai.index') }}" 
                    class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                        Batal
                    </a>
                    <button type="submit" 
                            class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                        {{ $isEdit ? 'Update' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
