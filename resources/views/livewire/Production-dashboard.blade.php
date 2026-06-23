<div x-data="{ showPurgingModal: false }">
    <div class="container mx-auto px-4 py-6">
        {{-- Header --}}
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Production Dashboard</h1>
                <p class="text-gray-600 mt-1">Monitor production performance and NG rates</p>
            </div>
        </div>

        {{-- Filters Section --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                {{-- View Type --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">View Type</label>
                    <select wire:model.live="viewType" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="monthly">Monthly</option>
                        <option value="weekly">Weekly</option>
                    </select>
                </div>

                {{-- Year --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                    <select wire:model.live="year" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Month --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                    <select wire:model.live="month" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach($months as $key => $monthName)
                            <option value="{{ $key }}">{{ $monthName }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Week (only shown for weekly view) --}}
                @if($viewType === 'weekly')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Week</label>
                    <select wire:model.live="week" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach($weeks as $index => $weekData)
                            <option value="{{ $weekData['number'] }}">{{ $weekData['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Item Code - Searchable Dropdown --}}
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Item Code</label>
                    <div class="relative">
                        <input 
                            type="text" 
                            wire:model.live="itemCodeSearch"
                            wire:focus="showItemCodeDropdown = true"
                            placeholder="Search item code..."
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 pr-8"
                        >
                        
                        {{-- Clear button --}}
                        @if($itemCode)
                        <button 
                            wire:click="clearItemCode"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                        @endif

                        {{-- Dropdown List --}}
                        @if($showItemCodeDropdown && count($filteredItemCodes) > 0)
                        <div 
                            class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
                            x-data="{ open: true }"
                            @click.away="$wire.showItemCodeDropdown = false"
                        >
                            @foreach($filteredItemCodes as $code)
                            <div 
                                wire:click="selectItemCode('{{ $code }}')"
                                class="px-3 py-2 hover:bg-blue-50 cursor-pointer {{ $itemCode === $code ? 'bg-blue-100' : '' }}"
                            >
                                <span class="text-sm text-gray-800">{{ $code }}</span>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        {{-- No results --}}
                        @if($showItemCodeDropdown && empty($itemCodeSearch) === false && count($filteredItemCodes) === 0)
                        <div class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                            <div class="px-3 py-2 text-sm text-gray-500">
                                No items found
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    {{-- Selected item badge --}}
                    @if($itemCode)
                    <div class="mt-1">
                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded">
                            {{ $itemCode }}
                        </span>
                    </div>
                    @endif
                </div>

                {{-- Machine - Simple Dropdown --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Machine</label>
                    <select wire:model.live="machineUserId" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Machines</option>
                        @foreach($machines as $machine)
                            <option value="{{ $machine['id'] }}">{{ $machine['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Reset Button & Filter Info --}}
            <div class="mt-4 flex items-center gap-4">
                <button 
                    wire:click="resetFilters" 
                    class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                >
                    Reset Filters
                </button>
                
                @if($itemCode || $machineUserId)
                <div class="text-sm text-gray-500">
                    Active filters: 
                    @if($itemCode)
                        <span class="font-medium">Item: {{ $itemCode }}</span>
                    @endif
                    @if($itemCode && $machineUserId) | @endif
                    @if($machineUserId)
                        @php
                            $selectedMachine = collect($machines)->firstWhere('id', $machineUserId);
                        @endphp
                        <span class="font-medium">Machine: {{ $selectedMachine['name'] ?? $machineUserId }}</span>
                    @endif
                </div>
                @endif
            </div>
        </div>

        {{-- Summary Cards (5 cards) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
            {{-- Total Target --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Target</p>
                        <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($summary['total_target'] ?? 0) }}</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Total Actual --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Actual</p>
                        <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($summary['total_actual'] ?? 0) }}</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Achievement Rate --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Achievement Rate</p>
                        <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($summary['achievement_rate'] ?? 0, 1) }}%</p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- NG Rate --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">NG Rate</p>
                        <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($summary['ng_rate'] ?? 0, 2) }}%</p>
                        <p class="text-xs text-gray-500 mt-1">{{ number_format($summary['total_ng'] ?? 0) }} pcs</p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Total Hasil Purging --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Hasil Purging</p>
                        <p class="text-2xl font-bold text-orange-600 mt-2">{{ number_format($summary['total_purging'] ?? 0, 2) }} KG</p>
                        <button @click="showPurgingModal = true" class="text-xs text-blue-600 hover:text-blue-800 font-bold mt-2 outline-none focus:outline-none flex items-center">
                            Detail Data Purging
                            <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- ✅ NEW: Downtime & Machine Hours Cards (3-column grid) --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            {{-- Downtime Summary Card --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Downtime</p>
                        <p class="text-3xl font-bold text-orange-600 mt-2">
                            {{ number_format($downtimeAnalysis['total_downtime_hours'] ?? 0, 1) }} hrs
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ number_format($downtimeAnalysis['total_downtime_minutes'] ?? 0, 1) }} minutes
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ $downtimeAnalysis['problem_hours_count'] ?? 0 }} problem hours
                        </p>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                
                {{-- Top problem hours --}}
                @if(count($downtimeAnalysis['downtime_by_hour'] ?? []) > 0)
                <div class="border-t pt-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Worst Hours:</h3>
                    @foreach(array_slice($downtimeAnalysis['downtime_by_hour'] ?? [], 0, 5) as $hourData)
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-gray-600">{{ $hourData['hour'] }}</span>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-orange-600">
                                {{ number_format($hourData['total_downtime'], 1) }}m
                            </span>
                            <span class="text-xs text-gray-400">({{ $hourData['occurrences'] }}x)</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Downtime Distribution Chart --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Downtime Distribution by Hour</h2>
                <div class="space-y-2 max-h-80 overflow-y-auto">
                    @forelse($downtimeAnalysis['downtime_by_hour'] ?? [] as $hourData)
                        @php
                            $maxDowntime = max(array_column($downtimeAnalysis['downtime_by_hour'], 'total_downtime') ?: [1]);
                            $width = $maxDowntime > 0 ? ($hourData['total_downtime'] / $maxDowntime) * 100 : 0;
                        @endphp
                        <div class="flex items-center">
                            <span class="text-xs font-medium text-gray-600 w-16">{{ $hourData['hour'] }}</span>
                            <div class="flex-1 mx-3">
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div class="bg-orange-500 h-3 rounded-full transition-all" style="width: {{ $width }}%"></div>
                                </div>
                            </div>
                            <span class="text-xs font-medium text-orange-600 w-20 text-right">
                                {{ number_format($hourData['total_downtime'], 1) }}m
                            </span>
                            <span class="text-xs text-gray-400 w-12 text-right">({{ $hourData['occurrences'] }})</span>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <p class="text-sm">No downtime recorded</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- ✅ NEW: Machine Working Hours Card --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Working Hours per Machine</h2>
                <div class="space-y-2 max-h-80 overflow-y-auto">
                    @forelse($machineWorkingHours as $mHours)
                        @php
                            $maxHours = max(array_column($machineWorkingHours, 'hours') ?: [1]);
                            $width = $maxHours > 0 ? ($mHours['hours'] / $maxHours) * 100 : 0;
                        @endphp
                        <div class="flex items-center">
                            <span class="text-xs font-semibold text-gray-700 w-20 truncate" title="{{ $mHours['name'] }}">
                                {{ $mHours['name'] }}
                            </span>
                            <div class="flex-1 mx-3">
                                <div class="w-full bg-gray-100 rounded-full h-3">
                                    <div class="bg-blue-600 h-3 rounded-full transition-all" style="width: {{ $width }}%"></div>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-blue-600 w-16 text-right">
                                {{ number_format($mHours['hours']) }} hrs
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <p class="text-sm">No working hours recorded</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Chart & NG Breakdown Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Production Overview</h2>
                <div class="h-96" wire:ignore>
                    <canvas id="productionChart"></canvas>
                </div>
            </div>

            {{-- NG Breakdown --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">NG Breakdown</h2>
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($ngBreakdown as $ng)
                        <div class="border-l-4 border-red-500 pl-3 py-2">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700">{{ $ng['name'] }}</span>
                                <span class="text-sm font-bold text-red-600">{{ number_format($ng['total']) }}</span>
                            </div>
                            @if(($summary['total_ng'] ?? 0) > 0)
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                    <div class="bg-red-500 h-2 rounded-full" style="width: {{ ($ng['total'] / $summary['total_ng']) * 100 }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500">{{ number_format(($ng['total'] / $summary['total_ng']) * 100, 1) }}%</span>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p>No NG data available</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ✅ NEW: Top 10 Problematic Remarks Table --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Top 10 Problem Hours</h2>
            
            @if(count($topRemarks) > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Hour</th>
                            <th class="px-4 py-3">Machine</th>
                            <th class="px-4 py-3">Item</th>
                            <th class="px-4 py-3 text-right">Target</th>
                            <th class="px-4 py-3 text-right">Actual</th>
                            <th class="px-4 py-3 text-right">Gap</th>
                            <th class="px-4 py-3 text-right">Downtime</th>
                            <th class="px-4 py-3">Remark</th>
                            <th class="px-4 py-3 text-center">Severity</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($topRemarks as $index => $remark)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ \Carbon\Carbon::parse($remark['date'])->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                {{ $remark['hour'] }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $remark['machine'] }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $remark['item_code'] }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">
                                {{ number_format($remark['target']) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">
                                {{ number_format($remark['actual']) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right font-medium text-red-600">
                                {{ number_format($remark['gap']) }} pcs
                            </td>
                            <td class="px-4 py-3 text-sm text-right font-medium text-orange-600">
                                {{ number_format($remark['downtime_minutes'], 1) }}m
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate" title="{{ $remark['remark'] }}">
                                {{ $remark['remark'] }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    @if($remark['severity'] === 'critical') bg-red-100 text-red-800
                                    @elseif($remark['severity'] === 'high') bg-orange-100 text-orange-800
                                    @elseif($remark['severity'] === 'medium') bg-yellow-100 text-yellow-800
                                    @else bg-blue-100 text-blue-800
                                    @endif">
                                    {{ ucfirst($remark['severity']) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-12 text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-lg font-medium">No Problem Hours Found</p>
                <p class="text-sm mt-1">All production hours met their targets!</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Scripts untuk Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        let productionChart = null;
        let rawChartData = [];

        function initChart(data) {
            console.log('🔵 initChart called with data:', data);
            rawChartData = data;
            const ctx = document.getElementById('productionChart');
            
            if (!ctx) {
                console.error('❌ Chart canvas not found!');
                return;
            }

            if (!data || data.length === 0) {
                console.warn('⚠️ No data to display');
                return;
            }

            const labels = data.map(d => d.date);
            const targetData = data.map(d => d.target);
            const actualData = data.map(d => d.actual);
            const ngRateData = data.map(d => d.ng_rate);

            if (productionChart) {
                console.log('🔄 Updating existing chart...');
                productionChart.data.labels = labels;
                productionChart.data.datasets[0].data = targetData;
                productionChart.data.datasets[1].data = actualData;
                productionChart.data.datasets[2].data = ngRateData;
                productionChart.update('active');
                console.log('✅ Chart updated!');
                return;
            }

            console.log('🆕 Creating new chart...');
            productionChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Target',
                            data: targetData,
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1,
                            yAxisID: 'y',
                        },
                        {
                            label: 'Actual',
                            data: actualData,
                            backgroundColor: 'rgba(34, 197, 94, 0.7)',
                            borderColor: 'rgba(34, 197, 94, 1)',
                            borderWidth: 1,
                            yAxisID: 'y',
                        },
                        {
                            label: 'NG Rate (%)',
                            data: ngRateData,
                            type: 'line',
                            backgroundColor: 'rgba(239, 68, 68, 0.2)',
                            borderColor: 'rgba(239, 68, 68, 1)',
                            borderWidth: 2,
                            fill: false,
                            yAxisID: 'y1',
                            tension: 0.4,
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
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.dataset.yAxisID === 'y1') {
                                        label += context.parsed.y.toFixed(2) + '%';
                                    } else {
                                        label += context.parsed.y.toLocaleString();
                                    }
                                    return label;
                                },
                                footer: function(tooltipItems) {
                                    if (tooltipItems.length > 0) {
                                        const dataIndex = tooltipItems[0].dataIndex;
                                        const dayInfo = rawChartData[dataIndex];
                                        if (dayInfo && typeof dayInfo.working_hours !== 'undefined') {
                                            return 'Active Hours: ' + dayInfo.working_hours + ' hrs';
                                        }
                                    }
                                    return '';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Quantity (pcs)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'NG Rate (%)'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            },
                            min: 0,
                            max: 100
                        }
                    }
                }
            });
            console.log('✅ Chart created!');
        }

        document.addEventListener('DOMContentLoaded', function() {
            console.log('📄 DOM Content Loaded');
            const initialData = @json($chartData);
            console.log('📊 Initial chart data:', initialData);
            initChart(initialData);
        });

        document.addEventListener('livewire:init', () => {
            console.log('⚡ Livewire initialized, setting up event listener...');
            
            Livewire.on('chartDataUpdated', (event) => {
                console.log('🎯 chartDataUpdated event received!');
                const data = event.chartData;
                console.log('📊 Chart data extracted:', data);
                
                if (data) {
                    initChart(data);
                }
            });
        });
    </script>

    {{-- Purging Detail Modal --}}
    <div x-show="showPurgingModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showPurgingModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showPurgingModal = false">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <!-- Trick browser to center modal contents. -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showPurgingModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center pb-3 border-b mb-4">
                        <h3 class="text-xl font-bold text-gray-900">Detail Data Purging</h3>
                        <button @click="showPurgingModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto max-h-96">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DIC ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mesin</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Code</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Berat Purging (KG)</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($purgingDetails as $detail)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-blue-600">
                                            #{{ $detail['dic_id'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ \Carbon\Carbon::parse($detail['date'])->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            Shift {{ $detail['shift'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ $detail['machine_name'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-mono">
                                            {{ $detail['item_code'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-orange-600">
                                            {{ number_format($detail['resin_usage'], 2) }} KG
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500 italic">
                                            Tidak ada data purging untuk periode/filter ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t">
                    <button @click="showPurgingModal = false" type="button" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>