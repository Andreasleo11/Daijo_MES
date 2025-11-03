<x-app-layout>
    <div class="container mx-auto p-6">
        <h1 class="text-4xl font-extrabold text-center text-gray-900 mb-12">Forecast Data</h1>

        <div class="mb-6 text-center">
            <h2 class="text-6xl font-extrabold text-gray-900">{{ \Carbon\Carbon::now()->year }}</h2>
        </div>
        
        <!-- Filter Dropdown -->
        <div class="mb-6">
            <form method="GET" action="{{ route('production.forecast.index') }}" class="flex justify-center items-center space-x-4">
                <label for="forecast_name" class="text-lg text-gray-800">Filter by Forecast Name:</label>
                <select name="forecast_name" id="forecast_name" class="p-2 border rounded-lg text-gray-700">
                    <option value="">-- Select Forecast Name --</option>
                    @foreach($forecastNames as $forecastName)
                        <option value="{{ $forecastName }}" {{ $forecastName == $forecastNameFilter ? 'selected' : '' }}>
                            {{ $forecastName }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Apply Filter</button>
            </form>
        </div>

        @foreach($processedData as $forecastName => $items)
            <div class="mb-16">
                <h2 class="text-3xl font-semibold text-gray-800 mb-6">{{ $forecastName }}</h2>

                <!-- Table Container -->
                <div class="overflow-x-auto bg-white shadow-xl rounded-lg border border-gray-200">
                    <table class="min-w-full table-auto text-sm text-gray-700">
                        <thead class="bg-gray-800 text-white">
                            <tr>
                                <th class="px-6 py-4 text-left border-b border-gray-200 font-medium">Item No</th>
                                @for ($month = 1; $month <= 12; $month++)
                                    <th class="px-4 py-4 text-center border-b border-gray-200 font-medium">
                                        {{ \Carbon\Carbon::create(2025, $month, 1)->format('F') }}
                                    </th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @foreach($items as $item)
                                <tr class="hover:bg-gray-50 transition duration-200 ease-in-out">
                                    <td class="px-6 py-4 border-b border-gray-200 text-left font-semibold text-gray-800">{{ $item['item_no'] }}</td>
                                    @foreach($item['monthly_quantities'] as $quantity)
                                        <td class="px-4 py-4 border-b border-gray-200 text-center">
                                            <span class="{{ $quantity > 0 ? 'text-green-600 font-bold' : 'text-gray-400' }}">
                                                {{ $quantity > 0 ? $quantity : '-' }}
                                            </span>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
