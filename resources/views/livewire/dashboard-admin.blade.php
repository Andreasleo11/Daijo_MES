<div class="p-4">
    <div class="p-6 bg-gray-100">
        <h1 class="text-2xl font-bold mb-4">Dashboard</h1>

        <!-- Summary Cards -->
        <div class="grid grid-cols-4 gap-4">
            <div class="p-4 bg-white rounded shadow">
                <h2 class="text-lg font-semibold">Total BOM</h2>
                <p class="text-2xl">{{ count($parents) }}</p>
            </div>
            <div class="p-4 bg-white rounded shadow">
                <h2 class="text-lg font-semibold">Overall Completion Percentage</h2>
                <p class="text-2xl">{{ round($overallCompletionPercentage, 2) }}%</p>
            </div>
            <div class="p-4 bg-white rounded shadow">
                <h2 class="text-lg font-semibold">Pending Items</h2>
                <p class="text-2xl">{{ $totalPendingChildren }}</p>
            </div>
            <div class="p-4 bg-white rounded shadow">
                <h2 class="text-lg font-semibold">Completed Items</h2>
                <p class="text-2xl">{{ $completedItems }}</p>
            </div>
        </div>

        <div class="mt-8">
            <h2 class="text-lg font-semibold mb-4">BOM</h2>


            @foreach ($parents as $parent)
                <!-- Parent Card -->
                <div class="mb-6 p-4 rounded shadow-lg bg-white border">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-bold">{{ $parent->code }}</h3>
                            <p class="text-sm text-gray-500">{{ $parent->description }} |
                                {{ $parent->type }}</p>
                            <p class="text-sm text-gray-500">{{ $parent->customer }}</p>
                        </div>
                        <div>
                            <!-- Completion Percentage for Parent -->
                            @php
                                $finishedChildren = 0; // Count of children considered fully finished
                                $totalChildren = $childs->where('parent_id', $parent->id)->count(); // Total children under the parent

                                foreach ($childs->where('parent_id', $parent->id) as $child) {
                                    $processCount = $materialLogs->where('child_id', $child->id)->count();
                                    $finishedCount = $materialLogs
                                        ->where('child_id', $child->id)
                                        ->filter(fn($log) => $log->status == 2)
                                        ->count();

                                    // Check if child is fully finished
                                    if (
                                        ($child->action_type === 'buyfinish' && $child->status === 'Finished') ||
                                        $child->action_type === 'stockfinish'
                                    ) {
                                        $finishedChildren++;
                                    } elseif (
                                        $child->action_type === 'stockprocess' ||
                                        $child->action_type === 'buyprocess'
                                    ) {
                                        // For children in progress, count as finished only if all processes are done
                                        if ($processCount > 0 && $finishedCount === $processCount) {
                                            $finishedChildren++;
                                        } else {
                                            if ($processCount > 0) {
                                                    $finishedChildren += $finishedCount / $processCount;
                                                } else {
                                                    // Handle the case where processCount is 0
                                                    $finishedChildren += 0; // or handle it as per your requirement
                                                }
                                        }
                                    }
                                }

                                // Calculate the completion percentage for the parent
                                $parentCompletionPercentage =
                                    $totalChildren > 0 ? ($finishedChildren / $totalChildren) * 100 : 0;
                            @endphp

                            <div class="text-right">
                                <p class="text-lg font-semibold">{{ round($parentCompletionPercentage, 2) }}%
                                    Completed
                                </p>
                                <div class="w-48 bg-gray-300 h-4 rounded-full overflow-hidden">
                                    <div class="bg-green-500 h-4" style="width: {{ $parentCompletionPercentage }}%;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Child Items -->
                    <div class="mt-4">
                        <h4 class="text-md font-semibold mb-2">Item Codes</h4>
                        <div class="grid grid-cols-1 gap-4">
                            @foreach ($childs->where('parent_id', $parent->id) as $child)
                                <!-- Progress Calculation for Each Child -->
                                @php
                                    $childProgress = 0;

                                    if ($child->action_type === 'buyfinish') {
                                        $childProgress = $child->status === 'Finished' ? 100 : 0;
                                    } elseif ($child->action_type === 'stockfinish') {
                                        $childProgress = 100;
                                    } elseif (in_array($child->action_type, ['stockprocess', 'buyprocess'])) {
                                        $processCount = $materialLogs->where('child_id', $child->id)->count();
                                        $finishedCount = $materialLogs
                                            ->where('child_id', $child->id)
                                            ->filter(fn($log) => $log->status == 2)
                                            ->count();
                                        $childProgress = $processCount > 0 ? ($finishedCount / $processCount) * 100 : 0;
                                    }
                                @endphp

                                @if ($childProgress === 100)
                                    <!-- Completed Badge -->
                                    <div class="p-4 rounded shadow bg-green-50 border border-green-500">
                                        <p class="text-lg font-semibold text-green-700">{{ $child->item_code }} -
                                            {{ $child->item_description }}</p>
                                        <p class="text-sm text-gray-600">Quantity: {{ $child->quantity }}
                                            {{ $child->measure }}</p>
                                        <p class="text-sm text-gray-600">
                                            Action Type:
                                            <span class="font-semibold">
                                                {{ $child->action_type }}
                                            </span>
                                        </p>
                                        <div class="mt-2">
                                            <span
                                                class="px-4 py-1 text-sm font-semibold text-white bg-green-500 rounded-full">
                                                Completed
                                            </span>
                                        </div>
                                    </div>
                                @elseif ($child->action_type === 'buyfinish')
                                    <!-- Buyfinish Card with Status Badge -->
                                    <div class="p-4 rounded shadow bg-yellow-50 border border-yellow-500">
                                        <p class="text-lg font-semibold text-yellow-700">{{ $child->item_code }} -
                                            {{ $child->item_description }}</p>
                                        <p class="text-sm text-gray-600">Quantity: {{ $child->quantity }}
                                            {{ $child->measure }}</p>
                                        <p class="text-sm text-gray-600">
                                            Action Type:
                                            <span class="font-semibold">
                                                {{ $child->action_type }}
                                            </span>
                                        </p>
                                        <div class="mt-2">
                                            <span
                                                class="px-4 py-1 text-sm font-semibold text-white bg-yellow-500 rounded-full">
                                                {{ $child->status }}
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <!-- Regular Card with Progress -->
                                    <div class="p-4 rounded shadow bg-gray-100 border">
                                        <p class="text-lg font-semibold">{{ $child->item_code }} -
                                            {{ $child->item_description }}</p>
                                        <p class="text-sm text-gray-600">Quantity: {{ $child->quantity }}
                                            {{ $child->measure }}</p>
                                        <p class="text-sm text-gray-600">
                                            Action Type:
                                            <span class="font-semibold">
                                                {{ $child->action_type }}
                                            </span>
                                        </p>

                                        <!-- Progress Bar for Child -->
                                        <div class="mt-2">
                                            <p class="text-sm font-semibold">
                                                Progress: {{ round($childProgress, 2) }}%
                                            </p>
                                            <div class="w-full bg-gray-300 h-4 rounded-full overflow-hidden">
                                                <div class="bg-blue-500 h-4" style="width: {{ $childProgress }}%;">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Process Details -->
                                        @if (!in_array($child->action_type, ['buyfinish', 'stockfinish']))
                                            <div class="mt-4">
                                                <h5 class="text-sm font-semibold mb-2">Processes</h5>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
                                                    @forelse ($materialLogs->where('child_id', $child->id) as $log)
                                                        @php
                                                            $statusClass = 'bg-red-500'; // Default to Not Started
                                                            if ($log->status == 1) {
                                                                $statusClass = 'bg-yellow-500';
                                                            } elseif ($log->status == 2) {
                                                                $statusClass = 'bg-green-500';
                                                            }
                                                            $statusLabel =
                                                                $log->status == 0
                                                                    ? 'Not Started'
                                                                    : ($log->status == 1
                                                                        ? 'Started'
                                                                        : 'Finish');
                                                        @endphp
                                                        <div class="p-4 rounded shadow {{ $statusClass }} text-white">
                                                            <p class="text-md font-semibold">
                                                                {{ $log->process_name }}</p>
                                                            <p class="text-sm">Status: {{ $statusLabel }}</p>
                                                        </div>
                                                    @empty
                                                        <div class="py-2 text-sm">
                                                            No Process assigned.
                                                        </div>
                                                    @endforelse
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@script
    <script>
        // Listen for the "ParentDataUpdated" event on the Laravel Echo channel
        Echo.channel('dashboard-data')
            .listen('ParentDataUpdated', (event) => {
                // Use Livewire's @this to call the updateDashboard method
                @this.call('updateDashboard', event.parents, event.childs, event.materialLogs, event.mouldingUserLogs);
            });
    </script>
@endscript
