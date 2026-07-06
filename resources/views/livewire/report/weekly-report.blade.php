
<div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">📊 Weekly Report</h2>
            <div class="flex gap-2">
                <button wire:click="export('excel')" 
                        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    📊 Export Excel
                </button>
                <button wire:click="export('pdf')" 
                        class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                    📄 Export PDF
                </button>
            </div>
        </div>

        {{-- Week Navigation --}}
        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <div class="flex items-center justify-between mb-4">
                <button wire:click="previousWeek" 
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    ← Previous Week
                </button>
                
                <div class="text-center">
                    <div class="text-xl font-bold">
                        {{ \Carbon\Carbon::parse($weekStart)->format('d M Y') }} 
                        - 
                        {{ \Carbon\Carbon::parse($weekEnd)->format('d M Y') }}
                    </div>
                    <button wire:click="currentWeek" 
                            class="text-sm text-blue-600 hover:underline mt-1">
                        Go to Current Week
                    </button>
                </div>
                
                <button wire:click="nextWeek" 
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Next Week →
                </button>
            </div>

            {{-- Filters --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Customer</label>
                    <select wire:model.live="customerFilter" class="w-full border rounded px-3 py-2">
                        <option value="">Semua Customer</option>
                        @foreach($customers as $customer)
                        <option value="{{ $customer }}">{{ $customer }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Shift</label>
                    <select wire:model.live="shiftFilter" class="w-full border rounded px-3 py-2">
                        <option value="">Semua Shift</option>
                        <option value="Shift 1">Shift 1</option>
                        <option value="Shift 2">Shift 2</option>
                        <option value="Shift 3">Shift 3</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-500 text-white rounded-lg shadow p-4">
                <div class="text-sm opacity-80">Total Issues</div>
                <div class="text-3xl font-bold">{{ $total }}</div>
            </div>

            <div class="bg-green-500 text-white rounded-lg shadow p-4">
                <div class="text-sm opacity-80">Total Quantity</div>
                <div class="text-3xl font-bold">{{ number_format($totalQuantity) }}</div>
            </div>

            <div class="bg-purple-500 text-white rounded-lg shadow p-4">
                <div class="text-sm opacity-80">Avg per Day</div>
                <div class="text-3xl font-bold">{{ round($total / 7, 1) }}</div>
            </div>
        </div>

        {{-- ================================ --}}
        {{-- CHART.JS BAR CHART - DAILY TREND --}}
        {{-- ================================ --}}
        @if(count($chartData) > 0)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
            {{-- Daily Trend Chart --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold mb-4">📊 Daily Trend</h3>
                <div class="relative" style="height: 250px;" wire:ignore>
                    <canvas id="weeklyDailyTrendChart"></canvas>
                </div>
            </div>

            {{-- Top 5 Customers Chart --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold mb-4">👥 Top 5 Customers</h3>
                <div class="relative" style="height: 250px;" wire:ignore>
                    <canvas id="weeklyCustomerChart"></canvas>
                </div>
            </div>
        </div>
        @endif

        
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let dailyChart = null;
                let customerChart = null;

                // Initialize charts
                function initCharts() {
                    const dailyCtx = document.getElementById('weeklyDailyTrendChart');
                    const customerCtx = document.getElementById('weeklyCustomerChart');

                    if (!dailyCtx || !customerCtx) return;

                    // Daily Trend Chart
                    dailyChart = new Chart(dailyCtx.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: @json(array_column($chartData, 'dateShort')),
                            datasets: [{
                                label: 'Issues',
                                data: @json(array_column($chartData, 'count')),
                                backgroundColor: 'rgba(59, 130, 246, 0.6)',
                                borderColor: 'rgba(59, 130, 246, 1)',
                                borderWidth: 2,
                                borderRadius: 5,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { stepSize: 1, precision: 0 }
                                }
                            }
                        }
                    });

                    // Customer Chart
                    customerChart = new Chart(customerCtx.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: @json($byCustomerChart->keys()),
                            datasets: [{
                                label: 'Issues',
                                data: @json($byCustomerChart->values()),
                                backgroundColor: [
                                    'rgba(239, 68, 68, 0.6)',
                                    'rgba(251, 146, 60, 0.6)',
                                    'rgba(234, 179, 8, 0.6)',
                                    'rgba(34, 197, 94, 0.6)',
                                    'rgba(59, 130, 246, 0.6)',
                                ],
                                borderWidth: 2,
                                borderRadius: 5,
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    ticks: { stepSize: 1, precision: 0 }
                                }
                            }
                        }
                    });
                }

                // Initialize on page load
                initCharts();

                // Listen for Livewire updates
                Livewire.on('updateWeeklyCharts', (data) => {
                    if (dailyChart && customerChart) {
                        // Update Daily Chart
                        dailyChart.data.labels = data[0].dailyLabels;
                        dailyChart.data.datasets[0].data = data[0].dailyData;
                        dailyChart.update('none'); // 'none' = no animation for instant update

                        // Update Customer Chart
                        customerChart.data.labels = data[0].customerLabels;
                        customerChart.data.datasets[0].data = data[0].customerData;
                        customerChart.update('none');
                    }
                });
            });
        </script>
        
        {{-- Breakdown by Customer --}}
        @if($byCustomer->count() > 0)
        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <h3 class="font-bold mb-3">Breakdown by Customer</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($byCustomer as $customer => $items)
                <div class="border rounded p-3">
                    <div class="text-sm text-gray-600 truncate" title="{{ $customer }}">{{ $customer }}</div>
                    <div class="text-2xl font-bold text-blue-600">{{ $items->count() }}</div>
                    <div class="text-sm text-gray-500">Qty: {{ $items->sum('quantity') }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Grouped by Date --}}
        <div class="space-y-4">
            @forelse($groupedByDate as $date => $items)
            <div class="bg-white rounded-lg shadow">
                <div class="bg-gray-100 px-4 py-3 font-bold border-b">
                    {{ \Carbon\Carbon::parse($date)->format('l, d M Y') }} 
                    <span class="text-sm font-normal text-gray-600">({{ $items->count() }} issues)</span>
                </div>
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left">Customer</th>
                                    <th class="px-3 py-2 text-left">Part No</th>
                                    <th class="px-3 py-2 text-left">Issue</th>
                                    <th class="px-3 py-2 text-left">Qty</th>
                                    <th class="px-3 py-2 text-left">Shift</th>
                                    <th class="px-3 py-2 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $asakai)
                                <tr class="border-t hover:bg-gray-50">
                                    <td class="px-3 py-2">{{ $asakai->customer }}</td>
                                    <td class="px-3 py-2">{{ $asakai->part_no }}</td>
                                    <td class="px-3 py-2">
                                        <div class="max-w-xs truncate" title="{{ $asakai->issue }}">
                                            {{ Str::limit($asakai->issue, 40) }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">{{ $asakai->quantity }}</td>
                                    <td class="px-3 py-2">{{ $asakai->lot_shift }}</td>
                                    <td class="px-3 py-2">
                                        <span class="px-2 py-1 text-xs rounded
                                            @if($asakai->status === 'draft') bg-gray-200 text-gray-700
                                            @elseif($asakai->status === 'submitted') bg-yellow-200 text-yellow-800
                                            @else bg-green-200 text-green-800
                                            @endif">
                                            {{ ucfirst($asakai->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
                Tidak ada data untuk minggu ini
            </div>
            @endforelse
        </div>
    </div>
