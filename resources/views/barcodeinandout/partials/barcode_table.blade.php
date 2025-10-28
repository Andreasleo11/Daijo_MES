@foreach ($result as $item)
    <div class="bg-white shadow-lg rounded-lg p-6 mb-8">
        <h2 class="text-xl font-bold mb-2">Date Scan: {{ $item['dateScan'] }}</h2>
        <p class="text-gray-700 mb-1"><strong>No Dokumen:</strong> {{ $item['noDokumen'] }}</p>
        <p class="text-gray-700 mb-1"><strong>Customer :</strong> {{ $item['customer'] }}</p>
        <p class="text-gray-700 mb-1"><strong>Tipe Barcode:</strong> {{ strtoupper($item['tipeBarcode']) }}</p>
        <p class="text-gray-700 mb-4"><strong>Location:</strong> {{ strtoupper($item['location']) }}</p>

        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border-collapse border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 border border-gray-300 text-left">Part No</th>
                        <th class="px-4 py-2 border border-gray-300 text-left">Label</th>
                        <th class="px-4 py-2 border border-gray-300 text-left">Position</th>
                        <th class="px-4 py-2 border border-gray-300 text-left">Scan Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($item[$item['noDokumen']] as $detail)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2 border border-gray-300">{{ $detail['partNo'] }}</td>
                            <td class="px-4 py-2 border border-gray-300">{{ $detail['label'] }}</td>
                            <td class="px-4 py-2 border border-gray-300">{{ $item['customer'] }}</td>
                            <td class="px-4 py-2 border border-gray-300">{{ $detail['scantime'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <hr class="my-6">
    </div>
@endforeach
