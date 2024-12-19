<x-app-layout>
    <div class="max-w-7xl mx-auto">
        <!-- Title -->
        <h1 class="text-3xl font-bold text-center my-8">Latest Barcode Items</h1>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('updated.barcode.item.position') }}"
            class="bg-white p-6 rounded-lg shadow-md mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Part No -->
                <div class="flex flex-col">
                    <label for="partNo" class="font-semibold text-gray-700 mb-2">Part No:</label>
                    <select id="partNo" name="partNo"
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Part No</option>
                        @foreach ($partNumbers as $partNumber)
                            <option value="{{ $partNumber->partNo }}"
                                {{ request('partNo') == $partNumber->partNo ? 'selected' : '' }}>
                                {{ $partNumber->partNo }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Scan Time -->
                <div class="flex flex-col">
                    <label for="scantime" class="font-semibold text-gray-700 mb-2">Scan Time (yyyy-mm-dd):</label>
                    <input type="date" id="scantime" name="scantime" value="{{ request('scantime') }}"
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- Position -->
                <div class="flex flex-col">
                    <label for="position" class="font-semibold text-gray-700 mb-2">Position:</label>
                    <select id="position" name="position"
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Position</option>
                        <option value="Jakarta" {{ request('position') == 'Jakarta' ? 'selected' : '' }}>Jakarta
                        </option>
                        <option value="Karawang" {{ request('position') == 'Karawang' ? 'selected' : '' }}>Karawang
                        </option>
                        <option value="CustomerJakarta"
                            {{ request('position') == 'CustomerJakarta' ? 'selected' : '' }}>CustomerJakarta</option>
                        <option value="CustomerKarawang"
                            {{ request('position') == 'CustomerKarawang' ? 'selected' : '' }}>CustomerKarawang</option>
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="flex flex-col justify-end">
                    <button type="submit"
                        class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 transition duration-300">
                        Filter
                    </button>
                </div>
            </div>
        </form>

        <!-- Table -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Part No</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Label</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Scan Time</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">No Dokumen</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Position</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($sortedItems as $item)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-sm text-gray-900">{{ (string) $item->partNo }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ (string) $item->label }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ (string) $item->scantime }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ (string) $item->noDokumen }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ (string) $item->position }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
