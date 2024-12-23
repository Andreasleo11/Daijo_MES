<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-semibold text-gray-800">Workshop Log Details</h1>
        <div class="mb-6">
            <a href="{{ route('workshop.main.menu') }}"
               class="inline-block bg-green-500 text-white px-4 py-2 text-sm font-medium rounded hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-400">
                Back to Main Menu
            </a>
        </div>
    </x-slot>

    <div class="flex flex-wrap gap-4">
    @foreach($allprocess as $process)
        <div class="w-full sm:w-1/2 md:w-1/3 lg:w-1/4 mb-3">
            <div class="card {{ $process->status == 2 ? 'border-green-500' : 'border-red-500' }} border-2 rounded-lg shadow-sm">
                <div class="card-body {{ $process->status == 2 ? 'bg-green-100' : 'bg-red-100' }} p-4 rounded-lg">
                    <h5 class="text-xl font-semibold mb-2">{{ $process->process_name }}</h5>
                    <p class="text-lg">
                        Status: 
                        <strong class="{{ $process->status == 2 ? 'text-green-800' : 'text-red-800' }}">
                            {{ $process->status == 2 ? 'Completed' : 'Pending' }}
                        </strong>
                    </p>
                    @if($process->remark)
                        <p class="mt-2 text-gray-700"><strong>Remark:</strong> {{ $process->remark }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>


    <div class="container mx-auto p-6">
        <!-- Job Information Section -->
        <!-- Material Log Information Section -->

        @if (session('error'))
            <div 
                class="fixed top-5 right-5 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-md z-50"
                role="alert"
            >
                <div class="flex justify-between items-center">
                    <span class="font-semibold">{{ session('error') }}</span>
                    <button 
                        class="ml-4 text-red-700 hover:text-red-900 focus:outline-none"
                        onclick="this.parentElement.parentElement.remove()"
                    >
                        &times;
                    </button>
                </div>
            </div>
        @endif

        @if(is_null($log->scan_start))
                <form action="{{ route('workshop.set_scan_start') }}" method="POST">
                    @csrf
                    <input type="hidden" name="log_id" value="{{ $log->id }}">
                    <button type="submit" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none">Start Work</button>
                </form>
            @endif
      
        <div class="mb-6 p-4 bg-white rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Material Log Information</h2>
            @if($log)
                <div>
                    <p><strong>Log ID:</strong> {{ $log->id }}</p>
                    <p><strong>Process Name:</strong> {{ $log->process_name }}</p>
                    <p><strong>Scan In:</strong> 
                    {{ \Carbon\Carbon::parse($log->scan_in)->format('Y-m-d H:i:s') }}
                </p>
                    <p><strong>Scan Out:</strong>
                    {{ $log->scan_out ? \Carbon\Carbon::parse($log->scan_out)->format('Y-m-d H:i:s') : 'Not Finished' }}
                </p>
                    <!-- <p><strong>Status:</strong> {{ $log->status }}</p> -->
                    <p><strong>Dibuat :</strong>
                    {{ \Carbon\Carbon::parse($log->created_at)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') }}
                </p>
                    <p><strong>Update:</strong> 
                    {{ \Carbon\Carbon::parse($log->updated_at)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') }}
                </p>
                </div>

                <!-- Child Data Section -->
                @if($log->childData)
                    <hr class="my-4">
                    <h3 class="text-lg font-semibold mb-2">Material Data</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p><strong>ID:</strong> {{ $log->childData->id }}</p>
                            <p><strong>Item Code:</strong> {{ $log->childData->item_code }}</p>
                            <p><strong>Item Description:</strong> {{ $log->childData->item_description }}</p>
                            <p><strong>Quantity:</strong> {{ $log->childData->quantity }}</p>
                            <p><strong>Measure:</strong> {{ $log->childData->measure }}</p>
                            <p><strong>Status:</strong> {{ $log->childData->status }}</p>
                        </div>

                        <!-- Parent Data Section -->
                        @if($log->childData->parent)
                            <div>
                                <h4 class="text-lg font-semibold mb-2">BOM/Project Data</h4>
                                <p><strong>BOM/Project code:</strong> {{ $log->childData->parent->code }}</p>
                                <p><strong>BOM/Project Description:</strong> {{ $log->childData->parent->description }}</p>
                                <p><strong>BOM/Project Type:</strong> {{ $log->childData->parent->type }}</p>
                            </div>
                        @else
                            <p>No parent data found for this child.</p>
                        @endif
                    </div>
                @else
                    <p>No child data found for this log.</p>
                @endif
            @else
                <p>No material log found for this job.</p>
            @endif
        </div>

        <!-- Workers Section -->
        <div class="mb-6 p-4 bg-white rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Workers Involved</h2>
            @if($workers->isNotEmpty())
                <table class="table-auto w-full border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border border-gray-300 px-4 py-2">Worker Name</th>
                            <th class="border border-gray-300 px-4 py-2">Shift</th>
                            @if($log->process_name === "MANUAL")
                            <th class="border border-gray-300 px-4 py-2">Job</th>
                            @endif
                            <th class="border border-gray-300 px-4 py-2">Remark</th>
                            <th class="border border-gray-300 px-4 py-2">Created At</th>
                            <th class="border border-gray-300 px-4 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($workers as $worker)
                            <tr>
                                <td class="border border-gray-300 px-4 py-2">{{ $worker->username }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ $worker->shift }}</td>
                                @if($log->process_name === "MANUAL")
                                <th class="border border-gray-300 px-4 py-2">{{ $worker->jobs }}</th>
                                @endif
                                <th class="border border-gray-300 px-4 py-2">{{ $worker->remark }}</th>
                                <td class="border border-gray-300 px-4 py-2">
                                {{ \Carbon\Carbon::parse($worker->created_at)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') }}
                                </td>
                                <td class="border border-gray-300 px-4 py-2">
                                    <!-- Edit Button -->
                                    <button 
                                        class="px-4 py-2 bg-blue-500 text-white rounded-lg" 
                                        onclick="openEditModal({{$worker->id}})">
                                        Edit
                                    </button>

                                    <div id="edit-worker-modal-{{$worker->id}}" class="fixed inset-0 flex justify-center items-center bg-gray-900 bg-opacity-50 hidden">
                                        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                                            <h3 class="text-xl font-semibold mb-4">Edit Worker</h3>
                                            <form action="{{ route('workshop.update.worker') }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" id="worker-id" name="worker_id" value="{{$worker->id}}">

                                                <!-- Worker Name -->
                                                <div class="mb-4">
                                                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Worker Name</label>
                                                    <input 
                                                        type="text" 
                                                        name="username" 
                                                        id="username" 
                                                        class="block w-full p-2 border rounded-lg focus:ring focus:ring-blue-200 focus:outline-none"
                                                        value = "{{ $worker->username }}"
                                                        required
                                                    />
                                                </div>
                                                @if($log->process_name === "MANUAL")
                                                <!-- Job (if applicable) -->
                                                <div class="mb-4">
                                                    <label for="job" class="block text-sm font-medium text-gray-700 mb-2">Job</label>
                                                    <select 
                                                        name="job" 
                                                        id="job" 
                                                        class="job-input block w-full p-2 border rounded-lg focus:ring focus:ring-blue-200 focus:outline-none"
                                                    >
                                                        <option value="">Select Job</option>
                                                        <option value="Grinding" {{ $worker->jobs === 'Grinding' ? 'selected' : '' }} >Grinding</option>
                                                        <option value="Miling" {{ $worker->jobs === 'Miling' ? 'selected' : '' }}>Miling</option>
                                                        <option value="Bor" {{ $worker->jobs === 'Bor' ? 'selected' : '' }}>Bor</option>
                                                        <option value="Bubut" {{ $worker->jobs === 'Bubut' ? 'selected' : '' }}>Bubut</option>
                                                        <option value="Spoting" {{ $worker->jobs === 'Spoting' ? 'selected' : '' }}>Spoting</option>
                                                        <option value="Matching" {{ $worker->jobs === 'Matching' ? 'selected' : '' }}>Matching</option>
                                                        <option value="Weilding" {{ $worker->jobs === 'Weilding' ? 'selected' : '' }}>Weilding</option>
                                                    </select>
                                                </div>
                                                @endif

                                                <!-- Remark -->
                                                <div class="mb-4">
                                                    <label for="remark" class="block text-sm font-medium text-gray-700 mb-2">Remark</label>
                                                    <input 
                                                        type="text" 
                                                        name="remark" 
                                                        id="remark" 
                                                        value = "{{ $worker->remark }}"
                                                        class="block w-full p-2 border rounded-lg focus:ring focus:ring-blue-200 focus:outline-none"
                                                    />
                                                </div>

                                                <!-- Buttons -->
                                                <div class="flex justify-between items-center">
                                                    <button 
                                                        type="button" 
                                                        class="px-4 py-2 bg-gray-500 text-white rounded-lg"
                                                        onclick="closeEditModal({{$worker->id}})"
                                                    >
                                                        Cancel
                                                    </button>
                                                    <button 
                                                        type="submit" 
                                                        class="px-4 py-2 bg-blue-500 text-white rounded-lg"
                                                    >
                                                        Save Changes
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
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
                <button 
                    type="button" 
                    class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg"
                    onclick="document.getElementById('add-worker-modal').classList.remove('hidden')"
                >
                    Add Worker
                </button>
            @endif
        </div>

        <div class="mb-6 p-4 bg-white rounded-lg shadow-md">
            <h1>Remark</h1>
            <!-- Check if scan_out is null -->
            @if ($log->scan_out === null)
                <!-- Display remark and button logic -->
                @if ($log->remark === null)
                    <!-- If remark is null, show the "Create" button -->
                    <p>No remark added yet.</p>
                    <button type="button" class="btn btn-primary" data-modal-toggle="createRemarkModal">
                        Create Remark
                    </button>
                @else
                    <!-- If remark is not null, show the "Edit" button -->
                    <p>{{ $log->remark }}</p>
                    <button type="button" class="btn btn-secondary" data-modal-toggle="editRemarkModal">
                        Edit Remark
                    </button>
                @endif
            @else
                <!-- If scan_out is not null, hide the buttons -->
                <p>{{ $log->remark }}</p>
            @endif
        </div>

        <!-- Modal for creating a remark -->
        <div id="createRemarkModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
            <div class="bg-white rounded-lg w-full max-w-lg p-6">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-semibold">Create Remark</h2>
                    <button type="button" class="text-gray-600" onclick="closeModal('createRemarkModal')">
                        <span class="text-xl">&times;</span>
                    </button>
                </div>
                <form action="{{ route('remark.store', ['log_id' => $log->id]) }}" method="POST">
                    @csrf
                    <div class="mt-4">
                        <label for="remark" class="block text-sm font-medium text-gray-700">Remark</label>
                        <textarea id="remark" name="remark" class="mt-2 w-full p-2 border border-gray-300 rounded-md" required>{{ old('remark') }}</textarea>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="button" class="px-4 py-2 text-white bg-gray-500 rounded-md mr-2" onclick="closeModal('createRemarkModal')">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-white bg-blue-500 rounded-md">Save Remark</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal for editing a remark -->
        <div id="editRemarkModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
            <div class="bg-white rounded-lg w-full max-w-lg p-6">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-semibold">Edit Remark</h2>
                    <button type="button" class="text-gray-600" onclick="closeModal('editRemarkModal')">
                        <span class="text-xl">&times;</span>
                    </button>
                </div>
                <form action="{{ route('remark.store', ['log_id' => $log->id]) }}" method="POST">
                    @csrf
                    @method('POST') <!-- Or @method('PUT') if you're updating an existing resource -->
                    <div class="mt-4">
                        <label for="remark" class="block text-sm font-medium text-gray-700">Remark</label>
                        <textarea id="remark" name="remark" class="mt-2 w-full p-2 border border-gray-300 rounded-md" required>{{ $log->remark }}</textarea>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="button" class="px-4 py-2 text-white bg-gray-500 rounded-md mr-2" onclick="closeModal('editRemarkModal')">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-white bg-blue-500 rounded-md">Update Remark</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add Worker Modal -->
        <div id="add-worker-modal" class="fixed inset-0 flex justify-center items-center bg-gray-900 bg-opacity-50 hidden">
            <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                <h3 class="text-xl font-semibold mb-4">Add Involved Worker</h3>
                <form action="{{ route('workshop.add.worker') }}" method="POST">
                    @csrf
                    <input type="hidden" name="log_id" value="{{ $log->id }}">

                    <div class="mb-4">
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Worker Name</label>
                        <input 
                            type="text" 
                            name="username" 
                            id="username" 
                            class="block w-full p-2 border rounded-lg focus:ring focus:ring-blue-200 focus:outline-none"
                            required 
                        />
                    </div>

                    @if($log->process_name === "MANUAL")
                        <div class="mb-4">
                            <label for="job" class="block text-sm font-medium text-gray-700 mb-2">Job</label>
                            <select 
                                name="job" 
                                id="job" 
                                class="job-input block w-full p-2 border rounded-lg focus:ring focus:ring-blue-200 focus:outline-none"
                            >
                                <option value="">Select Job</option>
                                <option value="Grinding">Grinding</option>
                                <option value="Miling">Miling</option>
                                <option value="Bor">Bor</option>
                                <option value="Bubut">Bubut</option>
                                <option value="Spoting">Spoting</option>
                                <option value="Matching">Matching</option>
                                <option value="Weilding">Weilding</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    @endif
                        <!-- Remark Worker -->
                        <div class="mb-4">
                            <label for="remark_worker" class="block text-sm font-medium text-gray-700 mb-2">Remark (Optional)</label>
                            <textarea 
                                name="remark_worker" 
                                id="remark_worker" 
                                class="block w-full p-2 border rounded-lg focus:ring focus:ring-blue-200 focus:outline-none"
                                rows="4"
                            ></textarea>
                        </div>
                    

                    <div class="flex justify-between items-center">
                        <button 
                            type="button" 
                            class="px-4 py-2 bg-gray-500 text-white rounded-lg"
                            onclick="document.getElementById('add-worker-modal').classList.add('hidden')"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-2 bg-blue-500 text-white rounded-lg"
                        >
                            Add Worker
                        </button>
                    </div>
                </form>
            </div>
        </div>

       

        <!-- Scan Out Form -->
        @if (is_null($log->scan_out) && !is_null($log->scan_start))
            <div class="p-4 bg-gray-100 rounded-lg shadow">
                <form id="scan-out-form" action="{{ route('workshop.scan_out') }}" method="POST">
                    @csrf
                    <input type="hidden" name="log_id" value="{{ $log->id }}">
                    <input type="hidden" name="child_id" value="{{ $log->child_id }}">
                    <label for="scan-out" class="block text-sm font-medium text-gray-700 mb-2">Scan Out</label>
                    <input 
                        type="text" 
                        name="scan_out" 
                        id="scan-out" 
                        class="block w-full p-2 border rounded-lg focus:ring focus:ring-blue-200 focus:outline-none" 
                        placeholder="Scan here to complete the process" 
                        autofocus 
                        required
                        oninput="document.getElementById('scan-out-form').submit();"
                    >
                </form>
            </div>
        @endif

    </div>

    <script>

    function openEditModal(workerId) {
        // Show the modal
        document.getElementById(`edit-worker-modal-${workerId}`).classList.remove('hidden');
    }

    // Close the modal
    function closeEditModal(workerId) {
        document.getElementById(`edit-worker-modal-${workerId}`).classList.add('hidden');
    }

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
            selectElement.addEventListener('change', function () {
                const selectedValue = selectElement.value;
                const inputField = selectElement.querySelector('.tom-input'); // Get the input field of TomSelect

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
    button.addEventListener('click', function () {
        const modalId = button.getAttribute('data-modal-toggle');
        document.getElementById(modalId).classList.remove('hidden');
    });
});
    </script>
</x-app-layout>
