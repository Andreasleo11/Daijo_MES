<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('Second Process Daily Reports Analytics') }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Historical performance analysis, defect breakdown, and line comparison
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('second-process-reports.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded shadow-sm text-xs transition flex items-center gap-1">
                    Back to Reports List
                </a>
                <a href="{{ route('second-process.dashboard') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow-sm text-xs transition flex items-center gap-1">
                    Floor Live Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Filter Bar --}}
            <form method="GET" action="{{ route('second-process.report-analytics') }}" class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    {{-- Date From --}}
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">From Date</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}"
                            class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    {{-- Date To --}}
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">To Date</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}"
                            class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    {{-- Unit / Line --}}
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Unit / Line</label>
                        <select name="unit_line" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Lines</option>
                            @foreach($lines as $l)
                                <option value="{{ $l }}" {{ request('unit_line') == $l ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Shift --}}
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Shift</label>
                        <select name="shift" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Shifts</option>
                            <option value="1" {{ request('shift') == '1' ? 'selected' : '' }}>Shift 1</option>
                            <option value="2" {{ request('shift') == '2' ? 'selected' : '' }}>Shift 2</option>
                            <option value="3" {{ request('shift') == '3' ? 'selected' : '' }}>Shift 3</option>
                        </select>
                    </div>
                    {{-- Process --}}
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Process</label>
                        <select name="process_prod" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Processes</option>
                            @foreach($processes as $p)
                                <option value="{{ $p }}" {{ request('process_prod') == $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Action Buttons --}}
                    <div class="flex items-end gap-2">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded-lg text-sm shadow transition">
                            Apply Filter
                        </button>
                        <a href="{{ route('second-process.report-analytics') }}" class="w-full text-center bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-3 rounded-lg text-sm transition">
                            Reset
                        </a>
                    </div>
                </div>
            </form>

            {{-- Summary KPI Cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wide">Total Reports</div>
                        <div class="text-3xl font-black text-gray-900 mt-1">{{ number_format($summary->total_reports) }}</div>
                        <div class="text-[11px] text-gray-400 mt-0.5">Submitted & recorded</div>
                    </div>
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wide">Total Production</div>
                        <div class="text-3xl font-black text-blue-600 mt-1">{{ number_format($summary->total_output) }}</div>
                        <div class="text-[11px] text-gray-400 mt-0.5">Total units produced</div>
                    </div>
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wide">Total OK Qty</div>
                        <div class="text-3xl font-black text-green-600 mt-1">{{ number_format($summary->total_ok) }}</div>
                        <div class="text-[11px] text-gray-400 mt-0.5">Good quality output</div>
                    </div>
                    <div class="p-3 bg-green-50 text-green-600 rounded-xl">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wide">Total NG & Rate</div>
                        <div class="text-3xl font-black text-red-600 mt-1 flex items-baseline gap-2">
                            {{ number_format($summary->total_ng) }}
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $avgNgRate >= 3 ? 'bg-red-100 text-red-800' : ($avgNgRate >= 1 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                {{ $avgNgRate }}%
                            </span>
                        </div>
                        <div class="text-[11px] text-gray-400 mt-0.5">Defect count and ratio</div>
                    </div>
                    <div class="p-3 bg-red-50 text-red-600 rounded-xl">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                </div>
            </div>

            {{-- Charts Row 1: Daily Production Trend & Daily NG Rate --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Chart 1: Daily Output Stacked --}}
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-800 text-sm">Daily Production Volume (OK vs NG)</h3>
                        <span class="text-xs text-gray-400">Stacked Bar</span>
                    </div>
                    <div class="relative h-72">
                        <canvas id="chartDailyTrend"></canvas>
                    </div>
                </div>

                {{-- Chart 2: Daily NG Rate Trend --}}
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-800 text-sm">Daily NG Rate Trend (%)</h3>
                        <span class="text-xs text-gray-400">Target Line: 2%</span>
                    </div>
                    <div class="relative h-72">
                        <canvas id="chartDailyNgRate"></canvas>
                    </div>
                </div>
            </div>

            {{-- Charts Row 2: Output by Line & Top NG Categories (Pareto) --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Chart 3: Line Output Comparison --}}
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-800 text-sm">Output & Quality by Line / Area</h3>
                        <span class="text-xs text-gray-400">Grouped Bar</span>
                    </div>
                    <div class="relative h-72">
                        <canvas id="chartByLine"></canvas>
                    </div>
                </div>

                {{-- Chart 4: NG Defects Pareto --}}
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-800 text-sm">Top NG Defects (Pareto Analysis)</h3>
                        <span class="text-xs text-gray-400">Defect Qty & Cumulative %</span>
                    </div>
                    <div class="relative h-72">
                        <canvas id="chartTopNg"></canvas>
                    </div>
                </div>
            </div>

            {{-- Charts Row 3: Output by Shift & Downtime by Category --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Chart 5: Output by Shift --}}
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-800 text-sm">Production Share by Shift</h3>
                        <span class="text-xs text-gray-400">Doughnut</span>
                    </div>
                    <div class="relative h-72 flex justify-center">
                        <canvas id="chartByShift"></canvas>
                    </div>
                </div>

                {{-- Chart 6: Downtime by Category --}}
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-800 text-sm">Process Downtime (Loss Minutes)</h3>
                        <span class="text-xs text-gray-400">Horizontal Bar</span>
                    </div>
                    <div class="relative h-72">
                        <canvas id="chartDowntime"></canvas>
                    </div>
                </div>
            </div>

            {{-- Section 4: Top 5 Highest Production Parts Table --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-gray-800 text-sm">Top Production Parts in Selected Range</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Ranked by total output volume</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase">Rank</th>
                                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase">Part Number</th>
                                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase">Part Name</th>
                                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase">Customer</th>
                                <th class="px-5 py-3 text-right text-xs font-bold text-gray-500 uppercase">Total Output</th>
                                <th class="px-5 py-3 text-right text-xs font-bold text-gray-500 uppercase">OK Qty</th>
                                <th class="px-5 py-3 text-right text-xs font-bold text-gray-500 uppercase">NG Qty</th>
                                <th class="px-5 py-3 text-right text-xs font-bold text-gray-500 uppercase">NG Rate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($topProductsRaw as $index => $prod)
                                @php
                                    $pNgRate = $prod->total_output > 0 ? round(($prod->total_ng / $prod->total_output) * 100, 2) : 0;
                                @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-5 py-3 text-sm font-bold text-gray-400">#{{ $index + 1 }}</td>
                                    <td class="px-5 py-3 text-sm font-bold text-blue-600">{{ $prod->part_number }}</td>
                                    <td class="px-5 py-3 text-sm text-gray-800">{{ $prod->part_name }}</td>
                                    <td class="px-5 py-3 text-sm text-gray-500">{{ $prod->customer }}</td>
                                    <td class="px-5 py-3 text-sm text-right font-black text-gray-900">{{ number_format($prod->total_output) }}</td>
                                    <td class="px-5 py-3 text-sm text-right font-bold text-green-600">{{ number_format($prod->total_ok) }}</td>
                                    <td class="px-5 py-3 text-sm text-right font-bold text-red-600">{{ number_format($prod->total_ng) }}</td>
                                    <td class="px-5 py-3 text-sm text-right font-bold {{ $pNgRate >= 3 ? 'text-red-500' : ($pNgRate >= 1 ? 'text-yellow-600' : 'text-green-600') }}">
                                        {{ $pNgRate }}%
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-8 text-center text-gray-400 text-sm">
                                        No production data found for the selected filter range.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- Chart JS Initialization --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.Chart === 'undefined') {
                console.error('Chart.js is not loaded');
                return;
            }

            const dailyTrendData = @json($dailyTrend);
            const byLineData = @json($byLine);
            const topNgData = @json($topNg);
            const byShiftData = @json($byShift);
            const downtimeData = @json($downtime);

            // Chart 1: Daily Production Stacked Bar
            new window.Chart(document.getElementById('chartDailyTrend'), {
                type: 'bar',
                data: {
                    labels: dailyTrendData.labels,
                    datasets: [
                        {
                            label: 'OK Qty',
                            data: dailyTrendData.ok,
                            backgroundColor: '#22c55e',
                            borderRadius: 4,
                        },
                        {
                            label: 'NG Qty',
                            data: dailyTrendData.ng,
                            backgroundColor: '#ef4444',
                            borderRadius: 4,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { stacked: true },
                        y: { stacked: true, beginAtZero: true }
                    },
                    plugins: {
                        legend: { position: 'top' }
                    }
                }
            });

            // Chart 2: Daily NG Rate Line
            new window.Chart(document.getElementById('chartDailyNgRate'), {
                type: 'line',
                data: {
                    labels: dailyTrendData.labels,
                    datasets: [
                        {
                            label: 'NG Rate (%)',
                            data: dailyTrendData.ng_rate,
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: 4,
                            pointBackgroundColor: '#ef4444'
                        },
                        {
                            label: 'Target Limit (2%)',
                            data: new Array(dailyTrendData.labels.length).fill(2.0),
                            borderColor: '#f59e0b',
                            borderDash: [5, 5],
                            pointRadius: 0,
                            fill: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { callback: v => v + '%' } }
                    },
                    plugins: {
                        legend: { position: 'top' }
                    }
                }
            });

            // Chart 3: Line Output Comparison
            new window.Chart(document.getElementById('chartByLine'), {
                type: 'bar',
                data: {
                    labels: byLineData.labels,
                    datasets: [
                        {
                            label: 'OK Qty',
                            data: byLineData.ok,
                            backgroundColor: '#3b82f6',
                            borderRadius: 4,
                        },
                        {
                            label: 'NG Qty',
                            data: byLineData.ng,
                            backgroundColor: '#f97316',
                            borderRadius: 4,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    },
                    plugins: {
                        legend: { position: 'top' }
                    }
                }
            });

            // Chart 4: Top NG Defects Pareto (Bar + Cumulative Line)
            new window.Chart(document.getElementById('chartTopNg'), {
                type: 'bar',
                data: {
                    labels: topNgData.labels.length ? topNgData.labels : ['No Defects'],
                    datasets: [
                        {
                            type: 'line',
                            label: 'Cumulative %',
                            data: topNgData.cumulative_pct.length ? topNgData.cumulative_pct : [0],
                            borderColor: '#8b5cf6',
                            backgroundColor: 'transparent',
                            yAxisID: 'y1',
                            tension: 0.2,
                            pointRadius: 4
                        },
                        {
                            type: 'bar',
                            label: 'Defect Qty',
                            data: topNgData.values.length ? topNgData.values : [0],
                            backgroundColor: '#ec4899',
                            borderRadius: 4,
                            yAxisID: 'y'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            type: 'linear',
                            position: 'left',
                            beginAtZero: true,
                            title: { display: true, text: 'Defect Qty' }
                        },
                        y1: {
                            type: 'linear',
                            position: 'right',
                            beginAtZero: true,
                            max: 100,
                            grid: { drawOnChartArea: false },
                            ticks: { callback: v => v + '%' },
                            title: { display: true, text: 'Cumulative %' }
                        }
                    },
                    plugins: {
                        legend: { position: 'top' }
                    }
                }
            });

            // Chart 5: Output by Shift Doughnut
            new window.Chart(document.getElementById('chartByShift'), {
                type: 'doughnut',
                data: {
                    labels: byShiftData.labels.length ? byShiftData.labels : ['Shift 1', 'Shift 2', 'Shift 3'],
                    datasets: [{
                        data: byShiftData.output.length ? byShiftData.output : [0, 0, 0],
                        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });

            // Chart 6: Downtime by Category Horizontal Bar
            new window.Chart(document.getElementById('chartDowntime'), {
                type: 'bar',
                data: {
                    labels: downtimeData.labels.length ? downtimeData.labels : ['No Downtime'],
                    datasets: [{
                        label: 'Downtime (Minutes)',
                        data: downtimeData.minutes.length ? downtimeData.minutes : [0],
                        backgroundColor: '#64748b',
                        borderRadius: 4
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { beginAtZero: true, title: { display: true, text: 'Minutes' } }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        });
    </script>
</x-app-layout>
