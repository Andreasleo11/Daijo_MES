<x-operator-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-black text-xl text-gray-800 uppercase tracking-wide">
                    {{ __('Second Process Operator & Shop Floor Dashboard') }}
                </h2>
                <p class="text-xs font-semibold text-gray-500 mt-0.5">Real-time line status, First Piece QC gate tracking, and active session management</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-900 hover:bg-black text-white font-bold text-xs rounded-xl shadow-sm transition uppercase tracking-wider">
                    Main MES
                </a>
                <a href="{{ route('sp-work-orders.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold text-xs rounded-xl shadow-sm transition uppercase tracking-wider">
                    New Work Order
                </a>
                <a href="{{ route('sp-work-orders.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl border border-gray-300 shadow-sm transition uppercase tracking-wider">
                    Work Orders List
                </a>
                <a href="{{ route('first-piece-inspections.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl border border-gray-300 shadow-sm transition uppercase tracking-wider">
                    First Piece Inspections
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6" x-data="{ 
            init() { 
                setInterval(() => { 
                    if(!document.hidden) window.location.reload(); 
                }, 30000);
            } 
        }">

        {{-- Filter Control Bar --}}
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
            <form action="{{ route('second-process.dashboard') }}" method="GET" class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                <div class="flex items-center gap-2">
                    <label class="font-black text-xs uppercase text-gray-500">Date:</label>
                    <input type="date" name="date" value="{{ $date }}" class="rounded-xl border-gray-300 text-xs font-bold py-1.5 focus:ring-blue-500" onchange="this.form.submit()">
                </div>
                <div class="flex items-center gap-2">
                    <label class="font-black text-xs uppercase text-gray-500">Shift:</label>
                    <select name="shift" class="rounded-xl border-gray-300 text-xs font-bold py-1.5 focus:ring-blue-500" onchange="this.form.submit()">
                        @foreach(config('mes.shifts', []) as $sId => $sConf)
                            <option value="{{ $sId }}" {{ $shift == $sId ? 'selected' : '' }}>{{ $sConf['name'] }} ({{ $sConf['start'] }} - {{ $sConf['end'] }})</option>
                        @endforeach
                        @if(empty(config('mes.shifts', [])))
                            <option value="1" {{ $shift == 1 ? 'selected' : '' }}>Shift 1</option>
                            <option value="2" {{ $shift == 2 ? 'selected' : '' }}>Shift 2</option>
                            <option value="3" {{ $shift == 3 ? 'selected' : '' }}>Shift 3</option>
                        @endif
                    </select>
                </div>
                <noscript><button type="submit" class="bg-gray-100 px-3 py-1 text-xs font-bold rounded-xl border border-gray-300">Filter</button></noscript>
            </form>

            <div class="flex items-center gap-2 text-xs font-bold text-gray-500">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Auto-refreshing every 30s</span>
            </div>
        </div>

        {{-- Top KPI Metric Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            {{-- KPI 1: Active Lines --}}
            <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex justify-between items-center">
                <div>
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Active Production Lines</div>
                    <div class="text-2xl font-black text-gray-900">{{ $runningSessionsCount }} <span class="text-xs text-gray-400 font-bold">/ {{ count($lines) }} Lines</span></div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 uppercase tracking-widest border border-emerald-200">
                    Running
                </span>
            </div>

            {{-- KPI 2: Queued Work Orders --}}
            <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex justify-between items-center">
                <div>
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Queued Work Orders</div>
                    <div class="text-2xl font-black text-gray-900">{{ $pendingWoCount }} <span class="text-xs text-gray-400 font-bold">Orders</span></div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-black bg-blue-100 text-blue-800 uppercase tracking-widest border border-blue-200">
                    Pending
                </span>
            </div>

            {{-- KPI 3: First Piece Inspections --}}
            <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex justify-between items-center">
                <div>
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">First Piece QC Gate</div>
                    <div class="text-2xl font-black text-gray-900">{{ $approvedFirstPieceCount }} <span class="text-xs text-gray-400 font-bold">Approved Today</span></div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-800 uppercase tracking-widest border border-amber-200">
                    QC Gate
                </span>
            </div>

            {{-- KPI 4: Shift Output & NG Rate --}}
            <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex justify-between items-center">
                <div>
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Shift Total Output</div>
                    <div class="text-2xl font-black text-gray-900">{{ number_format($totalShiftGood) }} <span class="text-xs text-gray-400 font-bold">Pcs</span></div>
                </div>
                <div class="text-right">
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">NG Rate</div>
                    <div class="text-sm font-black {{ $overallNgRate > 2 ? 'text-red-600' : 'text-emerald-600' }}">{{ $overallNgRate }}%</div>
                </div>
            </div>
        </div>

        {{-- Shop Floor Line Status Grid --}}
        <div>
            <div class="mb-4">
                <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">Shop Floor Lines Overview</h3>
                <p class="text-xs text-gray-500 font-medium">Real-time operational state for each production line</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach($lines as $lineName)
                    @php
                        $report = $reports->get($lineName);
                        $assignedWo = $workOrdersByLine->get($lineName);
                        $firstPiece = null;
                        if ($assignedWo) {
                            $firstPiece = $firstPieceMap->get($assignedWo->part_number);
                        }
                        $spLines = config('mes.sp_lines', []);
                        $lineSlug = array_search($lineName, $spLines) ?: \Illuminate\Support\Str::slug($lineName);
                    @endphp

                    @if($report)
                        {{-- STATE 1: RUNNING SESSION --}}
                        <div class="bg-white rounded-2xl border border-emerald-300 shadow-sm overflow-hidden flex flex-col justify-between">
                            <div>
                                <div class="bg-emerald-50 px-5 py-3 border-b border-emerald-100 flex justify-between items-center">
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-black text-emerald-950 uppercase tracking-wider text-sm">{{ $lineName }}</h4>
                                        <a href="{{ route('sp-sessions.line-gateway', ['lineSlug' => $lineSlug]) }}" class="text-[10px] font-bold text-emerald-700 hover:underline uppercase tracking-wider">Gateway &rarr;</a>
                                    </div>
                                    @if($report->status === 'running')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-200 text-emerald-900 uppercase tracking-widest animate-pulse border border-emerald-300">
                                            Running
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-gray-200 text-gray-700 uppercase tracking-widest">
                                            Finished
                                        </span>
                                    @endif
                                </div>
                                <div class="p-5 space-y-4">
                                    <div>
                                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider">Work Order & Part</div>
                                        <div class="font-black text-xs text-blue-700">{{ $report->workOrder->wo_number ?? '-' }}</div>
                                        <div class="font-bold text-sm text-gray-800 truncate" title="{{ $report->workOrder->part_name ?? '-' }}">{{ $report->workOrder->part_name ?? '-' }}</div>
                                        <div class="text-xs text-gray-500 font-mono font-medium">{{ $report->workOrder->part_number ?? '-' }}</div>
                                    </div>

                                    {{-- Target Progress --}}
                                    @php
                                        $targetQty = $report->workOrder->target_qty ?? 0;
                                        $goodQty = $report->total_good ?? 0;
                                        $pct = $targetQty > 0 ? min(100, round(($goodQty / $targetQty) * 100)) : 0;
                                    @endphp
                                    <div>
                                        <div class="flex justify-between items-center text-[10px] font-black text-gray-500 uppercase mb-1">
                                            <span>Progress</span>
                                            <span>{{ number_format($goodQty) }} / {{ number_format($targetQty) }} Pcs ({{ $pct }}%)</span>
                                        </div>
                                        <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden border border-gray-200">
                                            <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-3 gap-2 pt-2 border-t border-gray-100 text-center">
                                        <div>
                                            <div class="text-[9px] font-black text-gray-400 uppercase">Good Output</div>
                                            <div class="font-black text-sm text-emerald-600">{{ number_format($report->total_good) }}</div>
                                        </div>
                                        <div>
                                            <div class="text-[9px] font-black text-gray-400 uppercase">Defects</div>
                                            <div class="font-black text-sm text-red-600">{{ number_format($report->total_reject) }}</div>
                                        </div>
                                        <div>
                                            <div class="text-[9px] font-black text-gray-400 uppercase">NG Rate</div>
                                            <div class="font-black text-sm {{ $report->ng_rate > 2 ? 'text-red-600' : 'text-emerald-600' }}">{{ $report->ng_rate }}%</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 bg-gray-50 border-t border-gray-100 flex items-center gap-2">
                                <a href="{{ route('second-process.line-dashboard', ['line' => $lineSlug, 'date' => $date, 'shift' => $shift]) }}" class="w-1/3 text-center bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-2 rounded-xl text-xs transition uppercase tracking-wider">
                                    Analytics
                                </a>
                                <a href="{{ route('app.sp-sessions.show', $report->id) }}" class="w-2/3 text-center bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-black py-3 px-4 rounded-xl shadow-sm text-xs transition uppercase tracking-wider">
                                    Operator Screen
                                </a>
                            </div>
                        </div>

                    @elseif($assignedWo)
                        @php
                            $isApproved = $firstPiece && $firstPiece->isApproved();
                        @endphp

                        @if($isApproved)
                            {{-- STATE 2: READY TO START (QC APPROVED) --}}
                            <div class="bg-white rounded-2xl border border-blue-300 shadow-sm overflow-hidden flex flex-col justify-between">
                                <div>
                                    <div class="bg-blue-50 px-5 py-3 border-b border-blue-100 flex justify-between items-center">
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-black text-blue-950 uppercase tracking-wider text-sm">{{ $lineName }}</h4>
                                            <a href="{{ route('sp-sessions.line-gateway', ['lineSlug' => $lineSlug]) }}" class="text-[10px] font-bold text-blue-700 hover:underline uppercase tracking-wider">Gateway &rarr;</a>
                                        </div>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-blue-200 text-blue-900 uppercase tracking-widest border border-blue-300">
                                            QC Approved
                                        </span>
                                    </div>
                                    <div class="p-5 space-y-3">
                                        <div>
                                            <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider">Assigned Work Order</div>
                                            <div class="font-black text-xs text-blue-700">{{ $assignedWo->wo_number }}</div>
                                            <div class="font-bold text-sm text-gray-800 truncate" title="{{ $assignedWo->part_name }}">{{ $assignedWo->part_name }}</div>
                                            <div class="text-xs text-gray-500 font-mono font-medium">{{ $assignedWo->part_number }}</div>
                                        </div>

                                        <div class="p-3 bg-blue-50/50 rounded-xl border border-blue-100">
                                            <div class="text-[10px] font-black text-blue-800 uppercase">Target Quantity</div>
                                            <div class="text-base font-black text-blue-950">{{ number_format($assignedWo->target_qty) }} Pcs</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4 bg-gray-50 border-t border-gray-100 flex items-center gap-2">
                                    <a href="{{ route('second-process.line-dashboard', ['line' => $lineSlug, 'date' => $date, 'shift' => $shift]) }}" class="w-1/3 text-center bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-2 rounded-xl text-xs transition uppercase tracking-wider">
                                        Analytics
                                    </a>
                                    <form action="{{ route('sp-sessions.start', $assignedWo->id) }}" method="POST" class="w-2/3">
                                        @csrf
                                        <button type="submit" class="w-full text-center bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-black py-3 px-4 rounded-xl shadow-sm text-xs transition uppercase tracking-wider">
                                            Start Production
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            {{-- STATE 3: GATE BLOCKED (PENDING QC FIRST PIECE) --}}
                            <div class="bg-white rounded-2xl border border-amber-300 shadow-sm overflow-hidden flex flex-col justify-between">
                                <div>
                                    <div class="bg-amber-50 px-5 py-3 border-b border-amber-100 flex justify-between items-center">
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-black text-amber-950 uppercase tracking-wider text-sm">{{ $lineName }}</h4>
                                            <a href="{{ route('sp-sessions.line-gateway', ['lineSlug' => $lineSlug]) }}" class="text-[10px] font-bold text-amber-800 hover:underline uppercase tracking-wider">Gateway &rarr;</a>
                                        </div>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-200 text-amber-900 uppercase tracking-widest border border-amber-300">
                                            QC Gate Pending
                                        </span>
                                    </div>
                                    <div class="p-5 space-y-3">
                                        <div>
                                            <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider">Assigned Work Order</div>
                                            <div class="font-black text-xs text-amber-800">{{ $assignedWo->wo_number }}</div>
                                            <div class="font-bold text-sm text-gray-800 truncate" title="{{ $assignedWo->part_name }}">{{ $assignedWo->part_name }}</div>
                                            <div class="text-xs text-gray-500 font-mono font-medium">{{ $assignedWo->part_number }}</div>
                                        </div>

                                        <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs font-semibold text-amber-900">
                                            First Piece Inspection is required before starting production session.
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4 bg-gray-50 border-t border-gray-100 flex items-center gap-2">
                                    <a href="{{ route('second-process.line-dashboard', ['line' => $lineSlug, 'date' => $date, 'shift' => $shift]) }}" class="w-1/3 text-center bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-2 rounded-xl text-xs transition uppercase tracking-wider">
                                        Analytics
                                    </a>
                                    <a href="{{ route('first-piece-inspections.create', ['work_order_id' => $assignedWo->id, 'part_number' => $assignedWo->part_number, 'part_name' => $assignedWo->part_name, 'model' => $assignedWo->model]) }}"
                                        class="block w-2/3 text-center bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white font-black py-3 px-4 rounded-xl shadow-sm text-xs transition uppercase tracking-wider">
                                        First Piece Inspection
                                    </a>
                                </div>
                            </div>
                        @endif

                    @else
                        {{-- STATE 4: IDLE LINE --}}
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col justify-between">
                            <div>
                                <div class="bg-gray-50 px-5 py-3 border-b border-gray-100 flex justify-between items-center">
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-black text-gray-600 uppercase tracking-wider text-sm">{{ $lineName }}</h4>
                                        <a href="{{ route('sp-sessions.line-gateway', ['lineSlug' => $lineSlug]) }}" class="text-[10px] font-bold text-gray-500 hover:underline uppercase tracking-wider">Gateway &rarr;</a>
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-gray-100 text-gray-500 uppercase tracking-widest border border-gray-200">
                                        Idle
                                    </span>
                                </div>
                                <div class="p-5 flex flex-col items-center justify-center text-center py-10">
                                    <p class="text-xs font-black text-gray-400 uppercase tracking-wider mb-1">No Active Production</p>
                                    <p class="text-[11px] text-gray-500 font-medium">Line is available for assignment</p>
                                </div>
                            </div>
                            <div class="p-4 bg-gray-50 border-t border-gray-100 flex items-center gap-2">
                                <a href="{{ route('second-process.line-dashboard', ['line' => $lineSlug, 'date' => $date, 'shift' => $shift]) }}" class="w-1/3 text-center bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-2 rounded-xl text-xs transition uppercase tracking-wider">
                                    Analytics
                                </a>
                                <a href="{{ route('sp-work-orders.create', ['unit_line' => $lineName]) }}" class="block w-2/3 text-center bg-gray-700 hover:bg-gray-800 active:bg-gray-900 text-white font-black py-3 px-4 rounded-xl shadow-sm text-xs transition uppercase tracking-wider">
                                    Create Work Order
                                </a>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Work Order Dispatch Quick List Table --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <div>
                    <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">Active & Planned Work Orders Dispatch</h3>
                    <p class="text-xs text-gray-500 font-medium">Quick production gating & session startup list</p>
                </div>
                <a href="{{ route('sp-work-orders.index') }}" class="text-xs font-bold text-blue-600 hover:underline uppercase tracking-wider">View All Work Orders &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                            <th class="px-6 py-3">WO Number</th>
                            <th class="px-6 py-3">Line / Shift</th>
                            <th class="px-6 py-3">Part Details</th>
                            <th class="px-6 py-3 text-right">Target Qty</th>
                            <th class="px-6 py-3 text-center">First Piece Gate</th>
                            <th class="px-6 py-3 text-center">Status</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs">
                        @forelse($activeWorkOrders as $wo)
                            @php
                                $fp = $firstPieceMap->get($wo->part_number);
                                $fpApproved = $fp && $fp->isApproved();
                            @endphp
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-4 font-black text-blue-700">
                                    <a href="{{ route('sp-work-orders.show', $wo->id) }}" class="hover:underline">{{ $wo->wo_number }}</a>
                                </td>
                                <td class="px-6 py-4 font-bold text-gray-800">
                                    <div>{{ $wo->unit_line }}</div>
                                    <div class="text-[10px] text-gray-400 font-medium">Shift {{ $wo->shift }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900">{{ $wo->part_name }}</div>
                                    <div class="text-[10px] text-gray-400 font-mono">{{ $wo->part_number }}</div>
                                </td>
                                <td class="px-6 py-4 text-right font-black text-gray-900">
                                    {{ number_format($wo->target_qty) }} Pcs
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($fpApproved)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 uppercase tracking-wider border border-emerald-200">
                                            QC Approved
                                        </span>
                                    @elseif($fp)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 uppercase tracking-wider border border-amber-200">
                                            Pending Sign-off
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-red-100 text-red-800 uppercase tracking-wider border border-red-200">
                                            Gate Required
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border
                                        {{ $wo->status === 'in_progress' ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-gray-100 text-gray-700 border-gray-200' }}">
                                        {{ str_replace('_', ' ', $wo->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($wo->status === 'in_progress')
                                        @php
                                            $session = $wo->sessions->where('status', 'running')->first();
                                        @endphp
                                        @if($session)
                                            <a href="{{ route('app.sp-sessions.show', $session->id) }}" class="inline-block bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-3 py-1.5 rounded-lg text-xs transition uppercase tracking-wider">
                                                Open Operator Screen
                                            </a>
                                        @endif
                                    @elseif($fpApproved)
                                        <form action="{{ route('sp-sessions.start', $wo->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-3 py-1.5 rounded-lg text-xs transition uppercase tracking-wider">
                                                Start Production
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('first-piece-inspections.create', ['work_order_id' => $wo->id, 'part_number' => $wo->part_number, 'part_name' => $wo->part_name, 'model' => $wo->model]) }}"
                                            class="inline-block bg-amber-600 hover:bg-amber-700 text-white font-bold px-3 py-1.5 rounded-lg text-xs transition uppercase tracking-wider">
                                            Inspection Gate
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-400 font-medium text-xs">No active or planned Work Orders found for this shift.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-operator-layout>
