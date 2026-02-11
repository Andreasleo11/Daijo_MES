    
<div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">📈 Monthly Report</h2>
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

        {{-- Month Navigation --}}
        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <div class="flex items-center justify-between mb-4">
                <button wire:click="previousMonth" 
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    ← Previous Month
                </button>
                
                <div class="text-center">
                    <div class="text-2xl font-bold">{{ $monthName }}</div>
                    <button wire:click="currentMonth" 
                            class="text-sm text-blue-600 hover:underline mt-1">
                        Go to Current Month
                    </button>
                </div>
                
                <button wire:click="nextMonth" 
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Next Month →
                </button>
            </div>

            {{-- Filter --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Customer</label>
                    <select wire:model.live="customerFilter" class="w-full border rounded px-3 py-2">
                        <option value="">Semua Customer</option>
                        @foreach($customers as $customer)
                        <option value="{{ $customer }}">{{ $customer }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-blue-500 text-white rounded-lg shadow p-4">
                <div class="text-sm opacity-80">Total Issues</div>
                <div class="text-3xl font-bold">{{ $total }}</div>
            </div>

            <div class="bg-green-500 text-white rounded-lg shadow p-4">
                <div class="text-sm opacity-80">Total Quantity</div>
                <div class="text-3xl font-bold">{{ number_format($totalQuantity) }}</div>
            </div>

            <div class="bg-yellow-500 text-white rounded-lg shadow p-4">
                <div class="text-sm opacity-80">Avg Reply Time</div>
                <div class="text-3xl font-bold">{{ $avgReplyDays }} <span class="text-lg">days</span></div>
            </div>

            <div class="bg-red-500 text-white rounded-lg shadow p-4">
                <div class="text-sm opacity-80">Overdue</div>
                <div class="text-3xl font-bold">{{ $overdueCount }}</div>
            </div>

            <div class="bg-purple-500 text-white rounded-lg shadow p-4">
                <div class="text-sm opacity-80">Customers</div>
                <div class="text-3xl font-bold">{{ $byCustomer->count() }}</div>
            </div>
        </div>

        {{-- ================================ --}}
        {{-- CHARTS --}}
        {{-- ================================ --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
            {{-- Daily Trend in Month --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold mb-4">📊 Daily Trend in {{ $monthName }}</h3>
                <div class="relative" style="height: 300px;" wire:ignore>
                    <canvas id="monthlyDailyTrendChart"></canvas>
                </div>
            </div>

            {{-- Top 5 Customers --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold mb-4">👥 Top 5 Customers</h3>
                <div class="relative" style="height: 300px;" wire:ignore>
                    <canvas id="monthlyCustomerChart"></canvas>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let dailyChart = null;
                let customerChart = null;

                // Initialize charts
                function initCharts() {
                    const dailyCtx = document.getElementById('monthlyDailyTrendChart');
                    const customerCtx = document.getElementById('monthlyCustomerChart');

                    if (!dailyCtx || !customerCtx) return;

                    // Destroy existing charts
                    if (dailyChart) {
                        dailyChart.destroy();
                        dailyChart = null;
                    }
                    if (customerChart) {
                        customerChart.destroy();
                        customerChart = null;
                    }

                    // Get data from backend
                    const dailyLabels = @json($dailyTrend->keys()->map(fn($d) => 'Day ' . $d)->values());
                    const dailyData = @json($dailyTrend->values()->values());
                    const customerLabels = @json($customerTrend->keys()->values());
                    const customerData = @json($customerTrend->pluck('count')->values()->values());

                    // Check if we have data
                    if (dailyData.length === 0) return;

                    // Daily Trend Chart
                    const dailyGradient = dailyCtx.getContext('2d').createLinearGradient(0, 0, 0, 300);
                    dailyGradient.addColorStop(0, 'rgba(59, 130, 246, 0.4)');
                    dailyGradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

                    dailyChart = new Chart(dailyCtx.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: dailyLabels,
                            datasets: [{
                                label: 'Issues per Day',
                                data: dailyData,
                                backgroundColor: dailyGradient,
                                borderColor: 'rgba(59, 130, 246, 1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointRadius: 5,
                                pointHoverRadius: 8,
                                pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    padding: 12,
                                    titleFont: { size: 14 },
                                    bodyFont: { size: 13 }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { stepSize: 1, precision: 0, font: { size: 12 } },
                                    grid: { color: 'rgba(0, 0, 0, 0.05)' }
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: { font: { size: 11 } }
                                }
                            }
                        }
                    });

                    // Customer Chart
                    customerChart = new Chart(customerCtx.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: customerLabels,
                            datasets: [{
                                label: 'Issues',
                                data: customerData,
                                backgroundColor: [
                                    'rgba(239, 68, 68, 0.7)',
                                    'rgba(251, 146, 60, 0.7)',
                                    'rgba(234, 179, 8, 0.7)',
                                    'rgba(34, 197, 94, 0.7)',
                                    'rgba(59, 130, 246, 0.7)',
                                ],
                                borderColor: [
                                    'rgba(239, 68, 68, 1)',
                                    'rgba(251, 146, 60, 1)',
                                    'rgba(234, 179, 8, 1)',
                                    'rgba(34, 197, 94, 1)',
                                    'rgba(59, 130, 246, 1)',
                                ],
                                borderWidth: 2,
                                borderRadius: 8,
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    padding: 12,
                                    callbacks: {
                                        label: function(context) {
                                            return 'Issues: ' + context.parsed.x;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    ticks: { stepSize: 1, precision: 0, font: { size: 12 } },
                                    grid: { color: 'rgba(0, 0, 0, 0.05)' }
                                },
                                y: {
                                    grid: { display: false },
                                    ticks: { font: { size: 12, weight: 'bold' } }
                                }
                            }
                        }
                    });
                }

                // Initialize on page load
                initCharts();

                // Listen for Livewire updates (month navigation, filter changes)
                Livewire.on('updateMonthlyCharts', (data) => {
                    if (dailyChart && customerChart) {
                        // Update charts with new data
                        dailyChart.data.labels = data[0].dailyLabels;
                        dailyChart.data.datasets[0].data = data[0].dailyData;
                        dailyChart.update('none');

                        customerChart.data.labels = data[0].customerLabels;
                        customerChart.data.datasets[0].data = data[0].customerData;
                        customerChart.update('none');
                    } else {
                        // Re-initialize if charts don't exist
                        initCharts();
                    }
                });
            });
        </script>


        {{-- Rest of the content (tables, etc.) --}}
        {{-- ... sisanya sama seperti sebelumnya ... --}}
</div>