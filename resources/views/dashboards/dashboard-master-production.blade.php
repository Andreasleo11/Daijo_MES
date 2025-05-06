<x-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Production Dashboard
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <form method="GET" action="{{ route('djoni.dashboard') }}">
                <div class="flex flex-wrap gap-4 items-end">
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
                    @foreach ($structuredData as $userName => $data)
                        <div class="mb-8 border-b pb-4">
                            <h3 class="text-2xl font-bold text-gray-700">{{ $userName }}</h3>


                             <!-- Repair Machine Logs -->
              <!-- Repair Machine Logs -->
                    @if(count($data['repair_machine_logs']) > 0)
                        <h4 class="text-lg font-semibold mt-4 mb-4">Repair Machine Logs</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @foreach ($data['repair_machine_logs'] as $repairLog)
                                <div class="bg-white border rounded-lg p-4 shadow-md">
                                    <div class="flex items-center space-x-4">
                                        <img src="{{ $repairLog['pic_profile_path'] }}" alt="Operator Profile" class="w-16 h-16 rounded-full border-2 border-gray-300">
                                        <div>
                                            <h5 class="text-xl font-semibold">{{ $repairLog['pic'] }}</h5>
                                            <p class="text-sm text-gray-600">{{ $repairLog['status'] }}</p>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <p><strong>Start Time:</strong> {{ $repairLog['start_time'] }}</p>
                                        <p><strong>End Time:</strong> {{ $repairLog['end_time'] }}</p>
                                        <p><strong>Actual Time:</strong> {{ $repairLog['actual_time'] }} min</p>
                                        <p><strong>Problem:</strong> {{ $repairLog['problem'] }} min</p>
                                        <p><strong>Remark:</strong> {{ $repairLog['remark'] }} min</p>
                                    </div>

                                  
                                </div>

                            @endforeach
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
                                <div class="bg-green-200 p-4 rounded-lg">
                                    <p class="text-xl font-semibold">{{ array_sum(array_column($data['mould_change_log'], 'actual_time')) }} min</p>
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
                                                    <th class="border px-4 py-2">Photo</th>
                                                    <th class="border px-4 py-2">Status</th>
                                                    <th class="border px-4 py-2">Type</th>
                                                    <th class="border px-4 py-2">Action</th>
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
                                                        <td class="border px-4 py-2">
                                                            <img src="{{ $log['pic_profile_path'] ?? '/images/default-placeholder.png' }}" 
                                                                alt="Log Image" 
                                                                class="w-16 h-16 rounded-lg">
                                                        </td>
                                                        <td class="border px-4 py-2 font-bold">{{ ucfirst($log['status'] ?? 'N/A') }}</td>
                                                        <td class="border px-4 py-2 font-bold">
                                                            {{ in_array($log, $data['mould_change_log'], true) ? 'Mould Change' : 'Adjusting Machine' }}
                                                        </td>
                                                        <td class="border px-4 py-2 text-center">
                                                            <button class="show-photo bg-blue-500 text-white px-3 py-1 rounded" data-photo="{{ $log['pic_profile_path'] }}">
                                                                Show Photo
                                                            </button>
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
                            
                            <!-- Hourly Production -->
                            <h4 class="text-lg font-semibold mt-6">Hourly Production</h4>
                                <div class="overflow-x-auto">
                                    <table class="w-full border-collapse border border-gray-300 mt-2">
                                        <thead class="bg-gray-200">
                                            <tr>
                                                <th class="border px-4 py-2">Hour</th>
                                                <th class="border px-4 py-2">Total Quantity</th>
                                                <th class="border px-4 py-2">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $hourlySummary = [];
                                            $hourlyDetails = [];
                                        @endphp

                                        @foreach ($data['hourly_production'] as $hourly)
                                            @php
                                                $hour = $hourly['hour'];
                                                $totalQuantity = array_sum(array_column($hourly['users'], 'quantity')); // ✅ Extract "quantity" values and sum

                                                $hourlySummary[$hour] = $totalQuantity;
                                                $hourlyDetails[$hour] = $hourly['users']; // ✅ Keep full user data
                                            @endphp
                                        @endforeach

                                            @foreach ($hourlySummary as $hour => $totalQuantity)
                                                <tr>
                                                    <td class="border px-4 py-2 font-bold">{{ $hour }}</td>
                                                    <td class="border px-4 py-2 font-bold">{{ $totalQuantity }}</td>
                                                    <td class="border px-4 py-2 text-center">
                                                        <button class="toggle-hourly-details bg-blue-500 text-white px-3 py-1 rounded-md" data-hour="{{ $hour }}">
                                                            View Details
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr class="hourly-details hidden" id="details-{{ $hour }}">
                                                    <td colspan="3">
                                                        <table class="w-full border-collapse border border-gray-300 mt-2">
                                                            <thead class="bg-gray-100">
                                                                <tr>
                                                                    <th class="border px-4 py-2">User</th>
                                                                    <th class="border px-4 py-2">Quantity</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                            <tbody>
                                                                @foreach ($hourlyDetails[$hour] as $user => $userData)
                                                                    <tr>
                                                                        <td class="border px-4 py-2 flex items-center">
                                                                            <img src="{{ $userData['user_profile_path'] }}" alt="{{ $user }}" class="w-20 h-20 rounded-full mr-2">
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
                                                <th class="border px-4 py-2">Planned Quantity</th>
                                                <th class="border px-4 py-2">Quantity Produksi</th>
                                                <th class="border px-4 py-2">Start Time</th>
                                                <th class="border px-4 py-2">End Time</th>
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
                                                    <td class="border px-4 py-2">{{ $dailyItem['shift'] }}</td>
                                                    <td class="border px-4 py-2">{{ $dailyItem['quantity'] }}</td>
                                                    <td class="border px-4 py-2">{{ $dailyItem['total_scanned_quantity'] }}</td>
                                                    <td class="border px-4 py-2">
                                                    {{ $dailyItem['start_date'] }} {{ \Carbon\Carbon::parse($dailyItem['start_time'])->subHours(7)->format('H:i') }}
                                                    </td>
                                                    <td class="border px-4 py-2">
                                                    {{ $dailyItem['end_date'] }} {{ \Carbon\Carbon::parse($dailyItem['end_time'])->subHours(7)->format('H:i') }}
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
                                                        $spkKey = $dailyItem['item_code'] . '-' . $scan['spk_code']; // Unique key (Item Code + SPK Code)
                                                        
                                                        if (!isset($spkSummary[$spkKey])) {
                                                            $spkSummary[$spkKey] = [
                                                                'item_code' => $dailyItem['item_code'],
                                                                'spk_code' => $scan['spk_code'],
                                                                'accumulated_quantity' => 0,
                                                            ];
                                                            $spkDetails[$spkKey] = [];
                                                        }
                                                        
                                                        $spkSummary[$spkKey]['accumulated_quantity'] += $scan['quantity'];

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

</script>
</x-dashboard-layout>
