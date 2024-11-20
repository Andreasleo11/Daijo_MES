<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-semibold text-gray-800">Workshop Log Details</h1>
    </x-slot>

    <div class="container mx-auto p-6">
        <!-- Job Information Section -->
        <div class="mb-6 p-4 bg-white rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Job Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p><strong>User ID:</strong> {{ $job->user_id }}</p>
                    <p><strong>Scan Start:</strong> {{ $job->scan_start }}</p>
                    <p><strong>Created At:</strong> {{ $job->created_at }}</p>
                </div>
                <div>
                    <p><strong>Scan Finish:</strong> {{ $job->scan_finish ?? 'Not Finished' }}</p>
                    <p><strong>Updated At:</strong> {{ $job->updated_at }}</p>
                </div>
            </div>
        </div>

        <!-- Material Log Information Section -->
        <div class="mb-6 p-4 bg-white rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Material Log Information</h2>
            @if($log)
                <div>
                    <p><strong>Log ID:</strong> {{ $log->id }}</p>
                    <p><strong>Process Name:</strong> {{ $log->process_name }}</p>
                    <p><strong>Scan In:</strong> {{ $log->scan_in }}</p>
                    <p><strong>Scan Out:</strong> {{ $log->scan_out ?? 'Not Available' }}</p>
                    <p><strong>Status:</strong> {{ $log->status }}</p>
                    <p><strong>Created At:</strong> {{ $log->created_at }}</p>
                    <p><strong>Updated At:</strong> {{ $log->updated_at }}</p>
                </div>

                <!-- Child Data Section -->
                @if($log->childData)
                    <hr class="my-4">
                    <h3 class="text-lg font-semibold mb-2">Child Data</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p><strong>Child ID:</strong> {{ $log->childData->id }}</p>
                            <p><strong>Item Code:</strong> {{ $log->childData->item_code }}</p>
                            <p><strong>Item Description:</strong> {{ $log->childData->item_description }}</p>
                            <p><strong>Quantity:</strong> {{ $log->childData->quantity }}</p>
                            <p><strong>Measure:</strong> {{ $log->childData->measure }}</p>
                            <p><strong>Status:</strong> {{ $log->childData->status }}</p>
                        </div>

                        <!-- Parent Data Section -->
                        @if($log->childData->parent)
                            <div>
                                <h4 class="text-lg font-semibold mb-2">Parent Data</h4>
                                <p><strong>Parent ID:</strong> {{ $log->childData->parent->id }}</p>
                                <p><strong>Parent Item Code:</strong> {{ $log->childData->parent->item_code }}</p>
                                <p><strong>Parent Item Description:</strong> {{ $log->childData->parent->item_description }}</p>
                                <p><strong>Parent Type:</strong> {{ $log->childData->parent->type }}</p>
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
    </div>

    <hr class="my-4">
                <div class="p-4 bg-gray-100 rounded-lg shadow">
                    <form id="scan-out-form" action="{{ route('workshop.scan_out') }}" method="POST">
                        @csrf
                        <input type="hidden" name="log_id" value="{{ $log->id }}">
                        <input type="hidden" name="job_id" value="{{ $job->id }}">
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
</x-app-layout>
