<div class="p-6 bg-gray-50 min-h-screen" @if($stats['pending'] > 0) wire:poll.3s @endif>
    <div class="max-w-7xl mx-auto">
        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tighter uppercase italic">
                    WMS <span class="text-blue-600">SAP</span> Sync Monitor
                </h1>
                <p class="text-sm text-gray-500 font-bold uppercase tracking-widest mt-1">
                    Batch Header Processing System
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                <button wire:click="retryAllFailed" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-lg shadow-red-500/20 active:scale-95">
                    Retry All Failed
                </button>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
            @foreach([
                ['label' => 'Total Pallets', 'value' => $stats['total'], 'color' => 'blue'],
                ['label' => 'Synced', 'value' => $stats['success'], 'color' => 'green'],
                ['label' => 'Failed', 'value' => $stats['failed'], 'color' => 'red'],
                ['label' => 'Pending', 'value' => $stats['pending'], 'color' => 'orange'],
            ] as $stat)
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">{{ $stat['label'] }}</span>
                    <span class="text-3xl font-black text-{{ $stat['color'] }}-600 tracking-tighter">{{ $stat['value'] }}</span>
                </div>
            @endforeach
        </div>

        {{-- Search & Filter --}}
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 mb-6 flex flex-col md:flex-row gap-4">
            <div class="flex-1 relative">
                <input wire:model.live="search" type="text" placeholder="Search Pallet ID or Part No..." class="w-full bg-gray-50 border-gray-200 rounded-xl px-10 py-2.5 text-sm font-bold focus:ring-blue-500 focus:border-blue-500">
                <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <select wire:model.live="statusFilter" class="bg-gray-50 border-gray-200 rounded-xl px-4 py-2.5 text-sm font-bold focus:ring-blue-500 focus:border-blue-500 min-w-[200px]">
                <option value="">All Statuses</option>
                <option value="0">Pending</option>
                <option value="1">Success</option>
                <option value="2">Failed</option>
            </select>
        </div>

        {{-- Main Table --}}
        <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="py-4 px-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Pallet ID</th>
                        <th class="py-4 px-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Part No / Model</th>
                        <th class="py-4 px-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Boxes</th>
                        <th class="py-4 px-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Status</th>
                        <th class="py-4 px-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Last Sync</th>
                        <th class="py-4 px-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pallets as $pallet)
                        <tr class="hover:bg-gray-50/80 transition-colors group">
                            <td class="py-4 px-6">
                                <span class="text-sm font-black text-gray-900 tracking-tight">{{ $pallet->pallet_id }}</span>
                                <div class="text-[9px] font-bold text-gray-400 uppercase mt-0.5">{{ $pallet->created_at->format('d M Y H:i') }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="text-sm font-black text-blue-600 tracking-tight">{{ $pallet->part_no }}</span>
                                <div class="text-[10px] font-bold text-gray-500 truncate max-w-[200px]">{{ $pallet->model_name }}</div>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-700 text-[10px] font-black">
                                    {{ $pallet->details_count }} BOXES
                                </span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if($pallet->sap_sync_status == 1)
                                    <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 text-[10px] font-black rounded-full uppercase tracking-tighter">
                                        SUCCESS
                                    </span>
                                @elseif($pallet->sap_sync_status == 2)
                                    <div class="flex flex-col items-center gap-1">
                                        <span class="inline-flex items-center px-3 py-1 bg-red-100 text-red-700 text-[10px] font-black rounded-full uppercase tracking-tighter">
                                            FAILED
                                        </span>
                                        <span class="text-[8px] text-red-500 font-bold italic leading-tight max-w-[120px] break-words">
                                            {{ $pallet->sap_error_msg }}
                                        </span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 bg-orange-100 text-orange-700 text-[10px] font-black rounded-full uppercase tracking-tighter animate-pulse">
                                        PENDING
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                @if($pallet->sap_sync_at)
                                    <div class="text-[10px] font-black text-gray-600">{{ $pallet->sap_sync_at->timezone('Asia/Jakarta')->format('d M Y') }}</div>
                                    <div class="text-[9px] font-bold text-gray-400">{{ $pallet->sap_sync_at->timezone('Asia/Jakarta')->format('H:i:s') }}</div>
                                @else
                                    <span class="text-[10px] font-bold text-gray-300 italic">Never</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex justify-end gap-2">
                                    <button wire:click="viewDetails('{{ $pallet->pallet_id }}')" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="View Details">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>
                                    <button wire:click="retrySync('{{ $pallet->pallet_id }}')" class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-all" title="Retry Sync">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-500 font-bold italic">No pallets found matching your criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $pallets->links() }}
        </div>
    </div>

    {{-- Detail Modal --}}
    @if($showDetails)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="closeDetails"></div>
            
            <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-4xl max-h-[85vh] overflow-hidden flex flex-col">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tighter uppercase italic">Pallet Details: <span class="text-blue-600">{{ $selectedPalletId }}</span></h3>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">Individual Box Data Prepared for SAP Array</p>
                    </div>
                    <button wire:click="closeDetails" class="p-2 hover:bg-gray-100 rounded-xl transition-all">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-6">
                    <table class="w-full text-left">
                        <thead class="sticky top-0 bg-white">
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="py-3 px-4 text-[9px] font-black text-gray-400 uppercase tracking-widest">SPK No</th>
                                <th class="py-3 px-4 text-[9px] font-black text-gray-400 uppercase tracking-widest">Part No</th>
                                <th class="py-3 px-4 text-[9px] font-black text-gray-400 uppercase tracking-widest text-center">Qty</th>
                                <th class="py-3 px-4 text-[9px] font-black text-gray-400 uppercase tracking-widest">Warehouse</th>
                                <th class="py-3 px-4 text-[9px] font-black text-gray-400 uppercase tracking-widest">Label</th>
                                <th class="py-3 px-4 text-[9px] font-black text-gray-400 uppercase tracking-widest text-center">Status</th>
                                <th class="py-3 px-4 text-[9px] font-black text-gray-400 uppercase tracking-widest">Error Message</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($palletDetails as $detail)
                                <tr class="text-xs">
                                    <td class="py-3 px-4 font-black text-gray-900">{{ $detail->spk_no }}</td>
                                    <td class="py-3 px-4 font-bold text-blue-600">{{ $detail->part_no }}</td>
                                    <td class="py-3 px-4 text-center font-black text-gray-900">{{ number_format($detail->qty) }}</td>
                                    <td class="py-3 px-4 font-bold text-gray-500 uppercase">{{ $detail->warehouse ?: 'FFI' }}</td>
                                    <td class="py-3 px-4 font-mono text-gray-400">{{ $detail->label }}</td>
                                    <td class="py-3 px-4 text-center">
                                        @if($detail->sap_sync_status == 1)
                                            <span class="text-green-500 font-black tracking-tighter">SUCCESS</span>
                                        @elseif($detail->sap_sync_status == 2)
                                            <span class="text-red-500 font-black tracking-tighter">FAILED</span>
                                        @else
                                            <span class="text-orange-500 font-black tracking-tighter">PENDING</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-[10px] text-red-600 font-bold italic">{{ $detail->sap_error_msg ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-6 border-t border-gray-100 bg-gray-50/50 flex justify-end">
                    <button wire:click="closeDetails" class="bg-gray-900 text-white px-8 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-gray-800 transition-all">
                        Close Diagnostics
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Flash Message --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="fixed bottom-6 right-6 bg-gray-900 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-4 animate-bounce">
            <div class="bg-green-500 p-1 rounded-full">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <span class="text-xs font-black uppercase tracking-widest">{{ session('message') }}</span>
        </div>
    @endif
</div>
