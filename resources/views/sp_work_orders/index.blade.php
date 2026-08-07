<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-black text-xl text-gray-800 uppercase tracking-wide">
                    {{ __('Second Process Work Orders') }}
                </h2>
                <p class="text-xs font-semibold text-gray-500 mt-0.5">Manage, release, and track production Work Orders & QC Gate status</p>
            </div>
            <a href="{{ route('sp-work-orders.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-black text-xs rounded-xl shadow-sm transition uppercase tracking-wider flex items-center gap-1">
                + New Work Order
            </a>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        <div class="max-w-7xl mx-auto space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-300 text-emerald-900 px-5 py-4 rounded-2xl text-xs font-bold shadow-sm" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-300 text-red-900 px-5 py-4 rounded-2xl text-xs font-bold shadow-sm" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Filter Bar --}}
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">Filter & Search Work Orders</h3>
                        <p class="text-xs text-gray-500 font-medium">Refine planned and active Work Orders by line, shift, status, or search terms</p>
                    </div>
                </div>

                <form method="GET" action="{{ route('sp-work-orders.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-5">
                        <div>
                            <label class="block text-[11px] font-black text-gray-500 uppercase tracking-wider mb-1">From Date</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}"
                                class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold text-gray-800">
                        </div>

                        <div>
                            <label class="block text-[11px] font-black text-gray-500 uppercase tracking-wider mb-1">To Date</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}"
                                class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold text-gray-800">
                        </div>

                        <div>
                            <label class="block text-[11px] font-black text-gray-500 uppercase tracking-wider mb-1">Line / Area</label>
                            <select name="unit_line" class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold text-gray-800">
                                <option value="">All Lines</option>
                                @foreach($lines as $l)
                                    <option value="{{ $l }}" {{ request('unit_line') == $l ? 'selected' : '' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-black text-gray-500 uppercase tracking-wider mb-1">Status</label>
                            <select name="status" class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold text-gray-800">
                                <option value="">All Status</option>
                                <option value="planned" {{ request('status') == 'planned' ? 'selected' : '' }}>Planned</option>
                                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-black text-gray-500 uppercase tracking-wider mb-1">Search Keyword</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="WO#, Part, Customer..."
                                class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold text-gray-800">
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                        <a href="{{ route('sp-work-orders.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl border border-gray-300 shadow-sm transition uppercase tracking-wider">
                            Reset Filters
                        </a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-black text-xs rounded-xl shadow-sm transition uppercase tracking-wider">
                            Apply Filter
                        </button>
                    </div>
                </form>
            </div>

            {{-- Table Display Card --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <div>
                        <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">Work Orders</h3>
                        <p class="text-xs text-gray-500 font-medium">Production planning and real-time execution status</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                                <th class="px-6 py-3">WO Number</th>
                                <th class="px-6 py-3">Planned Date</th>
                                <th class="px-6 py-3">Line / Shift</th>
                                <th class="px-6 py-3">Part Info</th>
                                <th class="px-6 py-3 text-right">Target Qty</th>
                                <th class="px-6 py-3 text-center">QC First Piece Gate</th>
                                <th class="px-6 py-3 text-center">Status</th>
                                <th class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs">
                            @forelse($workOrders as $wo)
                                @php
                                    $fpList = $firstPieceMap->get($wo->part_number) ?? collect();
                                    $fp = $fpList->first();
                                    $fpApproved = $fp && $fp->checked_at && $fp->overall_judgement === 'OK';
                                    $runningSession = $wo->sessions->where('status', 'running')->first();
                                @endphp
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 font-black text-blue-700 whitespace-nowrap">
                                        <a href="{{ route('sp-work-orders.show', $wo->id) }}" class="hover:underline">
                                            {{ $wo->wo_number }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-gray-800 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($wo->planned_date)->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 font-bold text-gray-800 whitespace-nowrap">
                                        <div>{{ $wo->unit_line }}</div>
                                        <div class="text-[10px] text-gray-400 font-medium">Shift {{ $wo->shift }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">{{ $wo->part_name }}</div>
                                        <div class="text-[10px] text-gray-400 font-mono">{{ $wo->part_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right font-black text-gray-900 whitespace-nowrap">
                                        {{ number_format($wo->target_qty) }} Pcs
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        @if($fpApproved)
                                            <span class="px-3 py-1 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 uppercase tracking-wider border border-emerald-200">
                                                QC Approved
                                            </span>
                                        @elseif($fp)
                                            <span class="px-3 py-1 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 uppercase tracking-wider border border-amber-200">
                                                Pending Sign-off
                                            </span>
                                        @else
                                            <span class="px-3 py-1 rounded-full text-[10px] font-black bg-red-100 text-red-800 uppercase tracking-wider border border-red-200">
                                                Gate Required
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border
                                            {{ $wo->status === 'in_progress' ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-gray-100 text-gray-700 border-gray-200' }}">
                                            {{ str_replace('_', ' ', $wo->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-1 whitespace-nowrap">
                                        <a href="{{ route('sp-work-orders.show', $wo->id) }}"
                                            class="inline-block px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-lg border border-gray-300 transition uppercase tracking-wider">
                                            Details
                                        </a>

                                        @if($runningSession)
                                            <a href="{{ route('app.sp-sessions.show', $runningSession->id) }}"
                                                class="inline-block px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition uppercase tracking-wider">
                                                Open Screen
                                            </a>
                                        @elseif($wo->status === 'planned' && $fpApproved)
                                            <form action="{{ route('sp-sessions.start', $wo->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-lg transition uppercase tracking-wider">
                                                    Start Production
                                                </button>
                                            </form>
                                        @elseif($wo->status === 'planned')
                                            <a href="{{ route('first-piece-inspections.create', ['work_order_id' => $wo->id, 'part_number' => $wo->part_number, 'part_name' => $wo->part_name, 'model' => $wo->model]) }}"
                                                class="inline-block px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-lg transition uppercase tracking-wider">
                                                Perform Inspection
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-8 text-center text-gray-400 font-medium">
                                        No Work Orders found matching criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($workOrders->hasPages())
                    <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100">
                        {{ $workOrders->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
