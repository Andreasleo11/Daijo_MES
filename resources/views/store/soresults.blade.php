<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-4">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8">
                <h1 class="text-2xl font-bold text-gray-800 mb-2">
                    SO Number: {{ $docNum }}
                </h1>
                <h2 class="text-xl font-semibold text-gray-600 mb-2">
                    Customer: {{ $customer }}
                </h2>
                <h2 class="text-xl font-semibold text-gray-600 mb-6">
                    Date: {{ $date }}
                </h2>

                <!-- Success Alert -->
                @if (session('success'))
                    <div
                        class="bg-green-100 text-green-800 border border-green-300 rounded-md p-4 mb-4 relative flex items-center justify-between alert-container"
                    >
                        {{ session('success') }}
                        <button
                            type="button"
                            class="text-green-800 hover:text-green-900 ml-2 text-2xl"
                            onclick="this.parentElement.style.display='none';"
                        >
                            &times;
                        </button>
                    </div>
                @endif

                <!-- Error Alert -->
                @if ($errors->any())
                    <div
                        class="bg-red-100 text-red-800 border border-red-300 rounded-md p-4 mb-4 relative flex items-center justify-between alert-container"
                    >
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button
                            type="button"
                            class="text-red-800 hover:text-red-900 ml-2 text-2xl"
                            onclick="this.parentElement.style.display='none';"
                        >
                            &times;
                        </button>
                    </div>
                @endif

                @if ($data->isEmpty())
                    <p class="text-red-500 text-lg">
                        No data found for this SO Number.
                    </p>
                @else
                    <table
                        class="min-w-full bg-white border-collapse border border-gray-200"
                    >
                        <thead class="bg-gray-100">
                            <tr>
                                <th
                                    class="border border-gray-300 px-4 py-2 text-left"
                                >
                                    ID
                                </th>
                                <th
                                    class="border border-gray-300 px-4 py-2 text-left"
                                >
                                    Model
                                </th>
                                <th
                                    class="border border-gray-300 px-4 py-2 text-left"
                                >
                                    Description
                                </th>
                                <th
                                    class="border border-gray-300 px-4 py-2 text-left"
                                >
                                    Delivery Quantity
                                </th>
                                <th
                                    class="border border-gray-300 px-4 py-2 text-left"
                                >
                                    Quantity PerPack
                                </th>
                                <th
                                    class="border border-gray-300 px-4 py-2 text-left"
                                >
                                    CTN
                                </th>
                                <th
                                    class="border border-gray-300 px-4 py-2 text-left"
                                >
                                    Remarks
                                </th>
                                <th
                                    class="border border-gray-300 px-4 py-2 text-left"
                                >
                                    Scanned Box
                                </th>
                                <th
                                    class="border border-gray-300 px-4 py-2 text-left"
                                >
                                    Scanned Quantity
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalQuantity = 0;
                                $totalCTN = 0;
                            @endphp

                            @foreach ($data as $item)
                                @php
                                    $scannedTotalQuantity = $item->scannedData->where('item_code', $item->item_code)->sum('quantity');
                                    $ctn = ceil($item->quantity / $item->packaging_quantity);
                                    $totalQuantity += $item->quantity;
                                    $totalCTN += $ctn;

                                    // Check if scannedCount is greater than ctn to apply red background
                                    $rowClass = $item->scannedCount > $ctn ? 'bg-red-500' : '';
                                @endphp

                                <tr class="hover:bg-green-500 {{ $rowClass }}">
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    >
                                        {{ $item->id }}
                                    </td>
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    >
                                        {{ $item->item_code }}
                                    </td>
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    >
                                        {{ $item->item_name }}
                                    </td>
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    >
                                        {{ $item->quantity }}
                                    </td>
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    >
                                        {{ $item->packaging_quantity }}
                                    </td>
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    >
                                        {{ number_format($ctn) }}
                                    </td>
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    ></td>
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    >
                                        {{ $item->scannedCount }} /
                                        {{ number_format($ctn) }}
                                    </td>
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    >
                                        {{ $scannedTotalQuantity }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-100">
                            <tr>
                                <th
                                    colspan="3"
                                    class="border border-gray-300 px-4 py-2 text-left"
                                >
                                    Total
                                </th>
                                <th class="border border-gray-300 px-4 py-2">
                                    {{ $totalQuantity }}
                                </th>
                                <th
                                    class="border border-gray-300 px-4 py-2"
                                ></th>
                                <th class="border border-gray-300 px-4 py-2">
                                    {{ number_format($totalCTN) }}
                                </th>
                                <th
                                    colspan="3"
                                    class="border border-gray-300 px-4 py-2"
                                ></th>
                            </tr>
                        </tfoot>
                    </table>
                @endif

                @if ($allFinished && ! $allDone)
                    <a
                        href="{{ route('update.so.data', ['docNum' => $docNum]) }}"
                        class="inline-block mt-6 px-6 py-3 bg-blue-600 text-white font-semibold rounded-md shadow hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Update All
                    </a>
                @elseif ($allFinished && $allDone)
                @else
                    <p class="text-red-500 mt-6">
                        Not all items are finished yet.
                    </p>
                @endif

                @if (! $allDone)
                    <form
                        id="barcode-form"
                        method="POST"
                        action="{{ route('so.scanBarcode') }}"
                        class="mt-8 space-y-4"
                    >
                        @csrf
                        <input
                            type="hidden"
                            name="so_number"
                            value="{{ $docNum }}"
                        />

                        <div>
                            <label
                                for="item_code"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Item Code:
                            </label>
                            <input
                                type="text"
                                id="item_code"
                                name="item_code"
                                required
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            />
                        </div>

                        <div>
                            <label
                                for="quantity"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Quantity:
                            </label>
                            <input
                                type="number"
                                id="quantity"
                                name="quantity"
                                required
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            />
                        </div>

                        <div>
                            <label
                                for="warehouse"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Warehouse:
                            </label>
                            <input
                                type="text"
                                id="warehouse"
                                name="warehouse"
                                required
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            />
                        </div>

                        <div>
                            <label
                                for="label"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Label:
                            </label>
                            <input
                                type="number"
                                id="label"
                                name="label"
                                required
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            />
                        </div>

                        <button
                            type="submit"
                            class="w-full py-2 px-4 bg-indigo-600 text-white font-semibold rounded-md shadow hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Scan Barcode
                        </button>
                    </form>
                @else
                    <h1>DOCUMENT FINISHED</h1>
                @endif

                <h2 class="text-xl font-bold text-gray-800 mt-10">
                    Scanned Data
                </h2>

                @forelse ($scandatas as $itemCode => $scans)
                    <h3 class="text-lg font-semibold text-gray-700 mt-4">
                        Item Code: {{ $itemCode }}
                    </h3>
                    <table
                        class="min-w-full bg-white border-collapse border border-gray-200 mt-2"
                    >
                        <thead class="bg-gray-100">
                            <tr>
                                <th
                                    class="border border-gray-300 px-4 py-2 text-left"
                                >
                                    No
                                </th>
                                <th
                                    class="border border-gray-300 px-4 py-2 text-left"
                                >
                                    Quantity
                                </th>
                                <th
                                    class="border border-gray-300 px-4 py-2 text-left"
                                >
                                    Warehouse
                                </th>
                                <th
                                    class="border border-gray-300 px-4 py-2 text-left"
                                >
                                    Label
                                </th>
                                <th
                                    class="border border-gray-300 px-4 py-2 text-left"
                                >
                                    Created At
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($scans as $scan)
                                <tr class="hover:bg-gray-50">
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    >
                                        {{ $loop->iteration }}
                                    </td>
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    >
                                        {{ $scan->quantity }}
                                    </td>
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    >
                                        {{ $scan->warehouse }}
                                    </td>
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    >
                                        {{ $scan->label }}
                                    </td>
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    >
                                        {{ $scan->created_at->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @empty
                    <p class="text-red-500 text-lg">
                        No scanned data yet for this SO Number.
                    </p>
                @endforelse
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('barcode-form');
            const labelInput = document.getElementById('label');

            function submitForm() {
                form.submit();
            }

            labelInput.addEventListener('input', function () {
                submitForm();
            });
        });

        document.addEventListener('DOMContentLoaded', (event) => {
            document.getElementById('item_code').focus();
        });
    </script>
</x-app-layout>
