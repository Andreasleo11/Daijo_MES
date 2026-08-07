<x-operator-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="font-bold text-xl text-gray-800 leading-tight">
                        {{ __('Real-Time Production Screen') }}
                    </h2>
                    @if($session->status === 'running')
                        <span class="px-3 py-0.5 text-xs font-bold rounded-full bg-green-500 text-white uppercase animate-pulse">
                            RUNNING
                        </span>
                    @elseif($session->approved_by)
                        <span class="px-3 py-0.5 text-xs font-bold rounded-full bg-blue-500 text-white uppercase" title="Approved by {{ $session->approvedBy->name }}">
                            APPROVED
                        </span>
                    @else
                        <span class="px-3 py-0.5 text-xs font-bold rounded-full bg-gray-500 text-white uppercase">
                            COMPLETED
                        </span>
                    @endif
                    @if($session->is_qc_bypassed)
                        <span class="px-3 py-0.5 text-xs font-bold rounded-full bg-amber-500 text-white uppercase" title="QC Bypassed: {{ $session->qc_bypass_reason }}">
                            ⚠️ QC BYPASSED
                        </span>
                    @endif
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    Work Order: <strong class="text-blue-600 font-bold">{{ $session->workOrder->wo_number }}</strong> |
                    Line: <strong>{{ $session->unit_line }}</strong> |
                    Shift: <strong>{{ $session->shift }}</strong> |
                    Operator: <strong>{{ $session->operator?->name ?? 'Operator' }}</strong>
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('sp-work-orders.show', $session->work_order_id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg shadow-sm text-xs transition">
                    ← WO Details
                </a>
                @if($session->status === 'running')
                    <button onclick="document.getElementById('modalFinish').showModal()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 px-5 rounded-lg shadow transition text-xs flex items-center gap-1">
                        <span class="mr-2">Finish Production</span>
                    </button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="productionSession()" x-cloak>
        <script>
            function productionSession() {
                return {
                    tab: 'production',
                    totals: {
                        input: {{ $session->total_input ?? 0 }},
                        good: {{ $session->total_good ?? 0 }},
                        reject: {{ $session->total_reject ?? 0 }},
                        yield: {{ $session->yield ?? 0 }},
                        downtime_minutes: {{ $session->downtimeEntries->sum('duration_minutes') ?? 0 }},
                        downtime_count: {{ $session->downtimeEntries->count() ?? 0 }}
                    },
                    productionEntries: @json($session->productionEntries),
                    rejectEntries: @json($session->rejectEntries),
                    downtimeEntries: @json($session->downtimeEntries),
                    reworkEntries: @json($session->reworkEntries),
                    inputEntries: @json($session->inputEntries),

                    async submitForm(event, type) {
                        const form = event.target;
                        const formData = new FormData(form);
                        const url = form.action;

                        try {
                            const response = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                },
                                body: formData
                            });

                            if (!response.ok) {
                                const err = await response.json().catch(() => ({}));
                                throw new Error(err.error || 'Submission failed');
                            }

                            const data = await response.json();
                            if (data.success) {
                                this.totals = { ...this.totals, ...data.totals };

                                if (type === 'production') this.productionEntries.unshift(data.entry);
                                else if (type === 'reject') this.rejectEntries.unshift(data.entry);
                                else if (type === 'downtime') this.downtimeEntries.unshift(data.entry);
                                else if (type === 'rework') this.reworkEntries.unshift(data.entry);
                                else if (type === 'input') this.inputEntries.unshift(data.entry);

                                form.reset();

                                // Reset Alpine reactive variables on form submit
                                if (form._x_dataStack) {
                                    const alpineData = form._x_dataStack[0];
                                    if (alpineData) {
                                        if ('good_qty' in alpineData) alpineData.good_qty = 0;
                                        if ('reject_qty' in alpineData) alpineData.reject_qty = 0;
                                        if ('quantity' in alpineData) alpineData.quantity = 1;
                                        if ('input_qty' in alpineData) alpineData.input_qty = 0;
                                        if ('recovered_qty' in alpineData) alpineData.recovered_qty = 0;
                                        if ('scrapped_qty' in alpineData) alpineData.scrapped_qty = 0;
                                        if ('qty' in alpineData) alpineData.qty = 0;
                                        if ('defect' in alpineData) alpineData.defect = '';
                                        if ('reason' in alpineData) alpineData.reason = '';
                                    }
                                }

                                form.closest('dialog').close();
                            }
                        } catch (error) {
                            alert(error.message);
                        }
                    },
                    formatTime(dateString) {
                        if (!dateString) return '-';
                        return new Date(dateString).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit', hour12: false});
                    },
                    formatHM(dateString) {
                        if (!dateString) return '-';
                        return new Date(dateString).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', hour12: false});
                    },
                    formatNum(num) {
                        return new Intl.NumberFormat().format(num || 0);
                    }
                }
            }
        </script>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Part Info Header Card --}}
            <div class="bg-gradient-to-r from-blue-900 to-indigo-900 text-white p-5 rounded-xl shadow-md flex flex-wrap justify-between items-center gap-4">
                <div>
                    <div class="text-xs text-blue-200 uppercase font-bold tracking-wider">Part Info</div>
                    <div class="text-xl font-black mt-0.5">{{ $session->workOrder->part_number }}</div>
                    <div class="text-sm text-blue-100 mt-0.5">{{ $session->workOrder->part_name }} (Customer: {{ $session->workOrder->customer }})</div>
                </div>
                <div class="flex gap-6">
                    <div class="text-right">
                        <div class="text-xs text-blue-200 uppercase font-bold">Target Qty</div>
                        <div class="text-2xl font-black text-yellow-300">{{ number_format($session->workOrder->target_qty) }} Pcs</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-blue-200 uppercase font-bold">Start Time</div>
                        <div class="text-2xl font-black text-green-300">{{ $session->started_at ? $session->started_at->format('H:i') : '-' }}</div>
                    </div>
                </div>
            </div>

            {{-- Live KPI Counter Cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <div class="text-xs font-bold text-gray-500 uppercase">Input WIP</div>
                    <div class="text-3xl font-black text-gray-900 mt-1"><span x-text="formatNum(totals.input)"></span></div>
                    <div class="text-[11px] text-gray-400 mt-0.5">Received from Injection</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <div class="text-xs font-bold text-gray-500 uppercase">Good Qty</div>
                    <div class="text-3xl font-black text-green-600 mt-1"><span x-text="formatNum(totals.good)"></span></div>
                    <div class="text-[11px] text-gray-400 mt-0.5">Passed inspection</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <div class="text-xs font-bold text-gray-500 uppercase">Reject Qty</div>
                    <div class="text-3xl font-black text-red-600 mt-1"><span x-text="formatNum(totals.reject)"></span></div>
                    <div class="text-[11px] text-gray-400 mt-0.5">Defects recorded</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <div class="text-xs font-bold text-gray-500 uppercase">Yield Rate</div>
                    <div class="text-3xl font-black text-blue-600 mt-1"><span x-text="totals.yield"></span>%</div>
                    <div class="text-[11px] text-gray-400 mt-0.5">Good / Total Output</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm col-span-2 lg:col-span-1">
                    <div class="text-xs font-bold text-gray-500 uppercase">Downtime Loss</div>
                    <div class="text-3xl font-black text-orange-600 mt-1">
                        <span x-text="totals.downtime_minutes"></span> <span class="text-xs font-bold">Mins</span>
                    </div>
                    <div class="text-[11px] text-gray-400 mt-0.5"><span x-text="totals.downtime_count"></span> stop events</div>
                </div>
            </div>

            {{-- Action Control Panel --}}
            @if($session->status === 'running')
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Quick Event Recording</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                        <button onclick="document.getElementById('modalProduction').showModal()" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-xl shadow transition text-xs flex flex-col items-center gap-1">
                            <span class="text-sm font-black tracking-widest uppercase">Output</span>
                        </button>

                        <button onclick="document.getElementById('modalReject').showModal()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-xl shadow transition text-xs flex flex-col items-center gap-1">
                            <span class="text-sm font-black tracking-widest uppercase">Defect</span>
                        </button>

                        <button onclick="document.getElementById('modalRework').showModal()" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-3 px-4 rounded-xl shadow transition text-xs flex flex-col items-center gap-1">
                            <span class="text-sm font-black tracking-widest uppercase">Rework</span>
                        </button>

                        <button onclick="document.getElementById('modalDowntime').showModal()" class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 px-4 rounded-xl shadow transition text-xs flex flex-col items-center gap-1">
                            <span class="text-sm font-black tracking-widest uppercase">Downtime</span>
                        </button>

                        <button onclick="document.getElementById('modalInput').showModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow transition text-xs flex flex-col items-center gap-1">
                            <span class="text-sm font-black tracking-widest uppercase">Input WIP</span>
                        </button>

                        <button onclick="document.getElementById('modalManpower').showModal()" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-xl shadow transition text-xs flex flex-col items-center gap-1 col-span-2 sm:col-span-1">
                            <span class="text-sm font-black tracking-widest uppercase">+ Team</span>
                        </button>
                    </div>
                </div>
            @endif

            {{-- Event Log Tabs & Table --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="border-b border-gray-200 bg-gray-50 px-5 py-3 flex gap-4">
                    <button @click="tab = 'production'" :class="{ 'text-blue-600 border-blue-600 font-bold': tab === 'production', 'text-gray-500': tab !== 'production' }" class="py-2 text-sm border-b-2 font-semibold">
                        Output Logs (<span x-text="productionEntries.length"></span>)
                    </button>
                    <button @click="tab = 'defects'" :class="{ 'text-blue-600 border-blue-600 font-bold': tab === 'defects', 'text-gray-500': tab !== 'defects' }" class="py-2 text-sm border-b-2 font-semibold">
                        Defect Details (<span x-text="rejectEntries.length"></span>)
                    </button>
                    <button @click="tab = 'downtime'" :class="{ 'text-blue-600 border-blue-600 font-bold': tab === 'downtime', 'text-gray-500': tab !== 'downtime' }" class="py-2 text-sm border-b-2 font-semibold">
                        Downtime (<span x-text="downtimeEntries.length"></span>)
                    </button>
                    <button @click="tab = 'rework'" :class="{ 'text-blue-600 border-blue-600 font-bold': tab === 'rework', 'text-gray-500': tab !== 'rework' }" class="py-2 text-sm border-b-2 font-semibold">
                        Rework (<span x-text="reworkEntries.length"></span>)
                    </button>
                    <button @click="tab = 'input'" :class="{ 'text-blue-600 border-blue-600 font-bold': tab === 'input', 'text-gray-500': tab !== 'input' }" class="py-2 text-sm border-b-2 font-semibold">
                        Input WIP (<span x-text="inputEntries.length"></span>)
                    </button>
                    <button @click="tab = 'manpower'" :class="{ 'text-blue-600 border-blue-600 font-bold': tab === 'manpower', 'text-gray-500': tab !== 'manpower' }" class="py-2 text-sm border-b-2 font-semibold">
                        Line Team ({{ $session->manpowerEntries->count() }})
                    </button>
                </div>

                {{-- Output Logs --}}
                <div x-show="tab === 'production'" class="p-5">
                    <table class="min-w-full divide-y divide-gray-200 ">
                        <thead class="bg-gray-50 ">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Timestamp</th>
                                <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Good Qty</th>
                                <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Reject Qty</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 ">
                            <template x-for="entry in productionEntries" :key="entry.id">
                                <tr>
                                    <td class="px-4 py-2.5 text-sm text-gray-500" x-text="formatTime(entry.recorded_at)"></td>
                                    <td class="px-4 py-2.5 text-sm text-right font-bold text-green-600" x-text="'+' + formatNum(entry.good_qty)"></td>
                                    <td class="px-4 py-2.5 text-sm text-right font-bold text-red-600" x-text="'+' + formatNum(entry.reject_qty)"></td>
                                    <td class="px-4 py-2.5 text-sm text-gray-600" x-text="entry.remarks || '-'"></td>
                                </tr>
                            </template>
                            <tr x-show="productionEntries.length === 0">
                                <td colspan="4" class="px-4 py-6 text-center text-gray-400 text-sm">No production entries logged yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Defects --}}
                <div x-show="tab === 'defects'" class="p-5" style="display: none;">
                    <table class="min-w-full divide-y divide-gray-200 ">
                        <thead class="bg-gray-50 ">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Defect Type</th>
                                <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Quantity</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Cause</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 ">
                            <template x-for="entry in rejectEntries" :key="entry.id">
                                <tr>
                                    <td class="px-4 py-2.5 text-sm font-bold text-red-600" x-text="entry.defect_type"></td>
                                    <td class="px-4 py-2.5 text-sm text-right font-bold" x-text="formatNum(entry.quantity) + ' Pcs'"></td>
                                    <td class="px-4 py-2.5 text-sm text-gray-600" x-text="entry.cause || '-'"></td>
                                    <td class="px-4 py-2.5 text-sm text-gray-600" x-text="entry.remarks || '-'"></td>
                                </tr>
                            </template>
                            <tr x-show="rejectEntries.length === 0">
                                <td colspan="4" class="px-4 py-6 text-center text-gray-400 text-sm">No defects logged yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Downtime --}}
                <div x-show="tab === 'downtime'" class="p-5" style="display: none;">
                    <table class="min-w-full divide-y divide-gray-200 ">
                        <thead class="bg-gray-50 ">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Reason</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Start Time</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Resume Time</th>
                                <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Duration</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 ">
                            <template x-for="entry in downtimeEntries" :key="entry.id">
                                <tr>
                                    <td class="px-4 py-2.5 text-sm font-bold text-orange-600" x-text="entry.reason"></td>
                                    <td class="px-4 py-2.5 text-sm text-gray-600" x-text="formatHM(entry.start_time)"></td>
                                    <td class="px-4 py-2.5 text-sm text-gray-600" x-text="formatHM(entry.resume_time)"></td>
                                    <td class="px-4 py-2.5 text-sm text-right font-bold text-gray-900" x-text="entry.duration_minutes + ' Mins'"></td>
                                    <td class="px-4 py-2.5 text-sm text-gray-600" x-text="entry.remarks || '-'"></td>
                                </tr>
                            </template>
                            <tr x-show="downtimeEntries.length === 0">
                                <td colspan="5" class="px-4 py-6 text-center text-gray-400 text-sm">No downtime events logged yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Rework --}}
                <div x-show="tab === 'rework'" class="p-5" style="display: none;">
                    <table class="min-w-full divide-y divide-gray-200 ">
                        <thead class="bg-gray-50 ">
                            <tr>
                                <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Rework Input</th>
                                <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Recovered Good</th>
                                <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Scrapped Qty</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 ">
                            <template x-for="entry in reworkEntries" :key="entry.id">
                                <tr>
                                    <td class="px-4 py-2.5 text-sm text-right font-bold text-gray-800" x-text="formatNum(entry.input_qty) + ' Pcs'"></td>
                                    <td class="px-4 py-2.5 text-sm text-right font-bold text-green-600" x-text="formatNum(entry.recovered_qty) + ' Pcs'"></td>
                                    <td class="px-4 py-2.5 text-sm text-right font-bold text-red-600" x-text="formatNum(entry.scrapped_qty) + ' Pcs'"></td>
                                    <td class="px-4 py-2.5 text-sm text-gray-600" x-text="entry.remarks || '-'"></td>
                                </tr>
                            </template>
                            <tr x-show="reworkEntries.length === 0">
                                <td colspan="4" class="px-4 py-6 text-center text-gray-400 text-sm">No rework entries logged yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Input WIP --}}
                <div x-show="tab === 'input'" class="p-5" style="display: none;">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Timestamp</th>
                                <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Quantity</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Pallet Number</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Source</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <template x-for="entry in inputEntries" :key="entry.id">
                                <tr>
                                    <td class="px-4 py-2.5 text-sm text-gray-500 font-mono" x-text="formatTime(entry.created_at || entry.recorded_at)"></td>
                                    <td class="px-4 py-2.5 text-sm font-bold text-blue-600 text-right" x-text="'+' + formatNum(entry.quantity) + ' Pcs'"></td>
                                    <td class="px-4 py-2.5 text-sm text-gray-700 font-mono" x-text="entry.pallet_number || '-'"></td>
                                    <td class="px-4 py-2.5 text-sm text-gray-500 uppercase" x-text="entry.source || 'WIP'"></td>
                                </tr>
                            </template>
                            <tr x-show="inputEntries.length === 0">
                                <td colspan="4" class="px-4 py-6 text-center text-gray-400 text-sm">No input WIP entries recorded.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Line Team / Manpower Logs --}}
                <div x-show="tab === 'manpower'" class="p-5" style="display: none;">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Role / Position</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Operator Name</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Employee NIK</th>
                                <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($session->manpowerEntries as $mp)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-bold text-purple-700 uppercase tracking-wide">{{ $mp->role }}</td>
                                    <td class="px-4 py-3 text-sm font-bold text-gray-800">{{ $mp->operator_name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 font-mono">{{ $mp->employee_no ?: '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        @if($session->status === 'running')
                                            <form action="{{ route('app.sp-sessions.remove-manpower', [$session->id, $mp->id]) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" onclick="return confirm('Remove worker from line team?')" class="text-xs text-red-600 hover:text-red-800 font-bold uppercase">
                                                    Remove
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-400">No extra line team members added yet. Click "+ Team" to assign operators.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    {{-- MODALS --}}

    {{-- Modal 1: Log Production --}}
    <dialog id="modalProduction" class="rounded-xl p-0 shadow-2xl border-0 w-full max-w-lg backdrop:bg-gray-900/50 bg-transparent">
        <div class="bg-white rounded-xl overflow-hidden shadow-2xl">
            <div class="bg-green-600 px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-black text-white">Log Output</h3>
                <button type="button" onclick="document.getElementById('modalProduction').close()" class="text-green-200 hover:text-white text-xl font-bold">&times;</button>
            </div>
            <form action="{{ route('app.sp-sessions.add-production', $session->id) }}" method="POST" @submit.prevent="submitForm($event, 'production')" x-data="{ good_qty: 0, reject_qty: 0 }" class="p-6">
                @csrf
                <div class="space-y-6">
                    <!-- Good Qty Stepper -->
                    <div>
                        <label class="block text-sm font-bold text-gray-500 uppercase mb-2">Good Qty (Pcs) *</label>
                        <div class="flex items-stretch h-20 rounded-2xl border-2 border-gray-300 overflow-hidden bg-white shadow-sm">
                            <button type="button" @click="good_qty = Math.max(0, good_qty - 1)" class="w-24 flex items-center justify-center bg-gray-100 hover:bg-gray-200 active:bg-gray-300 transition text-gray-600 text-4xl font-black border-r border-gray-300">
                                -
                            </button>
                            <input type="number" name="good_qty" value="0" x-model.number="good_qty" @focus="$event.target.select()" @blur="if (!good_qty && good_qty !== 0) good_qty = 0" required class="flex-1 text-center text-5xl font-black text-green-600 border-0 focus:ring-0 w-full bg-transparent p-0">
                            <button type="button" @click="good_qty += 1" class="w-24 flex items-center justify-center bg-green-100 hover:bg-green-200 active:bg-green-300 transition text-green-700 text-4xl font-black border-l border-gray-300">
                                +
                            </button>
                        </div>
                        <div class="grid grid-cols-4 gap-2 mt-4">
                            <button type="button" @click="good_qty += 5" class="py-4 bg-green-50 active:bg-green-100 text-green-700 font-black rounded-xl text-xl shadow-sm border border-green-200">+5</button>
                            <button type="button" @click="good_qty += 10" class="py-4 bg-green-50 active:bg-green-100 text-green-700 font-black rounded-xl text-xl shadow-sm border border-green-200">+10</button>
                            <button type="button" @click="good_qty += 50" class="py-4 bg-green-50 active:bg-green-100 text-green-700 font-black rounded-xl text-xl shadow-sm border border-green-200">+50</button>
                            <button type="button" @click="good_qty += 100" class="py-4 bg-green-50 active:bg-green-100 text-green-700 font-black rounded-xl text-xl shadow-sm border border-green-200">+100</button>
                        </div>
                    </div>
                    
                    <hr class="border-gray-100">

                    <!-- Reject Qty Stepper -->
                    <div>
                        <label class="block text-sm font-bold text-gray-500 uppercase mb-2">Reject Qty (Pcs)</label>
                        <div class="flex items-stretch h-20 rounded-2xl border-2 border-gray-300 overflow-hidden bg-white shadow-sm">
                            <button type="button" @click="reject_qty = Math.max(0, reject_qty - 1)" class="w-24 flex items-center justify-center bg-gray-100 hover:bg-gray-200 active:bg-gray-300 transition text-gray-600 text-4xl font-black border-r border-gray-300">
                                -
                            </button>
                            <input type="number" name="reject_qty" value="0" x-model.number="reject_qty" @focus="$event.target.select()" @blur="if (!reject_qty && reject_qty !== 0) reject_qty = 0" class="flex-1 text-center text-5xl font-black text-red-600 border-0 focus:ring-0 w-full bg-transparent p-0">
                            <button type="button" @click="reject_qty += 1" class="w-24 flex items-center justify-center bg-red-100 hover:bg-red-200 active:bg-red-300 transition text-red-700 text-4xl font-black border-l border-gray-300">
                                +
                            </button>
                        </div>
                    </div>

                    <div>
                        <input type="text" name="remarks" placeholder="Optional notes / remarks..." class="w-full border-gray-300 rounded-lg text-lg p-3 bg-gray-50 focus:bg-white">
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t border-gray-100">
                    <button type="submit" class="w-full bg-green-600 active:bg-green-700 text-white py-4 rounded-xl text-xl font-black shadow-lg">SAVE LOG</button>
                </div>
            </form>
        </div>
    </dialog>

    {{-- Modal 2: Log Defect --}}
    <dialog id="modalReject" class="rounded-xl p-0 shadow-2xl border-0 w-full max-w-lg backdrop:bg-gray-900/50 bg-transparent">
        <div class="bg-white rounded-xl overflow-hidden shadow-2xl">
            <div class="bg-red-600 px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-black text-white">Log Defect Type</h3>
                <button type="button" onclick="document.getElementById('modalReject').close()" class="text-red-200 hover:text-white text-xl font-bold">&times;</button>
            </div>
            <form action="{{ route('app.sp-sessions.add-reject', $session->id) }}" method="POST" @submit.prevent="submitForm($event, 'reject')" x-data="{ defect: '', quantity: 1 }" class="p-6">
                @csrf
                <input type="hidden" name="defect_type" :value="defect">
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-500 uppercase mb-2">Defect Type *</label>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach($defectTypes as $d)
                                <button type="button" @click="defect = '{{ $d }}'" :class="defect === '{{ $d }}' ? 'bg-red-600 text-white ring-4 ring-red-200' : 'bg-gray-100 text-gray-700'" class="py-4 px-2 rounded-xl font-bold text-center transition shadow-sm text-sm">
                                    {{ $d }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-500 uppercase mb-2">Quantity (Pcs) *</label>
                        <div class="flex items-stretch h-20 rounded-2xl border-2 border-gray-300 overflow-hidden bg-white shadow-sm">
                            <button type="button" @click="quantity = Math.max(1, quantity - 1)" class="w-24 flex items-center justify-center bg-gray-100 hover:bg-gray-200 active:bg-gray-300 transition text-gray-600 text-4xl font-black border-r border-gray-300">
                                -
                            </button>
                            <input type="number" name="quantity" value="1" x-model.number="quantity" @focus="$event.target.select()" @blur="if (!quantity && quantity !== 0) quantity = 1" min="1" required class="flex-1 text-center text-5xl font-black text-red-600 border-0 focus:ring-0 w-full bg-transparent p-0">
                            <button type="button" @click="quantity += 1" class="w-24 flex items-center justify-center bg-red-100 hover:bg-red-200 active:bg-red-300 transition text-red-700 text-4xl font-black border-l border-gray-300">
                                +
                            </button>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-4">
                            <button type="button" @click="quantity += 5" class="py-4 bg-red-50 active:bg-red-100 text-red-700 font-black rounded-xl text-xl shadow-sm border border-red-200">+5</button>
                            <button type="button" @click="quantity += 10" class="py-4 bg-red-50 active:bg-red-100 text-red-700 font-black rounded-xl text-xl shadow-sm border border-red-200">+10</button>
                        </div>
                    </div>

                    <div>
                        <input type="text" name="cause" placeholder="Probable Cause (Optional)..." class="w-full border-gray-300 rounded-lg text-lg p-3 bg-gray-50 focus:bg-white">
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t border-gray-100">
                    <button type="submit" :disabled="!defect" :class="!defect ? 'opacity-50 cursor-not-allowed' : ''" class="w-full bg-red-600 active:bg-red-700 text-white py-4 rounded-xl text-xl font-black shadow-lg transition">SAVE DEFECT</button>
                </div>
            </form>
        </div>
    </dialog>

    {{-- Modal 3: Log Downtime --}}
    <dialog id="modalDowntime" class="rounded-xl p-0 shadow-2xl border-0 w-full max-w-lg backdrop:bg-gray-900/50 bg-transparent">
        <div class="bg-white rounded-xl overflow-hidden shadow-2xl">
            <div class="bg-orange-500 px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-black text-white">Log Downtime</h3>
                <button type="button" onclick="document.getElementById('modalDowntime').close()" class="text-orange-200 hover:text-white text-xl font-bold">&times;</button>
            </div>
            <form action="{{ route('app.sp-sessions.add-downtime', $session->id) }}" method="POST" @submit.prevent="submitForm($event, 'downtime')" x-data="{ reason: '' }" class="p-6">
                @csrf
                <input type="hidden" name="reason" :value="reason">
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-500 uppercase mb-2">Reason *</label>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach($downtimeReasons as $r)
                                <button type="button" @click="reason = '{{ $r }}'" :class="reason === '{{ $r }}' ? 'bg-orange-500 text-white ring-4 ring-orange-200' : 'bg-gray-100 text-gray-700'" class="py-3 px-2 rounded-xl font-bold text-center transition shadow-sm text-sm">
                                    {{ $r }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-500 uppercase mb-2">Start Time *</label>
                            <input type="time" name="start_time" required class="w-full h-14 text-xl border-gray-300 rounded-xl text-center font-bold">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-500 uppercase mb-2">Resume Time *</label>
                            <input type="time" name="resume_time" required class="w-full h-14 text-xl border-gray-300 rounded-xl text-center font-bold">
                        </div>
                    </div>

                    <div>
                        <input type="text" name="remarks" placeholder="Optional remarks..." class="w-full border-gray-300 rounded-lg text-lg p-3 bg-gray-50 focus:bg-white">
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t border-gray-100">
                    <button type="submit" :disabled="!reason" :class="!reason ? 'opacity-50 cursor-not-allowed' : ''" class="w-full bg-orange-500 active:bg-orange-600 text-white py-4 rounded-xl text-xl font-black shadow-lg transition">SAVE DOWNTIME</button>
                </div>
            </form>
        </div>
    </dialog>

    {{-- Modal 4: Log Rework --}}
    <dialog id="modalRework" class="rounded-xl p-0 shadow-2xl border-0 w-full max-w-lg backdrop:bg-gray-900/50 bg-transparent">
        <div class="bg-white rounded-xl overflow-hidden shadow-2xl">
            <div class="bg-yellow-500 px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-black text-white">Log Rework</h3>
                <button type="button" onclick="document.getElementById('modalRework').close()" class="text-yellow-200 hover:text-white text-xl font-bold">&times;</button>
            </div>
            <form action="{{ route('app.sp-sessions.add-rework', $session->id) }}" method="POST" @submit.prevent="submitForm($event, 'rework')" x-data="{ input_qty: 0, recovered_qty: 0, scrapped_qty: 0 }" class="p-6">
                @csrf
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-500 uppercase mb-2">Input for Rework (Pcs) *</label>
                        <div class="flex items-stretch h-20 rounded-2xl border-2 border-gray-300 overflow-hidden bg-white shadow-sm">
                            <button type="button" @click="input_qty = Math.max(0, input_qty - 1)" class="w-24 flex items-center justify-center bg-gray-100 hover:bg-gray-200 active:bg-gray-300 transition text-gray-600 text-4xl font-black border-r border-gray-300">
                                -
                            </button>
                            <input type="number" name="input_qty" value="0" x-model.number="input_qty" @focus="$event.target.select()" @blur="if (!input_qty && input_qty !== 0) input_qty = 0" required class="flex-1 text-center text-5xl font-black text-gray-800 border-0 focus:ring-0 w-full bg-transparent p-0">
                            <button type="button" @click="input_qty += 1" class="w-24 flex items-center justify-center bg-gray-200 hover:bg-gray-300 active:bg-gray-400 transition text-gray-700 text-4xl font-black border-l border-gray-300">
                                +
                            </button>
                        </div>
                    </div>
                    <hr class="border-gray-100">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-500 uppercase mb-2">Recovered (Pcs)</label>
                            <div class="flex items-stretch h-20 rounded-2xl border-2 border-gray-300 overflow-hidden bg-white shadow-sm">
                                <button type="button" @click="recovered_qty = Math.max(0, recovered_qty - 1)" class="w-24 flex items-center justify-center bg-gray-100 hover:bg-gray-200 active:bg-gray-300 transition text-gray-600 text-4xl font-black border-r border-gray-300">
                                    -
                                </button>
                                <input type="number" name="recovered_qty" value="0" x-model.number="recovered_qty" @focus="$event.target.select()" @blur="if (!recovered_qty && recovered_qty !== 0) recovered_qty = 0" class="flex-1 text-center text-5xl font-black text-green-600 border-0 focus:ring-0 w-full bg-transparent p-0">
                                <button type="button" @click="recovered_qty += 1" class="w-24 flex items-center justify-center bg-green-100 hover:bg-green-200 active:bg-green-300 transition text-green-700 text-4xl font-black border-l border-gray-300">
                                    +
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-500 uppercase mb-2">Scrapped (Pcs)</label>
                            <div class="flex items-stretch h-20 rounded-2xl border-2 border-gray-300 overflow-hidden bg-white shadow-sm">
                                <button type="button" @click="scrapped_qty = Math.max(0, scrapped_qty - 1)" class="w-24 flex items-center justify-center bg-gray-100 hover:bg-gray-200 active:bg-gray-300 transition text-gray-600 text-4xl font-black border-r border-gray-300">
                                    -
                                </button>
                                <input type="number" name="scrapped_qty" value="0" x-model.number="scrapped_qty" @focus="$event.target.select()" @blur="if (!scrapped_qty && scrapped_qty !== 0) scrapped_qty = 0" class="flex-1 text-center text-5xl font-black text-red-600 border-0 focus:ring-0 w-full bg-transparent p-0">
                                <button type="button" @click="scrapped_qty += 1" class="w-24 flex items-center justify-center bg-red-100 hover:bg-red-200 active:bg-red-300 transition text-red-700 text-4xl font-black border-l border-gray-300">
                                    +
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t border-gray-100">
                    <button type="submit" class="w-full bg-yellow-500 active:bg-yellow-600 text-white py-4 rounded-xl text-xl font-black shadow-lg">SAVE REWORK</button>
                </div>
            </form>
        </div>
    </dialog>

    {{-- Modal 5: Log Input WIP --}}
    <dialog id="modalInput" class="rounded-xl p-0 shadow-2xl border-0 w-full max-w-lg backdrop:bg-gray-900/50 bg-transparent">
        <div class="bg-white rounded-xl overflow-hidden shadow-2xl">
            <div class="bg-blue-600 px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-black text-white">Receive Input WIP</h3>
                <button type="button" onclick="document.getElementById('modalInput').close()" class="text-blue-200 hover:text-white text-xl font-bold">&times;</button>
            </div>
            <form action="{{ route('app.sp-sessions.add-input', $session->id) }}" method="POST" @submit.prevent="submitForm($event, 'input')" x-data="{ qty: 0 }" class="p-6">
                @csrf
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-500 uppercase mb-2">Quantity (Pcs) *</label>
                        <div class="flex items-stretch h-20 rounded-2xl border-2 border-gray-300 overflow-hidden bg-white shadow-sm">
                            <button type="button" @click="qty = Math.max(0, qty - 1)" class="w-24 flex items-center justify-center bg-gray-100 hover:bg-gray-200 active:bg-gray-300 transition text-gray-600 text-4xl font-black border-r border-gray-300">
                                -
                            </button>
                            <input type="number" name="quantity" value="0" x-model.number="qty" @focus="$event.target.select()" @blur="if (!qty && qty !== 0) qty = 0" required class="flex-1 text-center text-5xl font-black text-blue-600 border-0 focus:ring-0 w-full bg-transparent p-0">
                            <button type="button" @click="qty += 1" class="w-24 flex items-center justify-center bg-blue-100 hover:bg-blue-200 active:bg-blue-300 transition text-blue-700 text-4xl font-black border-l border-gray-300">
                                +
                            </button>
                        </div>
                        <div class="grid grid-cols-4 gap-2 mt-4">
                            <button type="button" @click="qty += 10" class="py-4 bg-blue-50 active:bg-blue-100 text-blue-700 font-black rounded-xl text-xl shadow-sm border border-blue-200">+10</button>
                            <button type="button" @click="qty += 50" class="py-4 bg-blue-50 active:bg-blue-100 text-blue-700 font-black rounded-xl text-xl shadow-sm border border-blue-200">+50</button>
                            <button type="button" @click="qty += 100" class="py-4 bg-blue-50 active:bg-blue-100 text-blue-700 font-black rounded-xl text-xl shadow-sm border border-blue-200">+100</button>
                            <button type="button" @click="qty += 500" class="py-4 bg-blue-50 active:bg-blue-100 text-blue-700 font-black rounded-xl text-xl shadow-sm border border-blue-200">+500</button>
                        </div>
                    </div>
                    <div>
                        <input type="text" name="pallet_number" placeholder="Pallet / Box No. (Optional)" class="w-full border-gray-300 rounded-lg text-lg p-3 bg-gray-50 focus:bg-white font-mono uppercase">
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t border-gray-100">
                    <button type="submit" class="w-full bg-blue-600 active:bg-blue-700 text-white py-4 rounded-xl text-xl font-black shadow-lg">SAVE INPUT</button>
                </div>
            </form>
        </div>
    </dialog>

    {{-- Modal 6: Manage Line Team / Manpower --}}
    <dialog id="modalManpower" class="rounded-xl p-0 shadow-2xl border-0 w-full max-w-lg backdrop:bg-gray-900/50 bg-transparent">
        <div class="bg-white rounded-xl overflow-hidden shadow-2xl">
            <div class="bg-purple-600 px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-black text-white">+ Add Line Team Member</h3>
                <button type="button" onclick="document.getElementById('modalManpower').close()" class="text-purple-200 hover:text-white text-xl font-bold">&times;</button>
            </div>
            <form action="{{ route('app.sp-sessions.add-manpower', $session->id) }}" method="POST" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Worker Role / Position *</label>
                        <select name="role" required class="w-full border-gray-300 rounded-xl text-lg p-3 bg-gray-50 focus:bg-white font-bold text-purple-700">
                            <option value="Main Operator">Main Operator</option>
                            <option value="Quality Inspector">Quality Inspector</option>
                            <option value="Assembly Operator">Assembly Operator</option>
                            <option value="Buffing Operator">Buffing Operator</option>
                            <option value="Packing Operator">Packing Operator</option>
                            <option value="Helper">Helper / Material Feeder</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Operator Name *</label>
                        <input type="text" name="operator_name" placeholder="Full Employee Name" required class="w-full border-gray-300 rounded-xl text-lg p-3 bg-gray-50 focus:bg-white font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Employee ID / NIK (Optional)</label>
                        <input type="text" name="employee_no" placeholder="e.g. EMP-1045" class="w-full border-gray-300 rounded-xl text-lg p-3 bg-gray-50 focus:bg-white font-mono">
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t border-gray-100 flex gap-3">
                    <button type="button" onclick="document.getElementById('modalManpower').close()" class="w-1/3 bg-gray-200 active:bg-gray-300 text-gray-800 py-4 rounded-xl text-lg font-bold">Cancel</button>
                    <button type="submit" class="w-2/3 bg-purple-600 active:bg-purple-700 text-white py-4 rounded-xl text-xl font-black shadow-lg">ADD MEMBER</button>
                </div>
            </form>
        </div>
    </dialog>

    {{-- Modal 7: Finish Production --}}
    <dialog id="modalFinish" class="rounded-xl p-0 shadow-2xl border-0 w-full max-w-lg backdrop:bg-gray-900/50 bg-transparent">
        <div class="bg-white rounded-xl overflow-hidden shadow-2xl">
            <div class="bg-red-600 px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-black text-white">Complete Session</h3>
                <button type="button" onclick="document.getElementById('modalFinish').close()" class="text-red-200 hover:text-white text-xl font-bold">&times;</button>
            </div>
            <form action="{{ route('app.sp-sessions.finish', $session->id) }}" method="POST" class="p-6">
                @csrf
                <p class="text-sm text-gray-500 mb-6 font-semibold">Are you sure you want to finish this session? Final totals will be computed and saved to the daily production report.</p>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-500 uppercase mb-2">Final Remarks / Handover</label>
                        <textarea name="remarks" rows="3" placeholder="Optional handover notes..." class="w-full border-gray-300 rounded-xl text-lg p-3 bg-gray-50 focus:bg-white"></textarea>
                    </div>
                </div>
                <div class="flex gap-3 mt-6 pt-4 border-t border-gray-100">
                    <button type="button" onclick="document.getElementById('modalFinish').close()" class="w-1/3 bg-gray-200 active:bg-gray-300 text-gray-800 py-4 rounded-xl text-lg font-bold">Cancel</button>
                    <button type="submit" class="w-2/3 bg-red-600 active:bg-red-700 text-white py-4 rounded-xl text-xl font-black shadow-lg">FINISH NOW</button>
                </div>
            </form>
        </div>
    </dialog>
    </div> <!-- End of x-data -->

</x-operator-layout>
