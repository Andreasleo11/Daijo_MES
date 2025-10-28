<x-app-layout>
    <script src="https://unpkg.com/alpinejs" defer></script>

    <div class="container mx-auto px-4 py-6" x-data="{ detailModal: null, historyModal: null, historyData: [] }">
        <h1 class="text-2xl font-bold mb-6">Barcode Packaging Dashboard</h1>

        @foreach ($summaryData as $group)
            @php $partNo = $group['part_no']; @endphp

            <div class="bg-white shadow-md rounded-lg p-4 mb-6">
               <div class="overflow-x-auto bg-white p-4 shadow rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 text-sm text-center">
                        <thead class="bg-gray-50 text-gray-600 font-semibold">
                            <tr>
                                <th class="px-4 py-2 text-left">Part No</th>
                                <th class="px-4 py-2">Daijo</th>
                                <th class="px-4 py-2">KIIC</th>
                                <th class="px-4 py-2">Customer</th>
                                <th class="px-4 py-2">Total</th>
                                <th class="px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                                <tr>
                                    <td class="px-4 py-2 text-left font-medium text-gray-700">
                                        {{ $partNo }}
                                    </td>
                                    <td class="px-4 py-2">{{ $group['quantity_daijo'] }}</td>
                                    <td class="px-4 py-2">{{ $group['quantity_kiic'] }}</td>
                                    <td class="px-4 py-2">{{ $group['quantity_customer'] }}</td>
                                    <td class="px-4 py-2 font-bold">{{ $group['total'] }}</td>
                                    <td class="px-4 py-2">
                                        <button 
                                            @click="detailModal = '{{ $partNo }}'" 
                                            class="bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700 transition"
                                        >
                                            Show Details
                                        </button>
                                    </td>
                                </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Modal Detail -->
                <div x-show="detailModal === '{{ $partNo }}'" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
                    <div class="bg-white rounded-lg w-full max-w-4xl p-6 overflow-y-auto max-h-[90vh]">
                      <h2 class="text-xl font-bold mb-4 flex items-center justify-between">
                        <span>Details for {{ $partNo }}</span>
                        <button 
                            @click="detailModal = null" 
                            class="ml-2 px-3 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600 transition">
                            CLOSE
                        </button>
                    </h2>

                        
                        <table class="w-full table-auto border text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border px-2 py-1">Label</th>
                                    <th class="border px-2 py-1">Position</th>
                                    <th class="border px-2 py-1">Last Transaction</th>
                                    <th class="border px-2 py-1">Quantity</th>
                                    <th class="border px-2 py-1">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($group['details'] as $detail)
                                    @php
                                        $labelSlug = \Illuminate\Support\Str::slug($detail['label'], '_');
                                        $encodedHistory = json_encode($detail['history']);
                                    @endphp
                                    <tr>
                                        <td class="border px-2 py-1 text-center">{{ $detail['label'] }}</td>
                                        <td class="border px-2 py-1 text-center">{{ $detail['position'] }} ({{$detail['customer']}})</td>
                                        <td class="border px-2 py-1 text-center">{{ $detail['last_transaction'] }}</td>
                                        <td class="border px-2 py-1 text-center">{{ $detail['quantity'] }}</td>
                                        <td class="border px-2 py-1 text-center">
                                            <button
                                                @click="historyModal = '{{ $partNo }}-{{ $labelSlug }}'; historyData = {{ $encodedHistory }}"
                                                class="text-xs bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">
                                                Show History
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mt-4 text-right">
                            <button @click="detailModal = null" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
                <!-- End Modal Detail -->

                <!-- Modal History -->
                <div x-show="historyModal && historyModal.startsWith('{{ $partNo }}-')" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
                    <div class="bg-white rounded-lg w-full max-w-3xl p-6 overflow-y-auto max-h-[90vh]">
                        <h2 class="text-lg font-bold mb-4">Label History</h2>

                        <template x-if="historyData.length">
                            <table class="w-full text-sm table-auto border">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="border px-2 py-1">Scan Time</th>
                                        <th class="border px-2 py-1">Position</th>
                                        <th class="border px-2 py-1">Label</th>
                                        <th class="border px-2 py-1">No Dokumen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="item in historyData" :key="item.scantime">
                                        <tr class="text-center">
                                            <td class="border px-2 py-1" x-text="item.scantime"></td>
                                            <td class="border px-2 py-1" x-text="item.position"></td>
                                            <td class="border px-2 py-1" x-text="item.label"></td>
                                            <td class="border px-2 py-1" x-text="item.no_dokumen"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </template>

                        <template x-if="!historyData.length">
                            <p class="text-sm text-gray-500">No history available.</p>
                        </template>

                        <div class="mt-4 text-right">
                            <button @click="historyModal = null; historyData = []"
                                class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
                <!-- End Modal History -->
            </div>
        @endforeach
    </div>
</x-app-layout>
