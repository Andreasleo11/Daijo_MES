<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Pallet Form History</h1>
                <p class="text-gray-500">Lihat dan cetak ulang pallet form yang sudah dibuat.</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('wms.pallet-form.create') }}" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-200 transition-all flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    CREATE NEW PALLET
                </a>
            </div>
        </div>

        <!-- Filter & Search Section -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="relative max-w-md">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari ID Palet atau Part No..." 
                    class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
            </div>
        </div>

        <!-- Table Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-gray-500 text-xs font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Pallet ID</th>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">Part No / Model</th>
                            <th class="px-6 py-4">Qty</th>
                            <th class="px-6 py-4">Rack Position</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($palletForms as $form)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-800">{{ $form->pallet_id }}</div>
                                    <div class="text-xs text-gray-400">#{{ $form->lot_no ?: 'No Lot' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $form->created_at->format('d M Y') }}
                                    <div class="text-xs text-gray-400">{{ $form->created_at->format('H:i') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-800">{{ $form->part_no }}</div>
                                    <div class="text-xs text-gray-500">{{ $form->model_name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-blue-600">{{ number_format($form->total_pallet_qty, 0) }} pcs</div>
                                    <div class="text-xs text-gray-400">{{ $form->box_qty }} Boxes</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 bg-gray-800 text-white text-xs font-bold rounded-lg uppercase tracking-tight">
                                        {{ $form->position->position_code ?? 'UNASSIGNED' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('wms.pallet-form.print', ['id' => $form->pallet_id]) }}" target="_blank"
                                        class="inline-flex items-center px-4 py-2 bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white rounded-lg text-sm font-bold transition-all">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2v4h10z"></path></svg>
                                        REPRINT
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-20 text-center text-gray-400 italic">
                                    Belum ada data pallet form yang tersimpan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($palletForms->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    {{ $palletForms->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
