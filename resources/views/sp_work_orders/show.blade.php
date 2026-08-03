<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="font-black text-2xl text-gray-800 leading-tight tracking-tight">
                        {{ $workOrder->wo_number }}
                    </h2>
                    @switch($workOrder->status)
                        @case('planned') <span class="px-3 py-1 text-[10px] font-black rounded-full bg-blue-100 text-blue-800 uppercase tracking-widest border border-blue-200">Planned</span> @break
                        @case('in_progress') <span class="px-3 py-1 text-[10px] font-black rounded-full bg-emerald-100 text-emerald-800 uppercase tracking-widest border border-emerald-200">Running</span> @break
                        @case('completed') <span class="px-3 py-1 text-[10px] font-black rounded-full bg-gray-100 text-gray-700 uppercase tracking-widest border border-gray-200">Completed</span> @break
                        @case('cancelled') <span class="px-3 py-1 text-[10px] font-black rounded-full bg-red-100 text-red-800 uppercase tracking-widest border border-red-200">Cancelled</span> @break
                    @endswitch
                </div>
                <p class="text-xs text-gray-500 mt-1 font-semibold">Created by {{ $workOrder->creator?->name ?? 'System' }} on {{ $workOrder->created_at->format('d M Y, H:i') }}</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('sp-work-orders.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl border border-gray-300 shadow-sm transition uppercase tracking-wider">
                    &larr; Work Orders List
                </a>
                @if($workOrder->status === 'planned')
                    <a href="{{ route('sp-work-orders.edit', $workOrder->id) }}" class="px-4 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 font-bold text-xs rounded-xl shadow-sm transition uppercase tracking-wider">
                        Edit Work Order
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        <div class="max-w-7xl mx-auto space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-300 text-emerald-900 px-5 py-4 rounded-2xl text-xs font-bold shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-300 text-red-900 px-5 py-4 rounded-2xl text-xs font-bold shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            @php
                $activeSession = $workOrder->sessions->where('status', 'running')->first();
                $completedSessions = $workOrder->sessions->where('status', 'completed');
                $isFpiApproved = isset($firstPiece) && $firstPiece && $firstPiece->isApproved();

                // Compute aggregated session metrics
                $totalInputWip = $workOrder->sessions->flatMap->productionEntries->sum('input_qty');
                $totalGood = $workOrder->total_good;
                $totalReject = $workOrder->total_reject;
                $totalProduced = $totalGood + $totalReject;
                $ngRate = $totalProduced > 0 ? round(($totalReject / $totalProduced) * 100, 1) : 0;
                $totalDowntimeMin = $workOrder->sessions->flatMap->downtimeEntries->sum('duration_minutes');
                $activeManpowerCount = $workOrder->sessions->flatMap->manpowers->count();
            @endphp

            {{-- Target Completion Progress Card --}}
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm space-y-3">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-2">
                    <div>
                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Production Progress</h3>
                        <div class="text-3xl font-black text-gray-900 mt-1">
                            {{ number_format($totalGood) }} <span class="text-sm font-bold text-gray-500">/ {{ number_format($workOrder->target_qty) }} Pcs</span>
                        </div>
                    </div>
                    <div class="text-3xl font-black {{ $workOrder->progress_percentage >= 100 ? 'text-emerald-600' : 'text-blue-600' }}">
                        {{ $workOrder->progress_percentage }}%
                    </div>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-3.5 overflow-hidden border border-gray-200">
                    <div class="{{ $workOrder->progress_percentage >= 100 ? 'bg-emerald-500' : 'bg-blue-600' }} h-3.5 rounded-full transition-all duration-700" style="width: {{ min(100, $workOrder->progress_percentage) }}%"></div>
                </div>
            </div>

            {{-- Real-time KPI Metric Summary Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Input WIP Received</div>
                    <div class="text-xl font-black text-gray-900 mt-1">{{ number_format($totalInputWip) }} <span class="text-xs font-bold text-gray-500">Pcs</span></div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Good Output</div>
                    <div class="text-xl font-black text-emerald-700 mt-1">{{ number_format($totalGood) }} <span class="text-xs font-bold text-gray-500">Pcs</span></div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Defects & NG Rate</div>
                    <div class="text-xl font-black text-red-700 mt-1">{{ number_format($totalReject) }} <span class="text-xs font-bold text-gray-500">({{ $ngRate }}%)</span></div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Downtime / Line Team</div>
                    <div class="text-xl font-black text-gray-900 mt-1">{{ $totalDowntimeMin }} <span class="text-xs font-bold text-gray-500">Min</span> • {{ $activeManpowerCount }} <span class="text-xs font-bold text-gray-500">Op</span></div>
                </div>
            </div>

            {{-- Main Work Order & QC Action Layout --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Left 2 Columns: Specs --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Product & Manufacturing Setup Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                            <div class="mb-4 pb-2 border-b border-gray-100">
                                <h3 class="text-xs font-black text-gray-700 uppercase tracking-widest">Product Information</h3>
                            </div>
                            <div class="space-y-4 text-xs">
                                <div>
                                    <div class="text-[10px] text-gray-400 font-black uppercase tracking-wider">Part Number</div>
                                    <div class="font-bold text-blue-700 font-mono text-sm mt-0.5">{{ $workOrder->part_number }}</div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-gray-400 font-black uppercase tracking-wider">Part Name</div>
                                    <div class="font-bold text-gray-900 text-sm mt-0.5">{{ $workOrder->part_name }}</div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <div class="text-[10px] text-gray-400 font-black uppercase tracking-wider">Customer</div>
                                        <div class="font-bold text-gray-800 mt-0.5">{{ $workOrder->customer }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-gray-400 font-black uppercase tracking-wider">Model Code</div>
                                        <div class="font-bold text-gray-800 mt-0.5">{{ $workOrder->model ?: '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                            <div class="mb-4 pb-2 border-b border-gray-100">
                                <h3 class="text-xs font-black text-gray-700 uppercase tracking-widest">Manufacturing Setup</h3>
                            </div>
                            <div class="space-y-4 text-xs">
                                <div>
                                    <div class="text-[10px] text-gray-400 font-black uppercase tracking-wider">Process Type</div>
                                    <div class="font-bold text-gray-900 text-sm mt-0.5">{{ $workOrder->process_prod }}</div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <div class="text-[10px] text-gray-400 font-black uppercase tracking-wider">Production Line</div>
                                        <div class="font-bold text-gray-800 mt-0.5">{{ $workOrder->unit_line }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-gray-400 font-black uppercase tracking-wider">Shift</div>
                                        <div class="font-bold text-gray-800 mt-0.5">Shift {{ $workOrder->shift }}</div>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-gray-400 font-black uppercase tracking-wider">Planned Date</div>
                                    <div class="font-bold text-gray-800 mt-0.5">{{ \Carbon\Carbon::parse($workOrder->planned_date)->format('l, d F Y') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Right Column: Action & Gate Center --}}
                <div class="space-y-6">

                    {{-- Action Command Box --}}
                    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm space-y-4">
                        <h3 class="text-xs font-black text-gray-800 uppercase tracking-widest border-b border-gray-100 pb-3">Production Command Center</h3>

                        {{-- QC First Piece Inspection Gate Box --}}
                        <div class="p-4 rounded-xl border text-xs space-y-2
                            {{ $isFpiApproved ? 'bg-emerald-50 border-emerald-300 text-emerald-900' : (isset($firstPiece) && $firstPiece ? 'bg-amber-50 border-amber-300 text-amber-900' : 'bg-red-50 border-red-300 text-red-900') }}">
                            <div class="flex items-center justify-between">
                                <span class="font-black uppercase tracking-wider text-[10px] text-gray-500">QC First Piece Gate</span>
                                @if($isFpiApproved)
                                    <span class="px-2.5 py-0.5 text-[10px] font-black rounded-full bg-emerald-200 text-emerald-900 uppercase">QC Approved (OK)</span>
                                @elseif(isset($firstPiece) && $firstPiece)
                                    <span class="px-2.5 py-0.5 text-[10px] font-black rounded-full bg-amber-200 text-amber-900 uppercase">Pending Verification</span>
                                @else
                                    <span class="px-2.5 py-0.5 text-[10px] font-black rounded-full bg-red-200 text-red-900 uppercase">Gate Required</span>
                                @endif
                            </div>

                            <p class="font-medium text-xs leading-relaxed">
                                @if($isFpiApproved)
                                    First Piece inspected & approved by QC Inspector <span class="font-black">{{ $firstPiece->checked_by ?: 'QC Inspector' }}</span>.
                                @elseif(isset($firstPiece) && $firstPiece)
                                    Inspection recorded, awaiting QC inspector signature sign-off.
                                @else
                                    QC First Piece Inspection must be completed & approved before starting production.
                                @endif
                            </p>

                            <div class="pt-1">
                                @if($isFpiApproved)
                                    <a href="{{ route('first-piece-inspections.show', $firstPiece->id) }}" class="inline-block text-xs font-black text-emerald-800 hover:underline uppercase tracking-wider">
                                        View Inspection #{{ $firstPiece->id }} &rarr;
                                    </a>
                                @elseif(isset($firstPiece) && $firstPiece)
                                    <a href="{{ route('first-piece-inspections.show', $firstPiece->id) }}" class="inline-block px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-lg transition uppercase tracking-wider">
                                        Sign QC Approval #{{ $firstPiece->id }}
                                    </a>
                                @else
                                    <a href="{{ route('first-piece-inspections.create', ['work_order_id' => $workOrder->id, 'part_number' => $workOrder->part_number, 'part_name' => $workOrder->part_name, 'model' => $workOrder->model]) }}"
                                        class="inline-block w-full text-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl transition uppercase tracking-wider">
                                        + Perform First Piece Inspection
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Production Execution Action Button --}}
                        @if($activeSession)
                            <a href="{{ route('app.sp-sessions.show', $activeSession->id) }}"
                                class="block w-full text-center bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-black py-3.5 px-4 rounded-xl shadow-sm text-xs transition uppercase tracking-wider">
                                Open Operator Recording Screen
                            </a>
                        @elseif($workOrder->status === 'planned' && $isFpiApproved)
                            <form action="{{ route('sp-sessions.start', $workOrder->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full text-center bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-black py-3.5 px-4 rounded-xl shadow-sm text-xs transition uppercase tracking-wider">
                                    Start Production
                                </button>
                            </form>
                        @elseif($completedSessions->count() > 0)
                            <a href="{{ route('second-process-reports.index') }}"
                                class="block w-full text-center bg-gray-700 hover:bg-gray-800 text-white font-black py-3.5 px-4 rounded-xl shadow-sm text-xs transition uppercase tracking-wider">
                                View Daily Production Reports
                            </a>
                        @else
                            <button type="button" disabled class="w-full bg-gray-200 text-gray-400 cursor-not-allowed font-black py-3.5 px-4 rounded-xl text-xs uppercase tracking-wider border border-gray-300">
                                Start Production (QC Gate Required)
                            </button>
                        @endif

                    </div>

                </div>
            </div>

            {{-- Production Sessions List Table Card --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mt-6">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <div>
                        <h3 class="font-black text-gray-800 text-sm uppercase tracking-widest">Production Sessions Log</h3>
                        <p class="text-xs text-gray-500 font-medium">Recorded shift sessions for this Work Order</p>
                    </div>
                    <span class="text-xs font-bold bg-white px-3 py-1 rounded-full border border-gray-200 text-gray-600 shadow-sm">
                        {{ $workOrder->sessions->count() }} Session(s)
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                                <th class="px-6 py-3">Session ID</th>
                                <th class="px-6 py-3">Operator</th>
                                <th class="px-6 py-3">Time Range</th>
                                <th class="px-6 py-3 text-right">Input WIP</th>
                                <th class="px-6 py-3 text-right">Good Qty</th>
                                <th class="px-6 py-3 text-right">Reject Qty</th>
                                <th class="px-6 py-3 text-right">Yield %</th>
                                <th class="px-6 py-3 text-center">Status</th>
                                <th class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs">
                            @forelse($workOrder->sessions as $session)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 font-black text-blue-700 whitespace-nowrap">#SESSION-{{ $session->id }}</td>
                                    <td class="px-6 py-4 font-bold text-gray-800 whitespace-nowrap">{{ $session->operator?->name ?? 'Operator' }}</td>
                                    <td class="px-6 py-4 font-semibold text-gray-600 whitespace-nowrap">
                                        {{ $session->started_at ? $session->started_at->format('H:i') : '-' }} &rarr; {{ $session->finished_at ? $session->finished_at->format('H:i') : 'Now' }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-black text-gray-900 whitespace-nowrap">
                                        {{ number_format($session->productionEntries->sum('input_qty')) }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-black text-emerald-700 whitespace-nowrap">
                                        {{ number_format($session->total_good) }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-black text-red-700 whitespace-nowrap">
                                        {{ number_format($session->total_reject) }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-black whitespace-nowrap">
                                        {{ $session->yield }}%
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        @if($session->status === 'running')
                                            <span class="px-3 py-1 text-[10px] font-black rounded-full bg-emerald-100 text-emerald-800 uppercase tracking-wider border border-emerald-200">Running</span>
                                        @elseif($session->approved_by)
                                            <span class="px-3 py-1 text-[10px] font-black rounded-full bg-blue-100 text-blue-800 uppercase tracking-wider border border-blue-200">Approved</span>
                                        @else
                                            <span class="px-3 py-1 text-[10px] font-black rounded-full bg-gray-100 text-gray-700 uppercase tracking-wider border border-gray-200">Completed</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-1 whitespace-nowrap">
                                        <a href="{{ route('app.sp-sessions.show', $session->id) }}"
                                            class="inline-block px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-lg border border-gray-300 transition uppercase tracking-wider">
                                            Open Screen
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-8 text-center text-gray-400 font-medium">
                                        No production sessions recorded for this Work Order.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
