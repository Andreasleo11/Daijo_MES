<x-dashboard-layout>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<section class="header py-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-semibold text-gray-800">Capacity By Forecast Periode {{ $time->start_date }}</h1>
        </div>
        <!-- <div>
            <a href="{{ route('viewstep1') }}" class="bg-gray-800 text-white py-2 px-4 rounded-md hover:bg-gray-700">Mulai Proses</a>
        </div> -->
    </div>
</section>

<section class="content py-6">
    <div class="bg-white shadow-md rounded-lg mt-5">
        <div class="p-4">
            <div class="overflow-x-auto">
                <!-- DataTable with Tailwind classes -->
                {{ $dataTable->table(['class' => 'min-w-full table-auto text-sm text-left text-gray-500', 'id' => 'capacity-forecast-table']) }}
            </div>
        </div>
    </div>
    <!-- <div class="mt-4 flex justify-end space-x-2">
        <a href="{{ route('capacityforecastline') }}" class="bg-gray-800 text-white py-2 px-4 rounded-md hover:bg-gray-700">Line</a>
        <a href="{{ route('capacityforecastdistribution') }}" class="bg-gray-800 text-white py-2 px-4 rounded-md hover:bg-gray-700">Distribution</a>
        <a href="{{ route('capacityforecastdetail') }}" class="bg-gray-800 text-white py-2 px-4 rounded-md hover:bg-gray-700">Detail</a>
    </div> -->
</section>
 
{{ $dataTable->scripts() }}


    <section class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-gray-800">Line Category Chart</h1>
            </div>
        </div>
    </section>

    <section class="content">
        <!-- Chart Container -->
        <div class="bg-white shadow-md rounded-lg mt-5 p-4">
            <canvas id="lineCategoryChart"></canvas>
        </div>
    </section>

    <script>
        // Function to generate a color based on the quantity
        function getColorBasedOnQuantity(quantity) {
            // Define a color scale based on line_quantity range
            const minQuantity = Math.min(...lineSummaries.map(summary => summary.line_quantity));
            const maxQuantity = Math.max(...lineSummaries.map(summary => summary.line_quantity));
            
            // Normalize the quantity to be between 0 and 1
            const normalized = (quantity - minQuantity) / (maxQuantity - minQuantity);

            // Interpolate the color between blue (low) and red (high)
            const r = Math.floor(255 * normalized);
            const g = Math.floor(255 * (1 - normalized));
            const b = 255;

            return `rgb(${r}, ${g}, ${b})`;
        }

        // Passing the lineSummaries data from PHP to JS
        const lineSummaries = @json($lineSummaries);

        // X-axis (line_category), Y-axis (line_quantity)
        const labels = lineSummaries.map(summary => summary.line_category);
        const quantities = lineSummaries.map(summary => summary.line_quantity);

        // Generate an array of colors based on line_quantity
        const barColors = lineSummaries.map(summary => getColorBasedOnQuantity(summary.line_quantity));

        // Chart.js Data Configuration
        const chartData = {
            labels: labels,
            datasets: [{
                label: 'Line Quantity',
                data: quantities,
                backgroundColor: barColors, // Apply the colors based on line_quantity
                borderColor: barColors,     // Apply matching border colors
                borderWidth: 1
            }]
        };

        // Chart.js Configuration
        const config = {
            type: 'bar', // Bar chart type
            data: chartData,
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            // Tooltip title: Display line category when hovering
                            title: function(tooltipItem) {
                                const index = tooltipItem[0].dataIndex;
                                const summary = lineSummaries[index];
                                return `${summary.line_category}`;
                            },
                            // Tooltip label: Show line quantity and all other details
                            label: function(tooltipItem) {
                                const index = tooltipItem.dataIndex;
                                const summary = lineSummaries[index];

                                // Display all details when hovering
                                return [
                                    `Line Quantity: ${summary.line_quantity}`,
                                    `Work Day: ${summary.work_day}`,
                                    `Ready Time: ${summary.ready_time}`,
                                    `Efficiency: ${summary.efficiency}`,
                                    `Max Capacity: ${summary.max_capacity}`,
                                    `Capacity Req Hour: ${summary.capacity_req_hour}`,
                                    `Capacity Req Percent: ${summary.capacity_req_percent}`
                                ];
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Line Category'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Line Quantity'
                        },
                        beginAtZero: true
                    }
                }
            }
        };

        // Render the chart
        const ctx = document.getElementById('lineCategoryChart').getContext('2d');
        new Chart(ctx, config);
    </script>

</x-dashboard-layout>