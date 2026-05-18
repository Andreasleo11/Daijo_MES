<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">🌐 API Logs Dashboard</h1>
                <p class="text-gray-500 text-sm italic">Menampilkan log aktivitas 30 hari terakhir untuk performa optimal.</p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-bold border border-blue-100">
                    Live Updates
                </span>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2 tracking-widest">Search Endpoint / Message</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </span>
                        <input type="text" wire:model.live.debounce.300ms="search" 
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm"
                            placeholder="Cari endpoint atau pesan...">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2 tracking-widest">Filter API Name</label>
                    <select wire:model.live="apiNameFilter" 
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm">
                        <option value="">Semua API</option>
                        @foreach($apiNames as $name)
                            <option value="{{ $name }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2 tracking-widest">Status</label>
                    <div class="flex space-x-2">
                        <select wire:model.live="statusFilter" 
                            class="flex-grow px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm">
                            <option value="">Semua Status</option>
                            <option value="success">Success</option>
                            <option value="failed">Failed</option>
                            <option value="error">Error</option>
                        </select>
                        <button wire:click="clearFilters" class="p-2.5 bg-gray-100 hover:bg-gray-200 text-gray-500 rounded-xl transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logs Table Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-gray-400 text-[10px] font-black uppercase tracking-[0.15em] border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 w-48">Timestamp</th>
                            <th class="px-6 py-4">API Name & Endpoint</th>
                            <th class="px-6 py-4 w-32 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Details</th>
                        </tr>
                    </thead>
                    @forelse($logs as $log)
                        <tbody x-data="{ open: false }" class="border-b border-gray-50 last:border-0">
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 align-top">
                                    <div class="text-sm font-bold text-gray-700">{{ $log->created_at->format('d M Y') }}</div>
                                    <div class="text-[10px] font-mono text-gray-400">{{ $log->created_at->format('H:i:s') }}</div>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-black text-blue-600 uppercase mb-1 tracking-tighter">{{ $log->api_name ?: 'Unknown API' }}</span>
                                        <div class="flex items-center space-x-2">
                                            <span class="px-1.5 py-0.5 bg-gray-100 text-gray-500 rounded text-[9px] font-bold">{{ $log->method }}</span>
                                            <span class="text-xs font-mono text-gray-600 break-all">{{ $log->endpoint }}</span>
                                        </div>
                                        @if($log->message)
                                            <p class="mt-2 text-[11px] text-gray-500 italic line-clamp-1">{{ $log->message }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-top text-center">
                                    @php
                                        $statusClass = match(strtolower($log->status)) {
                                            'success' => 'bg-green-100 text-green-700',
                                            'failed', 'error' => 'bg-red-100 text-red-700',
                                            default => 'bg-gray-100 text-gray-700'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $statusClass }}">
                                        {{ $log->status ?: $log->status_code }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 align-top text-right">
                                    <button @click="open = !open" 
                                        class="inline-flex items-center text-gray-400 hover:text-blue-600 font-bold text-xs transition-all outline-none">
                                        <span x-text="open ? 'HIDE DATA' : 'VIEW DATA'"></span>
                                        <svg class="w-4 h-4 ml-1 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>
                                </td>
                            </tr>

                            <!-- Expandable Detail Row -->
                            <tr x-show="open" x-cloak x-transition.opacity class="bg-gray-50/50">
                                <td colspan="4" class="px-8 py-6">
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                        <!-- Request Section -->
                                        <div class="space-y-2">
                                            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center">
                                                <svg class="w-3 h-3 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                                                Request Payload
                                            </h4>
                                            <div class="bg-slate-900 rounded-xl p-4 overflow-x-auto max-h-80 custom-scrollbar">
                                                <pre class="text-[11px] font-mono text-blue-300"><code>{{ json_encode($log->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                                            </div>
                                        </div>

                                        <!-- Response Section -->
                                        <div class="space-y-2">
                                            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center">
                                                <svg class="w-3 h-3 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
                                                Response Payload
                                            </h4>
                                            <div class="bg-slate-900 rounded-xl p-4 overflow-x-auto max-h-80 custom-scrollbar">
                                                <pre class="text-[11px] font-mono {{ strtolower($log->status) === 'success' ? 'text-green-400' : 'text-red-400' }}"><code>{{ json_encode($log->response_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                                            </div>
                                        </div>
                                    </div>
                                    @if($log->message)
                                        <div class="mt-4 p-4 bg-white rounded-xl border border-gray-100 shadow-inner">
                                            <h5 class="text-[10px] font-black text-red-500 uppercase mb-1">System Message / Error</h5>
                                            <p class="text-xs text-gray-700 font-medium">{{ $log->message }}</p>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    @empty
                        <tbody>
                            <tr>
                                <td colspan="4" class="px-6 py-20 text-center">
                                    <div class="inline-flex items-center justify-center w-12 h-12 bg-gray-100 rounded-full mb-4">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-800">Tidak ada log ditemukan</h3>
                                    <p class="text-xs text-gray-500">Coba ubah filter atau kata kunci pencarian Anda.</p>
                                </td>
                            </tr>
                        </tbody>
                    @endforelse
                </table>
            </div>
            
            <!-- Pagination Section -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                {{ $logs->links() }}
            </div>
        </div>

    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #1e293b;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }
    </style>
</div>
