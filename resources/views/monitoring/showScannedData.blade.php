<x-dashboard-layout>
   <h2 class="text-2xl font-bold mb-6">Detail SPK: {{ $spk }}</h2>

    {{-- ========================= --}}
    {{-- TABLE: PRODUCTION SCAN --}}
    {{-- ========================= --}}
    <div class="mb-10">
        <h3 class="text-xl font-semibold mb-2">Production Scan Data</h3>

        <table class="w-full border-collapse">
            <thead class="bg-gray-200">
                <tr>
                    <th class="border px-3 py-2">Item Code</th>
                    <th class="border px-3 py-2">Quantity</th>
                    <th class="border px-3 py-2">Scan Time</th>
                    <th class="border px-3 py-2">Label</th>
                    <th class="border px-3 py-2">Operator</th>
                    <th class="border px-3 py-2">Machine Scan </th>
                </tr>
            </thead>

            <tbody>
                @foreach($data as $row)
                <tr>
                    <td class="border px-3 py-2">{{ $row->item_code }}</td>
                    <td class="border px-3 py-2 text-center">{{ $row->quantity }}</td>
                    <td class="border px-3 py-2">
                        {{ \Carbon\Carbon::parse($row->created_at)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') }}
                    </td>
                    <td class="border px-3 py-2">{{ $row->label }}</td>
                    <td class="border px-3 py-2">{{ $row->user }}</td>
                    <td class="border px-3 py-2">{{ $row->ParentDailyItemCode->user->name ?? 'Unknown' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-dashboard-layout>