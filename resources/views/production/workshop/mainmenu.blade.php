<x-app-layout>
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">
            Jobs for {{ $user->name }} , Hello <span class="text-3xl text-yellow-500">{{ $user->username }}</span>
        </h1>
        <div class="mb-6">
            <a href="{{ route('dashboard') }}"
               class="inline-block bg-green-500 text-white px-4 py-2 text-sm font-medium rounded hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-400">
                Create Process
            </a>
        </div>
        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="w-full border-collapse table-auto">
                <thead class="bg-gray-50 border-b-2 border-gray-200">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600 text-center">ID</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600 text-center">BOM/Project Code</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600 text-center">Material</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600 text-center">Scan Start</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600 text-center">Scan Finish</th>
                        <th class="px-4 py-2 text-center text-sm font-semibold text-gray-600 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($logs as $log)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-800 text-center">{{ $log->id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-800 text-center">{{ $log->childData->parent->code }}</td>
                            <td class="px-4 py-3 text-sm text-gray-800 text-center">
                                {{ $log->childData->item_description ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-800 text-center">{{ $log->scan_in }}</td>
                            <td class="px-4 py-3 text-sm text-gray-800 text-center">{{ $log->scan_out }}</td>
                            <td class="px-4 py-3 text-center text-center">
                                <a href="{{ route('workshop.index', $log->id) }}"
                                   class="inline-block bg-blue-500 text-white px-4 py-2 text-sm font-medium rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                No jobs available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>