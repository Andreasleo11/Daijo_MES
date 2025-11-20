<x-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Production Dashboard
        </h2>
    </x-slot>

    <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-slate-800 tracking-tight mb-3">Daily Production</h1>
            <div class="w-16 h-0.5 bg-gradient-to-r from-slate-600 to-slate-400 mx-auto rounded-full"></div>
        </div>


    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <form method="GET" action="{{ route('djoni.dashboard') }}">
                <div class="flex flex-wrap gap-4 items-end">

                <div class="w-full sm:max-w-xs mb-6">
                    <label for="item_code" class="block text-sm font-medium text-gray-700 mb-1">Select Item Code</label>
                    <select id="item_code" class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Select Item Code --</option>
                        @php
                            $distinctItemCodes = collect($structuredData)
                                ->flatMap(fn($d) => collect($d['daily_item_code'])->pluck('item_code'))
                                ->unique()
                                ->values();
                        @endphp
                        @foreach ($distinctItemCodes as $code)
                            <option value="{{ $code }}">{{ $code }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- AJAX Output -->
                <div id="machine-info" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mt-4">
                    <!-- Cards will be inserted here -->
                </div>


                    <!-- Filter by Machine -->
                    <div class="w-full sm:w-auto">
                        <label for="machine_name" class="block text-sm font-medium text-gray-700">Filter by Machine</label>
                        <select name="machine_name" id="machine_name" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md">
                            <option value="">Select Machine</option>
                            @foreach ($machines as $machine)
                                <option value="{{ $machine }}" {{ request('machine_name') == $machine ? 'selected' : '' }}>
                                    {{ $machine }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Select Date -->
                    <div class="w-full sm:w-auto">
                        <label for="date" class="block text-sm font-medium text-gray-700">Select Date</label>
                        <input type="date" id="date" name="date" value="{{ request('date', now()->toDateString()) }}"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md">
                    </div>

                    <!-- Apply Filter Button -->
                    <div class="w-full sm:w-auto">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Apply Filter</button>
                    </div>
                </div>
            </form>
                @if (request('machine_name')) <!-- Only show tables if a machine is selected -->
                
                @foreach($structuredData as $machineName => $data)
                    @php
                        $zoneName = collect($data['pengawas'])->pluck('zone_name')->first();
                    @endphp
                    <div class="border p-4 rounded-xl mb-6 shadow-md bg-white">
                        <h2 class="text-xl font-bold mb-4">
                            Machine: {{ $machineName }} 
                            @if($zoneName)
                                || ZONA {{ $zoneName }}
                            @endif
                        </h2>
                         <h1>
                            Daily Percentage : {{ $data['average_achievement'] ?? 0 }} %
                        </h1>

                        <!-- @if(isset($data['pengawas']))
                            <div class="grid md:grid-cols-3 gap-4">
                                @foreach($data['pengawas'] as $shift => $pengawasData)
                                    <div class="flex items-center space-x-3 border p-3 rounded-lg shadow-sm">
                                        <img src="{{ $pengawasData['profile_path'] }}" alt="Pengawas Profile" class="w-12 h-12 rounded-full border">
                                        <div>
                                            <p class="text-xs text-gray-400">Shift {{ $shift }} - Zona {{ $pengawasData['zone_name'] }}</p>
                                            <p class="font-semibold text-sm">{{ $pengawasData['name'] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Optional: You can show logs or other data next --}} -->
                    </div>
                @endforeach


                    @foreach ($structuredData as $userName => $data)
                        <div class="mb-8 border-b pb-4">
                            <!-- <h3 class="text-2xl font-bold text-gray-700">{{ $userName }}</h3> -->


                        <!-- Repair Machine Logs -->
                     
                        @php
                            $selectedDate = request('date', now()->toDateString());
                            $selectedMonth = \Carbon\Carbon::parse($selectedDate)->format('Y-m');
                            $selectedMachine = request('machine_name');

                            $allLogs = $data['repair_machine_logs'] ?? [];
                            

                            // Filter berdasarkan bulan dan mesin
                            $filteredRepairLogs = collect($allLogs)->filter(function ($log) use ($selectedMonth, $selectedMachine) {
                                return \Carbon\Carbon::parse($log['start_time'])->format('Y-m') === $selectedMonth
                                    && (!$selectedMachine || $log['machine_name'] === $selectedMachine);
                            });
                           
                        @endphp
                    <!-- Repair Machine Logs -->
                    @if(isset($filteredRepairLogs) && count($filteredRepairLogs) > 0)
                    <h4 class="text-lg font-semibold mt-4 mb-4">Repair Machine Logs</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Operator</th>
                                        <th class="px-4 py-2 text-left">Status</th>
                                        <th class="px-4 py-2 text-left">Start Time</th>
                                        <th class="px-4 py-2 text-left">End Time</th>
                                        <th class="px-4 py-2 text-left">Actual Time</th>
                                        <th class="px-4 py-2 text-left">Problem</th>
                                        <th class="px-4 py-2 text-left">Remark</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($filteredRepairLogs as $repairLog)
                                        <tr class="border-t">
                                            <td class="px-4 py-2 flex items-center space-x-3">
                                                <img src="{{ $repairLog['pic_profile_path'] }}" alt="Operator Profile" class="w-10 h-10 rounded-full border">
                                                <span class="font-medium">{{ $repairLog['pic'] }}</span>
                                            </td>
                                            <td class="px-4 py-2">{{ $repairLog['status'] }}</td>
                                            <td class="px-4 py-2">{{ $repairLog['start_time'] }}</td>
                                            <td class="px-4 py-2">{{ $repairLog['end_time'] }}</td>
                                            <td class="px-4 py-2">{{ $repairLog['actual_time'] }} min</td>
                                            <td class="px-4 py-2">{{ $repairLog['problem'] }}</td>
                                            <td class="px-4 py-2">{{ $repairLog['remark'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p>No Repair Machine Logs Available</p>
                    @endif

                            <!-- Mould Change Log -->
                            <h4 class="text-lg font-semibold mt-4">Mould Change Summary</h4>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="bg-blue-200 p-4 rounded-lg">
                                    <p class="text-xl font-semibold">{{ count($data['mould_change_log']) }}</p>
                                    <p>Total Mould Changes</p>
                                </div>
                                @php
                                    $totalDowntime = array_sum(array_column(array_merge($data['mould_change_log'], $data['adjust_machine_logs']), 'actual_time'));
                                @endphp

                                <div class="bg-green-200 p-4 rounded-lg">
                                    <p class="text-xl font-semibold">{{ $totalDowntime }} min</p>
                                    <p>Total Downtime</p>
                                </div>
                            
                            </div>

                           <!-- Button to show details -->
                            <button id="showDetails" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded">View Details</button>

                            <div id="detailModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
                                <div class="bg-white p-6 rounded-lg shadow-lg w-3/4">
                                    <h2 class="text-lg font-bold mb-4">Mould Change & Adjusting Machine Details</h2>
                                    <div class="overflow-x-auto">
                                        <table class="w-full border-collapse border border-gray-300">
                                            <thead class="bg-gray-200">
                                                <tr>
                                                    <th class="border px-4 py-2">Machine</th>
                                                    <th class="border px-4 py-2">Item Code</th>
                                                    <th class="border px-4 py-2">Start Time</th>
                                                    <th class="border px-4 py-2">End Time</th>
                                                    <th class="border px-4 py-2">Predicted Time (min)</th>
                                                    <th class="border px-4 py-2">Actual Time (min)</th>
                                                    <th class="border px-4 py-2">PIC</th>
                                                    <th class="border px-4 py-2">Remark</th>
                                                    <th class="border px-4 py-2">Status</th>
                                                    <th class="border px-4 py-2">Type</th>
                                                    <th class="border px-4 py-2">Foto</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $combinedLogs = array_merge($data['mould_change_log'], $data['adjust_machine_logs']);
                                                    usort($combinedLogs, fn($a, $b) => strcmp($a['item_code'], $b['item_code']));
                                                @endphp

                                                @foreach ($combinedLogs as $log)
                                                    <tr class="{{ isset($log['status']) && $log['status'] == 'problem' ? 'bg-red-200' : 'bg-green-200' }}">
                                                        <td class="border px-4 py-2">{{ $log['machine_name'] }}</td>
                                                        <td class="border px-4 py-2">{{ $log['item_code'] }}</td>
                                                        <td class="border px-4 py-2">
                                                            {{ \Carbon\Carbon::parse($log['start_time'])->setTimezone('Asia/Jakarta')->format('Y-m-d H:i') }}
                                                        </td>
                                                        <td class="border px-4 py-2">
                                                            {{ \Carbon\Carbon::parse($log['end_time'])->setTimezone('Asia/Jakarta')->format('Y-m-d H:i') }}
                                                        </td>
                                                        <td class="border px-4 py-2">{{ $log['predicted_time'] }} Min</td>
                                                        <td class="border px-4 py-2">{{ $log['actual_time'] }} Min</td>
                                                        <td class="border px-4 py-2">{{ $log['pic'] }}</td>
                                                        <td class="border px-4 py-2">{{ $log['remark'] }}</td>
                                                        <td class="border px-4 py-2 font-bold">{{ ucfirst($log['status'] ?? 'N/A') }}</td>
                                                        <td class="border px-4 py-2 font-bold">
                                                            {{ in_array($log, $data['mould_change_log'], true) ? 'Mould Change' : 'Adjusting Machine' }}
                                                        </td>
                                                        <td class="border px-4 py-2 flex items-center gap-2">
                                                            <img src="{{ asset($log['pic_profile_path']) }}" alt="{{ $log['pic'] }}" class="w-8 h-8 rounded-full object-cover">
                                                        
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <button id="closeModal" class="mt-4 bg-gray-500 text-white px-4 py-2 rounded">Close</button>
                                </div>
                            </div>

                            

                            <div id="photoModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
                                <div class="bg-white p-6 rounded-lg shadow-lg relative">
                                    <!-- Close Button -->
                                    <button id="closePhotoModal" class="absolute top-2 right-2 bg-red-500 text-white px-3 py-1 rounded-full">
                                        &times;
                                    </button>
                                    
                                    <!-- Image -->
                                    <img id="modalPhoto" src="" alt="Mould Change Photo" class="max-w-full max-h-screen cursor-pointer transition-transform transform hover:scale-125">
                                </div>
                            </div>

                            <div class="p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-900 rounded shadow-sm mb-4">
                                <h3 class="text-lg font-bold mb-2">📝 Catatan Penting</h3>

                                @php
                                    $remarks = collect($data['daily_item_code'])->filter(fn($dic) => !empty($dic['remark']));
                                @endphp

                                @if ($remarks->isNotEmpty())
                                    <ul class="list-disc list-inside space-y-2 text-sm">
                                        @foreach ($remarks as $dic)
                                            <li>
                                                <strong>Shift {{ $dic['shift'] }} - {{ $dic['item_code'] }}:</strong><br>
                                                {!! nl2br(e($dic['remark'])) !!}
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="italic text-sm text-gray-600">Tidak ada remark hari ini.</p>
                                @endif
                            </div>

                            <!-- Hourly Remarks -->
                            @if(count($data['hourly_remarks']) > 0)
                                <h4 class="text-lg font-semibold mt-6 mb-4">Hourly Remarks</h4>
                                <div class="overflow-x-auto">
                                    <table class="w-full border-collapse border border-gray-300 mt-2">
                                        <thead class="bg-gray-200">
                                            <tr>
                                                <th class="border px-4 py-2">PIC</th>
                                                <th class="border px-4 py-2">Item Code</th>
                                                <th class="border px-4 py-2">Dibuat Jam</th>
                                                <th class="border px-4 py-2">Time Range</th>
                                                <th class="border px-4 py-2">Shift</th>
                                                <th class="border px-4 py-2">Target</th>
                                                <th class="border px-4 py-2">Actual Scan</th>
                                                <th class="border px-4 py-2">Actual Production</th>
                                                <th class="border px-4 py-2">NG</th>
                                                <th class="border px-4 py-2">Status</th>
                                                <th class="border px-4 py-2">Remark</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $currentShift = null;
                                            $totalActual = 0;
                                        @endphp

                                        @foreach ($data['hourly_remarks'] as $index => $remark)
                                            @php
                                                $statusClass = $remark['is_achieve'] ? 'bg-green-100' : 'bg-red-100';

                                                // Cek jika ganti shift
                                                if ($currentShift !== $remark['shift']) {
                                                    $currentShift = $remark['shift'];
                                                    $totalActual = 0;
                                                }

                                                $totalActual += $remark['actual_production'];
                                            @endphp

                                            <tr class="{{ $statusClass }}">
                                                <td class="border px-4 py-2 flex items-center space-x-3">
                                                    <img src="{{ $remark['pic_profile_path'] }}" alt="PIC Profile" class="w-10 h-10 rounded-full border">
                                                    <span class="font-medium">{{ $remark['pic'] }}</span>
                                                </td>
                                                <td class="border px-4 py-2">{{ $remark['item_code'] }}</td>
                                                 <td class="border px-4 py-2">{{ $remark['updated_at'] }}</td>
                                                <td class="border px-4 py-2">{{ $remark['time_range'] }}</td>
                                                <td class="border px-4 py-2 text-center">  {{ $remark['shift'] }}</td>
                                                <td class="border px-4 py-2 text-center">{{ $remark['target'] }}</td>
                                                <td class="border px-4 py-2 text-center font-bold">{{ $remark['actual'] }}</td>
                                                <td class="border px-4 py-2 text-center font-bold">
                                                    {{ $remark['actual_production'] ?? 0 }}
                                                </td>
                                                <td class="border px-4 py-2 text-center font-bold">
                                                    {{ $remark['ng'] ?? 0 }}
                                                </td>
                                                <td class="border px-4 py-2 text-center">
                                                    <span class="px-2 py-1 rounded text-xs font-bold {{ $remark['is_achieve'] ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }}">
                                                        {{ ucfirst($remark['status']) }}
                                                    </span>
                                                </td>
                                                <td class="border px-4 py-2">{{ $remark['remark'] }}</td>
                                            </tr>

                                            @php
                                                $nextShift = $data['hourly_remarks'][$index + 1]['shift'] ?? null;
                                                $isLastInShift = $nextShift !== $currentShift;
                                            @endphp

                                            @if ($isLastInShift)
                                                <tr class="bg-blue-100 text-blue-900 font-semibold text-sm">
                                                    <td colspan="8" class="border px-4 py-2 text-right">Total Actual (Shift {{ $currentShift }})</td>
                                                    <td class="border px-4 py-2 text-center">{{ $totalActual }}</td>
                                                    <td colspan="2" class="border px-4 py-2"></td>
                                                </tr>
                                            @endif
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <h4 class="text-lg font-semibold mt-6 mb-4">Hourly Remarks</h4>
                                <p class="text-gray-500">No Hourly Remarks Available</p>
                            @endif
                            
                            <!-- Hourly Production -->
                            <!-- <h4 class="text-lg font-semibold mt-6">Hourly Production</h4>
                                <div class="overflow-x-auto">
                                    <table class="w-full border-collapse border border-gray-300 mt-2">
                                        <thead class="bg-gray-200">
                                            <tr>
                                                <th class="border px-4 py-2">Hour</th>
                                                <th class="border px-4 py-2">Item Code</th>
                                                <th class="border px-4 py-2">Total Quantity</th>
                                                <th class="border px-4 py-2">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $groupedHourly = [];
                                        @endphp

                                        @foreach ($data['hourly_production'] as $record)
                                            @php
                                                $hour = $record['hour'];
                                                $itemCode = $record['item_code'];
                                                $users = $record['users'];
                                                $totalQuantity = array_sum(array_column($users, 'quantity'));

                                                $groupedHourly[$hour][$itemCode] = [
                                                    'total_quantity' => $totalQuantity,
                                                    'users' => $users
                                                ];
                                            @endphp
                                        @endforeach

                                        @foreach ($groupedHourly as $hour => $itemGroups)
                                            @foreach ($itemGroups as $itemCode => $dataSet)
                                                <tr>
                                                    <td class="border px-4 py-2 font-bold">{{ $hour }}</td>
                                                    <td class="border px-4 py-2">{{ $itemCode }}</td>
                                                    <td class="border px-4 py-2 font-bold">{{ $dataSet['total_quantity'] }}</td>
                                                    <td class="border px-4 py-2 text-center">
                                                        <button class="toggle-hourly-details bg-blue-500 text-white px-3 py-1 rounded-md" data-hour="{{ $hour }}" data-item="{{ $itemCode }}">
                                                            View Details
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr class="hourly-details hidden" id="details-{{ $hour }}-{{ $itemCode }}">
                                                    <td colspan="4">
                                                        <table class="w-full border-collapse border border-gray-300 mt-2">
                                                            <thead class="bg-gray-100">
                                                                <tr>
                                                                    <th class="border px-4 py-2">User</th>
                                                                    <th class="border px-4 py-2">Quantity</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($dataSet['users'] as $user => $userData)
                                                                    <tr>
                                                                        <td class="border px-4 py-2 flex items-center">
                                                                            <img src="{{ $userData['user_profile_path'] }}" alt="{{ $user }}" class="w-10 h-10 rounded-full mr-2">
                                                                            {{ $user }}
                                                                        </td>
                                                                        <td class="border px-4 py-2">{{ $userData['quantity'] }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div> -->

                            <!-- Daily Item Code -->
                            <h4 class="text-lg font-semibold mt-6">Daily Item Code</h4>
                                <div class="overflow-x-auto">
                                    <table class="w-full border-collapse border border-gray-300 mt-2">
                                        <thead class="bg-gray-200">
                                            <tr>
                                                <th class="border px-4 py-2">Item Code</th>
                                                <th class="border px-4 py-2">Item Name</th>
                                                <th class="border px-4 py-2">Shift</th>
                                                <th class="border px-4 py-2">Planned Quantity</th>
                                                <th class="border px-4 py-2">Quantity Produksi</th>
                                                <th class="border px-4 py-2">Cycle Time</th>
                                                <th class="border px-4 py-2">Start Time</th>
                                                <th class="border px-4 py-2">End Time</th>
                                                <th class="border px-4 py-2">Delivery Schedule</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($data['daily_item_code'] as $dailyItem)
                                                @php
                                                    $rowColor = ($dailyItem['total_scanned_quantity'] < $dailyItem['quantity']) 
                                                        ? 'bg-red-300' 
                                                        : 'bg-green-300';
                                                @endphp
                                                <tr class="{{ $rowColor }}">
                                                    <td class="border px-4 py-2">{{ $dailyItem['item_code'] }}</td>
                                                    <td class="border px-4 py-2">{{ $dailyItem['item_name'] }}</td>
                                                    <td class="border px-4 py-2">{{ $dailyItem['shift'] }}</td>
                                                    <td class="border px-4 py-2">{{ $dailyItem['quantity'] }}</td>
                                                    <td class="border px-4 py-2">{{ $dailyItem['total_scanned_quantity'] }}</td>
                                                    <td class="border px-4 py-2">{{ $dailyItem['cycle_time_seconds'] }} Detik </td>
                                                    <td class="border px-4 py-2">
                                                    {{ $dailyItem['start_date'] }} {{ \Carbon\Carbon::parse($dailyItem['start_time'])->subHours(7)->format('H:i') }}
                                                    </td>
                                                    <td class="border px-4 py-2">
                                                    {{ $dailyItem['end_date'] }} {{ \Carbon\Carbon::parse($dailyItem['end_time'])->subHours(7)->format('H:i') }}
                                                    </td>
                                                   <td class="border px-4 py-2 text-center">
                                                        <button 
                                                            class="btn-delivery-schedule bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                                            data-item-code="{{ $dailyItem['item_code'] }}"
                                                            data-item-name="{{ $dailyItem['item_name'] }}"
                                                            data-delsched="{{ json_encode($dailyItem['delsched'] ?? []) }}">
                                                            <i class="fas fa-calendar-alt mr-1"></i> Schedule
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>


                                <div id="deliveryScheduleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
                                    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
                                        <div class="flex justify-between items-center mb-4 border-b pb-3">
                                            <h3 class="text-xl font-semibold text-gray-800">
                                                <i class="fas fa-truck mr-2"></i>Delivery Schedule - <span id="modalItemCode"></span>
                                            </h3>
                                            <button 
                                                id="closeDeliverySchedule" 
                                                class="text-gray-600 hover:text-gray-800 text-2xl font-bold leading-none">
                                                &times;
                                            </button>
                                        </div>
                                        
                                        <div class="overflow-x-auto">
                                            <table class="w-full border-collapse border border-gray-300">
                                                <thead class="bg-gray-200">
                                                    <tr>
                                                        <th class="border px-4 py-2">Delivery Date</th>
                                                        <th class="border px-4 py-2">Item Code</th>
                                                        <th class="border px-4 py-2">Item Name</th>
                                                        <th class="border px-4 py-2">Outstanding</th>
                                                        <th class="border px-4 py-2">Packaging Code</th>
                                                        <th class="border px-4 py-2">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="deliveryScheduleBody">
                                                    <!-- Data will be populated by JavaScript -->
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="mt-4 text-right border-t pt-3">
                                            <button 
                                                id="closeDeliveryScheduleBtn" 
                                                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded">
                                                <i class="fas fa-times mr-1"></i> Close
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            <!-- Scanned Data -->
                            <h4 class="text-lg font-semibold mt-6">Scanned Data</h4>
                                <div class="overflow-x-auto">
                                    <table class="w-full border-collapse border border-gray-300 mt-2">
                                        <thead class="bg-gray-200">
                                            <tr>
                                                <th class="border px-4 py-2">Item Code</th>
                                                <th class="border px-4 py-2">SPK Code</th>
                                                <th class="border px-4 py-2">Accumulated Quantity</th>
                                                <th class="border px-4 py-2">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $spkSummary = []; // Store accumulated quantity per item_code and spk_code
                                            $spkDetails = []; // Store details per item_code and spk_code
                                        @endphp

                                        @foreach ($data['daily_item_code'] as $dailyItem)
                                            @foreach ($dailyItem['scanned_data'] as $scan)
                                                @php
                                                    // Gunakan item_code dari scanned_data, bukan dari dailyItem
                                                    $itemCode = $scan['item_code'];
                                                    $spkCode = $scan['spk_code'];
                                                    $spkKey = $itemCode . '-' . $spkCode; // Unique key per item + SPK

                                                    // Inisialisasi jika belum ada
                                                    if (!isset($spkSummary[$spkKey])) {
                                                        $spkSummary[$spkKey] = [
                                                            'item_code' => $itemCode,
                                                            'spk_code' => $spkCode,
                                                            'accumulated_quantity' => 0,
                                                        ];
                                                        $spkDetails[$spkKey] = [];
                                                    }

                                                    // Tambah kuantitas
                                                    $spkSummary[$spkKey]['accumulated_quantity'] += $scan['quantity'];

                                                    // Simpan detail
                                                    $spkDetails[$spkKey][] = [
                                                        'warehouse'   => $scan['warehouse'],
                                                        'quantity'    => $scan['quantity'],
                                                        'label'       => $scan['label'],
                                                        'user'        => $scan['user'],
                                                        'scanned_at'  => $scan['scanned_at'],
                                                    ];
                                                @endphp
                                            @endforeach
                                        @endforeach


                                            @foreach ($spkSummary as $spkKey => $summary)
                                                <tr>
                                                    <td class="border px-4 py-2 font-bold">{{ $summary['item_code'] }}</td>
                                                    <td class="border px-4 py-2 font-bold">{{ $summary['spk_code'] }}</td>
                                                    <td class="border px-4 py-2 font-bold">{{ $summary['accumulated_quantity'] }}</td>
                                                    <td class="border px-4 py-2 text-center">
                                                        <button class="toggle-details bg-blue-500 text-white px-3 py-1 rounded-md" data-spk="{{ $spkKey }}">
                                                            View Details
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr class="spk-details hidden" id="details-{{ $spkKey }}">
                                                    <td colspan="4">
                                                        <table class="w-full border-collapse border border-gray-300 mt-2">
                                                            <thead class="bg-gray-100">
                                                                <tr>
                                                                    <th class="border px-4 py-2">Warehouse</th>
                                                                    <th class="border px-4 py-2">Quantity</th>
                                                                    <th class="border px-4 py-2">Label</th>
                                                                    <th class="border px-4 py-2">User</th>
                                                                    <th class="border px-4 py-2">Scanned At</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($spkDetails[$spkKey] as $detail)
                                                                    <tr>
                                                                        <td class="border px-4 py-2">{{ $detail['warehouse'] }}</td>
                                                                        <td class="border px-4 py-2">{{ $detail['quantity'] }}</td>
                                                                        <td class="border px-4 py-2">{{ $detail['label'] }}</td>
                                                                        <td class="border px-4 py-2">{{ $detail['user'] }}</td>
                                                                        <td class="border px-4 py-2">{{ $detail['scanned_at'] }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
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


    <script type="module">
    $(document).ready(function() {
        // Handle delivery schedule button click
        $('.btn-delivery-schedule').click(function() {
            const itemCode = $(this).data('item-code');
            const itemName = $(this).data('item-name');
            const delSched = $(this).data('delsched');
            
            // Set modal title
            $('#modalItemCode').text(itemCode);
            
            // Populate table
            const tbody = $('#deliveryScheduleBody');
            tbody.empty();
            
            if (delSched && delSched.length > 0) {
                delSched.forEach(function(sched) {
                    const statusClass = sched.status === 'completed' ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800';
                    const row = `
                        <tr class="hover:bg-gray-50">
                            <td class="border px-4 py-2">${sched.delivery_date || '-'}</td>
                            <td class="border px-4 py-2">${sched.item_code || '-'}</td>
                            <td class="border px-4 py-2">${sched.item_name || '-'}</td>
                            <td class="border px-4 py-2 text-right">${sched.outstanding || '-'}</td>
                            <td class="border px-4 py-2">${sched.packaging_code || '-'}</td>
                            <td class="border px-4 py-2">
                                <span class="px-3 py-1 rounded-full text-sm font-medium ${statusClass}">
                                    ${sched.status || '-'}
                                </span>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            } else {
                tbody.html(`
                    <tr>
                        <td colspan="6" class="border px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>No delivery schedule available</p>
                        </td>
                    </tr>
                `);
            }
            
            // Show modal
            $('#deliveryScheduleModal').removeClass('hidden');
        });
        
        // Close modal handlers
        $('#closeDeliverySchedule, #closeDeliveryScheduleBtn').click(function() {
            $('#deliveryScheduleModal').addClass('hidden');
        });
        
        // Close when clicking outside modal
        $('#deliveryScheduleModal').click(function(e) {
            if (e.target.id === 'deliveryScheduleModal') {
                $(this).addClass('hidden');
            }
        });
        
        // Close on ESC key
        $(document).keydown(function(e) {
            if (e.key === 'Escape') {
                $('#deliveryScheduleModal').addClass('hidden');
            }
        });
    });
</script>


    <script type=module>
        $(document).ready(function() {
        $('#showDetails').click(function() {
            $('#detailModal').removeClass('hidden'); // Show modal
        });

        $('#closeModal').click(function() {
            $('#detailModal').addClass('hidden'); // Hide modal
        });
    });
    </script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".toggle-details").forEach(button => {
            button.addEventListener("click", function () {
                let spkCode = this.getAttribute("data-spk");
                let detailsRow = document.getElementById("details-" + spkCode);
                detailsRow.classList.toggle("hidden");
            });
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".toggle-hourly-details").forEach(button => {
            button.addEventListener("click", function () {
                let hour = this.getAttribute("data-hour");
                let detailsRow = document.getElementById("details-" + hour);
                detailsRow.classList.toggle("hidden");
            });
        });
    });


    document.addEventListener("DOMContentLoaded", function () {
    const photoModal = document.getElementById("photoModal");
    const modalPhoto = document.getElementById("modalPhoto");
    const closePhotoModal = document.getElementById("closePhotoModal");

    document.querySelectorAll(".show-photo").forEach(button => {
        button.addEventListener("click", function () {
            let photoUrl = this.getAttribute("data-photo");
            if (photoUrl) {
                modalPhoto.src = photoUrl;
                photoModal.classList.remove("hidden");
            }
        });
    });

    // Close when clicking the close button
    closePhotoModal.addEventListener("click", function () {
        photoModal.classList.add("hidden");
    });

    // Close when clicking outside the modal
    photoModal.addEventListener("click", function (event) {
        if (event.target === photoModal) {
            photoModal.classList.add("hidden");
        }
    });

    // Clicking the image zooms in
    modalPhoto.addEventListener("click", function () {
        this.classList.toggle("scale-150");
    });
});


document.querySelectorAll('.toggle-hourly-details').forEach(button => {
        button.addEventListener('click', () => {
            const hour = button.dataset.hour;
            const item = button.dataset.item;
            const row = document.getElementById(`details-${hour}-${item}`);
            if (row) {
                row.classList.toggle('hidden');
            }
        });
    });


    document.getElementById('item_code').addEventListener('change', function() {
    const itemCode = this.value;
    const machineInfoDiv = document.getElementById('machine-info');

    if (!itemCode) {
        machineInfoDiv.innerHTML = '';
        return;
    }

    fetch(`/get-machines-by-item?item_code=${itemCode}`)
        .then(res => res.json())
        .then(data => {
            machineInfoDiv.innerHTML = ''; // Kosongkan dulu

            if (data.length === 0) {
                machineInfoDiv.innerHTML = '<p class="text-gray-500">No machines found for this item code.</p>';
                return;
            }

            data.forEach(row => {
                const card = document.createElement('div');
                card.className = "bg-white shadow rounded-lg p-4 border border-gray-200 cursor-pointer hover:bg-gray-100 transition";

                card.innerHTML = `
                    <h3 class="text-lg font-semibold text-gray-800 mb-1">${row.machine}</h3>
                    <p class="text-sm text-gray-600">Date: <span class="font-medium">${row.date}</span></p>
                `;

                // 👇 Tambahkan event redirect on click
                card.addEventListener('click', () => {
                    const url = new URL("{{ route('djoni.dashboard') }}", window.location.origin);
                    url.searchParams.set('machine_name', row.machine);
                    url.searchParams.set('date', row.date);
                    window.location.href = url.toString();
                });

                machineInfoDiv.appendChild(card);
            });
        });
});

</script>
</x-dashboard-layout>
