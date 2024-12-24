<x-app-layout>
    @if (session('error'))
        <div class="fixed top-5 right-5 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-md z-50"
            role="alert">
            <div class="flex justify-between items-center">
                <span class="font-semibold">{{ session('error') }}</span>
                <button class="ml-4 text-red-700 hover:text-red-900 focus:outline-none"
                    onclick="this.parentElement.parentElement.remove()">
                    &times;
                </button>
            </div>
        </div>
    @endif

    <div class="py-6 container mx-auto space-y-2">
        <h1 class="text-3xl font-semibold text-gray-800">Workshop Log Details</h1>
        <div>
            <a href="{{ route('workshop.main.menu') }}"
                class="inline-block text-blue-500 text-sm font-medium hover:text-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-75 transition">
                ← Back to Main Menu
            </a>
        </div>
    </div>
    <div class="py-6 container mx-auto space-y-8">

        <!-- Status Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach ($allprocess as $process)
                <div
                    class="p-6 rounded-lg shadow-md border {{ $process->status == 2 ? 'border-green-500 bg-green-100' : 'border-red-500 bg-red-100' }} hover:shadow-lg transition">
                    <h3 class="text-xl font-semibold text-gray-800">{{ $process->process_name }}</h3>
                    <p class="mt-2 text-lg">
                        Status:
                        <span class="font-bold {{ $process->status == 2 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $process->status == 2 ? 'Completed' : 'Pending' }}
                        </span>
                    </p>
                    @if ($process->remark)
                        <p class="mt-2 text-sm text-gray-600">
                            <strong>Remark:</strong> {{ $process->remark }}
                        </p>
                    @endif
                </div>
            @endforeach
        </div>

        @if (is_null($log->scan_start))
            <form action="{{ route('workshop.set_scan_start') }}" method="POST">
                @csrf
                <input type="hidden" name="log_id" value="{{ $log->id }}">
                <button type="submit"
                    class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none">Start
                    Work</button>
            </form>
        @endif

        <!-- Material Log Information -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Material Log Information</h2>
            @if ($log)
                <!-- Log Details -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                    <div class="space-y-2">
                        <p class="text-lg text-gray-700">
                            <strong class="font-medium text-gray-900">Log ID:</strong> {{ $log->id }}
                        </p>
                        <p class="text-lg text-gray-700">
                            <strong class="font-medium text-gray-900">Process Name:</strong> {{ $log->process_name }}
                        </p>
                        <p class="text-lg text-gray-700">
                            <strong class="font-medium text-gray-900">Scan In:</strong>
                            {{ \Carbon\Carbon::parse($log->scan_in)->format('Y-m-d H:i:s') }}
                        </p>
                    </div>
                    <div class="space-y-2">
                        <p class="text-lg text-gray-700">
                            <strong class="font-medium text-gray-900">Scan Out:</strong>
                            {{ $log->scan_out ? \Carbon\Carbon::parse($log->scan_out)->format('Y-m-d H:i:s') : 'Not Finished' }}
                        </p>
                        <p class="text-lg text-gray-700">
                            <strong class="font-medium text-gray-900">Created At:</strong>
                            {{ \Carbon\Carbon::parse($log->created_at)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') }}
                        </p>
                        <p class="text-lg text-gray-700">
                            <strong class="font-medium text-gray-900">Updated At:</strong>
                            {{ \Carbon\Carbon::parse($log->updated_at)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') }}
                        </p>
                    </div>
                </div>

                <!-- Child Data -->
                @if ($log->childData)
                    <hr class="my-6">
                    <h3 class="text-2xl font-semibold text-gray-800 mb-4">Material Data</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <!-- Child Info -->
                        <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                            <h4 class="text-lg font-semibold text-gray-800 mb-3">Child Info</h4>
                            <p class="text-gray-700"><strong>ID:</strong> {{ $log->childData->id }}</p>
                            <p class="text-gray-700"><strong>Item Code:</strong> {{ $log->childData->item_code }}</p>
                            <p class="text-gray-700"><strong>Description:</strong>
                                {{ $log->childData->item_description }}</p>
                            <p class="text-gray-700"><strong>Quantity:</strong> {{ $log->childData->quantity }}</p>
                            <p class="text-gray-700"><strong>Measure:</strong> {{ $log->childData->measure }}</p>
                            <p class="text-gray-700">
                                <strong>Status:</strong>
                                <span
                                    class="{{ $log->childData->status === 'Active' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $log->childData->status }}
                                </span>
                            </p>
                        </div>

                        <!-- Parent Info -->
                        @if ($log->childData->parent)
                            <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                                <h4 class="text-lg font-semibold text-gray-800 mb-3">Parent Info</h4>
                                <p class="text-gray-700"><strong>Code:</strong> {{ $log->childData->parent->code }}</p>
                                <p class="text-gray-700"><strong>Description:</strong>
                                    {{ $log->childData->parent->description }}</p>
                                <p class="text-gray-700"><strong>Type:</strong> {{ $log->childData->parent->type }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            @else
                <p class="text-lg text-gray-600">No material log found for this job.</p>
            @endif
        </div>


        <!-- Workers Section -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Workers Involved</h2>
            @if ($workers->isNotEmpty())
                <table class="w-full border-collapse border border-gray-300 text-left text-gray-800">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-gray-300 px-4 py-2">Worker Name</th>
                            <th class="border border-gray-300 px-4 py-2">Shift</th>
                            @if ($log->process_name === 'MANUAL')
                                <th class="border border-gray-300 px-4 py-2">Job</th>
                            @endif
                            <th class="border border-gray-300 px-4 py-2">Remark</th>
                            <th class="border border-gray-300 px-4 py-2">Created At</th>
                            <th class="border border-gray-300 px-4 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($workers as $worker)
                            <tr class="hover:bg-gray-50">
                                <td class="border border-gray-300 px-4 py-2">{{ $worker->username }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ $worker->shift }}</td>
                                @if ($log->process_name === 'MANUAL')
                                    <th class="border border-gray-300 px-4 py-2">{{ $worker->jobs }}</th>
                                @endif
                                <th class="border border-gray-300 px-4 py-2">{{ $worker->remark }}</th>
                                <td class="border border-gray-300 px-4 py-2">
                                    {{ \Carbon\Carbon::parse($worker->created_at)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') }}
                                </td>
                                <td class="border border-gray-300 px-4 py-2">
                                    <!-- Edit Button -->
                                    <button class="px-4 py-2 bg-blue-500 text-white rounded-lg"
                                        onclick="openEditModal({{ $worker->id }})">
                                        Edit
                                    </button>

                                    @include('edit-worker-modal', ['worker' => $worker, 'log' => $log])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No workers have been logged for this job yet.</p>
            @endif
            <!-- Add Worker Button -->
            @if (is_null($log->scan_out))
                <button type="button" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg"
                    onclick="document.getElementById('add-worker-modal').classList.remove('hidden')">
                    Add Worker
                </button>
            @endif
        </div>

        <div class="p-4 bg-white rounded-lg shadow-md">
            <h1>Remark</h1>
            <!-- Check if scan_out is null -->
            @if ($log->scan_out === null)
                <!-- Display remark and button logic -->
                @if ($log->remark === null)
                    <!-- If remark is null, show the "Create" button -->
                    <p class="text-gray-600">No remark added yet.</p>
                    <button type="button"
                        class="bg-blue-500 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-75"
                        data-modal-toggle="add-remark-modal">
                        Create Remark
                    </button>
                @else
                    <!-- If remark is not null, show the "Edit" button -->
                    <p class="text-gray-800">{{ $log->remark }}</p>
                    <button type="button"
                        class="bg-blue-500 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-75"
                        data-modal-toggle="edit-remark-modal">
                        Edit Remark
                    </button>
                @endif
            @else
                <!-- If scan_out is not null, hide the buttons -->
                <p>{{ $log->remark }}</p>
            @endif
        </div>

        <!-- Modal for creating a remark -->
        @include('includes.add-remark-modal')

        <!-- Modal for editing a remark -->
        @include('includes.edit-remark-modal', ['log' => $log])

        <!-- Add Worker Modal -->
        @include('includes.add-worker-modal', ['log' => $log])

        <!-- Scan Out Form -->
        @if (is_null($log->scan_out) && !is_null($log->scan_start))
            <div class="p-4 bg-red-500 rounded-lg shadow">
                <form id="scan-out-form" action="{{ route('workshop.scan_out') }}" method="POST">
                    @csrf
                    <input type="hidden" name="log_id" value="{{ $log->id }}">
                    <input type="hidden" name="child_id" value="{{ $log->child_id }}">
                    <label for="scan-out" class="block text-lg font-medium text-white">Scan Out</label>
                    <span class="block text-sm text-gray-200">Proceed this scan when
                        the process is done</span>
                    <input type="text" name="scan_out" id="scan-out"
                        class="mt-4 block w-full p-2 border rounded-lg focus:ring focus:ring-red-200 focus:outline-none bg-red-100"
                        placeholder="Scan here to complete the process" autofocus required
                        oninput="document.getElementById('scan-out-form').submit();">
                </form>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Apply TomSelect to all select elements with the class .job-input
            const jobSelects = document.querySelectorAll('.job-input');

            jobSelects.forEach(function(selectElement) {
                new TomSelect(selectElement, {
                    create: true, // Allow the user to create new job options
                    sortField: 'text', // Sort items by text
                    placeholder: 'Select or type your job', // Placeholder text
                });
            });

            // Optional: If you want the "Other" option to trigger an editable input
            jobSelects.forEach(function(selectElement) {
                selectElement.addEventListener('change', function() {
                    const selectedValue = selectElement.value;
                    const inputField = selectElement.querySelector(
                        '.tom-input'); // Get the input field of TomSelect

                    // If "Other" is selected, focus on the input field for custom job title
                    if (selectedValue === 'Other') {
                        inputField.placeholder = 'Enter custom job title';
                    } else {
                        inputField.placeholder = 'Select or type your job';
                    }
                });
            });
        });

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            // Get all modals
            const modals = document.querySelectorAll('.modal');

            // Loop through each modal and close if clicked outside
            modals.forEach(function(modal) {
                if (event.target == modal) {
                    modal.classList.add('hidden');
                }
            });
        };

        // Close the modal when the close button is clicked
        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        // Open the modal when clicking on the button
        document.querySelectorAll('[data-modal-toggle]').forEach(button => {
            button.addEventListener('click', function() {
                const modalId = button.getAttribute('data-modal-toggle');
                document.getElementById(modalId).classList.remove('hidden');
            });
        });
    </script>
</x-app-layout>
