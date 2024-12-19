<!-- resources/views/dashboard.blade.php -->
<x-dashboard-layout>
    <div class="max-w-7xl mx-auto py-8 space-y-10">
        <!-- Dashboard Title -->
        <h1 class="text-3xl font-bold text-center mb-8">Production Dashboard</h1>

        <!-- Overview Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Efficiency Card -->
            <div class="bg-white p-6 rounded-lg shadow-md flex flex-col items-center">
                <h2 class="text-lg font-semibold">Efficiency</h2>
                <p class="text-3xl font-bold text-blue-600 mt-2">{{ number_format($efficiency, 2) }} items/hour</p>
            </div>

            <!-- Utilization Card -->
            <div class="bg-white p-6 rounded-lg shadow-md flex flex-col items-center">
                <h2 class="text-lg font-semibold">Utilization</h2>
                <p class="text-3xl font-bold text-green-600 mt-2">{{ number_format($utilization, 2) }}%</p>
            </div>

            <!-- Total Quantity Planned Card -->
            <div class="bg-white p-6 rounded-lg shadow-md flex flex-col items-center">
                <h2 class="text-lg font-semibold">Total Quantity Planned</h2>
                <p class="text-3xl font-bold text-red-600 mt-2">{{ $totalQuantityPlanned }}</p>
            </div>
        </div>

        <!-- Production Line Breakdown Table -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-lg font-semibold mb-4">Production Line Breakdown</h2>
            <table class="min-w-full border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-4 text-left">Line</th>
                        <th class="p-4 text-left">Item Code</th>
                        <th class="p-4 text-left">Description</th>
                        <th class="p-4 text-left">Quantity Plan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productionData as $data)
                        <tr class="border-b">
                            <td class="p-4">{{ $data->line }}</td>
                            <td class="p-4">{{ $data->item_code }}</td>
                            <td class="p-4">{{ $data->item_description }}</td>
                            <td class="p-4">{{ $data->quantity_plan }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Chart Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Shift Analysis Chart -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-semibold mb-4">Shift Analysis</h2>
                <canvas id="shiftChart"></canvas>
            </div>

            <!-- Customer Overview Chart -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-semibold mb-4">Customer Overview</h2>
                <canvas id="customerChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Load Chart.js from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Chart Initialization Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Shift Analysis Chart
            const shiftData = @json($shiftData->map->quantity->values());
            const shiftLabels = @json($shiftData->keys()->map(fn($shift) => 'Shift ' . $shift));
            const shiftCtx = document.getElementById('shiftChart').getContext('2d');
            new Chart(shiftCtx, {
                type: 'bar',
                data: {
                    labels: shiftLabels,
                    datasets: [{
                        label: 'Quantity Planned',
                        data: shiftData,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                    }],
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                        },
                    },
                },
            });

            // Customer Overview Chart
            const customerData = @json($customerData->values());
            const customerLabels = @json($customerData->keys());
            const customerCtx = document.getElementById('customerChart').getContext('2d');
            new Chart(customerCtx, {
                type: 'pie',
                data: {
                    labels: customerLabels,
                    datasets: [{
                        data: customerData,
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                            '#FF9F40'
                        ],
                    }],
                },
                options: {
                    responsive: true,
                },
            });
        });
    </script>
</x-dashboard-layout>
