<x-app-layout>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-6">
    <!-- Loop through each project -->
    @foreach ($projectProgress as $progress)
        <div class="bg-white rounded-lg shadow-lg p-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-800">{{ $progress['project_name'] }}</h3>
                <span class="text-sm text-gray-500">Project ID: {{ $progress['parent_id'] }}</span>
            </div>

            <!-- Project Completion Card -->
            <div class="mb-4">
                <h4 class="text-lg font-medium text-gray-700">Completion</h4>
                <div class="flex items-center mt-2">
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-blue-500 h-2.5 rounded-full" style="width: {{ $progress['completion'] }}%"></div>
                    </div>
                    <span class="ml-2 text-sm">{{ number_format($progress['completion'], 2) }}%</span>
                </div>
            </div>

            <!-- Distinct Users Card -->
            <div>
                <h4 class="text-lg font-medium text-gray-700">Distinct People Involved</h4>
                <p class="text-xl font-semibold text-gray-800">{{ $distinctUsers[$progress['parent_id']] }}</p>
            </div>
        </div>
    @endforeach
</div>
</x-app-layout>