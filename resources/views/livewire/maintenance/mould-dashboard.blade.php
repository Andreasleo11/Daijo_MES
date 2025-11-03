<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Maintenance Dashboard</h1>
                    <p class="text-gray-600 mt-1">Monitor dan kelola maintenance mesin & mould</p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                        Live Data
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Filter Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Filter Data</h2>
            
            <!-- Global Date Filter -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 pb-6 border-b border-gray-200">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                    <input type="date" wire:model.live="filterDateFrom" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                    <input type="date" wire:model.live="filterDateTo" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                </div>
            </div>

            <!-- Separate Filters -->
            <div class="grid grid-cols-1 lg:grid-cols-1 gap-8">
              
                <!-- Mould Filter -->
                <div class="bg-purple-50 rounded-lg p-4">
                    <h3 class="text-md font-semibold text-purple-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        Filter Mould
                    </h3>
                   <div class="grid grid-cols-4 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Mould</label>
                            <select wire:model.live="filterType" class="w-full border rounded px-3 py-2">
                                <option value="">Semua Tipe</option>
                                <option value="repair">Repair</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">PIC Mould</label>
                            <select wire:model.live="filterPIC" class="w-full border rounded px-3 py-2">
                                <option value="">Semua PIC</option>
                                @foreach($pics as $pic)
                                    <option value="{{ $pic }}">{{ $pic }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Part No</label>
                            <select wire:model.live="filterPartNo" class="w-full border rounded px-3 py-2">
                                <option value="">Semua Part</option>
                                @foreach($partNoList as $part)
                                    <option value="{{ $part }}">{{ $part }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols gap-8 mb-8">
          

            <!-- Mould Stats -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
                    <div class="flex items-center">
                        <div class="bg-white bg-opacity-20 rounded-lg p-2 mr-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white">Mould Maintenance</h3>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-3 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">{{ $mouldTotal }}</div>
                            <div class="text-sm text-gray-600">Total</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">{{ $mouldFinished }}</div>
                            <div class="text-sm text-gray-600">Selesai</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-orange-500">{{ $mouldOngoing }}</div>
                            <div class="text-sm text-gray-600">Ongoing</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-1 gap-8">

            <!-- Mould Maintenance Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Mould Maintenance</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mould</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Kerusakan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PIC</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($this->filteredMoulds as $mould)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">{{ $mould->part_no }} ({{ $mould->part_name }})</div>
                                        <div class="text-sm text-gray-500">{{ $mould->tipe }}</div>
                                    </td>
                                     <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $mould->jenis_kerusakan }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $mould->pic }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($mould->status == 1)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Selesai
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                Ongoing
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button wire:click="openMouldModal({{ $mould->id }})" class="text-blue-600 hover:text-blue-900 transition-colors">
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                        Tidak ada data mould maintenance
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    

    <!-- Mould Modal -->
    @if($showMouldModal && $selectedMould)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900">Detail Mould Maintenance</h3>
                    <button wire:click="closeMouldModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Part No</label>
                            <p class="text-gray-900">{{ $selectedMould->part_no ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Part Name</label>
                            <p class="text-gray-900">{{ $selectedMould->part_name ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
                            <p class="text-gray-900">{{ $selectedMould->tipe }}</p>
                        </div>
                          <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
                            <p class="text-gray-900">{{ $selectedMould->jenis_kerusakan }}</p>
                        </div>
                          <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
                            <p class="text-gray-900">{{ $selectedMould->perbaikan }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">PIC</label>
                            <p class="text-gray-900">{{ $selectedMould->pic }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">PIC</label>
                            <p class="text-gray-900">{{ $selectedMould->remark }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                            <p class="text-gray-900">{{ $selectedMould->tanggal ? \Carbon\Carbon::parse($selectedMould->tanggal)->format('d/m/Y') : '-' }}</p>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Mulai - Selesai</label>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                        <span class="text-sm font-medium text-gray-600">Mulai:</span>
                                        <span class="text-gray-900 font-mono">
                                            {{ $selectedMould->created_at ? \Carbon\Carbon::parse($selectedMould->created_at)->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') : '-' }}
                                        </span>
                                        <span class="text-xs text-gray-500">WIB</span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between mt-2">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-3 h-3 {{ $selectedMould->finished_at ? 'bg-blue-500' : 'bg-gray-300' }} rounded-full"></div>
                                        <span class="text-sm font-medium text-gray-600">Selesai:</span>
                                        <span class="text-gray-900 font-mono">
                                            {{ $selectedMould->finished_at ? \Carbon\Carbon::parse($selectedMould->finished_at)->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') : 'Belum selesai' }}
                                        </span>
                                        @if($selectedMould->finished_at)
                                            <span class="text-xs text-gray-500">WIB</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            @if($selectedMould->status == 1)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Selesai
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                    Ongoing
                                </span>
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Lama Pengerjaan</label>
                            <p class="text-gray-900">{{ $selectedMould->lama_pengerjaan ?? '-' }}</p>
                        </div>
                    </div>
                    @if($selectedMould->deskripsi)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <p class="text-gray-900 bg-gray-50 p-3 rounded-lg">{{ $selectedMould->deskripsi }}</p>
                        </div>
                    @endif
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                    <button wire:click="closeMouldModal" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>