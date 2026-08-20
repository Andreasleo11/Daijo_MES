<x-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Custom Barcode Print History & Logs
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Top Header & Action Buttons -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-xl p-6 border border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                        <span>📋</span> Custom Barcode Print History
                    </h1>
                    <p class="text-sm text-slate-500 mt-1">Rekap dan log aktivitas pencetakan label barcode custom beserta jumlah label, SPK, dan remark.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('barcode.custom.form') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-lg shadow transition">
                        <span>➕</span> Generate Label Baru
                    </a>
                </div>
            </div>

            <!-- Stats Overview Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-xl shadow-md border border-slate-100">
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Print Jobs</div>
                    <div class="text-2xl font-extrabold text-slate-800 mt-1">{{ number_format($stats['total_print_jobs'] ?? 0) }}</div>
                    <div class="text-xs text-slate-400 mt-1">Keseluruhan sesi print</div>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-md border border-slate-100">
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Label Dicetak</div>
                    <div class="text-2xl font-extrabold text-indigo-600 mt-1">{{ number_format($stats['total_labels_printed'] ?? 0) }}</div>
                    <div class="text-xs text-slate-400 mt-1">Stiker barcode dicetak</div>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-md border border-slate-100">
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Print Hari Ini</div>
                    <div class="text-2xl font-extrabold text-emerald-600 mt-1">{{ number_format($stats['today_print_jobs'] ?? 0) }}</div>
                    <div class="text-xs text-slate-400 mt-1">Sesi print pada hari ini</div>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-md border border-slate-100">
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Label Hari Ini</div>
                    <div class="text-2xl font-extrabold text-amber-600 mt-1">{{ number_format($stats['today_labels_printed'] ?? 0) }}</div>
                    <div class="text-xs text-slate-400 mt-1">Stiker dicetak hari ini</div>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="bg-white overflow-hidden shadow-md sm:rounded-xl p-6 border border-slate-100">
                <form action="{{ route('barcode.custom.logs') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Pencarian (Item, SPK, User, Customer, Remark)</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik kata kunci..." class="w-full border-slate-300 rounded-lg shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full border-slate-300 rounded-lg shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full border-slate-300 rounded-lg shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="md:col-span-4 flex justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-6 py-2 rounded-lg text-sm transition shadow-sm">
                            🔍 Filter
                        </button>
                        <a href="{{ route('barcode.custom.logs') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-6 py-2 rounded-lg text-sm transition">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table Card -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-xl p-6 border border-slate-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-bold text-slate-600 uppercase text-xs">Waktu Print</th>
                                <th class="px-4 py-3 text-left font-bold text-slate-600 uppercase text-xs">User</th>
                                <th class="px-4 py-3 text-left font-bold text-slate-600 uppercase text-xs">Item & SPK</th>
                                <th class="px-4 py-3 text-left font-bold text-slate-600 uppercase text-xs">Label Seq / Total</th>
                                <th class="px-4 py-3 text-left font-bold text-slate-600 uppercase text-xs">Qty/Box</th>
                                <th class="px-4 py-3 text-left font-bold text-slate-600 uppercase text-xs">Shift / WH / Prod Date</th>
                                <th class="px-4 py-3 text-left font-bold text-slate-600 uppercase text-xs">Type</th>
                                <th class="px-4 py-3 text-left font-bold text-slate-600 uppercase text-xs">Remark / Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($logs as $log)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-4 py-3.5 whitespace-nowrap text-slate-700">
                                        <div class="font-medium text-slate-900">{{ $log->created_at->format('Y-m-d H:i:s') }}</div>
                                        <div class="text-xs text-slate-400">{{ $log->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap text-slate-700">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800">
                                            {{ $log->user_name ?? ($log->user->name ?? 'Guest') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-700">
                                        <div class="font-bold text-indigo-700">{{ $log->item_code }}</div>
                                        <div class="text-xs text-slate-500">{{ $log->item_name }}</div>
                                        <div class="text-xs text-slate-600 font-medium mt-0.5">SPK: <span class="font-mono">{{ $log->spk_number }}</span></div>
                                        @if($log->customer && $log->customer !== '-')
                                            <div class="text-xs text-slate-400">Cust: {{ $log->customer }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap text-slate-700">
                                        <div class="font-semibold text-slate-800">
                                            Label #{{ $log->start_label }} - #{{ $log->end_label }}
                                        </div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 mt-0.5">
                                            {{ $log->total_labels }} label diprint
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap text-slate-700 font-semibold">
                                        {{ number_format($log->quantity) }}
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap text-slate-700 text-xs">
                                        <div>Shift <span class="font-bold">{{ $log->shift }}</span> ({{ $log->warehouse }})</div>
                                        <div class="text-slate-400">Prod: {{ $log->prod_date ? \Carbon\Carbon::parse($log->prod_date)->format('d/m/Y') : '-' }}</div>
                                        @if($log->operator && $log->operator !== '-')
                                            <div class="text-slate-400">Op: {{ $log->operator }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        @if($log->is_trial)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-700 border border-red-200">
                                                TRIAL
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                Standard
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-700 text-xs max-w-xs break-words">
                                        @if($log->remark)
                                            <span class="text-slate-800 bg-amber-50 border border-amber-200 px-2 py-1 rounded inline-block">
                                                {{ $log->remark }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 italic">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                                        Tidak ada data riwayat cetak barcode yang sesuai dengan filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($logs->hasPages())
                    <div class="mt-6 pt-4 border-t border-slate-100">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-dashboard-layout>
