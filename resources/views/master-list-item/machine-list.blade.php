<x-dashboard-layout>
    <div class="container mx-auto p-4 bg-white">
        <h2 class="text-2xl font-bold text-center mb-4">Master Item List {{ $machineName }}</h2>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-300 text-sm">
                <thead class="bg-gray-200">
                    <tr class="text-left">
                        <th class="border border-gray-300 px-4 py-2 text-center">Item Code</th>
                        <th class="border border-gray-300 px-4 py-2 text-center">Image</th>
                        <th class="border border-gray-300 px-4 py-2 text-center">QR Code</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($machines as $machine)
                        <tr class="border border-gray-300">
                            <td class="border border-gray-300 px-4 py-2 text-center font-semibold">{{ $machine->item_code }}</td>
                            <td class="border border-gray-300 px-4 py-2 text-center">
                                <img src="{{  asset($images[$machine->item_code]) }}" alt="Image" class="w-24 h-24 mx-auto border border-gray-400 rounded-lg shadow">
                            </td>
                            <td class="border border-gray-300 px-4 py-2 text-center">
                                <img src="data:image/png;base64,{{ $qrcodes[$machine->item_code] }}" class="w-24 h-24 mx-auto">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="text-center mt-6">
            <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white font-bold rounded-lg shadow hover:bg-blue-700">
                Print Master List
            </button>
        </div>
    </div>

    {{-- Print Styles --}}
    <style>
        @media print {
            body {
                background: white !important;
                color: black !important;
            }
            .container {
                width: 100%;
                padding: 0;
                margin: 0;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th, td {
                border: 1px solid black !important;
                padding: 8px !important;
                font-size: 12px;
            }
            img {
                max-width: 100px !important;
                max-height: 100px !important;
            }
            .text-center, .text-sm {
                text-align: center;
                font-size: 14px;
            }
            button {
                display: none;
            }
        }
    </style>
</x-dashboard-layout>
