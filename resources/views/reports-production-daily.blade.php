<x-dashboard-layout>
    <div class="container mx-auto px-4 py-6">
        <h2 class="text-2xl font-bold mb-6">Daily Production Report </h2>
        

        {{-- Form pilih tanggal --}}
        <form action="{{ route('production.report') }}" method="GET" class="mb-6 flex items-center space-x-2">
            <label for="date" class="text-sm font-medium text-gray-700">Pilih Tanggal:</label>
            <input type="date" id="date" name="date" 
                   value="{{ $date }}" 
                   class="border border-gray-300 rounded px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" 
                    class="bg-blue-500 text-white px-4 py-1 rounded hover:bg-blue-600 text-sm font-medium">
                Tampilkan
            </button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-300 divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Item Code</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Item Name</th>
                        <th class="px-4 py-2 text-center text-sm font-medium text-gray-700">Cycle Time</th>
                        <th class="px-4 py-2 text-center text-sm font-medium text-gray-700">SAP Cycle Time</th>
                        <th class="px-4 py-2 text-center text-sm font-medium text-gray-700">Cycle Time Diff</th>
                        <th class="px-4 py-2 text-center text-sm font-medium text-gray-700">Cavity</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Machine</th>
                        <th class="px-4 py-2 text-center text-sm font-medium text-gray-700">Shift 1</th>
                        <th class="px-4 py-2 text-center text-sm font-medium text-gray-700">Shift 2</th>
                        <th class="px-4 py-2 text-center text-sm font-medium text-gray-700">Shift 3</th>
                        <th class="px-4 py-2 text-center text-sm font-medium text-gray-700">Total OK</th>
                        <th class="px-4 py-2 text-center text-sm font-medium text-gray-700">Total NG</th>
                        <th class="px-4 py-2 text-center text-sm font-medium text-gray-700">Reject Rate (%)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($data as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-sm text-gray-800">
                                {{ $item['item_code'] }}{{ $item['pair'] ? ' / '.$item['pair'] : '' }}
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-800">{{ $item['item_name'] }}</td>
                            <td class="px-4 py-2 text-sm text-center text-gray-800">{{ $item['cycletime'] }}</td>
                            <td class="px-4 py-2 text-sm text-center text-gray-800">{{ $item['sap_cycletime'] }}</td>
                            @php
                                $difference = is_numeric($item['sap_cycletime']) && is_numeric($item['cycletime'])
                                    ? round($item['cycletime'] - $item['sap_cycletime'], 2)
                                    : '-';
                            @endphp
                            <td class="px-4 py-2 text-sm text-center font-semibold 
                                {{ is_numeric($difference) && $difference > 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $difference }}
                            </td>
                            <td class="px-4 py-2 text-sm text-center text-gray-800">{{ $item['cavity'] }}</td>
                            <td class="px-4 py-2 text-sm text-gray-800">{{ $item['machine'] }}</td>

                            {{-- Shift 1 --}}
                            <td class="px-4 py-2 text-sm text-center">
                                @if($item['shifts'][1])
                                    <div>OK: {{ $item['shifts'][1]['total_actual'] }}</div>
                                    <div class="{{ $item['shifts'][1]['total_ng'] > 0 ? 'text-red-600 font-semibold' : '' }}">
                                        NG: {{ $item['shifts'][1]['total_ng'] }}
                                    </div>
                                @else
                                    -
                                @endif
                            </td>

                            {{-- Shift 2 --}}
                            <td class="px-4 py-2 text-sm text-center">
                                @if($item['shifts'][2])
                                    <div>OK: {{ $item['shifts'][2]['total_actual'] }}</div>
                                    <div class="{{ $item['shifts'][2]['total_ng'] > 0 ? 'text-red-600 font-semibold' : '' }}">
                                        NG: {{ $item['shifts'][2]['total_ng'] }}
                                    </div>
                                @else
                                    -
                                @endif
                            </td>

                            {{-- Shift 3 --}}
                            <td class="px-4 py-2 text-sm text-center">
                                @if($item['shifts'][3])
                                    <div>OK: {{ $item['shifts'][3]['total_actual'] }}</div>
                                    <div class="{{ $item['shifts'][3]['total_ng'] > 0 ? 'text-red-600 font-semibold' : '' }}">
                                        NG: {{ $item['shifts'][3]['total_ng'] }}
                                    </div>
                                @else
                                    -
                                @endif
                            </td>

                            {{-- Total --}}
                            <td class="px-4 py-2 text-sm text-center">{{ $item['total_actual'] }}</td>
                            <td class="px-4 py-2 text-sm text-center">
                                @if($item['total_ng'] > 0)
                                    <!-- Tombol buka modal -->
                                    <button type="button" 
                                            onclick="document.getElementById('modal-{{ $loop->index }}').classList.remove('hidden')"
                                            class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 text-xs">
                                        {{ $item['total_ng'] }}
                                    </button>

                                    <!-- Modal NG Detail -->
                                    <div id="modal-{{ $loop->index }}" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
                                        <div class="bg-white rounded-lg w-96 max-w-full p-4 relative">
                                            <h3 class="text-lg font-bold mb-2">NG Details - {{ $item['item_code'] }}</h3>
                                            
                                            <table class="w-full text-sm border border-gray-200">
                                                <thead class="bg-gray-100">
                                                    <tr>
                                                        <th class="px-2 py-1 text-left border">NG Type</th>
                                                        <th class="px-2 py-1 text-center border">Shift 1</th>
                                                        <th class="px-2 py-1 text-center border">Shift 2</th>
                                                        <th class="px-2 py-1 text-center border">Shift 3</th>
                                                        <th class="px-2 py-1 text-center border">Total</th> 
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        // Ambil semua jenis NG unik dari semua shift
                                                        $allNgTypes = collect(array_merge(
                                                            array_keys($item['shifts'][1]['ng_details'] ?? []),
                                                            array_keys($item['shifts'][2]['ng_details'] ?? []),
                                                            array_keys($item['shifts'][3]['ng_details'] ?? [])
                                                        ))->unique();
                                                    @endphp

                                                    @foreach($allNgTypes as $ngType)
                                                    @php
                                                        $s1 = $item['shifts'][1]['ng_details'][$ngType] ?? 0;
                                                        $s2 = $item['shifts'][2]['ng_details'][$ngType] ?? 0;
                                                        $s3 = $item['shifts'][3]['ng_details'][$ngType] ?? 0;
                                                        $total = $s1 + $s2 + $s3;
                                                    @endphp
                                                    <tr class="border-b">
                                                        <td class="px-2 py-1 border">{{ $ngType }}</td>
                                                        <td class="px-2 py-1 text-center border">{{ $s1 }}</td>
                                                        <td class="px-2 py-1 text-center border">{{ $s2 }}</td>
                                                        <td class="px-2 py-1 text-center border">{{ $s3 }}</td>
                                                        <td class="px-2 py-1 text-center border font-bold">{{ $total }}</td> {{-- NEW --}}
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>

                                            <!-- Close button -->
                                            <button type="button" 
                                                    onclick="document.getElementById('modal-{{ $loop->index }}').classList.add('hidden')"
                                                    class="absolute top-2 right-2 text-gray-600 hover:text-gray-800 font-bold">&times;</button>
                                        </div>
                                    </div>
                                @else
                                    0
                                @endif
                            </td>
                            <td class="px-4 py-2 text-sm text-center">{{ $item['reject_rate'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-dashboard-layout>
