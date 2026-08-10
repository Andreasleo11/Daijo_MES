<x-operator-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="font-black text-2xl text-gray-900 uppercase tracking-wide flex items-center gap-2">
                        <span>{{ $line }}</span>
                        <span class="text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200 px-2.5 py-1 rounded-lg">Operator Gateway</span>
                    </h2>
                </div>
                <div class="flex flex-wrap items-center gap-3 mt-1.5 text-xs text-gray-500 font-semibold">
                    <span>Active Shift: <strong class="text-blue-700 font-black">Shift {{ $shift }}</strong> @if(!empty($currentShiftConfig)) <span class="text-gray-400 font-mono">({{ $currentShiftConfig['start'] }} - {{ $currentShiftConfig['end'] }})</span> @endif</span>
                    <span class="text-gray-300">•</span>
                    <span class="flex items-center gap-1.5 text-gray-500 font-medium">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Auto-refreshing 15s
                    </span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @foreach(['1' => 'Shift 1', '2' => 'Shift 2', '3' => 'Shift 3', 'all' => 'All Shifts'] as $sKey => $sLabel)
                    <a href="{{ route('sp-sessions.line-gateway', ['lineSlug' => $lineSlug, 'shift' => $sKey]) }}"
                       class="px-3 py-1.5 rounded-xl text-xs transition flex items-center gap-1 {{ (string)$selectedShift === (string)$sKey ? 'bg-blue-600 text-white shadow-sm font-black' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-300' }}">
                        <span>{{ $sLabel }}</span>
                        @if((string)$currentShift === (string)$sKey)
                            <span class="text-[9px] font-black uppercase px-1 py-0.2 rounded {{ (string)$selectedShift === (string)$sKey ? 'bg-blue-800 text-white' : 'bg-blue-100 text-blue-800' }}">Current</span>
                        @endif
                    </a>
                @endforeach

                <div class="h-5 w-px bg-gray-300 mx-1 hidden sm:block"></div>

                <a href="{{ route('second-process.dashboard', ['shift' => $selectedShift]) }}" class="px-3.5 py-1.5 bg-gray-900 hover:bg-black text-white font-bold text-xs rounded-xl shadow-sm transition uppercase tracking-wider">
                    Overview Dashboard
                </a>
                <a href="{{ route('second-process.line-dashboard', ['line' => $line, 'shift' => $selectedShift]) }}" class="px-3.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-800 font-bold text-xs rounded-xl border border-blue-200 shadow-sm transition uppercase tracking-wider">
                    Line Analytics
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6" x-data="{ 
            init() { 
                setInterval(() => { 
                    if(!document.hidden) window.location.reload(); 
                }, 15000);
            } 
        }">

        {{-- Main Assigned Work Orders Section --}}
        <div class="space-y-4">

            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-base font-black text-gray-900 uppercase tracking-wide">Assigned Work Orders</h3>
                    <p class="text-xs text-gray-500 font-medium">Select a Work Order below to launch or resume your production session</p>
                </div>
                <span class="px-3 py-1 bg-gray-100 border border-gray-200 rounded-full text-xs font-black text-gray-700">
                    {{ $workOrders->count() }} Orders Assigned
                </span>
            </div>

            @forelse($workOrders as $wo)
                @php
                    $runningSession = $wo->sessions->where('status', 'running')->first();
                    $completedSession = $wo->sessions->where('status', 'completed')->first();
                    $fp = $firstPieceMap->get($wo->part_number);
                    $fpApproved = $fp && $fp->isApproved();
                @endphp

                @if($runningSession)
                    {{-- STATE 1: RUNNING SESSION (ACTIVE) --}}
                    <div class="bg-white rounded-2xl border border-slate-200 border-l-4 border-l-emerald-500 shadow-sm overflow-hidden relative transition hover:shadow-md">
                        <div class="bg-emerald-50/80 px-6 py-3 border-b border-emerald-100 flex justify-between items-center">
                            <div class="flex items-center gap-3">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-200 text-emerald-900 uppercase tracking-widest animate-pulse border border-emerald-300">
                                    RUNNING SESSION #{{ $runningSession->id }}
                                </span>
                                @if($runningSession->is_qc_bypassed)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-purple-100 text-purple-900 uppercase tracking-widest border border-purple-200">
                                        QC BYPASSED
                                    </span>
                                @endif
                                <span class="text-xs font-mono text-emerald-800 font-medium">Started {{ $runningSession->started_at?->format('H:i') }} ({{ $runningSession->started_at?->diffForHumans() }})</span>
                            </div>
                            <span class="text-xs font-black uppercase tracking-wider text-emerald-900">Shift {{ $wo->shift }}</span>
                        </div>

                        <div class="p-6 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                            <div class="space-y-2.5">
                                <div class="text-2xl font-black text-gray-900 flex items-center gap-3">
                                    <a href="{{ route('sp-work-orders.show', $wo->id) }}" class="text-blue-700 hover:underline">{{ $wo->wo_number }}</a>
                                    <span class="text-sm font-bold text-gray-500">| Customer: {{ $wo->customer ?? '-' }}</span>
                                </div>
                                <div class="text-base font-bold text-gray-800">
                                    {{ $wo->part_name }} <span class="font-mono text-xs text-gray-500">({{ $wo->part_number }})</span>
                                </div>
                                @if($runningSession->is_qc_bypassed)
                                    <div class="p-3 bg-purple-50 border border-purple-200 rounded-xl text-xs font-bold text-purple-950 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                                        <div class="flex flex-col gap-0.5">
                                            <span><strong class="text-purple-700 uppercase">QC Bypass Reason:</strong> {{ $runningSession->qc_bypass_reason ?? 'Bypassed by Supervisor' }}</span>
                                            @if($runningSession->qcBypassedBy)
                                                <span class="text-[10px] text-purple-700 font-black uppercase">Bypassed By: {{ $runningSession->qcBypassedBy->name }}</span>
                                            @endif
                                        </div>
                                        <a href="{{ route('first-piece-inspections.create', [
                                                'work_order_id' => $wo->id,
                                                'part_number' => $wo->part_number,
                                                'part_name' => $wo->part_name,
                                                'model' => $wo->model
                                            ]) }}" 
                                           class="px-3 py-1.5 bg-purple-700 hover:bg-purple-800 text-white font-black text-xs rounded-lg shadow-sm transition uppercase tracking-wider whitespace-nowrap">
                                            + First Piece
                                        </a>
                                    </div>
                                @endif
                                <div class="text-xs font-semibold text-gray-500 flex flex-wrap items-center gap-4">
                                    <span>Target: <strong class="text-gray-900">{{ number_format($wo->target_qty) }} Pcs</strong></span>
                                    <span>Good Output: <strong class="text-emerald-600 font-bold">{{ number_format($runningSession->total_good) }} Pcs</strong></span>
                                    <span>Defects: <strong class="text-red-600 font-bold">{{ number_format($runningSession->total_reject) }} Pcs</strong></span>
                                    <span>NG Rate: <strong class="{{ $runningSession->ng_rate > 2 ? 'text-red-600' : 'text-emerald-600' }} font-bold">{{ $runningSession->ng_rate }}%</strong></span>
                                </div>
                            </div>

                            <div class="w-full lg:w-auto">
                                <a href="{{ route('app.sp-sessions.show', $runningSession->id) }}"
                                   class="block w-full lg:w-auto text-center bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-black py-4 px-8 rounded-xl shadow-md text-sm transition uppercase tracking-wider">
                                    Resume Production Screen
                                </a>
                            </div>
                        </div>
                    </div>

                @elseif($fpApproved)
                    {{-- STATE 2: READY TO START (QC APPROVED) --}}
                    <div class="bg-white rounded-2xl border border-blue-300 shadow-sm overflow-hidden transition hover:shadow-md">
                        <div class="bg-blue-50/80 px-6 py-3 border-b border-blue-100 flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-blue-200 text-blue-900 uppercase tracking-widest border border-blue-300">
                                    QC APPROVED — READY TO START
                                </span>
                                <span class="text-xs text-blue-800 font-semibold">&check; First Piece Inspection Passed</span>
                            </div>
                            <span class="text-xs font-black uppercase tracking-wider text-blue-900">Shift {{ $wo->shift }}</span>
                        </div>

                        <div class="p-6 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                            <div class="space-y-2">
                                <div class="text-xl font-black text-gray-900 flex items-center gap-3">
                                    <a href="{{ route('sp-work-orders.show', $wo->id) }}" class="text-blue-700 hover:underline">{{ $wo->wo_number }}</a>
                                    <span class="text-sm font-semibold text-gray-500">| Customer: {{ $wo->customer ?? '-' }}</span>
                                </div>
                                <div class="text-sm font-bold text-gray-800">
                                    {{ $wo->part_name }} <span class="font-mono text-xs text-gray-500">({{ $wo->part_number }})</span>
                                </div>
                                <div class="text-xs font-semibold text-gray-500">
                                    Target Quantity: <strong class="text-blue-950 font-black">{{ number_format($wo->target_qty) }} Pcs</strong>
                                </div>
                            </div>

                            <div class="w-full lg:w-auto">
                                <form action="{{ route('sp-sessions.start', $wo->id) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="w-full lg:w-auto text-center bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-black py-4 px-8 rounded-xl shadow-md text-sm transition uppercase tracking-wider">
                                        Start Production
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                @else
                    {{-- STATE 3: QC GATE PENDING --}}
                    <div class="bg-white rounded-2xl border border-amber-300 shadow-sm overflow-hidden transition hover:shadow-md">
                        <div class="bg-amber-50 px-6 py-3 border-b border-amber-100 flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-200 text-amber-900 uppercase tracking-widest border border-amber-300">
                                    QC GATE REQUIRED
                                </span>
                                <span class="text-xs text-amber-900 font-medium">First Piece Inspection required before starting production</span>
                            </div>
                            <span class="text-xs font-black uppercase tracking-wider text-amber-900">Shift {{ $wo->shift }}</span>
                        </div>

                        <div class="p-6 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                            <div class="space-y-2">
                                <div class="text-xl font-black text-gray-900 flex items-center gap-3">
                                    <a href="{{ route('sp-work-orders.show', $wo->id) }}" class="text-amber-800 hover:underline">{{ $wo->wo_number }}</a>
                                    <span class="text-sm font-semibold text-gray-500">| Customer: {{ $wo->customer ?? '-' }}</span>
                                </div>
                                <div class="text-sm font-bold text-gray-800">
                                    {{ $wo->part_name }} <span class="font-mono text-xs text-gray-500">({{ $wo->part_number }})</span>
                                </div>
                                <div class="text-xs font-semibold text-amber-800">
                                    Target Quantity: <strong>{{ number_format($wo->target_qty) }} Pcs</strong>
                                </div>
                            </div>

                            <div class="w-full lg:w-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-2" x-data="{ showBypassModal: false, selectedReason: '' }">
                                @can('execute-qc-inspections')
                                    <a href="{{ route('first-piece-inspections.create', ['work_order_id' => $wo->id, 'part_number' => $wo->part_number, 'part_name' => $wo->part_name, 'model' => $wo->model]) }}"
                                       class="text-center bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white font-black py-3.5 px-6 rounded-xl shadow-sm text-xs transition uppercase tracking-wider">
                                        Perform Inspection
                                    </a>
                                @endcan

                                <button type="button" @click="showBypassModal = true"
                                        class="text-center bg-amber-50 hover:bg-amber-100 border-2 border-amber-300 text-amber-900 font-bold py-3.5 px-4 rounded-xl text-xs transition uppercase tracking-wider flex items-center justify-center gap-1">
                                    <span>⚠️ Emergency Start (Bypass QC)</span>
                                </button>

                                {{-- Alpine.js Modal for QC Bypass Reason --}}
                                <template x-teleport="body">
                                    <div x-show="showBypassModal" 
                                         x-transition.opacity
                                         class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
                                         @keydown.escape.window="showBypassModal = false"
                                         x-cloak>
                                        <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 max-w-lg w-full p-6 space-y-4"
                                             @click.away="showBypassModal = false">
                                            <div class="flex justify-between items-start border-b border-gray-100 pb-3">
                                                <div>
                                                    <h3 class="text-base font-black text-amber-950 uppercase tracking-wide flex items-center gap-2">
                                                        <span>⚠️ Emergency Start (QC Gate Bypass)</span>
                                                    </h3>
                                                    <p class="text-xs text-gray-500 font-medium mt-0.5">WO: {{ $wo->wo_number }} — {{ $wo->part_name }}</p>
                                                </div>
                                                <button type="button" @click="showBypassModal = false" class="text-gray-400 hover:text-gray-600 text-lg font-bold">&times;</button>
                                            </div>

                                            <form action="{{ route('sp-sessions.start', $wo->id) }}" method="POST" class="space-y-4">
                                                @csrf
                                                <input type="hidden" name="bypass_qc" value="1">

                                                <div>
                                                    <label class="block text-xs font-black text-gray-700 uppercase mb-1">
                                                        Select Quick Suggestion Reason:
                                                    </label>
                                                    <div class="flex flex-wrap gap-1.5 max-h-36 overflow-y-auto p-2 bg-gray-50 rounded-xl border border-gray-200">
                                                        @foreach($quickBypassReasons as $preset)
                                                            <button type="button" 
                                                                    @click="selectedReason = @js($preset)"
                                                                    class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-white hover:bg-amber-100 border border-gray-300 text-gray-800 hover:border-amber-400 transition text-left">
                                                                + {{ $preset }}
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-black text-gray-700 uppercase mb-1">
                                                        Mandatory Bypass Reason <span class="text-red-500">*</span>
                                                    </label>
                                                    <textarea name="bypass_reason" 
                                                              x-model="selectedReason"
                                                              rows="3" 
                                                              required
                                                              minlength="3"
                                                              placeholder="Type or select a reason above (e.g. Urgent Delivery Run - Pre-approved by Supervisor)..."
                                                              class="w-full rounded-xl border-gray-300 text-xs font-medium focus:ring-amber-500 focus:border-amber-500 p-3 bg-gray-50/50"></textarea>
                                                </div>

                                                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-900 font-medium">
                                                    <strong>QC Audit Notice:</strong> This bypass event will be logged with your user ID and timestamp, and flagged for supervisor review.
                                                </div>

                                                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                                                    <button type="button" @click="showBypassModal = false"
                                                            class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl uppercase tracking-wider">
                                                        Cancel
                                                    </button>
                                                    <button type="submit"
                                                            class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-black text-xs rounded-xl shadow-md uppercase tracking-wider">
                                                        Confirm & Start Production
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                @endif

            @empty
                {{-- Empty State: No Work Orders Assigned --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm space-y-4">
                    <div>
                        <h4 class="text-base font-black text-gray-800 uppercase tracking-wide">No Work Orders Assigned to {{ $line }}</h4>
                        <p class="text-xs text-gray-500 mt-1 max-w-md mx-auto">There are no planned or active Work Orders assigned to this line right now. Please contact your line supervisor or production planner.</p>
                    </div>
                    <div class="pt-2 flex justify-center gap-3">
                        @can('manage-sp-work-orders')
                            <a href="{{ route('sp-work-orders.create', ['unit_line' => $line]) }}" class="px-4 py-2.5 bg-gray-900 hover:bg-black text-white font-bold text-xs rounded-xl shadow-sm transition uppercase tracking-wider">
                                Create New Work Order
                            </a>
                        @endcan
                        <a href="{{ route('second-process.dashboard') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl border border-gray-300 transition uppercase tracking-wider">
                            Return to Overview
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

    </div>
</x-operator-layout>
