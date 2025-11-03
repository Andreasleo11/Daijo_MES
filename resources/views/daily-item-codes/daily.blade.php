<x-app-layout>
    <!-- Display Success Message -->
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Display Error Message -->
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- View Assigned Item Codes -->
    <div class="py-12">
        <div class="sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div>
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                            <li class="inline-flex items-center">
                                <a href="{{ route('daily-item-code.index') }}"
                                    class="inline-flex items-center text-sm font-medium text-gray-400 hover:underline">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-3 h-3 me-2.5 size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                    </svg>
                                    Daily Production Calendar
                                </a>
                            </li>
                            <li aria-current="page">
                                <div class="flex items-center">
                                    <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m1 9 4-4-4-4" />
                                    </svg>
                                    <span
                                        class="ms-1 text-sm font-medium text-gray-500 md:ms-2 dark:text-gray-400">Daily
                                        Production Plan</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                </div>

                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl text-gray-700">Daily Production Plan for
                        <span class="font-bold text-black">
                            {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}
                        </span>
                    </h1>
                    <!-- Search Input -->
                    <div class="justify-end mb-4">
                        <label for="search" class="mr-2">Search:</label>
                        <input type="text" id="search" onkeyup="searchTable()"
                            class="border-gray-300 rounded-md shadow-sm" placeholder="Search by Machine Name...">
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="w-full p-4">
                        <div class="bg-white shadow-md rounded-lg p-6">
                            <h2 class="text-lg font-semibold mb-4">Assigned Item Codes</h2>
                            <table class="w-full" id="machine-table">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="py-3 px-4 text-left">Machine Name</th>
                                        <th class="py-3 px-4 text-left">Status</th>
                                        <th class="py-3 px-4 text-left">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($machines as $machine)
                                        @php
                                            $hasAssignedData =
                                                $machine->dailyItemCode->where('schedule_date', $selectedDate) &&
                                                count($machine->dailyItemCode->where('schedule_date', $selectedDate)) >
                                                    0;
                                        @endphp

                                        <!-- Summary Row (collapsible) -->
                                        <tr class="bg-white machine-row"
                                            data-status="{{ $hasAssignedData ? 'assigned' : 'not-assigned' }}">
                                            <!-- Machine Name -->
                                            <td class="py-2 px-4 border-b font-bold">{{ $machine->name }}</td>

                                            <!-- Status Badge -->
                                            <td class="py-2 px-4 border-b">
                                                @if ($hasAssignedData)
                                                    <span
                                                        class="inline-block px-3 py-1 bg-green-100 text-green-800 rounded-full">Assigned</span>
                                                @else
                                                    <span
                                                        class="inline-block px-3 py-1 bg-red-100 text-red-800 rounded-full">Not
                                                        Assigned</span>
                                                @endif
                                            </td>

                                            <td class="py-2 px-4 border-b text-blue-500">
                                                <button
                                                    class="items-center px-4 py-2 {{ $hasAssignedData ? 'bg-blue-50 border border-blue-400 text-blue-400 hover:bg-blue-500 hover:text-white hover:border-transparent' : 'bg-gray-200 text-gray-400 cursor-not-allowed' }} rounded-md "
                                                    {{ $hasAssignedData ? '' : 'disabled' }}
                                                    onclick="toggleRowDetails({{ $machine->id }})">Show
                                                    Details</button>

                                                <form action="{{ route('daily-item-code.create') }}" method="GET"
                                                    id="create-form-{{ $machine->id }}" class="inline-block">
                                                    <input type="hidden" name="selected_date"
                                                        id="selected-date-{{ $machine->id }}"
                                                        value="{{ $selectedDate }}">
                                                    <input type="hidden" name="machine_id" id="id-{{ $machine->id }}"
                                                        value="{{ $machine->id }}">
                                                    <button type="submit"
                                                        class="ms-2 items-center px-4 py-2 bg-blue-500 rounded-md text-white hover:bg-blue-600">Assign</button>
                                                </form>

                                            </td>
                                        </tr>

                                        <!-- Collapsible Details Row -->
                                        <tr id="details-row-{{ $machine->id }}" class="hidden">
                                            <td colspan="3" class="py-2 px-4 bg-gray-50">
                                                @if ($hasAssignedData)
                                                    <div class="p-4 border rounded-lg bg-white shadow-sm">
                                                        <h3 class="text-lg font-semibold mb-3">Details for
                                                            {{ $machine->name }}</h3>

                                                        <table class="w-full text-sm">
                                                            <thead>
                                                                <tr class="border-b">
                                                                    <th class="py-2 px-4 text-left">Item Code</th>
                                                                    <th class="py-2 px-4 text-left">Quantity</th>
                                                                    <th class="py-2 px-4 text-left">Shift</th>
                                                                    <th class="py-2 px-4 text-left">Schedule Date</th>
                                                                    <th class="py-2 px-4 text-left">Start Date</th>
                                                                    <th class="py-2 px-4 text-left">Start Time</th>
                                                                    <th class="py-2 px-4 text-left">End Date</th>
                                                                    <th class="py-2 px-4 text-left">End Time</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="details-body-{{ $machine->id }}">
                                                                @foreach ($machine->dailyItemCode->filter(function ($itemCode) use ($selectedDate) {
        return $itemCode->schedule_date == $selectedDate;
    }) as $itemCode)
                                                                    <tr class="border-b details-row"
                                                                        data-schedule-date="{{ $itemCode->schedule_date }}">
                                                                        <td class="py-2 px-4">
                                                                            {{ $itemCode->item_code }}</td>
                                                                        <td class="py-2 px-4">
                                                                            {{ $itemCode->quantity }}</td>
                                                                        <td class="py-2 px-4">{{ $itemCode->shift }}
                                                                        </td>
                                                                        <td class="py-2 px-4">
                                                                            {{ $itemCode->schedule_date }}</td>
                                                                        <td class="py-2 px-4">
                                                                            {{ $itemCode->start_date }}</td>
                                                                        <td class="py-2 px-4">
                                                                            {{ $itemCode->start_time }}</td>
                                                                        <td class="py-2 px-4">
                                                                            {{ $itemCode->end_date }}</td>
                                                                        <td class="py-2 px-4">
                                                                            {{ $itemCode->end_time }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @else
                                                    <span class="text-red-500">No data assigned for this machine</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for filtering and toggling details -->
    <script>
        function toggleRowDetails(machineId) {
            var detailsRow = document.getElementById('details-row-' + machineId);
            detailsRow.classList.toggle('hidden');
        }

        function searchTable() {
            var input = document.getElementById('search').value.toLowerCase();
            var rows = document.querySelectorAll('.machine-row');

            rows.forEach(function(row) {
                var machineName = row.cells[0].textContent.toLowerCase();

                if (machineName.includes(input)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</x-app-layout>
