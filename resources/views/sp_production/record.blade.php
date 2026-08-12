<x-operator-layout>
    <x-slot name="bodyClass">h-screen max-h-screen overflow-hidden select-none touch-none</x-slot>
    <x-slot name="headerContainerClass">px-3 py-1.5</x-slot>
    <x-slot name="mainClass">p-0 h-[calc(100vh-42px)] overflow-hidden</x-slot>

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
        <div class="flex items-center justify-between gap-4 py-0.5" x-data="headerClock()" x-cloak>
            {{-- Left: Status, Line & Work Order Context --}}
            <div class="flex items-center gap-3 min-w-0">
                @if($session->status === 'running')
                    @if($session->paused_at)
                        <span class="w-3 h-3 rounded-full bg-amber-500 animate-pulse flex-shrink-0" title="PAUSED"></span>
                    @else
                        <span class="w-3 h-3 rounded-full bg-green-500 animate-pulse flex-shrink-0" title="RUNNING"></span>
                    @endif
                @else
                    <span class="w-3 h-3 rounded-full bg-blue-500 flex-shrink-0" title="COMPLETED"></span>
                @endif

                <div class="min-w-0 flex flex-col">
                    <div class="flex items-center gap-2 leading-tight">
                        <span class="font-black text-slate-900 text-sm tracking-tight">
                            {{ $session->unit_line }}
                        </span>
                        <span class="text-slate-300 font-light">•</span>
                        <a href="{{ route('sp-work-orders.show', $session->work_order_id) }}" class="font-black text-blue-700 hover:text-blue-900 text-sm">
                            {{ $session->workOrder->wo_number }}
                        </a>
                        @if($session->is_qc_bypassed)
                            <span class="px-1.5 py-0.2 text-[10px] font-black rounded-md bg-amber-100 text-amber-800 uppercase" title="QC Bypassed: {{ $session->qc_bypass_reason }}">
                                BYPASSED
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 text-xs font-medium text-slate-500 leading-tight">
                        <span class="truncate max-w-[180px]" title="{{ $session->workOrder->part_number }} - {{ $session->workOrder->part_name }}">
                            {{ $session->workOrder->part_number }}
                        </span>
                        <span>•</span>
                        <span x-text="elapsedTime"></span>
                    </div>
                </div>
            </div>

            {{-- Right: Essential Controls (Team, Gateway, Finish) --}}
            @php
                $lineSlug = array_search($session->unit_line, config('mes.sp_lines', [])) ?: 'line-a';
            @endphp
            <div class="flex items-center gap-2 flex-shrink-0">
                {{-- Team Roster Trigger Pill --}}
                <button type="button" onclick="document.getElementById('modalTeamRoster').showModal()"
                        class="px-3 py-1.5 bg-purple-50 hover:bg-purple-100 active:bg-purple-200 text-purple-800 font-bold text-xs rounded-xl border border-purple-200 transition cursor-pointer flex items-center gap-1.5">
                    <span>Team</span>
                    <span class="bg-purple-600 text-white px-1.5 py-0.2 rounded-md text-[10px] font-black">
                        {{ $session->manpowerEntries->count() }}
                    </span>
                </button>

                {{-- Gateway Link --}}
                <a href="{{ route('sp-sessions.line-gateway', ['lineSlug' => $lineSlug]) }}"
                   class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl border border-slate-200 transition">
                    Gateway
                </a>

                {{-- Finish Button --}}
                @if($session->status === 'running')
                    <button type="button" onclick="document.getElementById('modalFinish').showModal()"
                            class="px-3.5 py-1.5 bg-red-600 hover:bg-red-500 active:bg-red-700 text-white font-black text-xs rounded-xl shadow-xs transition uppercase tracking-wider">
                        Finish
                    </button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="flex flex-col h-full overflow-hidden bg-gray-100" x-data="productionSession()" x-cloak>
        <script>
            function productionSession() {
                return {
                    tab: 'all',
                    totals: {
                        input: {{ $session->total_input ?? 0 }},
                        good: {{ $session->total_good ?? 0 }},
                        direct_good: {{ $session->productionEntries->sum('good_qty') ?? 0 }},
                        reject: {{ $session->total_reject ?? 0 }},
                        yield: {{ $session->yield ?? 0 }},
                        downtime_minutes: {{ $session->downtimeEntries->sum('duration_minutes') ?? 0 }},
                        downtime_count: {{ $session->downtimeEntries->count() ?? 0 }},
                        raw_reject: {{ $session->rejectEntries->sum('quantity') ?? 0 }},
                        rework_in: {{ $session->total_rework_in ?? 0 }},
                        rework_recovered: {{ $session->total_rework_recovered ?? 0 }},
                        rework_scrapped: {{ $session->total_scrap ?? 0 }}
                    },
                    get reworkPending() {
                        const inQty = parseInt(this.totals.rework_in) || 0;
                        const recQty = parseInt(this.totals.rework_recovered) || 0;
                        const scrapQty = parseInt(this.totals.rework_scrapped) || 0;
                        return Math.max(0, inQty - (recQty + scrapQty));
                    },
                    get availableDefectsForRework() {
                        const rawRej = parseInt(this.totals.raw_reject !== undefined ? this.totals.raw_reject : this.totals.reject) || 0;
                        const inQty = parseInt(this.totals.rework_in) || 0;
                        return Math.max(0, rawRej - inQty);
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
                    manpowerEntries: @json($session->manpowerEntries),

                    pausedAt: {{ $session->paused_at ? "'" . $session->paused_at->toIso8601String() . "'" : "null" }},
                    pausedDurationMinutes: 0,

                    get isPaused() {
                        return !!this.pausedAt;
                    },

                    get availableWip() {
                        return Math.max(0, this.totals.input - (this.totals.good + this.totals.reject));
                    },

                    get allRecords() {
                        const list = [];
                        (this.productionEntries || []).forEach(e => list.push({ ...e, streamType: 'production', timestamp: new Date(e.recorded_at || e.created_at).getTime() }));
                        (this.rejectEntries || []).forEach(e => list.push({ ...e, streamType: 'reject', timestamp: new Date(e.created_at).getTime() }));
                        (this.downtimeEntries || []).forEach(e => list.push({ ...e, streamType: 'downtime', timestamp: new Date(e.created_at).getTime() }));
                        (this.reworkEntries || []).forEach(e => list.push({ ...e, streamType: 'rework', timestamp: new Date(e.created_at).getTime() }));
                        (this.inputEntries || []).forEach(e => list.push({ ...e, streamType: 'input', timestamp: new Date(e.created_at).getTime() }));
                        (this.manpowerEntries || []).forEach(e => list.push({ ...e, streamType: 'manpower', timestamp: new Date(e.created_at).getTime() }));
                        return list.sort((a, b) => b.timestamp - a.timestamp);
                    },

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

                        if (this.pausedAt) {
                            const paused = new Date(this.pausedAt);
                            const diffSec = Math.max(0, Math.floor((now - paused) / 1000));
                            const hrs = Math.floor(diffSec / 3600);
                            const mins = Math.floor((diffSec % 3600) / 60);
                            this.pausedDurationMinutes = Math.max(1, Math.round(diffSec / 60));
                            this.elapsedTime = (hrs > 0 ? hrs + 'h ' : '') + mins + 'm (PAUSED)';
                        } else {
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
                    },

                    async togglePause() {
                        if (this.isPaused) {
                            document.getElementById('modalResumeDowntime').showModal();
                            return;
                        }

                        if (!confirm('Pause production line for breakdown / downtime?')) return;

                        try {
                            const response = await fetch('{{ route("app.sp-sessions.pause", $session->id) }}', {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });

                            if (!response.ok) {
                                const err = await response.json().catch(() => ({}));
                                throw new Error(err.error || 'Failed to pause line.');
                            }

                            const data = await response.json();
                            if (data.success) {
                                this.pausedAt = data.paused_at;
                                this.updateClock();
                            }
                        } catch (error) {
                            alert(error.message);
                        }
                    },

                    async submitResumeForm(event) {
                        const form = event.target;
                        const formData = new FormData(form);

                        try {
                            const response = await fetch('{{ route("app.sp-sessions.resume", $session->id) }}', {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                },
                                body: formData
                            });

                            if (!response.ok) {
                                const err = await response.json().catch(() => ({}));
                                throw new Error(err.error || 'Failed to resume line.');
                            }

                            const data = await response.json();
                            if (data.success) {
                                this.pausedAt = null;
                                if (data.downtime) {
                                    this.downtimeEntries.unshift(data.downtime);
                                    this.triggerHighlight(data.downtime.id);
                                }
                                if (data.totals) {
                                    this.totals = { ...this.totals, ...data.totals };
                                }
                                form.closest('dialog').close();
                                form.reset();
                            }
                        } catch (error) {
                            alert(error.message);
                        }
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

                    async removeManpower(manpowerId) {
                        if (!confirm('Remove worker from line team?')) return;

                        try {
                            const response = await fetch(`{{ url('app/sp-sessions/' . $session->id . '/manpower') }}/${manpowerId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });

                            if (!response.ok) {
                                const err = await response.json().catch(() => ({}));
                                throw new Error(err.error || 'Failed to remove team member.');
                            }

                            const data = await response.json();
                            if (data.success) {
                                this.manpowerEntries = this.manpowerEntries.filter(m => m.id !== manpowerId);
                            }
                        } catch (error) {
                            alert(error.message);
                        }
                    },

                    async deleteEntry(item) {
                        if (!confirm('Remove this log entry? Session totals will recalculate automatically.')) return;

                        let endpoint = '';
                        if (item.streamType === 'production') endpoint = `{{ url('app/sp-sessions/' . $session->id . '/production') }}/${item.id}`;
                        else if (item.streamType === 'reject') endpoint = `{{ url('app/sp-sessions/' . $session->id . '/reject') }}/${item.id}`;
                        else if (item.streamType === 'downtime') endpoint = `{{ url('app/sp-sessions/' . $session->id . '/downtime') }}/${item.id}`;
                        else if (item.streamType === 'rework') endpoint = `{{ url('app/sp-sessions/' . $session->id . '/rework') }}/${item.id}`;
                        else if (item.streamType === 'input') endpoint = `{{ url('app/sp-sessions/' . $session->id . '/input') }}/${item.id}`;
                        else if (item.streamType === 'manpower') {
                            this.removeManpower(item.id);
                            return;
                        }

                        if (!endpoint) return;

                        try {
                            const response = await fetch(endpoint, {
                                method: 'DELETE',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });

                            if (!response.ok) {
                                const err = await response.json().catch(() => ({}));
                                throw new Error(err.error || 'Failed to remove entry.');
                            }

                            const data = await response.json();
                            if (data.success) {
                                if (item.streamType === 'production') this.productionEntries = this.productionEntries.filter(e => e.id !== item.id);
                                else if (item.streamType === 'reject') this.rejectEntries = this.rejectEntries.filter(e => e.id !== item.id);
                                else if (item.streamType === 'downtime') this.downtimeEntries = this.downtimeEntries.filter(e => e.id !== item.id);
                                else if (item.streamType === 'rework') this.reworkEntries = this.reworkEntries.filter(e => e.id !== item.id);
                                else if (item.streamType === 'input') this.inputEntries = this.inputEntries.filter(e => e.id !== item.id);

                                if (data.totals) {
                                    this.totals = { ...this.totals, ...data.totals };
                                    this.recalcProgress();
                                }
                            }
                        } catch (error) {
                            alert(error.message);
                        }
                    },

                    async quickAddGood() {
                        if (this.isPaused) {
                            document.getElementById('modalResumeDowntime').showModal();
                            return;
                        }

                        if (this.batchSize > this.availableWip) {
                            alert('Available Input WIP (' + this.availableWip + ' Pcs) is less than batch size (+' + this.batchSize + ' Pcs). Please receive stock first.');
                            document.getElementById('modalInput').showModal();
                            return;
                        }

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
                                else if (type === 'manpower') this.manpowerEntries.unshift(data.entry);

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

                    async submitMultiRejectForm(event) {
                        const form = event.target;
                        const alpineData = form._x_dataStack ? form._x_dataStack[0] : null;
                        if (!alpineData || !alpineData.validDefects || !alpineData.validDefects.length) {
                            alert('Please select quantity for at least one defect type.');
                            return;
                        }

                        const payload = {
                            defects: alpineData.validDefects,
                            remarks: alpineData.remarks || null
                        };

                        try {
                            const response = await fetch(form.action, {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify(payload)
                            });

                            const contentType = response.headers.get('content-type') || '';
                            let data = {};
                            if (contentType.includes('application/json')) {
                                data = await response.json();
                            } else {
                                const rawText = await response.text();
                                console.error('Non-JSON server response:', rawText);
                                throw new Error('Server error (' + response.status + '). Please try again.');
                            }

                            if (!response.ok) {
                                throw new Error(data.error || data.message || 'Failed to submit defects.');
                            }

                            if (data.success) {
                                if (data.totals) {
                                    this.totals = { ...this.totals, ...data.totals };
                                }
                                if (data.entries && data.entries.length) {
                                    data.entries.forEach(e => this.rejectEntries.unshift(e));
                                } else if (data.entry) {
                                    this.rejectEntries.unshift(data.entry);
                                }

                                this.tab = 'defects';
                                this.recalcProgress();
                                if (alpineData.resetCounts) alpineData.resetCounts();
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

            function rejectBuilder(presetList) {
                return {
                    searchQuery: '',
                    presetList: presetList || [],
                    stagedItems: [],
                    cause: '',

                    get filteredPresets() {
                        const q = (this.searchQuery || '').trim().toLowerCase();
                        if (!q) return this.presetList;
                        return this.presetList.filter(p => p.toLowerCase().includes(q));
                    },

                    get showCustomAdd() {
                        const q = (this.searchQuery || '').trim();
                        if (!q) return false;
                        return !this.presetList.some(p => p.toLowerCase() === q.toLowerCase());
                    },

                    addDefect(name) {
                        if (!name || !name.trim()) return;
                        const cleanName = name.trim();
                        const existing = this.stagedItems.find(i => i.defect_type.toLowerCase() === cleanName.toLowerCase());
                        if (existing) {
                            existing.quantity += 1;
                        } else {
                            this.stagedItems.push({ defect_type: cleanName, quantity: 1 });
                        }
                        this.searchQuery = '';
                    },

                    removeDefect(idx) {
                        this.stagedItems.splice(idx, 1);
                    },

                    get totalDefectQty() {
                        return this.stagedItems.reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0);
                    },

                    get validDefects() {
                        return this.stagedItems.filter(item => (parseInt(item.quantity) || 0) > 0).map(item => ({
                            defect_type: item.defect_type,
                            quantity: parseInt(item.quantity),
                            cause: this.cause || null
                        }));
                    },

                    resetCounts() {
                        this.searchQuery = '';
                        this.stagedItems = [];
                        this.cause = '';
                    }
                };
            }
        </script>

        {{-- Flash Notification --}}
        @if(session('success'))
            <div class="bg-green-600 text-white px-4 py-1.5 text-xs font-bold text-center">
                {{ session('success') }}
            </div>
        @endif

        {{-- ULTRA-KISS CORE METRICS BAR (Equal Height KPI Cards) --}}
        <div class="bg-white border-b border-gray-200 px-4 py-2.5 shadow-sm flex items-stretch justify-between gap-3 sm:gap-4 flex-shrink-0">
            {{-- Metric 1: Standalone Good Output (Direct Good + Rework Recovered Breakdown) --}}
            <button type="button" @click="tab = 'production'; document.getElementById('modalHistory').showModal()"
                    class="flex items-center gap-2.5 px-3 py-2 bg-emerald-50/70 hover:bg-emerald-100/80 active:bg-emerald-200 border border-emerald-200/80 rounded-2xl transition cursor-pointer text-left shadow-xs flex-1 max-w-xs"
                    title="Click to view Good Output Logs">
                <div class="w-7 h-7 rounded-lg bg-emerald-200/60 text-emerald-800 flex items-center justify-center font-black text-xs flex-shrink-0">✓</div>
                <div class="min-w-0">
                    <span class="text-[10px] font-black uppercase tracking-wider text-emerald-700 block">Good Output</span>
                    <div class="text-base sm:text-lg font-black text-emerald-950 leading-tight">
                        <span x-text="formatNum(totals.good) + ' Pcs'"></span>
                    </div>
                    <div class="text-[9px] font-bold text-emerald-800/80 truncate">
                        <span x-text="formatNum(totals.direct_good || (totals.good - (totals.rework_recovered || 0))) + ' Direct' + (totals.rework_recovered > 0 ? (' • ' + formatNum(totals.rework_recovered) + ' Rec.') : '')"></span>
                    </div>
                </div>
            </button>

            {{-- Metric 2: Total Logged Defects (Total Raw Defects + Recovered / Scrap Breakdown) --}}
            <button type="button" @click="tab = 'defects'; document.getElementById('modalHistory').showModal()"
                    class="flex items-center gap-2.5 px-3 py-2 bg-red-50/70 hover:bg-red-100/80 active:bg-red-200 border border-red-200/80 rounded-2xl transition cursor-pointer text-left shadow-xs flex-1 max-w-xs"
                    title="Click to view Defect Logs">
                <div class="w-7 h-7 rounded-lg bg-red-200/60 text-red-800 flex items-center justify-center font-black text-xs flex-shrink-0">!</div>
                <div class="min-w-0">
                    <span class="text-[10px] font-black uppercase tracking-wider text-red-700 block">Total Defects</span>
                    <div class="text-base sm:text-lg font-black text-red-950 leading-tight">
                        <span x-text="formatNum(totals.raw_reject || totals.reject) + ' Pcs'"></span>
                    </div>
                    <div class="text-[9px] font-bold text-red-800/80 truncate">
                        <span x-text="formatNum(totals.rework_recovered || 0) + ' Rec. • ' + formatNum(totals.rework_scrapped || 0) + ' Scrap'"></span>
                    </div>
                </div>
            </button>

            {{-- Metric 2.5: Total Issued Rework KPI Card (Click to view Rework logs) --}}
            <button type="button" x-show="(totals.rework_in || 0) > 0" x-cloak
                    @click="tab = 'rework'; document.getElementById('modalHistory').showModal()"
                    class="flex items-center gap-2.5 px-3 py-2 bg-yellow-50/70 hover:bg-yellow-100/80 active:bg-yellow-200 border border-yellow-200/80 rounded-2xl transition cursor-pointer text-left shadow-xs flex-1 max-w-xs"
                    title="Click to view Rework Logs">
                <div class="w-7 h-7 rounded-lg bg-yellow-200/60 text-yellow-900 flex items-center justify-center font-black text-xs flex-shrink-0">r</div>
                <div class="min-w-0">
                    <span class="text-[10px] font-black uppercase tracking-wider text-yellow-800 block">Issued Rework</span>
                    <div class="text-base sm:text-lg font-black text-yellow-950 leading-tight">
                        <span x-text="formatNum(totals.rework_in) + ' Pcs'"></span>
                    </div>
                </div>
            </button>

            {{-- Metric 3: Available WIP Balance (Click to view WIP Input logs) --}}
            <button type="button" @click="tab = 'input'; document.getElementById('modalHistory').showModal()"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-2xl border transition cursor-pointer text-left shadow-xs flex-1 max-w-xs"
                    :class="availableWip > 0 ? 'bg-blue-50/70 hover:bg-blue-100/80 border-blue-200/80' : 'bg-amber-100/80 hover:bg-amber-200/90 border-amber-300 animate-pulse'"
                    title="Click to view Input WIP Logs">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center font-black text-xs flex-shrink-0"
                     :class="availableWip > 0 ? 'bg-blue-200/60 text-blue-800' : 'bg-amber-300/80 text-amber-900'">#</div>
                <div class="min-w-0">
                    <span class="text-[10px] font-black uppercase tracking-wider block" :class="availableWip > 0 ? 'text-blue-700' : 'text-amber-800'">Available WIP</span>
                    <div class="text-base sm:text-lg font-black leading-tight" :class="availableWip > 0 ? 'text-blue-950' : 'text-amber-950'">
                        <span x-text="formatNum(availableWip) + ' Pcs'"></span>
                    </div>
                </div>
            </button>

            {{-- Metric 4: Downtime Duration (Click to view Downtime logs) --}}
            <button type="button" @click="tab = 'downtime'; document.getElementById('modalHistory').showModal()"
                    class="flex items-center gap-2.5 px-3 py-2 bg-amber-50/70 hover:bg-amber-100/80 active:bg-amber-200 border border-amber-200/80 rounded-2xl transition cursor-pointer text-left shadow-xs flex-1 max-w-xs"
                    title="Click to view Downtime Logs">
                <div class="w-7 h-7 rounded-lg bg-amber-200/60 text-amber-900 flex items-center justify-center font-black text-xs flex-shrink-0">m</div>
                <div class="min-w-0">
                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-800 block">Downtime</span>
                    <div class="text-base sm:text-lg font-black text-amber-950 leading-tight">
                        <span x-text="formatNum(totals.downtime_minutes) + ' Mins'"></span>
                    </div>
                </div>
            </button>

            {{-- Metric 5: Rich WO Target Progress Card --}}
            <div class="flex-1 max-w-xs hidden md:flex flex-col justify-between bg-slate-50 border border-slate-200/80 rounded-2xl px-3 py-2 shadow-xs">
                <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-wider text-slate-600 mb-1">
                    <span>WO Target</span>
                    <span class="text-slate-900 font-black" x-text="progressPct + '%'"></span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden my-auto">
                    <div class="h-full rounded-full transition-all duration-500"
                         :class="progressPct >= 100 ? 'bg-emerald-500' : (progressPct >= 75 ? 'bg-blue-500' : (progressPct >= 50 ? 'bg-amber-500' : 'bg-slate-400'))"
                         :style="'width: ' + Math.min(100, progressPct) + '%'"></div>
                </div>
                <div class="flex items-center justify-between text-[10px] font-bold text-slate-500 mt-1">
                    <span>Target: <strong class="text-slate-700" x-text="formatNum(targetQty)"></strong></span>
                    <span>Rem: <strong :class="Math.max(0, targetQty - totals.good) > 0 ? 'text-amber-700' : 'text-emerald-700'" x-text="formatNum(Math.max(0, targetQty - totals.good)) + ' Pcs'"></strong></span>
                </div>
            </div>
        </div>

        {{-- Active Rework Bench Notification Banner (2-Cycle Rework) --}}
        <div x-show="reworkPending > 0" x-cloak class="bg-amber-500 text-white px-4 py-2 flex items-center justify-between text-xs font-bold shadow-md flex-shrink-0">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-white animate-ping"></span>
                <span>Active Rework Bench: <strong x-text="formatNum(reworkPending) + ' Pcs'"></strong> undergoing offline repair</span>
            </div>
            <button type="button" onclick="document.getElementById('modalCompleteRework').showModal()"
                    class="px-3 py-1 bg-white text-amber-900 hover:bg-amber-100 active:bg-amber-200 font-black text-xs rounded-xl shadow-xs transition cursor-pointer uppercase tracking-wider">
                Complete Rework Outcome →
            </button>
        </div>

        {{-- Line Team Warning Banner (0 Operators Assigned) --}}
        <div x-show="manpowerEntries.length === 0" x-cloak class="bg-amber-600 text-white px-4 py-2 flex items-center justify-between text-xs font-bold shadow-md flex-shrink-0">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-white animate-ping"></span>
                <span>Line Team Warning: No operators assigned to this running line team yet!</span>
            </div>
            <button type="button" onclick="document.getElementById('modalManpower').showModal()"
                    class="px-3 py-1 bg-white text-amber-950 hover:bg-amber-100 active:bg-amber-200 font-black text-xs rounded-xl shadow-xs transition cursor-pointer uppercase tracking-wider">
                + Add Line Team Member
            </button>
        </div>

        {{-- ULTRA-KISS 2X2 HERO GRID (Zero Footer Bar) --}}
        @if($session->status === 'running')
            <div class="flex-1 bg-gray-100 p-3 sm:p-5 flex flex-col justify-between select-none overflow-hidden min-h-0">
                
                {{-- 2X2 Hero Action Cards Grid --}}
                <div class="grid grid-cols-2 gap-3 sm:gap-4 flex-1 min-h-0">
                    
                    {{-- Card 1: Good Output (With Integrated Quick Batch Selector) --}}
                    <div class="rounded-3xl p-4 sm:p-5 bg-emerald-600 border-4 border-emerald-400 text-white shadow-xl flex flex-col justify-between items-center text-center transition-all relative overflow-hidden min-h-0"
                         :class="quickFlash && 'ring-8 ring-emerald-300 scale-105 bg-emerald-500'">
                        
                        {{-- Top Integrated Quick Batch Size Selector --}}
                        <div class="w-full flex items-center justify-between gap-1.5 bg-emerald-700/60 p-1.5 rounded-2xl backdrop-blur-xs flex-shrink-0 z-10" @click.stop>
                            <span class="text-[10px] font-black uppercase tracking-wider text-emerald-200 ml-1 hidden sm:inline">Batch:</span>
                            <div class="flex items-center gap-1 flex-1 justify-around">
                                <template x-for="size in [1, 5, 10, 50, 100]" :key="size">
                                    <button type="button" @click="setBatchSize(size)"
                                            :class="batchSize === size ? 'bg-white text-emerald-900 font-black shadow-md scale-105' : 'bg-emerald-800/80 hover:bg-emerald-700 text-white font-bold'"
                                            class="px-2 py-1 rounded-xl transition text-xs sm:text-sm flex-1 text-center cursor-pointer border border-emerald-500/40"
                                            x-text="'+' + size"></button>
                                </template>
                            </div>
                        </div>

                        {{-- Main Tap Target --}}
                        <button type="button" @click="quickAddGood()"
                                class="w-full flex-1 flex flex-col items-center justify-center cursor-pointer active:scale-95 transition-transform py-2">
                            <h1 class="text-3xl sm:text-5xl font-black tracking-tight drop-shadow-md uppercase" x-text="quickFlash ? '✓ SAVED!' : ('+' + batchSize + ' GOOD')"></h1>
                            <p class="text-xs sm:text-sm font-bold text-emerald-100 mt-1" x-text="'Tap to log ' + batchSize + ' OK piece(s)'"></p>
                        </button>
                    </div>

                    {{-- Card 2: Log Defect --}}
                    <button type="button" onclick="document.getElementById('modalReject').showModal()"
                            class="rounded-3xl p-4 sm:p-6 bg-red-600 hover:bg-red-500 active:bg-red-700 border-4 border-red-400 text-white shadow-xl transition-all flex flex-col items-center justify-center text-center cursor-pointer active:scale-95 min-h-0">
                        <h1 class="text-2xl sm:text-4xl font-black tracking-tight uppercase drop-shadow-md">LOG DEFECT</h1>
                        <p class="text-xs sm:text-sm font-bold text-red-100 mt-2">Log rejects & scrap entries</p>
                    </button>

                    {{-- Card 3: Receive WIP --}}
                    <button type="button" onclick="document.getElementById('modalInput').showModal()"
                            class="rounded-3xl p-4 sm:p-6 bg-blue-600 hover:bg-blue-500 active:bg-blue-700 border-4 border-blue-400 text-white shadow-xl transition-all flex flex-col items-center justify-center text-center cursor-pointer active:scale-95 min-h-0">
                        <h1 class="text-2xl sm:text-4xl font-black tracking-tight uppercase drop-shadow-md">RECEIVE WIP</h1>
                        <p class="text-xs sm:text-sm font-bold text-blue-100 mt-2">Add incoming WIP to line balance</p>
                    </button>

                    {{-- Card 4: Pause / Resume Line --}}
                    <button type="button" @click="togglePause()"
                            :class="isPaused ? 'bg-emerald-600 hover:bg-emerald-500 border-emerald-400 animate-pulse' : 'bg-amber-600 hover:bg-amber-500 border-amber-400'"
                            class="rounded-3xl p-4 sm:p-6 border-4 text-white shadow-xl transition-all flex flex-col items-center justify-center text-center cursor-pointer active:scale-95 min-h-0">
                        <h1 class="text-2xl sm:text-4xl font-black tracking-tight uppercase drop-shadow-md" x-text="isPaused ? 'RESUME LINE' : 'PAUSE LINE'"></h1>
                        <p class="text-xs sm:text-sm font-bold text-amber-100 mt-2" x-text="isPaused ? 'Restart line & select reason' : 'Stop line & log downtime'"></p>
                    </button>

                </div>

                {{-- Sub-Hero Action Strip (Action Triggers Only) --}}
                <div class="flex items-center justify-end gap-3 mt-3 flex-shrink-0">
                    <div class="flex items-center gap-2 flex-shrink-0 ml-auto">
                        {{-- Log Downtime Trigger (Manual Stoppages) --}}
                        <button type="button" onclick="document.getElementById('modalDowntime').showModal()"
                                title="Log Micro-stoppages or Past Downtime"
                                class="px-3.5 py-2 bg-amber-600 hover:bg-amber-500 active:bg-amber-700 text-white font-black text-xs rounded-xl shadow-md transition flex items-center gap-1.5 cursor-pointer">
                            <span>Log Downtime</span>
                        </button>

                        {{-- Logs History Drawer Trigger --}}
                        <button type="button" @click="tab = 'all'; document.getElementById('modalHistory').showModal()"
                                title="View Full Shift Log History"
                                class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 active:bg-slate-900 text-white font-black text-xs rounded-xl shadow-md transition flex items-center gap-2 cursor-pointer">
                            <span>Logs History</span>
                            <span class="bg-slate-700 text-white px-1.5 py-0.5 rounded-md text-[10px] font-black" x-text="allRecords.length"></span>
                        </button>
                    </div>
                </div>

            </div>
        @endif

        {{-- MODAL HISTORY (FULL SEARCHABLE EVENT STREAM DRAWER) --}}
        <dialog id="modalHistory" class="rounded-2xl p-0 shadow-2xl border-0 w-full max-w-4xl backdrop:bg-gray-900/60 bg-transparent">
            <div class="bg-white rounded-2xl overflow-hidden shadow-2xl flex flex-col max-h-[85vh]">
                <div class="bg-slate-900 px-6 py-4 flex justify-between items-center text-white flex-shrink-0">
                    <div>
                        <h3 class="text-lg font-black">Full Operational Event History</h3>
                        <p class="text-xs text-slate-400 font-medium">Complete chronological activity log for Session #{{ $session->id }}</p>
                    </div>
                    <button type="button" onclick="document.getElementById('modalHistory').close()" class="text-slate-400 hover:text-white text-2xl font-bold">&times;</button>
                </div>

                {{-- Filter Tabs (Fixed height row, flex-shrink-0) --}}
                <div class="bg-slate-100 border-b border-slate-200 px-6 flex items-center gap-2.5 text-xs font-bold py-3 overflow-x-auto whitespace-nowrap flex-shrink-0">
                    <button type="button" @click="tab = 'all'" :class="tab === 'all' ? 'bg-blue-600 text-white border-blue-600 shadow-sm font-black' : 'bg-white text-slate-700 hover:bg-slate-200 border-slate-200'" class="px-3.5 py-2 rounded-xl transition flex-shrink-0 cursor-pointer border">All (<span x-text="allRecords.length"></span>)</button>
                    <button type="button" @click="tab = 'production'" :class="tab === 'production' ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm font-black' : 'bg-white text-slate-700 hover:bg-slate-200 border-slate-200'" class="px-3.5 py-2 rounded-xl transition flex-shrink-0 cursor-pointer border">Good Output (<span x-text="productionEntries.length"></span>)</button>
                    <button type="button" @click="tab = 'defects'" :class="tab === 'defects' ? 'bg-red-600 text-white border-red-600 shadow-sm font-black' : 'bg-white text-slate-700 hover:bg-slate-200 border-slate-200'" class="px-3.5 py-2 rounded-xl transition flex-shrink-0 cursor-pointer border">Defects (<span x-text="rejectEntries.length"></span>)</button>
                    <button type="button" @click="tab = 'downtime'" :class="tab === 'downtime' ? 'bg-amber-600 text-white border-amber-600 shadow-sm font-black' : 'bg-white text-slate-700 hover:bg-slate-200 border-slate-200'" class="px-3.5 py-2 rounded-xl transition flex-shrink-0 cursor-pointer border">Downtime (<span x-text="downtimeEntries.length"></span>)</button>
                    <button type="button" @click="tab = 'rework'" :class="tab === 'rework' ? 'bg-yellow-600 text-white border-yellow-600 shadow-sm font-black' : 'bg-white text-slate-700 hover:bg-slate-200 border-slate-200'" class="px-3.5 py-2 rounded-xl transition flex-shrink-0 cursor-pointer border">Rework (<span x-text="reworkEntries.length"></span>)</button>
                    <button type="button" @click="tab = 'input'" :class="tab === 'input' ? 'bg-blue-600 text-white border-blue-600 shadow-sm font-black' : 'bg-white text-slate-700 hover:bg-slate-200 border-slate-200'" class="px-3.5 py-2 rounded-xl transition flex-shrink-0 cursor-pointer border">Input WIP (<span x-text="inputEntries.length"></span>)</button>
                    <button type="button" @click="tab = 'manpower'" :class="tab === 'manpower' ? 'bg-purple-600 text-white border-purple-600 shadow-sm font-black' : 'bg-white text-slate-700 hover:bg-slate-200 border-slate-200'" class="px-3.5 py-2 rounded-xl transition flex-shrink-0 cursor-pointer border">Team (<span x-text="manpowerEntries.length"></span>)</button>
                </div>

                {{-- Table Area --}}
                <div class="flex-1 overflow-y-auto p-0">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-black text-slate-500 uppercase tracking-wider border-b border-slate-200 sticky top-0">
                            <tr>
                                <th class="px-4 py-3">Timestamp</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Event Details</th>
                                <th class="px-4 py-3 text-right">Quantity / Duration</th>
                                <th class="px-4 py-3 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="item in (tab === 'all' ? allRecords : (tab === 'production' ? productionEntries : (tab === 'defects' ? rejectEntries : (tab === 'downtime' ? downtimeEntries : (tab === 'rework' ? reworkEntries : (tab === 'input' ? inputEntries : manpowerEntries))))))" :key="(item.streamType || tab) + '-' + (item.id || item.timestamp || Math.random())">
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-2.5 font-mono text-xs text-slate-500" x-text="formatTime(item.recorded_at || item.created_at)"></td>
                                    <td class="px-4 py-2.5">
                                        <span x-show="item.good_qty !== undefined || item.streamType === 'production'" class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-emerald-100 text-emerald-800 border border-emerald-200">GOOD</span>
                                        <span x-show="item.defect_type !== undefined || item.streamType === 'reject'" class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-red-100 text-red-800 border border-red-200">DEFECT</span>
                                        <span x-show="item.duration_minutes !== undefined || item.streamType === 'downtime'" class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-amber-100 text-amber-800 border border-amber-200">DOWNTIME</span>
                                        <span x-show="item.recovered_qty !== undefined || item.streamType === 'rework'" class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-yellow-100 text-yellow-800 border border-yellow-200">REWORK</span>
                                        <span x-show="item.source !== undefined || item.streamType === 'input'" class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-blue-100 text-blue-800 border border-blue-200">INPUT</span>
                                        <span x-show="item.role !== undefined || item.streamType === 'manpower'" class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-purple-100 text-purple-800 border border-purple-200">TEAM</span>
                                    </td>
                                    <td class="px-4 py-2.5 text-xs font-semibold text-slate-800">
                                        <template x-if="item.good_qty !== undefined || item.streamType === 'production'">
                                            <span>Good Output Logged <span class="text-slate-400 font-normal" x-text="item.remarks ? '(' + item.remarks + ')' : ''"></span></span>
                                        </template>
                                        <template x-if="item.defect_type !== undefined || item.streamType === 'reject'">
                                            <span><strong class="text-red-700" x-text="item.defect_type"></strong> <span class="text-slate-500 font-normal" x-text="item.cause ? '— Cause: ' + item.cause : ''"></span></span>
                                        </template>
                                        <template x-if="item.duration_minutes !== undefined || item.streamType === 'downtime'">
                                            <span><strong class="text-amber-800" x-text="item.reason"></strong> <span class="text-slate-500 font-normal" x-text="(item.start_time && item.resume_time) ? '(' + formatHM(item.start_time) + ' – ' + formatHM(item.resume_time) + ')' : (item.start_time ? '(' + formatHM(item.start_time) + ')' : '')"></span></span>
                                        </template>
                                        <template x-if="item.recovered_qty !== undefined || item.streamType === 'rework'">
                                            <span>
                                                <strong x-text="(item.recovered_qty > 0 || item.scrapped_qty > 0) ? 'Rework Inspection Outcome' : 'Issued to Rework Bench'"></strong>
                                                <span class="text-slate-500 font-normal" x-text="item.remarks ? '— ' + item.remarks : ''"></span>
                                            </span>
                                        </template>
                                        <template x-if="item.source !== undefined || item.streamType === 'input'">
                                            <span>WIP Stock Received <span class="text-slate-500 font-normal" x-text="item.source ? '(' + item.source + ')' : ''"></span></span>
                                        </template>
                                        <template x-if="item.role !== undefined || item.streamType === 'manpower'">
                                            <span>Team Member Added: <strong class="text-purple-800" x-text="item.operator_name || ('Worker #' + (item.user_id || item.id))"></strong> <span class="text-slate-400 font-normal" x-text="item.role ? '(' + item.role + ')' : ''"></span></span>
                                        </template>
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-black text-xs">
                                        <span x-show="item.good_qty !== undefined || item.streamType === 'production'" class="text-emerald-700" x-text="'+' + formatNum(item.good_qty || 0) + ' Pcs'"></span>
                                        <span x-show="item.defect_type !== undefined || item.streamType === 'reject'" class="text-red-600" x-text="formatNum(item.quantity || 0) + ' Pcs'"></span>
                                        <span x-show="item.duration_minutes !== undefined || item.streamType === 'downtime'" class="text-amber-700" x-text="(item.duration_minutes || 0) + 'm'"></span>
                                        
                                        <template x-if="item.recovered_qty !== undefined || item.streamType === 'rework'">
                                            <div class="flex items-center justify-end gap-1.5 font-mono text-xs font-black">
                                                <span x-show="item.input_qty > 0 && (item.recovered_qty || 0) === 0 && (item.scrapped_qty || 0) === 0" class="text-yellow-800" x-text="formatNum(item.input_qty) + ' Issued'"></span>
                                                <span x-show="(item.recovered_qty || 0) > 0" class="text-emerald-700" x-text="'+' + formatNum(item.recovered_qty) + ' OK'"></span>
                                                <span x-show="(item.scrapped_qty || 0) > 0" class="text-red-600" x-text="formatNum(item.scrapped_qty) + ' Scrap'"></span>
                                            </div>
                                        </template>

                                        <span x-show="item.source !== undefined || item.streamType === 'input'" class="text-blue-700" x-text="'+' + formatNum(item.quantity || 0) + ' WIP'"></span>
                                        <span x-show="item.role !== undefined || item.streamType === 'manpower'" class="text-purple-700" x-text="item.role || 'Team'"></span>
                                    </td>
                                    <td class="px-4 py-2.5 text-center whitespace-nowrap">
                                        @if($session->status === 'running')
                                            <button type="button" @click="deleteEntry(item)" class="px-2.5 py-1 bg-red-50 hover:bg-red-100 active:bg-red-200 text-red-700 font-bold text-xs rounded-lg border border-red-200 transition cursor-pointer">
                                                Delete
                                            </button>
                                        @else
                                            <span class="text-slate-300 font-normal text-xs">-</span>
                                        @endif
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="bg-slate-100 p-4 border-t border-slate-200 text-right">
                    <button type="button" onclick="document.getElementById('modalHistory').close()" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-black text-xs rounded-xl uppercase">Close History</button>
                </div>
            </div>
        </dialog>

    {{-- MODALS --}}

    {{-- Modal 1: Log Production --}}
    <dialog id="modalProduction" class="rounded-xl p-0 shadow-2xl border-0 w-full max-w-lg backdrop:bg-gray-900/50 bg-transparent">
        <div class="bg-white rounded-xl overflow-hidden shadow-2xl">
            <div class="bg-green-600 px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-black text-white">Log Output</h3>
                <button type="button" onclick="document.getElementById('modalProduction').close()" class="text-green-200 hover:text-white text-xl font-bold">&times;</button>
            </div>
            <form action="{{ route('app.sp-sessions.add-production', $session->id) }}" method="POST" @submit.prevent="submitForm($event, 'production')" x-data="{ good_qty: 0 }" class="p-6">
                @csrf
                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-xl flex items-center justify-between text-xs font-bold text-blue-900">
                    <span>Available Input WIP:</span>
                    <span class="text-sm font-black" x-text="formatNum(availableWip) + ' Pcs'"></span>
                </div>

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

                    <div>
                        <input type="text" name="remarks" placeholder="Optional notes / remarks..." class="w-full border-gray-300 rounded-lg text-lg p-3 bg-gray-50 focus:bg-white">
                    </div>
                </div>

                {{-- WIP Warning --}}
                <div x-show="good_qty > availableWip" class="mt-4 p-2 bg-red-100 border border-red-300 rounded-lg text-xs font-bold text-red-700 text-center">
                    Warning: Output quantity (<span x-text="good_qty"></span> Pcs) exceeds available WIP (<span x-text="availableWip"></span> Pcs). Please log Input WIP first.
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100">
                    <button type="submit" :disabled="good_qty <= 0 || good_qty > availableWip" :class="good_qty <= 0 || good_qty > availableWip ? 'opacity-50 cursor-not-allowed' : ''" class="w-full bg-green-600 active:bg-green-700 text-white py-4 rounded-xl text-xl font-black shadow-lg transition">SAVE LOG</button>
                </div>
            </form>
        </div>
    </dialog>

    {{-- Modal 2: Log Defect (Zero-Scroll Search-As-You-Type Builder) --}}
    <dialog id="modalReject" class="rounded-2xl p-0 shadow-2xl border-0 w-full max-w-xl backdrop:bg-gray-900/60 bg-transparent">
        <div class="bg-white rounded-2xl overflow-hidden shadow-2xl">
            <div class="bg-red-600 px-6 py-4 flex justify-between items-center text-white">
                <div>
                    <h3 class="text-xl font-black">Log Defect Entries</h3>
                    <p class="text-xs text-red-100 font-medium mt-0.5">Search or type defects, assign quantities, and submit batch</p>
                </div>
                <button type="button" onclick="document.getElementById('modalReject').close()" class="text-red-200 hover:text-white text-2xl font-bold">&times;</button>
            </div>

            @php
                $presetDefects = array_values(array_unique(array_merge($defectTypes ?? [], ['Flash', 'Burn Mark', 'Scratch', 'Short Shot', 'Discoloration', 'Sink Mark', 'Silver Streak', 'Deformation'])));
            @endphp

            <form action="{{ route('app.sp-sessions.add-reject', $session->id) }}" method="POST"
                  @submit.prevent="submitMultiRejectForm($event)"
                  x-data="rejectBuilder({{ json_encode($presetDefects) }})"
                  class="p-6">
                @csrf

                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-xl flex items-center justify-between text-xs font-bold text-blue-900">
                    <span>Available Input WIP:</span>
                    <span class="text-sm font-black" x-text="formatNum(availableWip) + ' Pcs'"></span>
                </div>

                {{-- Search / Type Defect Input --}}
                <div class="relative mb-4">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-1.5">Search or Type Defect Name</label>
                    <div class="flex items-center gap-2">
                        <input type="text" x-model="searchQuery"
                               @keydown.enter.prevent="if (searchQuery.trim()) { addDefect(searchQuery); }"
                               placeholder="Type e.g. Flash, Burn Mark, or custom defect..."
                               class="flex-1 text-sm font-bold border-slate-300 rounded-xl p-3 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-red-500 focus:border-red-500 text-slate-900 placeholder-slate-400">
                        <button type="button" @click="if (searchQuery.trim()) addDefect(searchQuery)"
                                class="px-4 py-3 bg-red-600 hover:bg-red-500 active:bg-red-700 text-white font-black text-xs rounded-xl shadow-xs transition cursor-pointer flex-shrink-0">
                            + ADD
                        </button>
                    </div>

                    {{-- Search Dropdown Suggestions --}}
                    <div x-show="searchQuery.trim().length > 0" x-cloak
                         class="absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 max-h-48 overflow-y-auto divide-y divide-slate-100">
                        <template x-for="p in filteredPresets" :key="p">
                            <button type="button" @click="addDefect(p)"
                                    class="w-full text-left px-4 py-2.5 hover:bg-red-50 transition flex items-center justify-between cursor-pointer">
                                <span class="font-bold text-sm text-slate-800" x-text="p"></span>
                                <span class="text-xs font-black text-red-600">+ Select</span>
                            </button>
                        </template>

                        <template x-if="showCustomAdd">
                            <button type="button" @click="addDefect(searchQuery)"
                                    class="w-full text-left px-4 py-2.5 bg-red-50 hover:bg-red-100 transition flex items-center justify-between cursor-pointer border-t border-red-200">
                                <span>Add Custom Defect: <strong class="text-red-700 font-black" x-text="'&quot;' + searchQuery.trim() + '&quot;'"></strong></span>
                                <span class="text-xs font-black text-red-700">+ Add Custom</span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Staged Defects List (Visible ONLY when at least 1 defect added) --}}
                <div x-show="stagedItems.length > 0" x-cloak class="mb-4">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-1.5">
                        Staged Defect Entries (<span x-text="stagedItems.length"></span>)
                    </label>

                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        <template x-for="(item, idx) in stagedItems" :key="item.defect_type + '-' + idx">
                            <div class="p-2.5 bg-red-50/80 border border-red-200 rounded-xl flex items-center justify-between gap-3">
                                <span class="font-black text-sm text-red-950 flex-1 truncate" x-text="item.defect_type"></span>

                                {{-- Counter & Remove --}}
                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                    <button type="button" @click="item.quantity = Math.max(1, (parseInt(item.quantity) || 1) - 1)"
                                            class="w-7 h-7 rounded-lg bg-white border border-red-200 text-red-700 font-black text-base flex items-center justify-center hover:bg-red-100 transition cursor-pointer">
                                        -
                                    </button>
                                    <input type="number" min="1" x-model.number="item.quantity" @focus="$event.target.select()"
                                           class="w-12 h-7 text-center font-black text-sm border border-red-200 rounded-lg bg-white text-red-700 p-0 focus:ring-red-500">
                                    <button type="button" @click="item.quantity = (parseInt(item.quantity) || 0) + 1"
                                            class="w-7 h-7 rounded-lg bg-red-600 text-white font-black text-base flex items-center justify-center hover:bg-red-500 transition cursor-pointer">
                                        +
                                    </button>

                                    <button type="button" @click="removeDefect(idx)"
                                            class="w-7 h-7 ml-1 text-slate-400 hover:text-red-600 font-bold text-lg flex items-center justify-center transition cursor-pointer" title="Remove">
                                        &times;
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Probable Cause Optional --}}
                <div x-show="stagedItems.length > 0" x-cloak class="mb-4">
                    <input type="text" x-model="cause" placeholder="Probable Cause for this batch (Optional)..."
                           class="w-full border-slate-300 rounded-xl text-xs p-2.5 bg-slate-50 focus:bg-white font-medium">
                </div>

                {{-- WIP Warning --}}
                <div x-show="totalDefectQty > availableWip" class="mb-4 p-2 bg-red-100 border border-red-300 rounded-xl text-xs font-bold text-red-700 text-center">
                    Warning: Total defect quantity (<span x-text="totalDefectQty"></span> Pcs) exceeds available WIP (<span x-text="availableWip"></span> Pcs).
                </div>

                <div>
                    <button type="submit"
                            :disabled="totalDefectQty <= 0 || stagedItems.length === 0 || totalDefectQty > availableWip"
                            :class="(totalDefectQty <= 0 || stagedItems.length === 0 || totalDefectQty > availableWip) ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-red-600 hover:bg-red-500 active:bg-red-700 shadow-lg'"
                            class="w-full text-white py-3.5 rounded-xl text-base font-black uppercase tracking-wider transition cursor-pointer flex items-center justify-center gap-2">
                        <span x-text="totalDefectQty > 0 ? ('SUBMIT ' + totalDefectQty + ' DEFECT(S) (' + stagedItems.length + ' TYPES)') : 'ADD DEFECT TYPES TO SUBMIT'"></span>
                    </button>
                </div>

                {{-- Defect Lifecycle & Rework Status Breakdown (2-Cycle Rework) --}}
                <div x-show="(totals.raw_reject || totals.reject) > 0" class="mt-5 pt-4 border-t border-slate-200">
                    <div class="flex items-center justify-between text-xs font-black text-slate-700 uppercase tracking-wider mb-2">
                        <span>Defect Lifecycle & Rework Status</span>
                        <span class="text-slate-500 font-bold" x-text="formatNum(totals.raw_reject || totals.reject) + ' Pcs Logged'"></span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 mb-3 text-center text-xs">
                        {{-- 1. Unsorted Defect Stock --}}
                        <div class="p-2 rounded-xl bg-amber-50 border border-amber-200">
                            <span class="block text-[10px] font-bold text-amber-800 uppercase">Available to Rework</span>
                            <strong class="text-sm font-black text-amber-950" x-text="formatNum(availableDefectsForRework) + ' Pcs'"></strong>
                        </div>

                        {{-- 2. Completed Outcomes --}}
                        <div class="p-2 rounded-xl bg-emerald-50 border border-emerald-200">
                            <span class="block text-[10px] font-bold text-emerald-800 uppercase">Completed</span>
                            <div class="text-[11px] font-black text-emerald-950 leading-tight">
                                <span class="text-emerald-700" x-text="formatNum(totals.rework_recovered) + ' OK'"></span> / 
                                <span class="text-red-700" x-text="formatNum(totals.rework_scrapped) + ' Scrap'"></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <button type="button" x-show="availableDefectsForRework > 0"
                                onclick="document.getElementById('modalReject').close(); document.getElementById('modalIssueRework').showModal();"
                                class="px-3 py-1.5 bg-yellow-500 hover:bg-yellow-400 active:bg-yellow-600 text-white font-black text-xs rounded-xl shadow-xs transition uppercase tracking-wider cursor-pointer">
                            Issue to Rework (<span x-text="availableDefectsForRework"></span> Pcs)
                        </button>
                        <button type="button" x-show="reworkPending > 0"
                                onclick="document.getElementById('modalReject').close(); document.getElementById('modalCompleteRework').showModal();"
                                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-black text-xs rounded-xl shadow-xs transition uppercase tracking-wider cursor-pointer">
                            Complete Outcome (<span x-text="reworkPending"></span> Pcs)
                        </button>
                    </div>
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

    {{-- Modal 4A: Cycle 1 — Issue Defects to Rework Bench --}}
    <dialog id="modalIssueRework" class="rounded-2xl p-0 shadow-2xl border-0 w-full max-w-md backdrop:bg-gray-900/60 bg-transparent">
        <div class="bg-white rounded-2xl overflow-hidden shadow-2xl">
            <div class="bg-yellow-500 px-6 py-4 flex justify-between items-center text-white">
                <h3 class="text-xl font-black">Issue to Rework Bench</h3>
                <button type="button" onclick="document.getElementById('modalIssueRework').close()" class="text-yellow-200 hover:text-white text-2xl font-bold">&times;</button>
            </div>

            <form action="{{ route('app.sp-sessions.add-rework', $session->id) }}" method="POST"
                  @submit.prevent="submitForm($event, 'rework')"
                  x-data="{ issue_qty: 0 }"
                  class="p-6">
                @csrf

                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-2xl flex items-center justify-between gap-3 text-xs font-bold text-yellow-900">
                    <div>
                        <span class="block text-[10px] text-yellow-700 uppercase font-black">Unsorted Defect Stock</span>
                        <span class="text-xl font-black" x-text="formatNum(availableDefectsForRework) + ' Pcs'"></span>
                    </div>
                    <button type="button" @click="issue_qty = availableDefectsForRework"
                            class="px-3.5 py-2 bg-yellow-600 hover:bg-yellow-500 active:bg-yellow-700 text-white font-black text-xs rounded-xl shadow-xs transition cursor-pointer">
                        ISSUE ALL
                    </button>
                </div>

                <div class="mb-6">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-black text-slate-600 uppercase tracking-wider">Rework Quantity</label>
                        <span class="text-xs font-bold text-amber-700" x-text="issue_qty + ' Pcs'"></span>
                    </div>
                    <div class="flex items-center gap-2 min-w-0">
                        <button type="button" @click="issue_qty = Math.max(0, issue_qty - 1)" class="w-12 sm:w-14 h-14 rounded-2xl bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 font-black text-3xl flex items-center justify-center transition cursor-pointer border border-slate-300 flex-shrink-0">-</button>
                        <input type="number" name="issue_qty" x-model.number="issue_qty" :max="availableDefectsForRework" @focus="$event.target.select()" required
                               class="flex-1 min-w-0 w-full h-14 text-center text-3xl font-black text-slate-950 border-2 border-amber-300 rounded-2xl bg-amber-50/40 focus:bg-white focus:ring-2 focus:ring-amber-500">
                        <button type="button" @click="if (issue_qty < availableDefectsForRework) issue_qty += 1" class="w-12 sm:w-14 h-14 rounded-2xl bg-yellow-500 hover:bg-yellow-400 active:bg-yellow-600 text-white font-black text-3xl flex items-center justify-center transition cursor-pointer flex-shrink-0">+</button>
                    </div>
                </div>

                <div x-show="issue_qty > availableDefectsForRework" class="mb-4 p-3 bg-red-100 border border-red-300 rounded-2xl text-xs font-bold text-red-700 text-center">
                    Cannot issue more than available defect stock (<span x-text="availableDefectsForRework"></span> Pcs).
                </div>

                <div>
                    <button type="submit"
                            :disabled="issue_qty <= 0 || issue_qty > availableDefectsForRework"
                            :class="issue_qty <= 0 || issue_qty > availableDefectsForRework ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-yellow-500 hover:bg-yellow-400 active:bg-yellow-600 shadow-xl'"
                            class="w-full text-white py-4 rounded-2xl text-lg font-black uppercase tracking-wider transition cursor-pointer flex items-center justify-center gap-2">
                        ISSUE TO REWORK BENCH
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    {{-- Modal 4B: Cycle 2 — Complete Rework Outcome --}}
    <dialog id="modalCompleteRework" class="rounded-2xl p-0 shadow-2xl border-0 w-full max-w-lg backdrop:bg-gray-900/60 bg-transparent">
        <div class="bg-white rounded-2xl overflow-hidden shadow-2xl">
            <div class="bg-emerald-600 px-6 py-4 flex justify-between items-center text-white">
                <h3 class="text-xl font-black">Complete Rework Outcome</h3>
                <button type="button" onclick="document.getElementById('modalCompleteRework').close()" class="text-emerald-200 hover:text-white text-2xl font-bold">&times;</button>
            </div>

            <form action="{{ route('app.sp-sessions.add-rework', $session->id) }}" method="POST"
                  @submit.prevent="submitForm($event, 'rework')"
                  x-data="{
                      recovered_qty: 0,
                      scrapped_qty: 0,
                      set100Recovered() {
                          this.recovered_qty = this.reworkPending;
                          this.scrapped_qty = 0;
                      },
                      set100Scrapped() {
                          this.scrapped_qty = this.reworkPending;
                          this.recovered_qty = 0;
                      }
                  }"
                  class="p-6">
                @csrf

                {{-- Active Bench Banner + 1-Tap Quick Action Pills --}}
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex flex-col gap-3 text-xs font-bold text-emerald-900">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black uppercase text-emerald-800">Active Rework Bench</span>
                        <span class="text-xl font-black text-emerald-950" x-text="formatNum(reworkPending) + ' Pcs'"></span>
                    </div>

                    <div class="flex items-center gap-2 pt-2 border-t border-emerald-200/60">
                        <span class="text-[11px] font-bold text-emerald-700">Quick Fill:</span>
                        <button type="button" @click="set100Recovered()"
                                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-black text-xs rounded-xl shadow-xs transition cursor-pointer flex-1 text-center">
                            100% RECOVERED
                        </button>
                        <button type="button" @click="set100Scrapped()"
                                class="px-3 py-1.5 bg-red-600 hover:bg-red-500 active:bg-red-700 text-white font-black text-xs rounded-xl shadow-xs transition cursor-pointer flex-1 text-center">
                            100% SCRAPPED
                        </button>
                    </div>
                </div>

                <div class="space-y-5">
                    {{-- 1. Recovered OK Pcs Stepper --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-black text-emerald-800 uppercase tracking-wider">Recovered OK</label>
                            <span class="text-xs font-bold text-emerald-600" x-text="recovered_qty + ' OK Pcs'"></span>
                        </div>
                        <div class="flex items-center gap-2 min-w-0">
                            <button type="button" @click="recovered_qty = Math.max(0, recovered_qty - 1)" class="w-12 sm:w-14 h-14 rounded-2xl bg-emerald-100 hover:bg-emerald-200 active:bg-emerald-300 text-emerald-800 font-black text-3xl flex items-center justify-center transition cursor-pointer border border-emerald-300 flex-shrink-0">-</button>
                            <input type="number" name="recovered_qty" x-model.number="recovered_qty" @focus="$event.target.select()" required
                                   class="flex-1 min-w-0 w-full h-14 text-center text-3xl font-black text-emerald-950 border-2 border-emerald-300 rounded-2xl bg-emerald-50/40 focus:bg-white focus:ring-2 focus:ring-emerald-500">
                            <button type="button" @click="if (!reworkPending || (recovered_qty + scrapped_qty) < reworkPending) recovered_qty += 1" class="w-12 sm:w-14 h-14 rounded-2xl bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-black text-3xl flex items-center justify-center transition cursor-pointer border border-emerald-300 flex-shrink-0">+</button>
                        </div>
                    </div>

                    {{-- 2. Scrapped Pcs Stepper --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-black text-red-800 uppercase tracking-wider">Scrapped</label>
                            <span class="text-xs font-bold text-red-600" x-text="scrapped_qty + ' Scrap Pcs'"></span>
                        </div>
                        <div class="flex items-center gap-2 min-w-0">
                            <button type="button" @click="scrapped_qty = Math.max(0, scrapped_qty - 1)" class="w-12 sm:w-14 h-14 rounded-2xl bg-red-100 hover:bg-red-200 active:bg-red-300 text-red-800 font-black text-3xl flex items-center justify-center transition cursor-pointer border border-red-300 flex-shrink-0">-</button>
                            <input type="number" name="scrapped_qty" x-model.number="scrapped_qty" @focus="$event.target.select()" required
                                   class="flex-1 min-w-0 w-full h-14 text-center text-3xl font-black text-red-950 border-2 border-red-300 rounded-2xl bg-red-50/40 focus:bg-white focus:ring-2 focus:ring-red-500">
                            <button type="button" @click="if (!reworkPending || (recovered_qty + scrapped_qty) < reworkPending) scrapped_qty += 1" class="w-12 sm:w-14 h-14 rounded-2xl bg-red-600 hover:bg-red-500 active:bg-red-700 text-white font-black text-3xl flex items-center justify-center transition cursor-pointer border border-red-300 flex-shrink-0">+</button>
                        </div>
                    </div>
                </div>

                {{-- Validation Warnings --}}
                <div x-show="(recovered_qty + scrapped_qty) > reworkPending && reworkPending > 0" class="mt-4 p-3 bg-red-100 border border-red-300 rounded-2xl text-xs font-bold text-red-700 text-center">
                    Warning: Total Outcome (<span x-text="recovered_qty + scrapped_qty"></span> Pcs) cannot exceed Rework Bench WIP (<span x-text="reworkPending"></span> Pcs).
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100">
                    <button type="submit"
                            :disabled="(recovered_qty + scrapped_qty) <= 0 || ((recovered_qty + scrapped_qty) > reworkPending && reworkPending > 0)"
                            :class="(recovered_qty + scrapped_qty) <= 0 || ((recovered_qty + scrapped_qty) > reworkPending && reworkPending > 0) ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 shadow-xl'"
                            class="w-full text-white py-4 rounded-2xl text-lg font-black uppercase tracking-wider transition cursor-pointer flex items-center justify-center gap-2">
                        SAVE REWORK OUTCOME
                    </button>
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

    {{-- Modal 6: Line Team Roster --}}
    <dialog id="modalTeamRoster" class="rounded-2xl p-0 shadow-2xl border-0 w-full max-w-xl backdrop:bg-gray-900/60 bg-transparent">
        <div class="bg-white rounded-2xl overflow-hidden shadow-2xl flex flex-col max-h-[85vh]">
            <div class="bg-purple-700 px-6 py-4 flex justify-between items-center text-white flex-shrink-0">
                <div>
                    <h3 class="text-xl font-black flex items-center gap-2">Active Line Team</h3>
                    <p class="text-xs text-purple-200 font-medium mt-0.5" x-text="manpowerEntries.length + ' Worker(s) assigned to {{ $session->unit_line }} Shift {{ $session->shift }}'"></p>
                </div>
                <button type="button" onclick="document.getElementById('modalTeamRoster').close()" class="text-purple-200 hover:text-white text-2xl font-bold">&times;</button>
            </div>

            <div class="p-6 flex-1 overflow-y-auto space-y-3">
                <template x-if="manpowerEntries.length === 0">
                    <div class="text-center py-8 text-gray-400">
                        <p class="text-base font-bold text-gray-500">No team members assigned yet</p>
                        <p class="text-xs font-medium text-gray-400 mt-1">Add operators, quality inspectors, or helpers assigned to this line shift.</p>
                    </div>
                </template>

                <template x-for="m in manpowerEntries" :key="m.id">
                    <div class="flex items-center justify-between p-3.5 bg-purple-50/60 border border-purple-200/80 rounded-2xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-purple-600 text-white font-black text-sm flex items-center justify-center shadow-xs"
                                 x-text="(m.operator_name || 'W').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()"></div>
                            <div>
                                <h4 class="font-black text-purple-950 text-sm" x-text="m.operator_name"></h4>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="px-2 py-0.5 bg-purple-200/80 text-purple-900 text-[10px] font-black uppercase rounded-md" x-text="m.role || 'Member'"></span>
                                    <span x-show="m.employee_no" class="text-[11px] font-mono text-purple-700" x-text="'#' + m.employee_no"></span>
                                </div>
                            </div>
                        </div>
                        @if($session->status === 'running')
                            <button type="button" @click="removeManpower(m.id)" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 font-bold text-xs rounded-xl border border-red-200 transition cursor-pointer">
                                Remove
                            </button>
                        @endif
                    </div>
                </template>
            </div>

            @if($session->status === 'running')
                <div class="p-4 bg-gray-50 border-t border-gray-100 flex-shrink-0">
                    <button type="button" onclick="document.getElementById('modalTeamRoster').close(); document.getElementById('modalManpower').showModal();"
                            class="w-full bg-purple-600 hover:bg-purple-500 active:bg-purple-700 text-white py-3.5 rounded-xl font-black text-sm uppercase tracking-wider shadow-md transition">
                        + ADD TEAM MEMBER
                    </button>
                </div>
            @endif
        </div>
    </dialog>

    {{-- Modal 7: Manage Line Team / Manpower --}}
    <dialog id="modalManpower" class="rounded-xl p-0 shadow-2xl border-0 w-full max-w-lg backdrop:bg-gray-900/50 bg-transparent">
        <div class="bg-white rounded-xl overflow-hidden shadow-2xl">
            <div class="bg-purple-600 px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-black text-white">+ Add Line Team Member</h3>
                <button type="button" onclick="document.getElementById('modalManpower').close()" class="text-purple-200 hover:text-white text-xl font-bold">&times;</button>
            </div>
            <form action="{{ route('app.sp-sessions.add-manpower', $session->id) }}" method="POST" @submit.prevent="submitForm($event, 'manpower')" class="p-6">
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

    {{-- Modal 8: Resume Downtime (Reason Selection) --}}
    <dialog id="modalResumeDowntime" class="rounded-2xl p-0 shadow-2xl border-0 w-full max-w-xl backdrop:bg-gray-900/60 bg-transparent">
        <div class="bg-white rounded-2xl overflow-hidden shadow-2xl">
            <div class="bg-amber-600 px-6 py-4 flex justify-between items-center text-white">
                <div>
                    <h3 class="text-xl font-black flex items-center gap-2">Resume Line Production</h3>
                    <p class="text-xs text-amber-100 font-medium mt-0.5" x-text="'Line stopped for ' + pausedDurationMinutes + ' Minute(s). Please select Downtime Reason to resume.'"></p>
                </div>
                <button type="button" onclick="document.getElementById('modalResumeDowntime').close()" class="text-amber-200 hover:text-white text-2xl font-bold">&times;</button>
            </div>

            <form @submit.prevent="submitResumeForm($event)" x-data="{ selectedReason: '' }" class="p-6">
                @csrf
                <input type="hidden" name="reason" :value="selectedReason">

                <div class="space-y-6">
                    <div>
                        <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-2">Select Downtime Reason *</label>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach($downtimeReasons as $r)
                                <button type="button" @click="selectedReason = '{{ $r }}'"
                                        :class="selectedReason === '{{ $r }}' ? 'bg-amber-600 text-white ring-4 ring-amber-200' : 'bg-gray-100 text-gray-800 hover:bg-gray-200'"
                                        class="py-3.5 px-3 rounded-xl font-bold text-center transition shadow-xs text-xs">
                                    {{ $r }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1">Optional Remarks / Action Taken</label>
                        <input type="text" name="remarks" placeholder="Countermeasure or breakdown details..." class="w-full border-gray-300 rounded-xl text-sm p-3 bg-gray-50 focus:bg-white">
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100">
                    <button type="submit" :disabled="!selectedReason" :class="!selectedReason ? 'opacity-50 cursor-not-allowed' : ''"
                            class="w-full bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white py-4 rounded-xl text-lg font-black shadow-lg transition uppercase tracking-wider">
                        RESUME PRODUCTION & SAVE DOWNTIME
                    </button>
                </div>
            </form>
        </div>
    </dialog>
    </div> <!-- End of x-data="productionSession()" -->
</x-operator-layout>
