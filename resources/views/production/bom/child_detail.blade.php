<x-app-layout>
    <style>
        @media print {

            /* Hide elements with these classes during print */
            .no-print,
            .print-hide {
                display: none !important;
            }

            /* Already existing rule to adjust layout */
            body {
                margin: 0;
                padding: 0;
            }
        }
    </style>

    <div class="container mx-auto py-6 px-4">
        <!-- Header & Navigation (Will not print) -->
        <div class="flex justify-between items-center mb-6 no-print">
            <div>
                <a href="{{ route('production.bom.index') }}" class="text-blue-500 hover:text-blue-700 text-sm">
                    ← Back to BOM Index
                </a>
                <h1 class="text-2xl font-bold mt-2">Item Code Details</h1>
                <p class="text-gray-600 text-sm">Viewing details for <strong>{{ $child->item_code }}</strong></p>
            </div>
            <div class="space-x-4">
                <button onclick="window.print()"
                    class="bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200 text-sm">
                    Print Page
                </button>
            </div>
        </div>

        <!-- Project & Child Information Section (Will print) -->
        <div class="grid gap-6 mb-6 md:grid-cols-2">
            <!-- Project Information Card -->
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">Project Information</h2>
                <p class="text-sm text-gray-700"><strong>Project Code:</strong> {{ $child->parent->code }}</p>
                <p class="text-sm text-gray-700"><strong>Project Description:</strong> {{ $child->parent->description }}
                </p>
            </div>

            <!-- Item Code Information Card -->
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">Item Code Information</h2>
                <p class="text-sm text-gray-700"><strong>Item Code:</strong> {{ $child->item_code }}</p>
                <p class="text-sm text-gray-700"><strong>Item Description:</strong> {{ $child->item_description }}</p>
                <p class="text-sm text-gray-700">
                    <strong>Quantity:</strong> {{ $child->quantity }}
                    <span class="block text-xs text-gray-500 mt-1">
                        Damaged: {{ $child->brokenChild->sum('broken_quantity') }} |
                        Good: {{ $child->quantity - $child->brokenChild->sum('broken_quantity') }}
                    </span>
                </p>
                <p class="text-sm text-gray-700"><strong>Measure:</strong> {{ $child->measure }}</p>
                <p class="text-sm text-gray-700"><strong>Status:</strong> {{ $child->status }}</p>
            </div>
        </div>

        <!-- Barcode Section (if available) (Will print if exists) -->
        @if (isset($barcodeUrl))
            <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">Generated Barcode</h3>
                <img src="{{ $barcodeUrl }}" alt="Barcode" class="mx-auto">
            </div>
        @endif

        <!-- Associated Processes Table (Hide on print) -->
        <div class="bg-white shadow-md rounded-lg p-6 print-hide">
            <h3 class="text-xl font-semibold mb-4 text-gray-800">Associated Processes</h3>
            <div class="overflow-auto">
                <table class="min-w-full table-auto border-collapse border border-gray-300 text-sm">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-2 border text-left font-medium text-gray-700">Process Name</th>
                            <th class="print-hidden px-4 py-2 border text-left font-medium text-gray-700">Scan In</th>
                            <th class="print-hidden px-4 py-2 border text-left font-medium text-gray-700">Scan Out</th>
                            <th class="print-hidden px-4 py-2 border text-left font-medium text-gray-700">Duration</th>
                            <th class="print-hidden px-4 py-2 border text-left font-medium text-gray-700">Pic</th>
                            <th class="print-hidden px-4 py-2 border text-left font-medium text-gray-700">Workers</th>
                            <th class="print-hidden px-4 py-2 border text-left font-medium text-gray-700">Status</th>
                            <th class="print-hidden px-4 py-2 border text-left font-medium text-gray-700">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($child->materialProcess as $process)
                            @php
                                $hours = 0;
                                $minutes = 0;
                                if ($process->scan_in && $process->scan_out) {
                                    $scanIn = \Carbon\Carbon::parse($process->scan_in);
                                    $scanOut = \Carbon\Carbon::parse($process->scan_out);
                                    $totalMinutes = $scanOut->diffInMinutes($scanIn);
                                    $hours = intdiv($totalMinutes, 60);
                                    $minutes = $totalMinutes % 60;
                                }
                            @endphp
                            <tr>
                                <td class="px-4 py-2 border text-gray-700">{{ $process->process_name }}</td>
                                <td class="print-hidden px-4 py-2 border text-gray-700">{{ $process->scan_in }}</td>
                                <td class="print-hidden px-4 py-2 border text-gray-700">{{ $process->scan_out }}</td>
                                <td class="print-hidden px-4 py-2 border text-gray-700">
                                    @if ($process->scan_in && $process->scan_out)
                                        {{ $hours }}h {{ $minutes }}m
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="print-hidden px-4 py-2 border text-gray-700">{{ $process->pic }}</td>
                                <td class="print-hidden px-4 py-2 border text-gray-700">
                                    @foreach ($process->workers as $worker)
                                        <div>{{ $worker->username }} - {{ $worker->shift }}</div>
                                    @endforeach
                                </td>
                                <td class="print-hidden px-4 py-2 border text-gray-700">
                                    {{ 
                                        $process->status == 0 ? 'Not Started' : 
                                        ($process->status == 1 ? 'Started' : 
                                        ($process->status == 2 ? 'Completed' : 'Unknown Status')) 
                                    }}
                                </td>
                                <td class="print-hidden px-4 py-2 border text-gray-700">
                                    @if (!$process->scan_in)
                                        <form action="{{ route('production.process.delete', $process->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 text-sm">
                                                Delete Process
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-500 text-sm">Cannot delete - Process already started</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
