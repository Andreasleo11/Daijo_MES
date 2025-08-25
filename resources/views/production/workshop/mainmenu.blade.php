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

            <a href="{{ route('workshop.addManual') }}"
                class="inline-block bg-green-500 text-white px-4 py-2 text-sm font-medium rounded hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-400">
                Jangan diklik
            </a>
        </div>


        <form method="GET" action="{{ route('workshop.main.menu') }}" class="mb-4">
            <div class="flex items-center gap-2">
                <select name="code" class="border border-gray-300 rounded px-3 py-2 text-sm">
                    <option value="">-- Filter by BOM/Project Code --</option>
                    @foreach ($distinctCodes as $code)
                        <option value="{{ $code }}" {{ request('code') == $code ? 'selected' : '' }}>
                            {{ $code }}
                        </option>
                    @endforeach
                </select>
                <button type="submit"
                    class="bg-blue-500 text-white px-4 py-2 text-sm rounded hover:bg-blue-600">
                    Filter
                </button>
                @if(request('code'))
                    <a href="{{ route('workshop.main.menu') }}"
                        class="text-sm text-gray-600 hover:underline ml-2">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="w-full border-collapse table-auto">
                <thead class="bg-gray-50 border-b-2 border-gray-200">
                    <tr>
                        <th class="px-4 py-2 text-sm font-semibold text-gray-600 text-center">ID</th>
                        <th class="px-4 py-2 text-sm font-semibold text-gray-600 text-center">BOM/Project Code</th>
                        <th class="px-4 py-2 text-sm font-semibold text-gray-600 text-center">Material</th>
                        <th class="px-4 py-2 text-sm font-semibold text-gray-600 text-center">Scan in</th>
                        <th class="px-4 py-2 text-sm font-semibold text-gray-600 text-center">Start Process</th>
                        <th class="px-4 py-2 text-sm font-semibold text-gray-600 text-center">Scan Finish</th>
                        <th class="px-4 py-2 text-sm font-semibold text-gray-600 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($logs as $log)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-800 text-center">{{ $log->id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-800 text-center">{{ $log->childData->parent->code }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-800 text-center">
                                {{ $log->childData->item_description ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-800 text-center">{{ $log->scan_in }}</td>
                            <td class="px-4 py-3 text-sm text-gray-800 text-center">{{ $log->scan_start }}</td>
                            <td class="px-4 py-3 text-sm text-gray-800 text-center">{{ $log->scan_out }}</td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('workshop.index', $log->id) }}"
                                    class="inline-block bg-blue-500 text-white px-4 py-2 text-sm font-medium rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                    View Details
                                </a>
                                @if (!$log->scan_out)
                                    <a href="{{ route('workshop.removeScanIn', $log->id) }}"
                                        class="inline-block bg-red-500 text-white px-4 py-2 text-sm font-medium rounded hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-400 ml-2"
                                        onclick="return confirmRemove();">
                                        Remove
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                No jobs available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <script>
        function confirmRemove() {
            return confirm('Salah scan ya ?');
        }
    </script>
</x-app-layout>
