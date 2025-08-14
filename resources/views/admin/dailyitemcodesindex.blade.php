<x-app-layout>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Log Operasi Mesin</h1>

    @php
        $byMachine = $dailyItemCodes->groupBy(fn($item) => $item->user->name ?? 'Unknown');
    @endphp

    @foreach ($byMachine as $machineName => $logs)
        <div x-data="{ open: false }" class="mb-6 border rounded shadow-sm">
            <!-- Mesin Header -->
            <button @click="open = !open" class="w-full text-left px-5 py-3 bg-blue-100 hover:bg-blue-200 text-blue-800 font-semibold flex justify-between items-center">
                <span>{{ $machineName }}</span>
                <svg :class="{'transform rotate-180': open}" class="h-5 w-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="open" class="px-4 py-3 bg-white space-y-4" x-transition>
                @php $groupedByDate = $logs->groupBy('start_date'); @endphp

                @foreach ([$yesterday, $today, $tomorrow] as $date)
                    @if (isset($groupedByDate[$date]))
                        <div>
                            <h3 class="text-md font-medium text-indigo-600 mt-4 mb-2">
                                @if ($date === $today)
                                    Hari Ini ({{ \Carbon\Carbon::parse($date)->format('d M Y') }})
                                @elseif ($date === $yesterday)
                                    Kemarin ({{ \Carbon\Carbon::parse($date)->format('d M Y') }})
                                @else
                                    Besok ({{ \Carbon\Carbon::parse($date)->format('d M Y') }})
                                @endif
                            </h3>

                            <div class="overflow-x-auto bg-white border rounded">
                                <table class="min-w-full divide-y divide-gray-200 text-sm text-left">
                                    <thead class="bg-gray-100 text-gray-700 uppercase">
                                        <tr>
                                            <th class="px-4 py-3">ID</th>
                                            <th class="px-4 py-3">Item Code</th>
                                            <th class="px-4 py-3">Shift</th>
                                            <th class="px-4 py-3">Start</th>
                                            <th class="px-4 py-3">End</th>
                                            <th class="px-4 py-3">Status</th>
                                            <th class="px-4 py-3">Qty</th>
                                            <th class="px-4 py-3">Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 text-gray-800">
                                        @foreach ($groupedByDate[$date]->sortBy('shift') as $code)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-2">{{ $code->id }}</td>
                                                <td class="px-4 py-2">{{ $code->item_code }}</td>
                                                <td class="px-4 py-2">Shift {{ $code->shift }}</td>
                                                <td class="px-4 py-2">{{ $code->start_time }}</td>
                                                <td class="px-4 py-2">{{ $code->end_time }}</td>
                                                <td class="px-4 py-2 flex items-center gap-2">
                                                    @if ($code->is_done)
                                                        <span class="inline-flex items-center px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
                                                            Done
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-1 text-xs font-semibold text-yellow-700 bg-yellow-100 rounded-full">
                                                            In Progress
                                                        </span>
                                                    @endif

                                                    <!-- Tombol: Set NULL -->
                                                    <form method="POST" action="{{ route('dailyitemcodes.set-status', $code->id) }}">
                                                        @csrf
                                                        <input type="hidden" name="status" value="null">
                                                        <button type="submit" class="text-red-600 hover:underline text-xs">✖</button>
                                                    </form>

                                                    <!-- Tombol: Set Done -->
                                                    <form method="POST" action="{{ route('dailyitemcodes.set-status', $code->id) }}">
                                                        @csrf
                                                        <input type="hidden" name="status" value="1">
                                                        <button type="submit" class="text-green-600 hover:underline text-xs">✔</button>
                                                    </form>
                                                </td>
                                                <td class="px-4 py-2">{{ $code->quantity }}</td>
                                                <td class="px-4 py-2">{{ $code->remark }}</td>
                                                  <!-- Dropdown detail -->
                                                    <!-- <tr x-show="open" class="bg-gray-50 text-xs">
                                                        <td colspan="8" class="px-4 py-3 space-y-4">
                                                            {{-- Hourly Remarks --}}
                                                            @if ($code->hourlyRemarks && count($code->hourlyRemarks))
                                                                <div>
                                                                    <div class="font-semibold text-blue-600 mb-1">Hourly Remarks</div>
                                                                    <table class="w-full border border-collapse border-gray-300">
                                                                        <thead class="bg-gray-100 text-gray-700">
                                                                            <tr>
                                                                                <th class="border px-2 py-1">Start</th>
                                                                                <th class="border px-2 py-1">End</th>
                                                                                <th class="border px-2 py-1">Target</th>
                                                                                <th class="border px-2 py-1">scan</th>
                                                                                <th class="border px-2 py-1">Actual Production Per Hour</th>
                                                                                <th class="border px-2 py-1">PIC</th>
                                                                                <th class="border px-2 py-1">Dibikin Tanggal</th>
                                                                                <th class="border px-2 py-1">Note</th>
                                                                                <th class="border px-2 py-1">Aksi</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach ($code->hourlyRemarks as $hr)
                                                                                <tr>
                                                                                    <td class="border px-2 py-1">{{ $hr->start_time }}</td>
                                                                                    <td class="border px-2 py-1">{{ $hr->end_time }}</td>
                                                                                    <td class="border px-2 py-1 text-green-600">{{ $hr->target }}</td>
                                                                                    <td class="border px-2 py-1 text-indigo-600">{{ $hr->actual }}</td>
                                                                                    <td class="border px-2 py-1">{{ $hr->actual_production }}</td>
                                                                                    <td class="border px-2 py-1">{{ $hr->pic }}</td>
                                                                                    <td class="border px-2 py-1">
                                                                                        {{ \Carbon\Carbon::parse($hr->created_at)->addHours(7)->format('Y-m-d H:i:s') }}
                                                                                    </td>
                                                                                    <td class="border px-2 py-1 text-gray-600">{{ $hr->remark ?? '-' }}</td>
                                                                                    <td class="border px-2 py-1 text-center">
                                                                                        <form action="{{ route('hourly-remarks.destroy', $hr->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                                                                            @csrf
                                                                                            @method('DELETE')
                                                                                            <button class="bg-red-500 text-white px-2 py-1 text-sm rounded hover:bg-red-600">Delete</button>
                                                                                        </form>
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            @endif

                                                            {{-- Scanned Data --}}
                                                            @if ($code->scannedData && count($code->scannedData))
                                                                <div>
                                                                    <div class="font-semibold text-blue-600 mb-1">Scanned Data</div>
                                                                    <table class="w-full border border-collapse border-gray-300">
                                                                        <thead class="bg-gray-100 text-gray-700">
                                                                            <tr>
                                                                                <th class="border px-2 py-1">SPK</th>
                                                                                <th class="border px-2 py-1">Item Code</th>
                                                                                <th class="border px-2 py-1">Qty</th>
                                                                                <th class="border px-2 py-1">Warehouse</th>
                                                                                <th class="border px-2 py-1">Label</th>
                                                                                <th class="border px-2 py-1">User</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach ($code->scannedData as $scan)
                                                                                <tr>
                                                                                    <td class="border px-2 py-1">{{ $scan->spk_code }}</td>
                                                                                    <td class="border px-2 py-1">{{ $scan->item_code }}</td>
                                                                                    <td class="border px-2 py-1 text-green-600">{{ $scan->quantity }}</td>
                                                                                    <td class="border px-2 py-1">{{ $scan->warehouse }}</td>
                                                                                    <td class="border px-2 py-1 text-indigo-600">{{ $scan->label }}</td>
                                                                                    <td class="border px-2 py-1 text-purple-600">{{ $scan->user }}</td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            @endif
                                                        </td>
                                                    </tr>
                                            </tr> -->
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endforeach
</div>
</x-app-layout>
