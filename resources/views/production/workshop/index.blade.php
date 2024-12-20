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
                            <th class="border border-gray-300 px-4 py-2">Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($workers as $worker)
                            <tr>
                                <td class="border border-gray-300 px-4 py-2">{{ $worker->username }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ $worker->shift }}</td>
                                <td class="border border-gray-300 px-4 py-2">
                                {{ \Carbon\Carbon::parse($worker->created_at)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') }}
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
        @if (is_null($log->scan_out))
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
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('add-worker-modal');
            if (event.target == modal) {
                modal.classList.add('hidden');
            }
        };
    </script>
</x-app-layout>
