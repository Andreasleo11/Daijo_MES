<x-dashboard-layout>
    <div class="p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">API Logs</h1>

        <div class="overflow-x-auto bg-white rounded-2xl shadow">
            <table class="min-w-full border-collapse">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">API Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Method</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Endpoint</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status Code</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Message</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Created At</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $log->id }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $log->api_name }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 text-xs rounded-lg 
                                    {{ $log->method === 'POST' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $log->method }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 truncate max-w-[200px]">{{ $log->endpoint }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 text-xs rounded-lg 
                                    {{ $log->status === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $log->status_code ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 truncate max-w-[200px]">{{ $log->message ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $log->created_at }}</td>
                            <td class="px-4 py-3 text-sm">
                                <button onclick="openModal({{ $log->id }})" class="px-3 py-1 text-xs bg-gray-200 hover:bg-gray-300 rounded-lg">
                                    Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-6 text-center text-gray-500">
                                No API Logs found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Detail -->
    <div id="detailModal" class="fixed inset-0 hidden items-center justify-center bg-black bg-opacity-40 z-50">
        <div class="bg-white rounded-2xl shadow-lg max-w-3xl w-full p-6 relative">
            <h2 class="text-lg font-semibold mb-4">Log Detail</h2>
            <pre id="modalContent" class="bg-gray-100 p-4 rounded-lg text-sm overflow-x-auto max-h-96"></pre>
            <button onclick="closeModal()" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">✖</button>
        </div>
    </div>

    <script>
        function openModal(id) {
            const log = @json($logs);
            const detail = log.find(l => l.id === id);
            document.getElementById('modalContent').textContent =
                JSON.stringify(detail, null, 2);
            document.getElementById('detailModal').classList.remove('hidden');
            document.getElementById('detailModal').classList.add('flex');
        }
        function closeModal() {
            document.getElementById('detailModal').classList.remove('flex');
            document.getElementById('detailModal').classList.add('hidden');
        }
    </script>
</x-dashboard-layout>
