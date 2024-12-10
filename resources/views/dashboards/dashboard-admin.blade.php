<x-app-layout>
    <div class="p-4">
        <div class="p-6 bg-gray-100">
            <h1 class="text-2xl font-bold mb-4">Dashboard</h1>

            <!-- Summary Cards -->
            <div class="grid grid-cols-4 gap-4">
                <div class="p-4 bg-white rounded shadow">
                    <h2 class="text-lg font-semibold">Total Product</h2>
                    <p class="text-2xl">{{ $parents->count() }}</p>
                </div>
                <div class="p-4 bg-white rounded shadow">
                    <h2 class="text-lg font-semibold">Total Item Code</h2>
                    <p class="text-2xl">{{ $childs->count() }}</p>
                </div>
                <div class="p-4 bg-white rounded shadow">
                    <h2 class="text-lg font-semibold">Coming Soon</h2>
                    <p class="text-2xl">120</p>
                    {{-- <p class="text-2xl">{{ $mouldingJobs->whereNotNull('scan_start')->count() }}</p> --}}
                </div>
                <div class="p-4 bg-white rounded shadow">
                    <h2 class="text-lg font-semibold">Coming Soon</h2>
                    <p class="text-2xl">9</p>
                    {{-- <p class="text-2xl">{{ $mouldingJobs->whereNotNull('scan_finish')->count() }}</p> --}}
                </div>
            </div>

            <div class="mt-8">
                <h2 class="text-lg font-semibold mb-4">BOM</h2>

                @foreach ($parents as $parent)
                    <!-- Parent Card -->
                    <div class="mb-6 p-4 rounded shadow-lg bg-white border">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-xl font-bold">{{ $parent->description }}</h3>
                                <p class="text-sm text-gray-500">Product Code: {{ $parent->item_code }} |
                                    Type:
                                    {{ $parent->type }}</p>
                            </div>
                            <div>
                                <!-- Completion Percentage -->
                                @php
                                    $childCount = $childs->where('parent_id', $parent->id)->count();
                                    $completedCount = $materialLogs
                                        ->whereIn('child_id', $childs->where('parent_id', $parent->id)->pluck('id'))
                                        ->filter(fn($log) => $log->status == 2) // Only count fully finished
                                        ->count();
                                    $completionPercentage = $childCount > 0 ? ($completedCount / $childCount) * 100 : 0;
                                @endphp
                                <div class="text-right">
                                    <p class="text-lg font-semibold">{{ round($completionPercentage, 2) }}%
                                        Completed
                                    </p>
                                    <div class="w-48 bg-gray-300 h-4 rounded-full overflow-hidden">
                                        <div class="bg-green-500 h-4" style="width: {{ $completionPercentage }}%;">
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
                                    <div class="p-4 rounded shadow bg-gray-100 border">
                                        <p class="text-lg font-semibold">{{ $child->item_code }} -
                                            {{ $child->item_description }}</p>
                                        <p class="text-sm text-gray-600">Quantity: {{ $child->quantity }}
                                            {{ $child->measure }}</p>

                                        <!-- Process Details -->
                                        <div class="mt-2">
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
                                                            {{ $log->process_name }}
                                                        </p>
                                                        <p class="text-sm">Status: {{ $statusLabel }}</p>
                                                    </div>
                                                @empty
                                                    <div class="py-2 text-sm">
                                                        No Process assigned.
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
