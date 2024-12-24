<x-print-layout>
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
                <a href="{{ route('production.bom.show', ['id' => $temp]) }}"
                    class="text-blue-500 hover:text-blue-700 text-sm">
                    ← Back
                </a>
                <h1 class="text-2xl font-bold mt-2">Item Code Details</h1>
                <p class="text-gray-600 text-sm">Viewing details for <strong>{{ $child->item_code }}</strong></p>
            </div>
            <div class="space-x-4">
                <button onclick="window.print()"
                    class="bg-blue-100 text-blue-700 px-4 py-2 rounded-md hover:bg-blue-200 text-sm border-blue-500 border">
                    Print Page
                </button>

                <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg cursor-pointer"
                    onclick="openModal('{{ $child->item_code }}')">Upload Files</button>
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
                <div class="flex justify-center space-x-6"> <!-- Flex container for side-by-side layout -->
                    {{-- <img src="{{ $barcodeUrl }}" alt="Barcode" class="mx-auto" style="height: 30px; width: 200px;"> --}}
                    <img src="data:image/png;base64, {{ $qrcoded }}" alt="QR Code">
                </div>
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
                            <th class="print-hidden px-4 py-2 border text-left font-medium text-gray-700">Scan Start
                            </th>
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
                                if ($process->scan_start && $process->scan_out) {
                                    $scanStart = \Carbon\Carbon::parse($process->scan_start);
                                    $scanOut = \Carbon\Carbon::parse($process->scan_out);
                                    $totalMinutes = $scanOut->diffInMinutes($scanStart);
                                    $hours = intdiv($totalMinutes, 60);
                                    $minutes = $totalMinutes % 60;
                                }
                            @endphp
                            <tr>
                                <td class="px-4 py-2 border text-gray-700">{{ $process->process_name }}</td>
                                <td class="print-hidden px-4 py-2 border text-gray-700">{{ $process->scan_in }}</td>
                                <td class="print-hidden px-4 py-2 border text-gray-700">{{ $process->scan_start }}</td>
                                <td class="print-hidden px-4 py-2 border text-gray-700">{{ $process->scan_out }}</td>
                                <td class="print-hidden px-4 py-2 border text-gray-700">
                                    @if ($process->scan_start && $process->scan_out)
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
                                    {{ $process->status == 0
                                        ? 'Not Started'
                                        : ($process->status == 1
                                            ? 'Started'
                                            : ($process->status == 2
                                                ? 'Completed'
                                                : 'Unknown Status')) }}
                                </td>
                                <td class="print-hidden px-4 py-2 border text-gray-700">
                                    @if (!$process->scan_in)
                                        <form action="{{ route('production.process.delete', $process->id) }}"
                                            method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 text-sm">
                                                Delete Process
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-500 text-sm">Cannot delete - Process already
                                            started</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($image && $image->name)
        <img src="{{ asset('storage/files/' . $image->name) }}" alt="Image" class="mx-auto h-32 object-contain">
    @else
        <img src="{{ asset('storage/files/placeholder.png') }}" alt="Placeholder Image"
            class="mx-auto h-32 object-contain">
    @endif


    <div id="uploadModal" class="fixed inset-0 items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="w-full max-w-lg mx-auto mt-12">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="px-6 py-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Upload Files</h2>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2" for="files">Select Files</label>
                        <div class="flex items-center justify-center w-full">
                            <label
                                class="flex flex-col items-center w-full h-32 px-4 transition bg-white border-2 border-gray-300 border-dashed rounded-md cursor-pointer hover:border-gray-400 hover:bg-gray-50">
                                <span class="flex items-center space-x-2">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 16V8m10 8V8m-5 8V8m-5 8h10"></path>
                                    </svg>
                                    <span class="font-medium text-gray-600">Drop files here or <span
                                            class="text-blue-600 underline">browse</span></span>
                                </span>
                                <form action="{{ route('file.upload') }}" method="post" enctype="multipart/form-data"
                                    id="formUploadFile">
                                    @csrf
                                    <input type="hidden" name="item_code" id="item_code">
                                    <input id="files" type="file" name="files[]" class="hidden" multiple>
                                </form>
                            </label>
                        </div>
                        <div id="fileList" class="mt-4 text-sm text-gray-600"></div>
                    </div>
                    <div class="flex justify-center">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full"
                            onclick="document.getElementById('formUploadFile').submit()">
                            Upload
                        </button>
                        <button class="ml-4 bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-full"
                            onclick="closeModal()">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function openModal(item_code) {
            console.log(item_code);
            document.getElementById('item_code').value = item_code;
            document.getElementById('uploadModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('uploadModal').classList.add('hidden');
        }

        document.getElementById('files').addEventListener('change', function() {
            const fileList = document.getElementById('fileList');
            fileList.innerHTML = '';
            for (let i = 0; i < this.files.length; i++) {
                const listItem = document.createElement('div');
                listItem.textContent = this.files[i].name;
                fileList.appendChild(listItem);
            }
        });
    </script>
</x-print-layout>
