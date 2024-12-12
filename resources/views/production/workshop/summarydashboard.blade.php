<x-dashboard-layout>

<div class="mt-6">
                        <a href="{{ route('dashboard') }}" class="text-blue-500 hover:text-blue-700">
                            Back to dashboard
                        </a>
                    </div>
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Production Summary Dashboard</h1>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">ID</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Process Name</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Scan in</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Scan out</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Status</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">PIC</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Workers</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Item Code/Material Code</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Item Description</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Item Quantity</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Project Code</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Project Description</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($datas as $data)
                        <tr class="border-t border-gray-200">
                            <td class="px-4 py-2 text-sm text-gray-800">{{ $data->id }}</td>
                            <td class="px-4 py-2 text-sm text-gray-800">{{ $data->process_name }}</td>
                            <td class="px-4 py-2 text-sm text-gray-800">{{ $data->scan_in }}</td>
                            <td class="px-4 py-2 text-sm text-gray-800">{{ $data->scan_out }}</td>
                            <td class="px-4 py-2 text-sm">
                                @if ($data->childData->status === 'Canceled')
                                    <span class="px-2 py-1 rounded bg-yellow-200 text-yellow-800">
                                        Canceled
                                    </span>
                                @elseif ($data->scan_in !== null && $data->scan_out !== null)
                                    <span class="px-2 py-1 rounded bg-green-200 text-green-800">
                                        Completed
                                    </span>
                                @elseif ($data->scan_in !== null)
                                    <span class="px-2 py-1 rounded bg-blue-200 text-blue-800">
                                        Started
                                    </span>
                                @else
                                    <span class="px-2 py-1 rounded {{ $data->status ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' }}">
                                        {{ $data->status ? 'Completed' : 'Pending' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-800">{{ $data->pic ?? 'Unassigned' }}</td>
                            <td class="print-hidden px-4 py-2 border">
                                @foreach($data->workers as $worker)
                                    {{ $worker->username }} - {{ $worker->shift }} <br>
                                @endforeach
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-800">{{ $data->childData->item_code ?? 'N/A' }}</td>
                            <td class="px-4 py-2 text-sm text-gray-800">{{ $data->childData->item_description ?? 'N/A' }}</td>
                            <td class="px-4 py-2 text-sm text-gray-800">{{ $data->childData->quantity ?? 'N/A' }}</td>
                            <td class="px-4 py-2 text-sm text-gray-800">{{ $data->childData->parent->code ?? 'N/A' }}</td>
                            <td class="px-4 py-2 text-sm text-gray-800">{{ $data->childData->parent->description ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-4 py-6 text-center text-gray-500 text-sm">No records available</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination">
        {{ $datas->links() }}
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const nextPageLink = document.querySelector('.pagination a[aria-label="Next &raquo;"]');
            
            if (nextPageLink && !nextPageLink.hasAttribute('aria-disabled')) {
                const nextPageHref = nextPageLink.href;
                console.log('Next page link:', nextPageHref);

                // Redirect to the next page after 5 seconds
                setTimeout(function () {
                    window.location.href = nextPageHref;
                }, 5000); // 5 seconds
            } else {
                console.log('No next page link or next page is disabled.');

                // Redirect to the first page after 5 seconds
                setTimeout(function () {
                    window.location.href = 'http://127.0.0.1:8000/workshop/summary?page=1';
                }, 5000); // 5 seconds
            }
        });
    </script>
</x-dashboard-layout>
