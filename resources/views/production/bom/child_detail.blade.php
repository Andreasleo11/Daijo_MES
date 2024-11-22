<x-app-layout>
<style>
    @media print {
        /* Hide columns when printing */
        .print-hidden {
            display: none;
        }
    }
</style>
<div class="container mx-auto py-6">
    <h1 class="text-2xl font-bold mb-4">Child Details - {{ $child->item_code }}</h1>
    <h1 class="text-2xl font-bold mb-4">Project Code - {{ $child->parent->item_code }}</h1>
    <h1 class="text-2xl font-bold mb-4">Project Description - {{ $child->parent->item_description }}</h1>

    <div class="bg-white shadow-lg rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">Child Information</h3>
        <p><strong>Item Code:</strong> {{ $child->item_code }}</p>
        <p><strong>Item Description:</strong> {{ $child->item_description }}</p>
        <p><strong>Quantity:</strong> {{ $child->quantity }}</p>
        <p><strong>Measure:</strong> {{ $child->measure }}</p>
        <p><strong>Status:</strong> {{ $child->status }}</p>

        @if (isset($barcodeUrl))
            <div class="mt-6">
                <h3 class="text-lg font-semibold mb-4">Generated Barcode</h3>
                <img src="{{ $barcodeUrl }}" alt="Barcode">
            </div>
        @endif

        <h3 class="text-lg font-semibold mt-6 mb-4">Associated Processes</h3>
        <table class="min-w-full table-auto border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 border">Process Name</th>
                    <!-- Other columns will be hidden when printing -->
                    <th class="print-hidden px-4 py-2 border">Scan In</th>
                    <th class="print-hidden px-4 py-2 border">Scan Out</th>
                    <th class="print-hidden px-4 py-2 border">Pic</th>
                    <th class="print-hidden px-4 py-2 border">Workers</th>
                    <th class="print-hidden px-4 py-2 border">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($child->materialProcess as $process)
                    <tr>
                        <td class="px-4 py-2 border">{{ $process->process_name }}</td>
                        <!-- These cells will be hidden when printing -->
                        <td class="print-hidden px-4 py-2 border">{{ $process->scan_in }}</td>
                        <td class="print-hidden px-4 py-2 border">{{ $process->scan_out }}</td>
                        <td class="print-hidden px-4 py-2 border">{{ $process->pic }}</td>
                        <td class="print-hidden px-4 py-2 border">
                            @foreach($process->workers as $worker)
                                {{ $worker->username }} - {{ $worker->shift }} <br>
                            @endforeach
                        </td>
                        <td class="print-hidden px-4 py-2 border">{{ $process->status == 0 ? 'Pending' : 'Completed' }}</td>     
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">
                        <a href="{{ route('production.bom.index') }}" class="text-blue-500 hover:text-blue-700">
                            Back to BOM Index
                        </a>
                    </div>
</x-app-layout>