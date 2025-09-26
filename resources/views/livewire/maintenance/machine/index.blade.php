<div class="container mx-auto px-4 py-6">
    <!-- Heading -->
    <h1 class="text-3xl font-extrabold mb-6 text-gray-800 tracking-wide">Maintenance / Repair Machine</h1>

    <!-- Add Button -->
    <div class="mb-6">
        <button wire:click="openModal"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg shadow-md transition-colors">
            Add Maintenance / Repair
        </button>
    </div>

    <!-- Success Message -->
    @if(session()->has('message'))
        <div class="bg-green-200 text-green-800 px-4 py-2 rounded-lg mb-4 shadow-sm">
            {{ session('message') }}
        </div>
    @endif

    <!-- Table -->
    <div class="overflow-x-auto rounded-lg shadow-md">
        <table class="min-w-full bg-white border border-gray-200 divide-y divide-gray-200">
            <thead class="bg-gray-50 text-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Tanggal</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Mesin</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Jenis Kerusakan</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Perbaikan</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">PIC</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Tipe</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Remark</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Action</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                @foreach($maintenanceMachines as $m)
                    <tr class="hover:bg-gray-50 transition-colors {{ $m->status == 1 ? 'bg-green-100' : '' }}">
                        <td class="px-6 py-3 border">{{ $m->tanggal->format('Y-m-d') }}</td>
                        <td class="px-6 py-3 border">{{ $m->user->name ?? $m->mesin }}</td>
                        <td class="px-6 py-3 border">{{ $m->jenis_kerusakan }}</td>
                        <td class="px-6 py-3 border">{{ $m->perbaikan }}</td>
                        <td class="px-6 py-3 border">{{ $m->pic }}</td>
                        <td class="px-6 py-3 border">{{ $m->tipe }}</td>
                        <td class="px-6 py-3 border">{{ $m->remark }}</td>
                        <td class="px-6 py-3 border">
                            <button wire:click="openDetailModal({{ $m->id }})"
                                    class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-lg text-sm shadow-sm transition-colors">
                                Detail
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>


    <!-- Modal Add -->
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 w-full max-w-lg shadow-lg">
                <h2 class="text-2xl font-bold mb-4 text-gray-800">Add Maintenance / Repair</h2>
                <div class="space-y-3">
                    <div>
                        <label class="font-semibold">Tanggal Pengerjaan</label>
                        <input type="date" wire:model="tanggal"
                               class="border rounded-md w-full px-3 py-2 mt-1 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="font-semibold">Mesin</label>
                        <select wire:model="mesin"
                                class="border rounded-md w-full px-3 py-2 mt-1 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Pilih Mesin --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->name }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="font-semibold">Jenis Kerusakan</label>
                        <input type="text" wire:model="jenis_kerusakan"
                               class="border rounded-md w-full px-3 py-2 mt-1 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="font-semibold">Perbaikan</label>
                        <input type="text" wire:model="perbaikan"
                               class="border rounded-md w-full px-3 py-2 mt-1 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="font-semibold">PIC</label>
                        <input type="text" wire:model="pic"
                               class="border rounded-md w-full px-3 py-2 mt-1 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="font-semibold">Tipe</label>
                        <select wire:model="tipe"
                                class="border rounded-md w-full px-3 py-2 mt-1 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Pilih Tipe --</option>
                            <option value="Repair">Repair</option>
                            <option value="Maintenance">Maintenance</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-semibold">Remark</label>
                        <textarea wire:model="remark"
                                  class="border rounded-md w-full px-3 py-2 mt-1 focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                    </div>
                </div>
                <div class="flex justify-end mt-4 space-x-2">
                    <button wire:click="closeModal"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">Cancel</button>
                    <button wire:click="saveMaintenance"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">Save</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Detail -->
    @if($showDetailModal && $selectedMaintenance)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 w-full max-w-lg shadow-lg">
                <h2 class="text-2xl font-bold mb-4 text-gray-800">Detail Maintenance</h2>
                <div class="space-y-2 text-gray-700">
                    <p><strong>Tanggal Pengerjaan:</strong> {{ $selectedMaintenance->tanggal->format('Y-m-d') }}</p>
                    <p><strong>Mesin:</strong> {{ $selectedMaintenance->user->name ?? $selectedMaintenance->mesin }}</p>
                    <p><strong>Jenis Kerusakan:</strong> {{ $selectedMaintenance->jenis_kerusakan }}</p>
                    <p><strong>Perbaikan:</strong> {{ $selectedMaintenance->perbaikan }}</p>
                    <p><strong>PIC:</strong> {{ $selectedMaintenance->pic }}</p>
                    <p><strong>Tipe:</strong> {{ $selectedMaintenance->tipe }}</p>
                    <p><strong>Remark:</strong> {{ $selectedMaintenance->remark }}</p>
                    <p><strong>Status:</strong>
                        <span class="{{ $selectedMaintenance->status ? 'text-green-600' : 'text-red-600' }}">
                            {{ $selectedMaintenance->status ? 'Finished' : 'Ongoing' }}
                        </span>
                    </p>
                    <p><strong>Lama Pengerjaan:</strong> {{ $selectedMaintenance->lama_pengerjaan ?? '-' }}</p>
                </div>
                <div class="flex justify-end mt-4 space-x-2">
                    <button wire:click="closeDetailModal"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">Close</button>
                    @if(!$selectedMaintenance->status)
                        <button wire:click="finishMaintenance"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">Finish</button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
