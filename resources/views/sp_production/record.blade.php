<x-operator-layout>
    <x-slot name="headerContainerClass">px-3 py-1.5</x-slot>
    <x-slot name="mainClass">p-0 overflow-hidden</x-slot>

    <x-slot name="header">
        <script>
            function headerClock() {
                return {
                    liveTime: '',
                    elapsedTime: '',
                    init() {
                        this.update();
                        setInterval(() => this.update(), 1000);
                    },
                    update() {
                        const now = new Date();
                        this.liveTime = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
                        @if($session->started_at)
                            const started = new Date('{{ $session->started_at->toIso8601String() }}');
                            const diffSec = Math.max(0, Math.floor((now - started) / 1000));
                            const hrs = Math.floor(diffSec / 3600);
                            const mins = Math.floor((diffSec % 3600) / 60);
                            this.elapsedTime = hrs + 'h ' + mins + 'm';
                        @else
                            this.elapsedTime = '0h 0m';
                        @endif
                    }
                }
            }
        </script>
        <div class="flex items-center justify-between gap-2 text-xs" x-data="headerClock()" x-cloak>
            {{-- Left: WO & Part info --}}
            <div class="flex items-center gap-2 min-w-0">
                <a href="{{ route('sp-work-orders.show', $session->work_order_id) }}" class="font-black text-blue-700 hover:text-blue-900 whitespace-nowrap text-sm">
                    {{ $session->workOrder->wo_number }}
                </a>
                <span class="text-gray-300 font-light">|</span>
                <span class="font-black text-gray-900 truncate max-w-[140px] sm:max-w-[200px]" title="{{ $session->workOrder->part_number }}">
                    {{ $session->workOrder->part_number }}
                </span>
                <span class="text-gray-500 truncate hidden md:inline max-w-[180px]" title="{{ $session->workOrder->part_name }}">
                    {{ $session->workOrder->part_name }}
                </span>
            </div>

            {{-- Center: Status & Session Elapsed --}}
            <div class="flex items-center gap-2 whitespace-nowrap">
                @if($session->status === 'running')
                    <span class="px-2 py-0.5 text-[10px] font-black rounded-full bg-green-500 text-white uppercase animate-pulse">
                        RUNNING
                    </span>
                    <span class="text-[10px] font-bold text-gray-500" x-text="elapsedTime"></span>
                @elseif($session->approved_by)
                    <span class="px-2 py-0.5 text-[10px] font-black rounded-full bg-blue-500 text-white uppercase">
                        APPROVED
                    </span>
                @else
                    <span class="px-2 py-0.5 text-[10px] font-black rounded-full bg-gray-500 text-white uppercase">
                        COMPLETED
                    </span>
                @endif

                @if($session->is_qc_bypassed)
                    <span class="px-2 py-0.5 text-[10px] font-black rounded-full bg-amber-500 text-white uppercase" title="QC Bypassed: {{ $session->qc_bypass_reason }}">
                        BYPASSED
                    </span>
                @endif
            </div>

            {{-- Right: Line/Shift, Live Clock, Gateway & Finish --}}
            @php
                $lineSlug = array_search($session->unit_line, config('mes.sp_lines', [])) ?: 'line-a';
            @endphp
            <div class="flex items-center gap-2 whitespace-nowrap">
                <span class="font-bold text-gray-700 text-[11px]">
                    {{ $session->unit_line }} S{{ $session->shift }}
                </span>
                <span class="font-mono font-bold text-gray-900 bg-gray-100 px-1.5 py-0.5 rounded text-[11px]" x-text="liveTime"></span>
                
                <a href="{{ route('sp-sessions.line-gateway', ['lineSlug' => $lineSlug]) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-1 px-2.5 rounded-lg text-[10px] transition uppercase">
                    Gateway
                </a>

                @if($session->status === 'running')
                    <button onclick="document.getElementById('modalFinish').showModal()" class="bg-red-600 hover:bg-red-700 text-white font-black py-1 px-3 rounded-lg text-[10px] uppercase tracking-wider transition">
                        Finish
                    </button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="flex flex-col h-[calc(100vh-42px)] bg-gray-100" x-data="productionSession()" x-cloak>
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
                    targetQty: {{ $session->workOrder->target_qty ?? 1 }},
                    progressPct: 0,
                    liveTime: '',
                    elapsedTime: '',
                    batchSize: 1,
                    quickFlash: false,
                    highlightedEntryId: null,
                    productionEntries: @json($session->productionEntries),
                    rejectEntries: @json($session->rejectEntries),
                    downtimeEntries: @json($session->downtimeEntries),
                    reworkEntries: @json($session->reworkEntries),
                    inputEntries: @json($session->inputEntries),

                    init() {
                        // Load persistent quick-tap batch size
                        const savedBatch = localStorage.getItem('sp_quick_batch_size');
                        if (savedBatch && !isNaN(savedBatch)) {
                            this.batchSize = parseInt(savedBatch);
                        }

                        this.updateClock();
                        setInterval(() => this.updateClock(), 1000);
                        this.recalcProgress();
                    },

                    setBatchSize(size) {
                        this.batchSize = size;
                        localStorage.setItem('sp_quick_batch_size', size);
                    },

                    updateClock() {
                        const now = new Date();
                        this.liveTime = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });

                        @if($session->started_at)
                            const started = new Date('{{ $session->started_at->toIso8601String() }}');
                            const diffSec = Math.max(0, Math.floor((now - started) / 1000));
                            const hrs = Math.floor(diffSec / 3600);
                            const mins = Math.floor((diffSec % 3600) / 60);
                            this.elapsedTime = hrs + 'h ' + mins + 'm';
                        @else
                            this.elapsedTime = '0h 0m';
                        @endif
                    },

                    recalcProgress() {
                        if (this.targetQty > 0) {
                            this.progressPct = Math.min(100, Math.round((this.totals.good / this.targetQty) * 100));
                        } else {
                            this.progressPct = 0;
                        }
                    },

                    triggerHighlight(entryId) {
                        if (entryId) {
                            this.highlightedEntryId = entryId;
                            setTimeout(() => {
                                if (this.highlightedEntryId === entryId) {
                                    this.highlightedEntryId = null;
                                }
                            }, 1500);
                        }
                    },

                    async quickAddGood() {
                        try {
                            const formData = new FormData();
                            formData.append('good_qty', this.batchSize);
                            formData.append('reject_qty', 0);
                            formData.append('_token', '{{ csrf_token() }}');

                            const response = await fetch('{{ route("app.sp-sessions.add-production", $session->id) }}', {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                },
                                body: formData
                            });

                            if (!response.ok) {
                                const err = await response.json().catch(() => ({}));
                                throw new Error(err.error || 'Quick log failed');
                            }

                            const data = await response.json();
                            if (data.success) {
                                this.totals = { ...this.totals, ...data.totals };
                                this.productionEntries.unshift(data.entry);
                                this.tab = 'production';
                                this.triggerHighlight(data.entry?.id);
                                this.recalcProgress();
                                this.quickFlash = true;
                                setTimeout(() => this.quickFlash = false, 600);
                            }
                        } catch (error) {
                            alert(error.message);
                        }
                    },

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

                                const tabMap = {
                                    'production': 'production',
                                    'reject': 'defects',
                                    'downtime': 'downtime',
                                    'rework': 'rework',
                                    'input': 'input',
                                    'manpower': 'manpower'
                                };

                                if (type === 'production') this.productionEntries.unshift(data.entry);
                                else if (type === 'reject') this.rejectEntries.unshift(data.entry);
                                else if (type === 'downtime') this.downtimeEntries.unshift(data.entry);
                                else if (type === 'rework') this.reworkEntries.unshift(data.entry);
                                else if (type === 'input') this.inputEntries.unshift(data.entry);

                                if (tabMap[type]) {
                                    this.tab = tabMap[type];
                                }

                                this.triggerHighlight(data.entry?.id);
                                this.recalcProgress();
                                form.reset();

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

                    parseUtc(dateString) {
                        if (!dateString) return null;
                        if (typeof dateString === 'string') {
                            if (!dateString.endsWith('Z') && !dateString.includes('+')) {
                                return new Date(dateString.replace(' ', 'T') + 'Z');
                            }
                        }
                        return new Date(dateString);
                    },
                    formatTime(dateString) {
                        const d = this.parseUtc(dateString);
                        if (!d || isNaN(d)) return '-';
                        return d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit', hour12: false});
                    },
                    formatHM(dateString) {
                        const d = this.parseUtc(dateString);
                        if (!d || isNaN(d)) return '-';
                        return d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', hour12: false});
                    },
                    formatNum(num) {
                        return new Intl.NumberFormat().format(num || 0);
                    }
                }
            }
        </script>

        {{-- Flash Notification --}}
        @if(session('success'))
            <div class="bg-green-600 text-white px-4 py-1.5 text-xs font-bold text-center">
                {{ session('success') }}
            </div>
        @endif

        {{-- KPI Strip + Target Progress Bar (Compact ~52px) --}}
        <div class="bg-white border-b border-gray-200 px-3 py-2 shadow-sm flex-shrink-0">
            <div class="flex items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-4 flex-wrap">
                    <div class="flex items-baseline gap-1">
                        <span class="text-[10px] font-black text-gray-400 uppercase">Input</span>
                        <span class="text-base font-black text-gray-900" x-text="formatNum(totals.input)"></span>
                    </div>

                    <div class="flex items-baseline gap-1">
                        <span class="text-[10px] font-black text-green-600 uppercase">Good</span>
                        <span class="text-base font-black text-green-600" x-text="formatNum(totals.good)"></span>
                    </div>

                    <div class="flex items-baseline gap-1">
                        <span class="text-[10px] font-black text-red-600 uppercase">NG</span>
                        <span class="text-base font-black text-red-600" x-text="formatNum(totals.reject)"></span>
                    </div>

                    <div class="flex items-baseline gap-1">
                        <span class="text-[10px] font-black uppercase" :class="totals.yield >= 90 ? 'text-green-600' : (totals.yield >= 80 ? 'text-amber-600' : 'text-red-600')">Yield</span>
                        <span class="text-base font-black" :class="totals.yield >= 90 ? 'text-green-600' : (totals.yield >= 80 ? 'text-amber-600' : 'text-red-600')" x-text="totals.yield + '%'"></span>
                    </div>

                    <div class="flex items-baseline gap-1">
                        <span class="text-[10px] font-black text-orange-600 uppercase">Downtime</span>
                        <span class="text-base font-black text-orange-600" x-text="totals.downtime_minutes + 'm'"></span>
                    </div>
                </div>

                {{-- Target Progress --}}
                <div class="flex-1 max-w-xs min-w-[140px] pl-2">
                    <div class="flex justify-between text-[10px] font-bold text-gray-500 mb-0.5">
                        <span>Target Progress</span>
                        <span><strong class="text-gray-900" x-text="formatNum(totals.good)"></strong> / {{ number_format($session->workOrder->target_qty) }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500"
                             :class="progressPct >= 100 ? 'bg-green-500' : (progressPct >= 75 ? 'bg-blue-500' : (progressPct >= 50 ? 'bg-amber-500' : 'bg-gray-400'))"
                             :style="'width: ' + Math.min(100, progressPct) + '%'"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Action Control Panel + Quick-Tap Configurable Toolbar (Compact ~44px) --}}
        @if($session->status === 'running')
            <div class="bg-slate-900 text-white px-3 py-1.5 flex items-center justify-between gap-2 shadow-inner flex-shrink-0">
                <div class="flex items-center gap-2">
                    {{-- Quick-Tap Button with Flash Effect --}}
                    <button type="button" @click="quickAddGood()"
                        class="bg-emerald-500 hover:bg-emerald-400 active:bg-emerald-600 text-white font-black py-1.5 px-4 rounded-xl text-xs uppercase tracking-wider transition shadow flex items-center gap-1.5 min-w-[110px] justify-center"
                        :class="quickFlash && 'ring-4 ring-emerald-300 bg-emerald-400 scale-105'">
                        <span x-text="quickFlash ? 'SAVED!' : ('+' + batchSize + ' GOOD')"></span>
                    </button>

                    {{-- Operator Configurable Batch Size Selector --}}
                    <div class="flex items-center bg-slate-800 rounded-lg p-0.5 border border-slate-700 text-[10px] font-bold">
                        <span class="px-1.5 text-slate-400 uppercase text-[9px]">Batch:</span>
                        <template x-for="size in [1, 5, 10, 50]" :key="size">
                            <button type="button" @click="setBatchSize(size)"
                                :class="batchSize === size ? 'bg-blue-600 text-white' : 'text-slate-300 hover:text-white'"
                                class="px-2 py-0.5 rounded transition" x-text="'+' + size"></button>
                        </template>
                    </div>
                </div>

                {{-- Modal Action Triggers --}}
                <div class="flex items-center gap-1.5 overflow-x-auto">
                    <button onclick="document.getElementById('modalProduction').showModal()" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-1.5 px-3 rounded-lg text-xs uppercase tracking-wider transition">
                        Output
                    </button>
                    <button onclick="document.getElementById('modalReject').showModal()" class="bg-red-600 hover:bg-red-500 text-white font-bold py-1.5 px-3 rounded-lg text-xs uppercase tracking-wider transition">
                        Defect
                    </button>
                    <button onclick="document.getElementById('modalDowntime').showModal()" class="bg-amber-600 hover:bg-amber-500 text-white font-bold py-1.5 px-3 rounded-lg text-xs uppercase tracking-wider transition">
                        Downtime
                    </button>
                    <button onclick="document.getElementById('modalRework').showModal()" class="bg-yellow-600 hover:bg-yellow-500 text-white font-bold py-1.5 px-3 rounded-lg text-xs uppercase tracking-wider transition">
                        Rework
                    </button>
                    <button onclick="document.getElementById('modalInput').showModal()" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-1.5 px-3 rounded-lg text-xs uppercase tracking-wider transition">
                        Input
                    </button>
                    <button onclick="document.getElementById('modalManpower').showModal()" class="bg-purple-600 hover:bg-purple-500 text-white font-bold py-1.5 px-3 rounded-lg text-xs uppercase tracking-wider transition">
                        + Team
                    </button>
                </div>
            </div>
        @endif

        {{-- Event Log Tabs (Compact ~28px) --}}
        <div class="bg-white border-b border-gray-200 px-3 flex gap-1 text-xs flex-shrink-0">
            <button @click="tab = 'production'" :class="{ 'text-blue-600 border-blue-600 font-black bg-blue-50/50': tab === 'production', 'text-gray-500 hover:text-gray-700': tab !== 'production' }" class="py-1.5 px-3 border-b-2 font-bold transition">
                Output (<span x-text="productionEntries.length"></span>)
            </button>
            <button @click="tab = 'defects'" :class="{ 'text-blue-600 border-blue-600 font-black bg-blue-50/50': tab === 'defects', 'text-gray-500 hover:text-gray-700': tab !== 'defects' }" class="py-1.5 px-3 border-b-2 font-bold transition">
                Defects (<span x-text="rejectEntries.length"></span>)
            </button>
            <button @click="tab = 'downtime'" :class="{ 'text-blue-600 border-blue-600 font-black bg-blue-50/50': tab === 'downtime', 'text-gray-500 hover:text-gray-700': tab !== 'downtime' }" class="py-1.5 px-3 border-b-2 font-bold transition">
                Downtime (<span x-text="downtimeEntries.length"></span>)
            </button>
            <button @click="tab = 'rework'" :class="{ 'text-blue-600 border-blue-600 font-black bg-blue-50/50': tab === 'rework', 'text-gray-500 hover:text-gray-700': tab !== 'rework' }" class="py-1.5 px-3 border-b-2 font-bold transition">
                Rework (<span x-text="reworkEntries.length"></span>)
            </button>
            <button @click="tab = 'input'" :class="{ 'text-blue-600 border-blue-600 font-black bg-blue-50/50': tab === 'input', 'text-gray-500 hover:text-gray-700': tab !== 'input' }" class="py-1.5 px-3 border-b-2 font-bold transition">
                Input WIP (<span x-text="inputEntries.length"></span>)
            </button>
            <button @click="tab = 'manpower'" :class="{ 'text-blue-600 border-blue-600 font-black bg-blue-50/50': tab === 'manpower', 'text-gray-500 hover:text-gray-700': tab !== 'manpower' }" class="py-1.5 px-3 border-b-2 font-bold transition">
                Line Team ({{ $session->manpowerEntries->count() }})
            </button>
        </div>

        {{-- Event Log Tables (Flex-1 Fill Remaining Heights & Scrollable) --}}
        <div class="flex-1 overflow-y-auto bg-white">

            {{-- Output Logs --}}
            <div x-show="tab === 'production'" class="p-0">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 text-[10px] font-black text-gray-400 uppercase tracking-wider border-b border-gray-200 sticky top-0 bg-gray-50">
                        <tr>
                            <th class="px-3 py-2">Timestamp</th>
                            <th class="px-3 py-2 text-right">Good Qty</th>
                            <th class="px-3 py-2 text-right">Reject Qty</th>
                            <th class="px-3 py-2">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="entry in productionEntries" :key="entry.id">
                            <tr class="transition-colors duration-500" :class="highlightedEntryId === entry.id ? 'bg-emerald-100 font-bold border-l-4 border-emerald-500' : 'hover:bg-gray-50'">
                                <td class="px-3 py-1.5 font-mono text-gray-500" x-text="formatTime(entry.recorded_at)"></td>
                                <td class="px-3 py-1.5 text-right font-black text-green-600" x-text="'+' + formatNum(entry.good_qty)"></td>
                                <td class="px-3 py-1.5 text-right font-black text-red-600" x-text="'+' + formatNum(entry.reject_qty)"></td>
                                <td class="px-3 py-1.5 text-gray-600 font-medium truncate max-w-xs" x-text="entry.remarks || '-'"></td>
                            </tr>
                        </template>
                        <tr x-show="productionEntries.length === 0">
                            <td colspan="4" class="px-3 py-6 text-center text-gray-400 text-xs">No production entries logged yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Defects --}}
            <div x-show="tab === 'defects'" class="p-0" style="display: none;">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 text-[10px] font-black text-gray-400 uppercase tracking-wider border-b border-gray-200 sticky top-0 bg-gray-50">
                        <tr>
                            <th class="px-3 py-2">Defect Type</th>
                            <th class="px-3 py-2 text-right">Quantity</th>
                            <th class="px-3 py-2">Cause</th>
                            <th class="px-3 py-2">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="entry in rejectEntries" :key="entry.id">
                            <tr class="transition-colors duration-500" :class="highlightedEntryId === entry.id ? 'bg-emerald-100 font-bold border-l-4 border-emerald-500' : 'hover:bg-gray-50'">
                                <td class="px-3 py-1.5 font-black text-red-600" x-text="entry.defect_type"></td>
                                <td class="px-3 py-1.5 text-right font-black" x-text="formatNum(entry.quantity) + ' Pcs'"></td>
                                <td class="px-3 py-1.5 text-gray-600 font-medium" x-text="entry.cause || '-'"></td>
                                <td class="px-3 py-1.5 text-gray-600 font-medium" x-text="entry.remarks || '-'"></td>
                            </tr>
                        </template>
                        <tr x-show="rejectEntries.length === 0">
                            <td colspan="4" class="px-3 py-6 text-center text-gray-400 text-xs">No defects logged yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Downtime --}}
            <div x-show="tab === 'downtime'" class="p-0" style="display: none;">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 text-[10px] font-black text-gray-400 uppercase tracking-wider border-b border-gray-200 sticky top-0 bg-gray-50">
                        <tr>
                            <th class="px-3 py-2">Reason</th>
                            <th class="px-3 py-2">Start Time</th>
                            <th class="px-3 py-2">Resume Time</th>
                            <th class="px-3 py-2 text-right">Duration</th>
                            <th class="px-3 py-2">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="entry in downtimeEntries" :key="entry.id">
                            <tr class="transition-colors duration-500" :class="highlightedEntryId === entry.id ? 'bg-emerald-100 font-bold border-l-4 border-emerald-500' : 'hover:bg-gray-50'">
                                <td class="px-3 py-1.5 font-black text-orange-600" x-text="entry.reason"></td>
                                <td class="px-3 py-1.5 text-gray-600 font-mono" x-text="formatHM(entry.start_time)"></td>
                                <td class="px-3 py-1.5 text-gray-600 font-mono" x-text="formatHM(entry.resume_time)"></td>
                                <td class="px-3 py-1.5 text-right font-black text-gray-900" x-text="entry.duration_minutes + ' Mins'"></td>
                                <td class="px-3 py-1.5 text-gray-600 font-medium" x-text="entry.remarks || '-'"></td>
                            </tr>
                        </template>
                        <tr x-show="downtimeEntries.length === 0">
                            <td colspan="5" class="px-3 py-6 text-center text-gray-400 text-xs">No downtime events logged yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Rework --}}
            <div x-show="tab === 'rework'" class="p-0" style="display: none;">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 text-[10px] font-black text-gray-400 uppercase tracking-wider border-b border-gray-200 sticky top-0 bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-right">Rework Input</th>
                            <th class="px-3 py-2 text-right">Recovered Good</th>
                            <th class="px-3 py-2 text-right">Scrapped Qty</th>
                            <th class="px-3 py-2">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="entry in reworkEntries" :key="entry.id">
                            <tr class="transition-colors duration-500" :class="highlightedEntryId === entry.id ? 'bg-emerald-100 font-bold border-l-4 border-emerald-500' : 'hover:bg-gray-50'">
                                <td class="px-3 py-1.5 text-right font-black text-gray-800" x-text="formatNum(entry.input_qty) + ' Pcs'"></td>
                                <td class="px-3 py-1.5 text-right font-black text-green-600" x-text="formatNum(entry.recovered_qty) + ' Pcs'"></td>
                                <td class="px-3 py-1.5 text-right font-black text-red-600" x-text="formatNum(entry.scrapped_qty) + ' Pcs'"></td>
                                <td class="px-3 py-1.5 text-gray-600 font-medium" x-text="entry.remarks || '-'"></td>
                            </tr>
                        </template>
                        <tr x-show="reworkEntries.length === 0">
                            <td colspan="4" class="px-3 py-6 text-center text-gray-400 text-xs">No rework entries logged yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Input WIP --}}
            <div x-show="tab === 'input'" class="p-0" style="display: none;">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 text-[10px] font-black text-gray-400 uppercase tracking-wider border-b border-gray-200 sticky top-0 bg-gray-50">
                        <tr>
                            <th class="px-3 py-2">Timestamp</th>
                            <th class="px-3 py-2 text-right">Quantity</th>
                            <th class="px-3 py-2">Pallet / Box #</th>
                            <th class="px-3 py-2">Source</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="entry in inputEntries" :key="entry.id">
                            <tr class="transition-colors duration-500" :class="highlightedEntryId === entry.id ? 'bg-emerald-100 font-bold border-l-4 border-emerald-500' : 'hover:bg-gray-50'">
                                <td class="px-3 py-1.5 font-mono text-gray-500" x-text="formatTime(entry.created_at || entry.recorded_at)"></td>
                                <td class="px-3 py-1.5 text-right font-black text-blue-600" x-text="'+' + formatNum(entry.quantity) + ' Pcs'"></td>
                                <td class="px-3 py-1.5 font-mono text-gray-700 uppercase" x-text="entry.pallet_number || '-'"></td>
                                <td class="px-3 py-1.5 text-gray-500 uppercase text-[10px] font-bold" x-text="entry.source || 'WIP'"></td>
                            </tr>
                        </template>
                        <tr x-show="inputEntries.length === 0">
                            <td colspan="4" class="px-3 py-6 text-center text-gray-400 text-xs">No input WIP entries recorded.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Line Team --}}
            <div x-show="tab === 'manpower'" class="p-0" style="display: none;">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 text-[10px] font-black text-gray-400 uppercase tracking-wider border-b border-gray-200 sticky top-0 bg-gray-50">
                        <tr>
                            <th class="px-3 py-2">Role / Position</th>
                            <th class="px-3 py-2">Operator Name</th>
                            <th class="px-3 py-2">Employee NIK</th>
                            <th class="px-3 py-2 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($session->manpowerEntries as $mp)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 font-black text-purple-700 uppercase text-[10px]">{{ $mp->role }}</td>
                                <td class="px-3 py-2 font-bold text-gray-900">{{ $mp->operator_name }}</td>
                                <td class="px-3 py-2 font-mono text-gray-500">{{ $mp->employee_no ?: '-' }}</td>
                                <td class="px-3 py-2 text-right">
                                    @if($session->status === 'running')
                                        <form action="{{ route('app.sp-sessions.remove-manpower', [$session->id, $mp->id]) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Remove worker from line team?')" class="text-[10px] text-red-600 hover:text-red-800 font-black uppercase">
                                                Remove
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-6 text-center text-gray-400 text-xs">No extra line team members added yet. Click "+ Team" to assign operators.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
            <div class="bg-amber-600 px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-black text-white">Log Downtime</h3>
                <button type="button" onclick="document.getElementById('modalDowntime').close()" class="text-amber-200 hover:text-white text-xl font-bold">&times;</button>
            </div>
            <form action="{{ route('app.sp-sessions.add-downtime', $session->id) }}" method="POST" @submit.prevent="submitForm($event, 'downtime')" x-data="{ reason: '' }" class="p-6">
                @csrf
                <input type="hidden" name="reason" :value="reason">
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-500 uppercase mb-2">Reason *</label>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach($downtimeReasons as $r)
                                <button type="button" @click="reason = '{{ $r }}'" :class="reason === '{{ $r }}' ? 'bg-amber-600 text-white ring-4 ring-amber-200' : 'bg-gray-100 text-gray-700'" class="py-3 px-2 rounded-xl font-bold text-center transition shadow-sm text-sm">
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
                    <button type="submit" :disabled="!reason" :class="!reason ? 'opacity-50 cursor-not-allowed' : ''" class="w-full bg-amber-600 active:bg-amber-700 text-white py-4 rounded-xl text-xl font-black shadow-lg transition">SAVE DOWNTIME</button>
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
    </div> <!-- End of x-data="productionSession()" -->
</x-operator-layout>
