<div class="px-6 py-8 min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto space-y-8">
        
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight italic uppercase">Warehouse Audit Trail</h1>
                <p class="text-gray-500 font-medium">Melacak setiap pergerakan palet di Gudang J06 secara real-time.</p>
            </div>
            
            <div class="relative w-full md:w-80">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400 font-bold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input wire:model.live="search" type="text" placeholder="Search Pallet ID or Notes..." 
                    class="block w-full pl-10 pr-4 py-3 bg-white border-2 border-gray-100 rounded-2xl text-sm font-bold focus:border-blue-500 focus:ring-0 outline-none transition-all shadow-sm shadow-blue-50/50">
            </div>
        </div>

        <!-- Statistics Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-gradient-to-br from-emerald-500 to-teal-600 p-6 rounded-[2rem] shadow-xl shadow-emerald-200/50 text-white relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 opacity-10 group-hover:scale-110 transition-transform">
                    <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"></path></svg>
                </div>
                <div class="relative z-10">
                    <div class="text-xs font-black uppercase tracking-widest opacity-80 mb-1">Today Inbound</div>
                    <div class="text-4xl font-black tracking-tighter">{{ $totalInToday }} <span class="text-xl italic font-medium">Pallets</span></div>
                    <div class="mt-4 flex items-center text-[10px] bg-white/20 w-fit px-3 py-1 rounded-full font-bold">
                        <span class="mr-1">●</span> TRANSACTION IN
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-rose-500 to-pink-600 p-6 rounded-[2rem] shadow-xl shadow-rose-200/50 text-white relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 opacity-10 group-hover:scale-110 transition-transform">
                    <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M19 13H5v-2h14v2z"></path></svg>
                </div>
                <div class="relative z-10">
                    <div class="text-xs font-black uppercase tracking-widest opacity-80 mb-1">Today Outbound</div>
                    <div class="text-4xl font-black tracking-tighter">{{ $totalOutToday }} <span class="text-xl italic font-medium">Pallets</span></div>
                    <div class="mt-4 flex items-center text-[10px] bg-white/20 w-fit px-3 py-1 rounded-full font-bold">
                        <span class="mr-1">●</span> TRANSACTION OUT
                    </div>
                </div>
            </div>
            
            <div class="hidden lg:block lg:col-span-2 bg-white p-2 rounded-[2rem] border-2 border-gray-100 shadow-sm overflow-hidden">
                 <div class="h-full bg-gray-50 flex items-center justify-center p-6 border-2 border-dashed border-gray-200 rounded-[1.5rem]">
                    <p class="text-gray-400 font-bold text-xs uppercase tracking-widest text-center italic">Live monitoring active & synchronized with Master WMS J06</p>
                 </div>
            </div>
        </div>

        <!-- Log Table Section -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50/50 text-left">
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest italic">Timestamp</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest italic">Direction</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest italic">Pallet Identifier</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest italic">Customer</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest italic">Location</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest italic">Operator</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest italic">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($logs as $log)
                            <tr class="hover:bg-gray-50/50 transition-colors group">
                                <td class="px-8 py-6">
                                    <div class="text-xs font-bold text-gray-800">{{ $log->created_at->setTimezone('Asia/Jakarta')->format('d M Y') }}</div>
                                    <div class="text-[10px] font-black text-blue-500 italic">{{ $log->created_at->setTimezone('Asia/Jakarta')->format('H:i:s') }} WIB</div>
                                </td>
                                <td class="px-8 py-6">
                                    @if($log->transaction_type == 'IN')
                                        <span class="inline-flex items-center px-3 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-[10px] font-black uppercase tracking-tighter border border-emerald-100">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg> INBOUND
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 bg-rose-50 text-rose-600 rounded-lg text-[10px] font-black uppercase tracking-tighter border border-rose-100">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg> OUTBOUND
                                        </span>
                                    @endif
                                </td>
                                <td class="px-8 py-6">
                                    <div class="text-sm font-black text-gray-900 tracking-tight italic">{{ $log->pallet_id }}</div>
                                </td>
                                <td class="px-8 py-6">
                                    @php
                                        $customers = optional($log->pallet)->details 
                                            ? $log->pallet->details->map(fn($d) => $d->item?->customer)->filter()->unique('customer_code') 
                                            : collect();
                                    @endphp
                                    @if($customers->isNotEmpty())
                                        <div class="flex flex-col gap-1">
                                            @foreach($customers as $cust)
                                                <div class="text-[10px] font-bold text-gray-800" title="{{ $cust->customer_code }}">
                                                    {{ $cust->customer_name }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-[10px] text-gray-400 font-bold italic">No Customer</span>
                                    @endif
                                </td>
                                <td class="px-8 py-6">
                                    @if($log->position)
                                        <span class="text-[10px] font-black bg-gray-50 px-2 py-1 rounded text-gray-500 border border-gray-100 uppercase tracking-tighter">
                                            <span class="text-blue-500 mr-1">●</span> {{ $log->position->position_code }}
                                        </span>
                                    @else
                                        <span class="text-[10px] font-bold text-gray-300 italic">No Location</span>
                                    @endif
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center">
                                        <div class="h-7 w-7 rounded-full bg-blue-100 flex items-center justify-center text-[10px] font-black text-blue-600 mr-2 border-2 border-white shadow-sm italic uppercase">
                                            {{ substr($log->user->name ?? '?', 0, 2) }}
                                        </div>
                                        <div class="text-[10px] font-bold text-gray-500 uppercase">{{ $log->user->name ?? 'System' }}</div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 max-w-xs">
                                    <div class="truncate text-xs font-medium text-gray-400 italic">"{{ $log->notes ?: '-- Tidak ada catatan --' }}"</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-20 text-center">
                                    <div class="space-y-4">
                                        <div class="bg-gray-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto grayscale opacity-40">
                                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        </div>
                                        <p class="text-gray-400 font-bold text-sm uppercase tracking-widest italic">Belum ada aktivitas transaksi terekam.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Custom Pagination styling could be added here if needed -->
            <div class="px-8 py-6 border-t border-gray-50 bg-gray-50/20">
                {{ $logs->links() }}
            </div>
        </div>

    </div>
</div>
