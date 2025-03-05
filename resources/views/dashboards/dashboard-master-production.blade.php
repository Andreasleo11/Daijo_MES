<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Production Dashboard
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <form method="GET" action="{{ route('djoni.dashboard') }}">
                    <div class="mb-4">
                        <label for="machine_name" class="block text-sm font-medium text-gray-700">Filter by Machine</label>
                        <select name="machine_name" id="machine_name" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md">
                            <option value="">Select Machine</option>
                            @foreach ($machines as $machine)
                                <option value="{{ $machine }}" {{ request('machine_name') == $machine ? 'selected' : '' }}>{{ $machine }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Apply Filter</button>
                </form>

                @if (request('machine_name')) <!-- Only show tables if a machine is selected -->
                    @foreach ($structuredData as $userName => $data)
                        <div class="mb-8 border-b pb-4">
                            <h3 class="text-2xl font-bold text-gray-700">{{ $userName }}</h3>

                            <!-- Mould Change Log -->
                            <h4 class="text-lg font-semibold mt-4">Mould Change Log</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full border-collapse border border-gray-300 mt-2">
                                    <thead class="bg-gray-200">
                                        <tr>
                                            <th class="border px-4 py-2">Machine</th>
                                            <th class="border px-4 py-2">Item Code</th>
                                            <th class="border px-4 py-2">Start Time</th>
                                            <th class="border px-4 py-2">End Time</th>
                                            <th class="border px-4 py-2">Predicted Time (min)</th>
                                            <th class="border px-4 py-2">Actual Time (min)</th>
                                            <th class="border px-4 py-2">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($data['mould_change_log'] as $log)
                                            <tr class="{{ $log['status'] == 'problem' ? 'bg-red-200' : 'bg-green-200' }}">
                                                <td class="border px-4 py-2">{{ $log['machine_name'] }}</td>
                                                <td class="border px-4 py-2">{{ $log['item_code'] }}</td>
                                                <td class="border px-4 py-2">
                                                    {{ \Carbon\Carbon::parse($log['start_time'])->setTimezone('Asia/Jakarta')->format('Y-m-d H:i') }}
                                                </td>
                                                <td class="border px-4 py-2">
                                                    {{ \Carbon\Carbon::parse($log['end_time'])->setTimezone('Asia/Jakarta')->format('Y-m-d H:i') }}
                                                </td>
                                                <td class="border px-4 py-2">{{ $log['predicted_time'] }}</td>
                                                <td class="border px-4 py-2">{{ $log['actual_time'] }}</td>
                                                <td class="border px-4 py-2 font-bold">{{ ucfirst($log['status']) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Hourly Production -->
                            <h4 class="text-lg font-semibold mt-6">Hourly Production</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full border-collapse border border-gray-300 mt-2">
                                    <thead class="bg-gray-200">
                                        <tr>
                                            <th class="border px-4 py-2">Hour</th>
                                            <th class="border px-4 py-2">User</th>
                                            <th class="border px-4 py-2">Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($data['hourly_production'] as $hourly)
                                            @foreach ($hourly['users'] as $user => $quantity)
                                                <tr>
                                                    <td class="border px-4 py-2">{{ $hourly['hour'] }}</td>
                                                    <td class="border px-4 py-2">{{ $user }}</td>
                                                    <td class="border px-4 py-2">{{ $quantity }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Daily Item Code -->
                            <h4 class="text-lg font-semibold mt-6">Daily Item Code</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full border-collapse border border-gray-300 mt-2">
                                    <thead class="bg-gray-200">
                                        <tr>
                                            <th class="border px-4 py-2">Item Code</th>
                                            <th class="border px-4 py-2">Shift</th>
                                            <th class="border px-4 py-2">Quantity</th>
                                            <th class="border px-4 py-2">Final Qty</th>
                                            <th class="border px-4 py-2">Loss Pkg</th>
                                            <th class="border px-4 py-2">Actual Qty</th>
                                            <th class="border px-4 py-2">Start Time</th>
                                            <th class="border px-4 py-2">End Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($data['daily_item_code'] as $dailyItem)
                                            <tr>
                                                <td class="border px-4 py-2">{{ $dailyItem['item_code'] }}</td>
                                                <td class="border px-4 py-2">{{ $dailyItem['shift'] }}</td>
                                                <td class="border px-4 py-2">{{ $dailyItem['quantity'] }}</td>
                                                <td class="border px-4 py-2">{{ $dailyItem['final_quantity'] }}</td>
                                                <td class="border px-4 py-2">{{ $dailyItem['loss_package_quantity'] }}</td>
                                                <td class="border px-4 py-2">{{ $dailyItem['actual_quantity'] }}</td>
                                                <td class="border px-4 py-2">
                                                    {{ \Carbon\Carbon::parse($dailyItem['start_date'] . ' ' . $dailyItem['start_time'])->subHours(7)->format('Y-m-d H:i') }}
                                                </td>
                                                <td class="border px-4 py-2">
                                                    {{ \Carbon\Carbon::parse($dailyItem['end_date'] . ' ' . $dailyItem['end_time'])->subHours(7)->format('Y-m-d H:i') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Scanned Data -->
                            <h4 class="text-lg font-semibold mt-6">Scanned Data</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full border-collapse border border-gray-300 mt-2">
                                    <thead class="bg-gray-200">
                                        <tr>
                                            <th class="border px-4 py-2">SPK Code</th>
                                            <th class="border px-4 py-2">Warehouse</th>
                                            <th class="border px-4 py-2">Quantity</th>
                                            <th class="border px-4 py-2">Label</th>
                                            <th class="border px-4 py-2">User</th>
                                            <th class="border px-4 py-2">Scanned At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($data['daily_item_code'] as $dailyItem)
                                            @foreach ($dailyItem['scanned_data'] as $scan)
                                                <tr>
                                                    <td class="border px-4 py-2">{{ $scan['spk_code'] }}</td>
                                                    <td class="border px-4 py-2">{{ $scan['warehouse'] }}</td>
                                                    <td class="border px-4 py-2">{{ $scan['quantity'] }}</td>
                                                    <td class="border px-4 py-2">{{ $scan['label'] }}</td>
                                                    <td class="border px-4 py-2">{{ $scan['user'] }}</td>
                                                    <td class="border px-4 py-2">{{ $scan['scanned_at'] }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    @endforeach
                @else
                    <p class="text-gray-500">Please select a machine to view the data.</p> <!-- Message when no machine is selected -->
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
