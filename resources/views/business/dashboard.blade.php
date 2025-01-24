<x-app-layout>
<body class="bg-gray-100">

<div class="container mx-auto p-8">
    <h1 class="text-3xl font-semibold mb-4">Delivery Schedule Dashboard</h1>

    <!-- Form to input the number of days -->
    <form method="GET" action="{{ route('delschedfinal.dashboard') }}" class="mb-6">
        <div class="mb-4">
            <label for="days" class="block text-lg font-medium">Enter number of days from today</label>
            <input type="number" name="days" id="days" class="mt-2 p-2 w-full border border-gray-300 rounded-lg" required min="1" value="{{ request('days') }}">
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Submit</button>

        @if(request('days'))
            <a href="{{ route('delschedfinal.dashboard') }}" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 ml-4">Reset Filter</a>
        @endif
    </form>

    <!-- Display the results -->
    @if($datas->isEmpty())
        <p class="mt-4 text-lg text-gray-600">No data found for the given criteria.</p>
    @else
        <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
            <table class="min-w-full table-auto border-collapse"> 
            <!-- style="max-height: 400px; overflow-y: auto; display: block; -->
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="px-4 py-2 border-b text-left">ID</th>
                        <th class="px-4 py-2 border-b text-left">Status</th>
                        <th class="px-4 py-2 border-b text-left">Delivery Date</th>
                        <th class="px-4 py-2 border-b text-left">SO Number</th>
                        <th class="px-4 py-2 border-b text-left">Customer Code</th>
                        <th class="px-4 py-2 border-b text-left">Customer Name</th>
                        <th class="px-4 py-2 border-b text-left">Item Code</th>
                        <th class="px-4 py-2 border-b text-left">Item Name</th>
                        <th class="px-4 py-2 border-b text-left">Department</th>
                        <th class="px-4 py-2 border-b text-left">Delivery Qty</th>
                        <th class="px-4 py-2 border-b text-left">Delivered</th>
                        <th class="px-4 py-2 border-b text-left">Outstanding</th>
                        <th class="px-4 py-2 border-b text-left">Stock</th>
                        <th class="px-4 py-2 border-b text-left">Balance</th>
                        <th class="px-4 py-2 border-b text-left">Outstanding Stock</th>
                        <th class="px-4 py-2 border-b text-left">Packaging Code</th>
                        <th class="px-4 py-2 border-b text-left">Standard Pack</th>
                        <th class="px-4 py-2 border-b text-left">Packaging Qty</th>
                        <th class="px-4 py-2 border-b text-left">Doc Status</th>
                    </tr>
                </thead>
                <tbody class="text-gray-800">
                    @foreach($datas as $data)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2">{{ $data->id }}</td>
                            <td class="px-4 py-2 
                                @if($data->status == 'Danger') text-red-600 font-semibold 
                                @elseif($data->status == 'Warning') text-yellow-500 font-semibold 
                                @else text-green-600 @endif">
                                {{ ucfirst($data->status) }}
                            </td>
                            <td class="px-4 py-2">{{ \Carbon\Carbon::parse($data->delivery_date)->format('d M Y') }}</td>
                            <td class="px-4 py-2">{{ $data->so_number }}</td>
                            <td class="px-4 py-2">{{ $data->customer_code }}</td>
                            <td class="px-4 py-2">{{ $data->customer_name }}</td>
                            <td class="px-4 py-2">{{ $data->item_code }}</td>
                            <td class="px-4 py-2">{{ $data->item_name }}</td>
                            <td class="px-4 py-2">{{ $data->departement }}</td>
                            <td class="px-4 py-2">{{ $data->delivery_qty }}</td>
                            <td class="px-4 py-2">{{ $data->delivered }}</td>
                            <td class="px-4 py-2">{{ $data->outstanding }}</td>
                            <td class="px-4 py-2">{{ $data->stock }}</td>
                            <td class="px-4 py-2">{{ $data->balance }}</td>
                            <td class="px-4 py-2">{{ $data->outstanding_stk }}</td>
                            <td class="px-4 py-2">{{ $data->packaging_code }}</td>
                            <td class="px-4 py-2">{{ $data->standar_pack }}</td>
                            <td class="px-4 py-2">{{ $data->packaging_qty }}</td>
                            <td class="px-4 py-2">{{ $data->doc_status }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Pagination controls with query string -->
    <div class="pagination">
        {{ $datas->appends(['days' => request('days')])->links() }}
    </div>

</div>

</body>


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
                    window.location.href = 'http://127.0.0.1:8000/delschedfinal/dashboard?page=1';
                }, 5000); // 5 seconds
            }
        });
    </script>
</x-app-layout>
