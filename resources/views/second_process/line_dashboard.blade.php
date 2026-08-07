<x-operator-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="font-black text-2xl text-gray-900 uppercase tracking-wide flex items-center gap-2">
                        <span>{{ $line }}</span>
                        <span class="text-xs font-bold text-gray-400 bg-gray-100 border border-gray-200 px-2.5 py-1 rounded-lg">Line Dashboard</span>
                    </h2>
                    @if($activeSession)
                        <span class="px-3 py-1 text-xs font-black rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 uppercase tracking-widest animate-pulse">
                            ACTIVE RUNNING
                        </span>
                    @else
                        <span class="px-3 py-1 text-xs font-black rounded-full bg-gray-100 text-gray-600 border border-gray-300 uppercase tracking-widest">
                            IDLE / NO RUNNING SESSION
                        </span>
                    @endif
                </div>
                <p class="text-xs font-semibold text-gray-500 mt-1">Dedicated shop floor line metrics, hourly performance, defect Pareto, downtime analysis, and session history</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('second-process.dashboard') }}" class="px-4 py-2 bg-gray-900 hover:bg-black text-white font-bold text-xs rounded-xl shadow-sm transition uppercase tracking-wider">
                    Overview Dashboard
                </a>
                @php $currentSlug = array_search($line, config('mes.sp_lines', [])) ?: \Illuminate\Support\Str::slug($line); @endphp
                <a href="{{ route('sp-sessions.line-gateway', ['lineSlug' => $currentSlug]) }}" class="px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-800 font-bold text-xs rounded-xl border border-blue-200 shadow-sm transition uppercase tracking-wider">
                    Operator Gateway
                </a>
                @if($activeSession)
                    <a href="{{ route('app.sp-sessions.show', $activeSession->id) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition uppercase tracking-wider">
                        Operator Screen
                    </a>
                @elseif($activeWo)
                    @php $isFpApproved = $firstPiece && $firstPiece->isApproved(); @endphp
                    @if($isFpApproved)
                        <form action="{{ route('sp-sessions.start', $activeWo->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-sm transition uppercase tracking-wider">
                                Start Production
                            </button>
                        </form>
                    @endif
                @endif
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

        {{-- Filter & Line Control Bar --}}
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                <div class="flex items-center gap-2 text-xs font-bold text-gray-700 bg-blue-50 px-3 py-1.5 rounded-xl border border-blue-200">
                    <span class="text-blue-400 uppercase font-black text-[10px]">Line:</span>
                    <span class="text-blue-900 font-black">{{ $line }}</span>
                </div>

                {{-- Date & Shift Form --}}
                <form action="{{ route('second-process.line-dashboard', $line) }}" method="GET" class="flex flex-wrap items-center gap-4">
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
            </div>

            <div class="flex items-center gap-2 text-xs font-bold text-gray-500">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Auto-refreshing every 30s</span>
            </div>
        </div>

        {{-- Active Session Banner (if running) --}}
        @if($activeSession)
            @php
                $wo = $activeSession->workOrder;
                $goodQty = $activeSession->total_good ?? 0;
                $target = $wo->target_qty ?? 0;
                $pct = $target > 0 ? min(100, round(($goodQty / $target) * 100)) : 0;
            @endphp
            <div class="bg-gradient-to-r from-emerald-900 to-teal-900 text-white rounded-2xl p-6 shadow-md border border-emerald-700 relative overflow-hidden">
                <div class="absolute right-0 top-0 bottom-0 opacity-10 flex items-center pr-6 pointer-events-none">
                    <span class="text-9xl font-black text-white">RUNNING</span>
                </div>
                <div class="relative z-10 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                    <div class="space-y-2">
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1 bg-emerald-500 text-white text-[10px] font-black uppercase rounded-full tracking-wider animate-pulse">Live Session #{{ $activeSession->id }}</span>
                            <span class="text-xs text-emerald-200 font-mono">Started: {{ $activeSession->started_at?->format('H:i:s') }} ({{ $activeSession->started_at?->diffForHumans() }})</span>
                        </div>
                        <div class="text-2xl font-black text-white flex items-center gap-3">
                            <span>WO: {{ $wo->wo_number ?? '-' }}</span>
                            <span class="text-sm font-semibold text-emerald-200">| Customer: {{ $wo->customer ?? '-' }}</span>
                        </div>
                        <div class="text-sm text-emerald-100 font-medium">
                            Part: <strong class="text-white">{{ $wo->part_name ?? '-' }}</strong> <span class="font-mono text-emerald-300">({{ $wo->part_number ?? '-' }})</span>
                        </div>
                        <div class="text-xs text-emerald-200 flex items-center gap-4 pt-1">
                            <span>Operator: <strong class="text-white">{{ $activeSession->operator?->name ?? 'Unassigned' }}</strong></span>
                            <span>Shift: <strong class="text-white">Shift {{ $activeSession->shift }}</strong></span>
                        </div>
                    </div>

                    <div class="w-full lg:w-80 bg-white/10 backdrop-blur p-4 rounded-xl border border-white/10 space-y-3">
                        <div class="flex justify-between items-center text-xs font-black uppercase text-emerald-200">
                            <span>Progress</span>
                            <span>{{ number_format($goodQty) }} / {{ number_format($target) }} Pcs ({{ $pct }}%)</span>
                        </div>
                        <div class="w-full bg-emerald-950/60 rounded-full h-3 overflow-hidden border border-emerald-500/30">
                            <div class="bg-emerald-400 h-3 rounded-full transition-all duration-500 shadow-sm" style="width: {{ $pct }}%"></div>
                        </div>
                        <div class="flex justify-between text-[11px] text-emerald-100 pt-1">
                            <span>Defects: <strong class="text-red-300 font-bold">{{ number_format($activeSession->total_reject) }}</strong></span>
                            <span>NG Rate: <strong class="{{ $activeSession->ng_rate > 2 ? 'text-red-300' : 'text-emerald-300' }} font-bold">{{ $activeSession->ng_rate }}%</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Top KPI Metric Cards (6 Grid) --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            {{-- KPI 1: Good Output --}}
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm flex flex-col justify-between">
                <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Good Output</div>
                <div class="text-2xl font-black text-emerald-600">{{ number_format($totalGood) }}</div>
                <div class="text-[10px] text-gray-400 font-semibold mt-1">Pcs produced</div>
            </div>

            {{-- KPI 2: Defect Output --}}
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm flex flex-col justify-between">
                <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Defects (NG)</div>
                <div class="text-2xl font-black text-red-600">{{ number_format($totalReject) }}</div>
                <div class="text-[10px] text-gray-400 font-semibold mt-1">Pcs rejected</div>
            </div>

            {{-- KPI 3: NG Rate --}}
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm flex flex-col justify-between">
                <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">NG Rate</div>
                <div class="text-2xl font-black {{ $ngRate > 2 ? 'text-red-600' : 'text-emerald-600' }}">{{ $ngRate }}%</div>
                <div class="text-[10px] text-gray-400 font-semibold mt-1">Target: &le; 2.0%</div>
            </div>

            {{-- KPI 4: Yield --}}
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm flex flex-col justify-between">
                <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Process Yield</div>
                <div class="text-2xl font-black text-blue-600">{{ $yieldPct }}%</div>
                <div class="text-[10px] text-gray-400 font-semibold mt-1">Good / Total output</div>
            </div>

            {{-- KPI 5: Input WIP --}}
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm flex flex-col justify-between">
                <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Total Input Received</div>
                <div class="text-2xl font-black text-gray-900">{{ number_format($totalInput) }}</div>
                <div class="text-[10px] text-gray-400 font-semibold mt-1">Pcs from molding/WIP</div>
            </div>

            {{-- KPI 6: Downtime Minutes --}}
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm flex flex-col justify-between">
                <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Downtime Loss</div>
                <div class="text-2xl font-black {{ $totalDowntimeMinutes > 30 ? 'text-amber-600' : 'text-gray-900' }}">{{ $totalDowntimeMinutes }} <span class="text-xs text-gray-400 font-normal">min</span></div>
                <div class="text-[10px] text-gray-400 font-semibold mt-1">{{ $downtimeBreakdown->sum('occurrences') }} stop event(s)</div>
            </div>
        </div>

        {{-- Charts Row: Hourly Output Bar Chart & Defect Pareto --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Chart 1: Hourly Output --}}
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm space-y-4">
                <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                    <div>
                        <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">Hourly Production Output</h3>
                        <p class="text-xs text-gray-400 font-medium">Good vs Reject distribution by hour</p>
                    </div>
                    <span class="text-[10px] font-black uppercase text-gray-400 bg-gray-50 border px-2 py-0.5 rounded-lg">Shift {{ $shift }}</span>
                </div>
                <div class="relative h-64 w-full">
                    <canvas id="chartHourlyOutput"></canvas>
                </div>
            </div>

            {{-- Chart 2: Defect Pareto --}}
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm space-y-4">
                <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                    <div>
                        <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">Defect Breakdown (Pareto)</h3>
                        <p class="text-xs text-gray-400 font-medium">Rejects grouped by defect classification</p>
                    </div>
                    <span class="text-[10px] font-black uppercase text-red-600 bg-red-50 border border-red-100 px-2 py-0.5 rounded-lg">Total: {{ number_format($totalReject) }} Pcs</span>
                </div>
                <div class="relative h-64 w-full">
                    <canvas id="chartDefectPareto"></canvas>
                </div>
            </div>
        </div>

        {{-- Downtime & Rework Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Downtime Log Table (2 Cols) --}}
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <div>
                            <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">Downtime Loss Log</h3>
                            <p class="text-xs text-gray-400 font-medium">Stop events recorded during shift</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-900 border border-amber-200">
                            Total: {{ $totalDowntimeMinutes }} Min
                        </span>
                    </div>

                    {{-- Downtime Categories Badges --}}
                    @if($downtimeBreakdown->count() > 0)
                        <div class="p-4 bg-amber-50/40 border-b border-amber-100/50 flex flex-wrap gap-2">
                            @foreach($downtimeBreakdown as $dt)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-white border border-amber-200 text-amber-950 shadow-2xs">
                                    <span>{{ $dt->reason }}:</span>
                                    <strong class="text-amber-700 font-black">{{ $dt->total_minutes }} min</strong>
                                    <span class="text-[10px] text-gray-400">({{ $dt->occurrences }}x)</span>
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                                    <th class="px-6 py-3">Reason</th>
                                    <th class="px-6 py-3">Start Time</th>
                                    <th class="px-6 py-3">Resume Time</th>
                                    <th class="px-6 py-3 text-right">Duration</th>
                                    <th class="px-6 py-3">Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-xs">
                                @forelse($downtimeLog as $dtEntry)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="px-6 py-3 font-bold text-gray-900">{{ $dtEntry->reason }}</td>
                                        <td class="px-6 py-3 text-gray-600 font-mono">{{ $dtEntry->start_time?->format('H:i') ?? '-' }}</td>
                                        <td class="px-6 py-3 text-gray-600 font-mono">{{ $dtEntry->resume_time?->format('H:i') ?? '-' }}</td>
                                        <td class="px-6 py-3 text-right font-black text-amber-700">{{ $dtEntry->duration_minutes }} min</td>
                                        <td class="px-6 py-3 text-gray-500 italic max-w-xs truncate">{{ $dtEntry->remarks ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-400 font-medium text-xs">No downtime events recorded for this shift.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Rework Summary Card (1 Col) --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-5 flex flex-col justify-between">
                <div>
                    <div class="border-b border-gray-100 pb-3 mb-4">
                        <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">Rework Recovery</h3>
                        <p class="text-xs text-gray-400 font-medium">Defects processed for recovery</p>
                    </div>

                    <div class="space-y-4">
                        <div class="bg-blue-50/60 p-4 rounded-xl border border-blue-100 flex justify-between items-center">
                            <div>
                                <div class="text-[10px] font-black text-blue-800 uppercase">Input to Rework</div>
                                <div class="text-xs text-blue-600 font-semibold">Parts sent for rework</div>
                            </div>
                            <div class="text-xl font-black text-blue-950">{{ number_format($totalReworkIn) }} <span class="text-xs text-blue-700">Pcs</span></div>
                        </div>

                        <div class="bg-emerald-50/60 p-4 rounded-xl border border-emerald-100 flex justify-between items-center">
                            <div>
                                <div class="text-[10px] font-black text-emerald-800 uppercase">Successfully Recovered</div>
                                <div class="text-xs text-emerald-600 font-semibold">Saved & passed QC</div>
                            </div>
                            <div class="text-xl font-black text-emerald-950">{{ number_format($totalReworkRecovered) }} <span class="text-xs text-emerald-700">Pcs</span></div>
                        </div>

                        <div class="bg-red-50/60 p-4 rounded-xl border border-red-100 flex justify-between items-center">
                            <div>
                                <div class="text-[10px] font-black text-red-800 uppercase">Scrapped / Irrecoverable</div>
                                <div class="text-xs text-red-600 font-semibold">Final scrap count</div>
                            </div>
                            <div class="text-xl font-black text-red-950">{{ number_format($totalScrap) }} <span class="text-xs text-red-700">Pcs</span></div>
                        </div>
                    </div>
                </div>

                @php
                    $reworkRecoveryRate = $totalReworkIn > 0 ? round(($totalReworkRecovered / $totalReworkIn) * 100, 1) : 0;
                @endphp
                <div class="pt-4 border-t border-gray-100 text-center">
                    <div class="text-[10px] font-black text-gray-400 uppercase mb-1">Rework Recovery Rate</div>
                    <div class="text-lg font-black text-gray-800">{{ $reworkRecoveryRate }}%</div>
                </div>
            </div>
        </div>

        {{-- Manpower Roster & Session History Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Manpower Roster --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <div>
                        <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">Shift Manpower Team</h3>
                        <p class="text-xs text-gray-400 font-medium">Assigned operators and line leaders</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-black bg-blue-100 text-blue-800 border border-blue-200">
                        {{ $manpower->count() }} Members
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                                <th class="px-6 py-3">Operator Name</th>
                                <th class="px-6 py-3">Employee No.</th>
                                <th class="px-6 py-3">Role</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs">
                            @forelse($manpower as $mp)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-3 font-bold text-gray-900">{{ $mp->operator_name }}</td>
                                    <td class="px-6 py-3 text-gray-500 font-mono">{{ $mp->employee_no ?? '-' }}</td>
                                    <td class="px-6 py-3 font-semibold text-blue-700">{{ $mp->role }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-gray-400 font-medium text-xs">No team members explicitly logged for this session.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Recent Line Session History --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <div>
                        <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">Recent Session History</h3>
                        <p class="text-xs text-gray-400 font-medium">Last 10 sessions on {{ $line }}</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                                <th class="px-6 py-3">Date / Shift</th>
                                <th class="px-6 py-3">WO Number</th>
                                <th class="px-6 py-3 text-right">Good Output</th>
                                <th class="px-6 py-3 text-right">Reject</th>
                                <th class="px-6 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs">
                            @forelse($sessionHistory as $sHist)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-3 font-bold text-gray-800">
                                        <div>{{ $sHist->started_at?->format('Y-m-d') }}</div>
                                        <div class="text-[10px] text-gray-400">Shift {{ $sHist->shift }}</div>
                                    </td>
                                    <td class="px-6 py-3 font-black text-blue-700">
                                        <a href="{{ route('sp-work-orders.show', $sHist->work_order_id) }}" class="hover:underline">
                                            {{ $sHist->workOrder->wo_number ?? '-' }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-3 text-right font-black text-emerald-600">{{ number_format($sHist->total_good) }}</td>
                                    <td class="px-6 py-3 text-right font-black text-red-600">{{ number_format($sHist->total_reject) }}</td>
                                    <td class="px-6 py-3 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider border
                                            {{ $sHist->status === 'running' ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-gray-100 text-gray-700 border-gray-200' }}">
                                            {{ $sHist->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-400 font-medium text-xs">No prior session history found for this line.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- Chart.js Initialization --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.Chart === 'undefined') {
                console.error('Chart.js is not loaded');
                return;
            }

            const hourlyData = @json($hourlyOutput);
            const defectData = @json($defectPareto);

            // 1. Hourly Output Stacked Bar Chart
            const hourlyLabels = Object.keys(hourlyData);
            const hourlyGood = hourlyLabels.map(h => hourlyData[h].good);
            const hourlyReject = hourlyLabels.map(h => hourlyData[h].reject);

            new window.Chart(document.getElementById('chartHourlyOutput'), {
                type: 'bar',
                data: {
                    labels: hourlyLabels.length ? hourlyLabels : ['No Data'],
                    datasets: [
                        {
                            label: 'Good Output',
                            data: hourlyGood.length ? hourlyGood : [0],
                            backgroundColor: '#10b981',
                            borderRadius: 6,
                        },
                        {
                            label: 'Rejects (NG)',
                            data: hourlyReject.length ? hourlyReject : [0],
                            backgroundColor: '#ef4444',
                            borderRadius: 6,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { stacked: true, grid: { display: false } },
                        y: { stacked: true, beginAtZero: true, grid: { color: '#f3f4f6' } }
                    },
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 12, font: { weight: 'bold', size: 11 } } }
                    }
                }
            });

            // 2. Defect Pareto Horizontal Bar Chart
            const defectLabels = defectData.map(d => d.defect_type);
            const defectCounts = defectData.map(d => parseInt(d.total));

            new window.Chart(document.getElementById('chartDefectPareto'), {
                type: 'bar',
                data: {
                    labels: defectLabels.length ? defectLabels : ['No Defects'],
                    datasets: [{
                        label: 'Defect Qty',
                        data: defectCounts.length ? defectCounts : [0],
                        backgroundColor: '#f59e0b',
                        borderRadius: 6,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                        y: { grid: { display: false } }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        });
    </script>
</x-operator-layout>
