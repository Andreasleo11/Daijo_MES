<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Delivery Schedule
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6">
                
                <!-- Filters -->
                <form method="GET" class="mb-6 flex flex-wrap gap-6">
                    <div>
                        <label for="year" class="block text-lg font-medium text-gray-700">Select Year:</label>
                        <select name="year" id="year" class="border-gray-300 rounded-md shadow-sm p-3 text-lg w-48" onchange="this.form.submit()">
                            @for ($y = 2024; $y <= 2025; $y++)
                                <option value="{{ $y }}" {{ $y == $selectedYear ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label for="month" class="block text-lg font-medium text-gray-700">Select Month:</label>
                        <select name="month" id="month" class="border-gray-300 rounded-md shadow-sm p-3 text-lg w-48" onchange="this.form.submit()">
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $m == $selectedMonth ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="customer_code" class="block text-lg font-medium text-gray-700">Customer Code:</label>
                        <select name="customer_code" id="customer_code" class="border-gray-300 rounded-md shadow-sm p-3 text-lg w-48" onchange="this.form.submit()">
                            <option value="">All Customers</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer }}" {{ $customer == $customerCode ? 'selected' : '' }}>
                                    {{ $customer }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>

                <!-- Buttons Section -->
                <div class="flex flex-wrap gap-4 mb-6">
                    <!-- Export to Excel -->
                    <form method="GET" action="{{ route('export.delivery.schedule') }}">
                        <input type="hidden" name="year" value="{{ $selectedYear }}">
                        <input type="hidden" name="month" value="{{ $selectedMonth }}">
                        <input type="hidden" name="customer_code" value="{{ $customerCode ?? '' }}">
                        <button type="submit" class="bg-green-600 text-white px-6 py-3 text-lg rounded-md shadow hover:bg-green-700 transition">
                            Export to Excel
                        </button>
                    </form>

                    <!-- Export Template -->
                    <form method="GET" action="{{ route('export.delivery.schedule.template') }}">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-3 text-lg rounded-md shadow hover:bg-blue-700 transition">
                            Export Template
                        </button>
                    </form>

                    <!-- Import Data -->
                    <form method="POST" action="{{ route('import.delivery.schedule') }}" enctype="multipart/form-data" class="flex items-center space-x-4">
                        @csrf
                        <input type="file" name="file" accept=".xlsx, .xls" required class="border border-gray-300 p-3 text-lg rounded-md w-64">
                        <button type="submit" class="bg-yellow-500 text-white px-6 py-3 text-lg rounded-md shadow hover:bg-yellow-600 transition">
                            Import Data
                        </button>
                    </form>
                </div>

                <!-- Delivery Schedule Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse border border-gray-300">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="border border-gray-300 px-4 py-2">Item Code</th>
                                @for ($day = 1; $day <= $daysInMonth; $day++)
                                    <th class="border border-gray-300 px-2 py-1 text-center">{{ $day }}</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($deliveryData as $itemCode => $deliveries)
                                <tr>
                                    <td class="border border-gray-300 px-4 py-2 font-semibold">{{ $itemCode }}</td>
                                    @for ($day = 1; $day <= $daysInMonth; $day++)
                                        <td class="border border-gray-300 px-2 py-1 text-center">
                                            {{ $deliveries[$day] ?? '-' }}
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
