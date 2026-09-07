<div class="min-h-screen bg-slate-950 text-slate-100 font-sans antialiased pb-16 selection:bg-blue-600 selection:text-white"
     @if($autoRefreshInterval > 0) wire:poll.{{ $autoRefreshInterval }}s @endif>

    <!-- Top Executive Banner & Navigation -->
    <header class="border-b border-slate-800 bg-slate-900/90 backdrop-blur-md sticky top-0 z-40 shadow-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex flex-col md:flex-row justify-between items-center gap-4">
            
            <!-- Branding & Title -->
            <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-start">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 via-indigo-500 to-cyan-400 p-0.5 shadow-lg shadow-blue-500/20 flex items-center justify-center">
                        <div class="w-full h-full bg-slate-950 rounded-[10px] flex items-center justify-center">
                            <span class="text-lg font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-300">DM</span>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-base sm:text-lg font-extrabold tracking-tight text-white flex items-center gap-2">
                                Material FIFO Executive Dashboard
                            </h1>
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 animate-pulse">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                LIVE
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 font-medium">
                            Monitoring Kepatuhan FIFO & Flow Material Gudang &bull; <span class="text-cyan-400 font-semibold">{{ $whseLabel }}</span>
                        </p>
                    </div>
                </div>

                <!-- Quick Return Link for Storekeepers -->
                <a href="{{ route('mwh.stock-card.index') }}" class="md:hidden text-xs text-slate-400 hover:text-white flex items-center gap-1 font-semibold bg-slate-800 px-2.5 py-1.5 rounded-lg border border-slate-700">
                    Stock Card &rarr;
                </a>
            </div>

            <!-- Controls (Warehouse Switcher, Date Presets, Auto-refresh) -->
            <div class="flex items-center gap-2 sm:gap-3 flex-wrap justify-end w-full md:w-auto">
                
                <!-- Warehouse Selector Pills -->
                <div class="inline-flex p-1 bg-slate-950 rounded-xl border border-slate-800 shadow-inner">
                    <button wire:click="setWarehouse('ALL')" 
                            class="px-3 py-1 text-xs font-bold rounded-lg transition-all {{ $whse_id === 'ALL' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-400 hover:text-white' }}">
                        ALL
                    </button>
                    @foreach($warehouses as $w)
                        <button wire:click="setWarehouse({{ $w['id'] }})" 
                                class="px-3 py-1 text-xs font-bold rounded-lg transition-all {{ (string)$whse_id === (string)$w['id'] ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-400 hover:text-white' }}">
                            {{ $w['whse_code'] }}
                        </button>
                    @endforeach
                </div>

                <!-- Date Range Preset Pills -->
                <div class="inline-flex p-1 bg-slate-950 rounded-xl border border-slate-800 shadow-inner">
                    <button wire:click="setPreset('today')" class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition-all {{ $preset === 'today' ? 'bg-slate-800 text-cyan-400 border border-slate-700' : 'text-slate-400 hover:text-white' }}">
                        Hari Ini
                    </button>
                    <button wire:click="setPreset('7_days')" class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition-all {{ $preset === '7_days' ? 'bg-slate-800 text-cyan-400 border border-slate-700' : 'text-slate-400 hover:text-white' }}">
                        7 Hari
                    </button>
                    <button wire:click="setPreset('30_days')" class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition-all {{ $preset === '30_days' ? 'bg-slate-800 text-cyan-400 border border-slate-700' : 'text-slate-400 hover:text-white' }}">
                        30 Hari
                    </button>
                    <button wire:click="setPreset('this_month')" class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition-all {{ $preset === 'this_month' ? 'bg-slate-800 text-cyan-400 border border-slate-700' : 'text-slate-400 hover:text-white' }}">
                        Bulan Ini
                    </button>
                </div>

                <!-- Auto Refresh Selector -->
                <div class="flex items-center gap-1.5 bg-slate-950 px-2.5 py-1 rounded-xl border border-slate-800 text-xs">
                    <span class="text-slate-500 font-bold uppercase text-[10px]">⏱️ Refresh:</span>
                    <select wire:model.live="autoRefreshInterval" class="bg-transparent border-0 text-cyan-400 font-extrabold text-xs focus:ring-0 cursor-pointer py-0.5 pr-6 pl-1">
                        <option value="15" class="bg-slate-900 text-white">15s</option>
                        <option value="30" class="bg-slate-900 text-white">30s</option>
                        <option value="60" class="bg-slate-900 text-white">60s</option>
                        <option value="0" class="bg-slate-900 text-white">Off</option>
                    </select>
                </div>

                <!-- Back to Stock Card (Desktop) -->
                <a href="{{ route('mwh.stock-card.index') }}" class="hidden md:inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl border border-slate-700 transition shadow-sm" title="Kembali ke Kartu Stok Material">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Stock Card
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 space-y-6">

        <!-- Top Section: Executive Health Meter & Key Metric Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
            
            <!-- 1. FIFO Compliance Health Card -->
            <div class="relative overflow-hidden bg-gradient-to-b from-slate-900 to-slate-900/90 rounded-2xl border {{ $kpis['compliance_rate'] >= 95 ? 'border-emerald-500/30' : ($kpis['compliance_rate'] >= 85 ? 'border-amber-500/30' : 'border-rose-500/40') }} p-5 shadow-lg flex flex-col justify-between group">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-black uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full {{ $kpis['compliance_rate'] >= 95 ? 'bg-emerald-400' : ($kpis['compliance_rate'] >= 85 ? 'bg-amber-400' : 'bg-rose-500') }}"></span>
                        FIFO Compliance Rate
                    </span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-black tracking-widest uppercase {{ $kpis['compliance_rate'] >= 95 ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : ($kpis['compliance_rate'] >= 85 ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-rose-500/20 text-rose-300 border border-rose-500/30') }}">
                        {{ $kpis['grade'] }}
                    </span>
                </div>

                <div class="my-3 flex items-baseline gap-2">
                    <span class="text-4xl sm:text-5xl font-black tracking-tight {{ $kpis['compliance_rate'] >= 95 ? 'text-emerald-400' : ($kpis['compliance_rate'] >= 85 ? 'text-amber-400' : 'text-rose-400') }}">
                        {{ number_format($kpis['compliance_rate'], 1) }}%
                    </span>
                    <span class="text-xs text-slate-500 font-semibold">Strict Adherence</span>
                </div>

                <!-- Mini Compliance Progress Bar -->
                <div class="w-full bg-slate-950 rounded-full h-2 overflow-hidden border border-slate-800">
                    <div class="h-full rounded-full transition-all duration-700 {{ $kpis['compliance_rate'] >= 95 ? 'bg-gradient-to-r from-emerald-500 to-cyan-400' : ($kpis['compliance_rate'] >= 85 ? 'bg-gradient-to-r from-amber-500 to-yellow-400' : 'bg-gradient-to-r from-rose-600 to-red-400') }}"
                         style="width: {{ min(100, $kpis['compliance_rate']) }}%"></div>
                </div>

                <div class="mt-3 flex justify-between text-[11px] text-slate-400 font-medium">
                    <span>Sesuai: <strong class="text-white">{{ $kpis['compliant_count'] }}</strong> pick</span>
                    <span>Pelanggaran: <strong class="{{ $kpis['deviation_count'] > 0 ? 'text-rose-400 font-bold' : 'text-slate-400' }}">{{ $kpis['deviation_count'] }}</strong></span>
                </div>
            </div>

            <!-- 2. Total Active Warehouse Inventory -->
            <div class="bg-gradient-to-b from-slate-900 to-slate-900/90 rounded-2xl border border-slate-800 p-5 shadow-lg flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-black uppercase tracking-wider text-slate-400">Total Stok Aktif</span>
                    <div class="p-1.5 rounded-lg bg-blue-500/10 text-blue-400 border border-blue-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                </div>

                <div class="my-3 flex items-baseline gap-1.5">
                    <span class="text-3xl sm:text-4xl font-black text-white tracking-tight">
                        {{ number_format($kpis['total_active_stock_kg'], 0) }}
                    </span>
                    <span class="text-xs font-black text-blue-400">KG</span>
                </div>

                <div class="flex items-center justify-between text-[11px] text-slate-400 pt-2 border-t border-slate-800/80">
                    <span>Total Pallet: <strong class="text-slate-200">{{ number_format($kpis['total_active_pallets']) }}</strong> Pallet</span>
                    <span class="text-cyan-400 font-semibold">{{ $agingSummary['tiers']['fresh']['percent'] }}% Fresh</span>
                </div>
            </div>

            <!-- 3. Turnaround Lead Time (Avg Days to Consume) -->
            <div class="bg-gradient-to-b from-slate-900 to-slate-900/90 rounded-2xl border border-slate-800 p-5 shadow-lg flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-black uppercase tracking-wider text-slate-400">Kecepatan Konsumsi (Lead Time)</span>
                    <div class="p-1.5 rounded-lg bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>

                <div class="my-3 flex items-baseline gap-1.5">
                    <span class="text-3xl sm:text-4xl font-black text-indigo-300 tracking-tight">
                        {{ $kpis['avg_lead_days'] }}
                    </span>
                    <span class="text-xs font-black text-slate-400">Hari Rata-rata</span>
                </div>

                <div class="flex items-center justify-between text-[11px] text-slate-400 pt-2 border-t border-slate-800/80">
                    <span>Total Pengambilan:</span>
                    <strong class="text-slate-200">{{ number_format($kpis['total_outgoing_qty'], 0) }} KG</strong>
                </div>
            </div>

            <!-- 4. Overaged & Stagnant Inventory Warning (>60 Days) -->
            <div class="bg-gradient-to-b from-slate-900 to-slate-900/90 rounded-2xl border {{ $kpis['overaged_stock_kg'] > 0 ? 'border-amber-500/40 bg-amber-950/10' : 'border-slate-800' }} p-5 shadow-lg flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-black uppercase tracking-wider text-slate-400">Stok Mengendap (>60 Hari)</span>
                    <div class="p-1.5 rounded-lg {{ $kpis['overaged_stock_kg'] > 0 ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-slate-800 text-slate-400' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                </div>

                <div class="my-3 flex items-baseline gap-1.5">
                    <span class="text-3xl sm:text-4xl font-black {{ $kpis['overaged_stock_kg'] > 0 ? 'text-amber-400' : 'text-slate-200' }} tracking-tight">
                        {{ number_format($kpis['overaged_stock_kg'], 0) }}
                    </span>
                    <span class="text-xs font-black text-slate-400">KG</span>
                </div>

                <div class="flex items-center justify-between text-[11px] text-slate-400 pt-2 border-t border-slate-800/80">
                    <span>Pallet Mengendap: <strong class="{{ $kpis['overaged_pallets_count'] > 0 ? 'text-amber-300 font-bold' : 'text-slate-300' }}">{{ $kpis['overaged_pallets_count'] }}</strong></span>
                    <span>QC Hold: <strong class="text-slate-300">{{ $kpis['qc_hold_pallets_count'] }} Plt</strong></span>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs Bar -->
        <div class="flex items-center justify-between border-b border-slate-800 gap-4 overflow-x-auto pb-1">
            <nav class="flex space-x-2" aria-label="Tabs">
                <button wire:click="setActiveTab('overview')"
                        class="px-4 py-2.5 text-xs font-black rounded-xl transition-all flex items-center gap-2 border {{ $activeTab === 'overview' ? 'bg-blue-600 text-white border-blue-500 shadow-md shadow-blue-600/30' : 'text-slate-400 hover:text-white bg-slate-900 border-slate-800 hover:border-slate-700' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Ringkasan & Throughput Flow
                </button>

                <button wire:click="setActiveTab('deviations')"
                        class="px-4 py-2.5 text-xs font-black rounded-xl transition-all flex items-center gap-2 border relative {{ $activeTab === 'deviations' ? 'bg-blue-600 text-white border-blue-500 shadow-md shadow-blue-600/30' : 'text-slate-400 hover:text-white bg-slate-900 border-slate-800 hover:border-slate-700' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Audit Pelanggaran FIFO
                    @if(count($deviations) > 0)
                        <span class="px-1.5 py-0.2 rounded-full text-[9px] font-black bg-rose-500 text-white ml-1">
                            {{ count($deviations) }}
                        </span>
                    @endif
                </button>

                <button wire:click="setActiveTab('queue')"
                        class="px-4 py-2.5 text-xs font-black rounded-xl transition-all flex items-center gap-2 border {{ $activeTab === 'queue' ? 'bg-blue-600 text-white border-blue-500 shadow-md shadow-blue-600/30' : 'text-slate-400 hover:text-white bg-slate-900 border-slate-800 hover:border-slate-700' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    Live FIFO Priority Queue (Next Picks)
                </button>

                <button wire:click="setActiveTab('aging')"
                        class="px-4 py-2.5 text-xs font-black rounded-xl transition-all flex items-center gap-2 border {{ $activeTab === 'aging' ? 'bg-blue-600 text-white border-blue-500 shadow-md shadow-blue-600/30' : 'text-slate-400 hover:text-white bg-slate-900 border-slate-800 hover:border-slate-700' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Piramida Umur Material (Aging)
                </button>
            </nav>

            <div class="text-[11px] text-slate-500 font-semibold whitespace-nowrap">
                Periode: <span class="text-slate-300 font-bold">{{ Carbon\Carbon::parse($fromDate)->format('d M Y') }} - {{ Carbon\Carbon::parse($toDate)->format('d M Y') }}</span>
            </div>
        </div>

        <!-- TAB 1: OVERVIEW & THROUGHPUT FLOW -->
        @if($activeTab === 'overview')
            <div class="space-y-6 animate-in fade-in duration-300">
                
                <!-- Main Flow Chart & Aging Pyramid Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Throughput Flow Chart (2 Cols) -->
                    <div class="lg:col-span-2 bg-slate-900 rounded-2xl border border-slate-800 p-6 shadow-xl space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-800/80 pb-3">
                            <div>
                                <h3 class="text-sm font-extrabold text-white uppercase tracking-wider flex items-center gap-2">
                                    <span>📈</span> Tren Keluar-Masuk Material (Throughput Flow)
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Perbandingan volume penerimaan (Incoming) vs pemakaian produksi (Outgoing) dalam KG</p>
                            </div>
                            <div class="flex items-center gap-4 text-xs font-bold">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-3 h-3 rounded bg-cyan-400"></span>
                                    <span class="text-slate-300">Incoming (KG)</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-3 h-3 rounded bg-indigo-500"></span>
                                    <span class="text-slate-300">Outgoing (KG)</span>
                                </div>
                            </div>
                        </div>

                        <div class="relative h-72 w-full"
                             x-data="{
                                 chart: null,
                                 initChart() {
                                     if (typeof Chart === 'undefined') {
                                         setTimeout(() => this.initChart(), 100);
                                         return;
                                     }
                                     const canvas = this.$refs.chartCanvas;
                                     if (!canvas) return;
                                     if (this.chart) {
                                         this.chart.destroy();
                                         this.chart = null;
                                     }
                                     const rawData = {{ \Illuminate\Support\Js::from($throughputTrend) }};
                                     const ctx = canvas.getContext('2d');
                                     this.chart = new Chart(ctx, {
                                         type: 'bar',
                                         data: {
                                             labels: rawData.labels || [],
                                             datasets: [
                                                 {
                                                     label: 'Incoming (KG)',
                                                     data: rawData.incoming || [],
                                                     backgroundColor: 'rgba(34, 211, 238, 0.75)',
                                                     borderColor: 'rgba(34, 211, 238, 1)',
                                                     borderWidth: 1,
                                                     borderRadius: 6,
                                                     barPercentage: 0.6,
                                                 },
                                                 {
                                                     label: 'Outgoing (KG)',
                                                     data: rawData.outgoing || [],
                                                     backgroundColor: 'rgba(99, 102, 241, 0.75)',
                                                     borderColor: 'rgba(99, 102, 241, 1)',
                                                     borderWidth: 1,
                                                     borderRadius: 6,
                                                     barPercentage: 0.6,
                                                 }
                                             ]
                                         },
                                         options: {
                                             responsive: true,
                                             maintainAspectRatio: false,
                                             interaction: {
                                                 mode: 'index',
                                                 intersect: false,
                                             },
                                             plugins: {
                                                 legend: {
                                                     display: false
                                                 },
                                                 tooltip: {
                                                     backgroundColor: '#0f172a',
                                                     titleColor: '#ffffff',
                                                     bodyColor: '#cbd5e1',
                                                     borderColor: '#334155',
                                                     borderWidth: 1,
                                                     padding: 10,
                                                     boxPadding: 4,
                                                     usePointStyle: true,
                                                 }
                                             },
                                             scales: {
                                                 x: {
                                                     grid: {
                                                         color: 'rgba(51, 65, 85, 0.3)',
                                                         drawBorder: false,
                                                     },
                                                     ticks: {
                                                         color: '#94a3b8',
                                                         font: {
                                                             size: 10,
                                                             weight: 'bold'
                                                         }
                                                     }
                                                 },
                                                 y: {
                                                     grid: {
                                                         color: 'rgba(51, 65, 85, 0.3)',
                                                         drawBorder: false,
                                                     },
                                                     ticks: {
                                                         color: '#94a3b8',
                                                         font: {
                                                             size: 10,
                                                             weight: 'bold'
                                                         },
                                                         callback: function(value) {
                                                             return value >= 1000 ? (value / 1000) + 'k' : value;
                                                         }
                                                     }
                                                 }
                                             }
                                         }
                                     });
                                 }
                             }"
                             x-init="$nextTick(() => initChart())"
                        >
                            <canvas x-ref="chartCanvas" id="fifoThroughputChart"></canvas>
                        </div>
                    </div>

                    <!-- Aging Distribution Snapshot (1 Col) -->
                    <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6 shadow-xl space-y-4 flex flex-col justify-between">
                        <div class="border-b border-slate-800/80 pb-3">
                            <h3 class="text-sm font-extrabold text-white uppercase tracking-wider flex items-center gap-2">
                                <span>⏳</span> Distribusi Umur Stok Material
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">Persentase stok aktif berdasarkan lama tersimpan di rak</p>
                        </div>

                        <!-- Aging Tier Bars -->
                        <div class="space-y-3.5 py-1">
                            <!-- Tier 1: 0-30 Days -->
                            <div>
                                <div class="flex justify-between text-xs font-bold mb-1">
                                    <span class="text-emerald-400">0 - 30 Hari (Fresh)</span>
                                    <span class="text-white">{{ number_format($agingSummary['tiers']['fresh']['qty']) }} KG ({{ $agingSummary['tiers']['fresh']['percent'] }}%)</span>
                                </div>
                                <div class="w-full bg-slate-950 h-2.5 rounded-full overflow-hidden border border-slate-800">
                                    <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $agingSummary['tiers']['fresh']['percent'] }}%"></div>
                                </div>
                            </div>

                            <!-- Tier 2: 31-60 Days -->
                            <div>
                                <div class="flex justify-between text-xs font-bold mb-1">
                                    <span class="text-blue-400">31 - 60 Hari (Normal)</span>
                                    <span class="text-white">{{ number_format($agingSummary['tiers']['normal']['qty']) }} KG ({{ $agingSummary['tiers']['normal']['percent'] }}%)</span>
                                </div>
                                <div class="w-full bg-slate-950 h-2.5 rounded-full overflow-hidden border border-slate-800">
                                    <div class="bg-blue-500 h-full rounded-full" style="width: {{ $agingSummary['tiers']['normal']['percent'] }}%"></div>
                                </div>
                            </div>

                            <!-- Tier 3: 61-90 Days -->
                            <div>
                                <div class="flex justify-between text-xs font-bold mb-1">
                                    <span class="text-amber-400">61 - 90 Hari (Warning)</span>
                                    <span class="text-white">{{ number_format($agingSummary['tiers']['warning']['qty']) }} KG ({{ $agingSummary['tiers']['warning']['percent'] }}%)</span>
                                </div>
                                <div class="w-full bg-slate-950 h-2.5 rounded-full overflow-hidden border border-slate-800">
                                    <div class="bg-amber-500 h-full rounded-full" style="width: {{ $agingSummary['tiers']['warning']['percent'] }}%"></div>
                                </div>
                            </div>

                            <!-- Tier 4: >90 Days -->
                            <div>
                                <div class="flex justify-between text-xs font-bold mb-1">
                                    <span class="text-rose-400">> 90 Hari (Stagnant / Dead Stock)</span>
                                    <span class="text-white">{{ number_format($agingSummary['tiers']['critical']['qty']) }} KG ({{ $agingSummary['tiers']['critical']['percent'] }}%)</span>
                                </div>
                                <div class="w-full bg-slate-950 h-2.5 rounded-full overflow-hidden border border-slate-800">
                                    <div class="bg-rose-500 h-full rounded-full" style="width: {{ $agingSummary['tiers']['critical']['percent'] }}%"></div>
                                </div>
                            </div>
                        </div>

                        <button wire:click="setActiveTab('aging')" class="w-full py-2.5 bg-slate-800 hover:bg-slate-700 text-cyan-400 rounded-xl text-xs font-bold transition border border-slate-700 flex items-center justify-center gap-1.5">
                            Lihat Rincian Umur Pallet &rarr;
                        </button>
                    </div>
                </div>

            </div>
        @endif

        <!-- TAB 2: AUDIT PELANGGARAN FIFO (DEVIATIONS) -->
        @if($activeTab === 'deviations')
            <div class="space-y-4 animate-in fade-in duration-300">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-slate-900 p-4 rounded-2xl border border-slate-800 gap-3">
                    <div>
                        <h3 class="text-sm font-black text-white uppercase tracking-wider flex items-center gap-2">
                            <span>🔍</span> Log Audit Forensik Pelanggaran FIFO (Bypass Older Lot)
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Daftar transaksi di mana lot baru diambil mendahului lot lama yang belum habis</p>
                    </div>
                    <div class="text-xs font-bold text-slate-400 bg-slate-950 px-3 py-1.5 rounded-xl border border-slate-800">
                        Total Pelanggaran: <strong class="text-rose-400">{{ count($deviations) }}</strong> Transaksi
                    </div>
                </div>

                @if(count($deviations) > 0)
                    <div class="bg-slate-900 rounded-2xl border border-slate-800 overflow-hidden shadow-xl">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-slate-950 text-slate-400 font-extrabold uppercase text-[10px] tracking-wider border-b border-slate-800">
                                        <th class="px-4 py-3">Tgl Outgoing</th>
                                        <th class="px-4 py-3">Material Info</th>
                                        <th class="px-4 py-3 text-right">Qty Diambil</th>
                                        <th class="px-4 py-3">Lot Yang Diambil (Aktual)</th>
                                        <th class="px-4 py-3">Lot Lama Yang Dilompati (Harusnya Diambil)</th>
                                        <th class="px-4 py-3 text-center">Selisih Umur</th>
                                        <th class="px-4 py-3">Operator / Tujuan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800">
                                    @foreach($deviations as $d)
                                        <tr class="hover:bg-slate-800/50 transition">
                                            <td class="px-4 py-3.5 font-mono text-slate-300 font-semibold">
                                                <div>{{ $d['outgoing_date'] }}</div>
                                                <div class="text-[10px] text-slate-500">{{ $d['outgoing_code'] }}</div>
                                            </td>
                                            <td class="px-4 py-3.5">
                                                <div class="font-extrabold text-white">{{ $d['item_code'] }}</div>
                                                <div class="text-[10px] text-slate-400 truncate max-w-xs">{{ $d['item_description'] }}</div>
                                            </td>
                                            <td class="px-4 py-3.5 text-right font-black text-cyan-400">
                                                {{ number_format($d['qty_taken'], 1) }} <span class="text-[9px] text-slate-500">{{ $d['uom'] }}</span>
                                            </td>
                                            <td class="px-4 py-3.5 space-y-0.5">
                                                <div class="flex items-center gap-1.5 font-bold text-amber-300">
                                                    <span>📦 {{ $d['picked_pallet_id'] }}</span>
                                                    <span class="text-[9px] text-slate-400 font-normal">({{ $d['picked_position_code'] }})</span>
                                                </div>
                                                <div class="text-[10px] text-slate-400">
                                                    Lot: <strong>{{ $d['picked_lot_no'] }}</strong> &bull; Masuk: {{ $d['picked_arrival_date'] }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-3.5 space-y-0.5 bg-rose-950/20">
                                                <div class="flex items-center gap-1.5 font-bold text-rose-400">
                                                    <span>⚠️ {{ $d['skipped_pallet_id'] }}</span>
                                                    <span class="text-[9px] text-slate-400 font-normal">({{ $d['skipped_position_code'] }})</span>
                                                </div>
                                                <div class="text-[10px] text-slate-300">
                                                    Lot: <strong>{{ $d['skipped_lot_no'] }}</strong> &bull; Masuk: <strong>{{ $d['skipped_arrival_date'] }}</strong> (Sisa: {{ number_format($d['skipped_current_qty'], 1) }} KG)
                                                </div>
                                            </td>
                                            <td class="px-4 py-3.5 text-center">
                                                <span class="inline-block px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider {{ $d['severity'] === 'HIGH' ? 'bg-rose-600 text-white shadow-sm shadow-rose-600/30' : ($d['severity'] === 'MEDIUM' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-slate-800 text-slate-300') }}">
                                                    +{{ $d['delta_days'] }} Hari Lebih Tua
                                                </span>
                                            </td>
                                            <td class="px-4 py-3.5 text-slate-300 font-medium">
                                                <div>{{ $d['issued_to'] }}</div>
                                                <div class="text-[10px] text-slate-500">{{ $d['warehouse_name'] }}</div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="py-16 text-center bg-slate-900 rounded-2xl border border-slate-800 p-8">
                        <div class="w-16 h-16 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 flex items-center justify-center mx-auto mb-3 text-2xl">
                            ✓
                        </div>
                        <h4 class="text-base font-extrabold text-white">Tidak Ada Pelanggaran FIFO Terdeteksi</h4>
                        <p class="text-xs text-slate-400 max-w-md mx-auto mt-1">Seluruh pengambilan material pada periode yang dipilih telah mematuhi aturan FIFO secara ketat.</p>
                    </div>
                @endif
            </div>
        @endif

        <!-- TAB 3: LIVE FIFO PRIORITY QUEUE (NEXT-IN-LINE PICKS) -->
        @if($activeTab === 'queue')
            <div class="space-y-4 animate-in fade-in duration-300">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-slate-900 p-4 rounded-2xl border border-slate-800 gap-3">
                    <div>
                        <h3 class="text-sm font-black text-white uppercase tracking-wider flex items-center gap-2">
                            <span>🎯</span> Antrian Prioritas FIFO (Wajib Diambil Selanjutnya)
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Daftar rekomendasi lot teratas yang harus diambil saat permintaan material berikutnya</p>
                    </div>

                    <div class="w-full sm:w-72">
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari Kode Material / Lot / Pallet..."
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($priorityQueue as $q)
                        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-lg space-y-3.5 hover:border-slate-700 transition flex flex-col justify-between">
                            <!-- Material Header -->
                            <div class="border-b border-slate-800/80 pb-2.5 flex items-start justify-between">
                                <div>
                                    <h4 class="text-sm font-black text-white">{{ $q['item_code'] }}</h4>
                                    <p class="text-[11px] text-slate-400 truncate max-w-[200px]">{{ $q['item_description'] }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-black text-cyan-400 block">{{ number_format($q['total_stock_kg'], 1) }} {{ $q['uom'] }}</span>
                                    <span class="text-[10px] text-slate-500 font-bold">{{ $q['pallet_count'] }} Pallet</span>
                                </div>
                            </div>

                            <!-- Rank 1 Pick (MUST PICK NEXT) -->
                            <div class="bg-gradient-to-br from-slate-950 to-blue-950/20 border border-blue-500/30 rounded-xl p-3 space-y-1.5 relative overflow-hidden">
                                <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-wider">
                                    <span class="text-cyan-400 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-ping"></span>
                                        RANK 1 : NEXT PICK
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[9px] font-black {{ $q['rank_1']['age_days'] > 60 ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' }}">
                                        {{ $q['rank_1']['age_days'] }} Hari
                                    </span>
                                </div>

                                <div class="text-xs font-extrabold text-white flex items-center justify-between">
                                    <span>📦 {{ $q['rank_1']['pallet_id'] }}</span>
                                    <span class="text-cyan-300 font-mono">{{ number_format($q['rank_1']['qty'], 1) }} KG</span>
                                </div>

                                <div class="flex justify-between text-[10px] text-slate-400 font-medium">
                                    <span>Lot: <strong class="text-slate-200">{{ $q['rank_1']['lot_no'] }}</strong></span>
                                    <span>Slot: <strong class="text-amber-300">{{ $q['rank_1']['position_code'] }}</strong></span>
                                </div>
                            </div>

                            <!-- Next In Line (Rank 2 & 3) -->
                            @if(!empty($q['next_in_line']))
                                <div class="space-y-1 text-[10px] border-t border-slate-800/80 pt-2">
                                    <span class="text-slate-500 font-bold uppercase tracking-wider text-[9px] block">Berikutnya dalam Antrian:</span>
                                    @foreach($q['next_in_line'] as $idx => $nxt)
                                        <div class="flex justify-between items-center text-slate-400 py-0.5 font-medium">
                                            <span>#{{ $idx + 2 }}. {{ $nxt['pallet_id'] }} ({{ $nxt['position_code'] }})</span>
                                            <span class="text-slate-300 font-bold">{{ number_format($nxt['qty'], 1) }} KG &bull; {{ $nxt['age_days'] }}d</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="col-span-3 py-12 text-center text-slate-500 text-xs font-semibold italic">
                            Tidak ada material aktif yang sesuai dengan pencarian.
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        <!-- TAB 4: PIRAMIDA UMUR MATERIAL (AGING DETAILS) -->
        @if($activeTab === 'aging')
            <div class="space-y-6 animate-in fade-in duration-300">
                <!-- Top Aging Overview Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-slate-900 rounded-2xl border border-emerald-500/30 p-5 space-y-2">
                        <span class="text-[10px] font-black text-emerald-400 uppercase tracking-wider block">0 - 30 Hari (Fresh)</span>
                        <div class="text-2xl sm:text-3xl font-black text-white">{{ number_format($agingSummary['tiers']['fresh']['qty']) }} <span class="text-xs text-slate-500">KG</span></div>
                        <p class="text-[11px] text-slate-400">{{ $agingSummary['tiers']['fresh']['count'] }} Pallet ({{ $agingSummary['tiers']['fresh']['percent'] }}% dari total)</p>
                    </div>

                    <div class="bg-slate-900 rounded-2xl border border-blue-500/30 p-5 space-y-2">
                        <span class="text-[10px] font-black text-blue-400 uppercase tracking-wider block">31 - 60 Hari (Normal)</span>
                        <div class="text-2xl sm:text-3xl font-black text-white">{{ number_format($agingSummary['tiers']['normal']['qty']) }} <span class="text-xs text-slate-500">KG</span></div>
                        <p class="text-[11px] text-slate-400">{{ $agingSummary['tiers']['normal']['count'] }} Pallet ({{ $agingSummary['tiers']['normal']['percent'] }}% dari total)</p>
                    </div>

                    <div class="bg-slate-900 rounded-2xl border border-amber-500/30 p-5 space-y-2">
                        <span class="text-[10px] font-black text-amber-400 uppercase tracking-wider block">61 - 90 Hari (Warning)</span>
                        <div class="text-2xl sm:text-3xl font-black text-white">{{ number_format($agingSummary['tiers']['warning']['qty']) }} <span class="text-xs text-slate-500">KG</span></div>
                        <p class="text-[11px] text-slate-400">{{ $agingSummary['tiers']['warning']['count'] }} Pallet ({{ $agingSummary['tiers']['warning']['percent'] }}% dari total)</p>
                    </div>

                    <div class="bg-slate-900 rounded-2xl border border-rose-500/40 p-5 space-y-2">
                        <span class="text-[10px] font-black text-rose-400 uppercase tracking-wider block">> 90 Hari (Critical)</span>
                        <div class="text-2xl sm:text-3xl font-black text-rose-400">{{ number_format($agingSummary['tiers']['critical']['qty']) }} <span class="text-xs text-slate-500">KG</span></div>
                        <p class="text-[11px] text-slate-400">{{ $agingSummary['tiers']['critical']['count'] }} Pallet ({{ $agingSummary['tiers']['critical']['percent'] }}% dari total)</p>
                    </div>
                </div>

                <!-- Top Oldest Stagnant Pallets Table -->
                <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6 shadow-xl space-y-4">
                    <div class="border-b border-slate-800/80 pb-3 flex justify-between items-center">
                        <div>
                            <h3 class="text-sm font-extrabold text-white uppercase tracking-wider flex items-center gap-2">
                                <span>🚨</span> Pallet Paling Lama Mengendap di Gudang (Oldest Stagnant Stock)
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">Perlu perhatian prioritas agar material segera dijadwalkan untuk diproduksi</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-950 text-slate-400 font-extrabold uppercase text-[10px] tracking-wider border-b border-slate-800">
                                    <th class="px-4 py-3">Pallet ID</th>
                                    <th class="px-4 py-3">Kode & Nama Material</th>
                                    <th class="px-4 py-3">Lot No</th>
                                    <th class="px-4 py-3">Posisi Rak</th>
                                    <th class="px-4 py-3">Tgl Masuk</th>
                                    <th class="px-4 py-3 text-right">Sisa Stok</th>
                                    <th class="px-4 py-3 text-center">Umur (Hari)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800">
                                @foreach($agingSummary['oldest_pallets'] as $old)
                                    <tr class="hover:bg-slate-800/50 transition">
                                        <td class="px-4 py-3 font-mono font-bold text-white">📦 {{ $old['pallet_id'] }}</td>
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-slate-200">{{ $old['item_code'] }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $old['item_description'] }}</div>
                                        </td>
                                        <td class="px-4 py-3 font-mono text-slate-300">{{ $old['lot_no'] }}</td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-0.5 rounded bg-slate-950 text-cyan-400 font-mono font-bold border border-slate-800">
                                                {{ $old['position_code'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-400">{{ $old['arrival_date'] }}</td>
                                        <td class="px-4 py-3 text-right font-black text-white">
                                            {{ number_format($old['current_qty'], 1) }} <span class="text-[9px] text-slate-500">{{ $old['uom'] }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-block px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider {{ $old['age_days'] > 90 ? 'bg-rose-600 text-white shadow-sm shadow-rose-600/30' : ($old['age_days'] > 60 ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-blue-500/20 text-blue-300 border border-blue-500/30') }}">
                                                {{ $old['age_days'] }} Hari
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

    </main>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</div>
