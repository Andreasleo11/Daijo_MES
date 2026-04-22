<x-app-layout>

    @if (session('photo'))
        {{-- Fancybox CSS --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/5.0.36/fancybox.min.css" />

        {{-- Fancybox JS --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/5.0.36/fancybox.umd.min.js"></script>

        {{-- Photo Modal --}}
        <div id="photoModal" class="hidden">
            <a href="{{ asset('storage/' . session('photo')) }}" 
                data-fancybox="gallery"
                data-caption="Item Photo"
                class="hidden">
            </a>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Fancybox.bind('[data-fancybox="gallery"]', {
                    Thumbs: {
                        autoStart: true,
                    },
                    Image: {
                        zoom: true,
                    },
                    transitionEffect: "fade",

                    on: {
                        reveal: (fancybox, slide) => {
                            // Auto close after 1 second
                            setTimeout(() => {
                                fancybox.close();
                            }, 1000);
                        },

                        destroy: () => {
                            // Focus back to SPK input after modal closed
                            const spkInput = document.getElementById('spk_code');
                            if (spkInput) {
                                spkInput.focus();
                            }
                        }
                    }
                });

                // Auto-open modal on page load
                const photoLink = document.querySelector('[data-fancybox="gallery"]');
                if (photoLink) {
                    setTimeout(() => {
                        photoLink.click();
                    }, 100);
                }
            });
        </script>

    @endif


    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-4">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-8">
                {{-- Header --}}
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 mb-1 sm:mb-2">
                    SO Number: {{ $docNum }}
                </h1>
                <h2 class="text-lg sm:text-xl font-semibold text-gray-600 mb-1 sm:mb-2">
                    Customer: {{ $customer }}
                </h2>
                <h2 class="text-lg sm:text-xl font-semibold text-gray-600 mb-4 sm:mb-6">
                    Date: {{ $date }}
                </h2>

                {{-- Error Alert --}}
                <!-- @if ($errors->any())
                    <div
                        class="bg-red-100 text-red-800 border border-red-300 rounded-md p-3 sm:p-4 mb-4 relative flex items-start sm:items-center justify-between alert-container text-sm sm:text-base"
                    >
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button
                            type="button"
                            class="text-red-800 hover:text-red-900 ml-2 text-xl sm:text-2xl"
                            onclick="this.parentElement.style.display='none';"
                        >
                            &times;
                        </button>
                    </div>
                @endif -->

                <a href="{{ route('pegawai.scan') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 7h16M4 12h16M4 17h16" />
                        </svg>
                        Scan Pegawai
                </a>

                <button id="check-finish-btn"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 13l4 4L19 7" />
                    </svg>
                    Check Finish
                </button>

                {{-- Tabel SO --}}
                @if ($data->isEmpty())
                    <p class="text-red-500 text-base sm:text-lg">
                        No data found for this SO Number.
                    </p>
                @else
                    {{-- ==================== DESKTOP TABLE ==================== --}}
                    <div class="hidden sm:block mt-4 overflow-x-auto -mx-4 sm:mx-0">
                        <table class="min-w-full bg-white border-collapse border border-gray-200 text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border border-gray-300 px-4 py-2 text-left">No</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Model</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Description</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Delivery Qty</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Qty/Pack</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">CTN</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Remarks</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Scanned Box</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Scanned Qty</th>
                                </tr>
                            </thead>

                            <tbody>
                                @php
                                    $totalCtn = 0;
                                @endphp

                                @foreach ($data as $item)
                                    @php
                                        $scannedTotalQuantity = $item->scannedTotalQuantity;
                                        $ctn = ceil($item->quantity / $item->packaging_quantity);
                                        $totalCtn += $ctn;
                                        $rowClass = $item->scannedCount > $ctn ? 'bg-red-100' : '';
                                    @endphp

                                    <tr id="row-desktop-{{ $item->item_code }}" class="hover:bg-green-50 {{ $rowClass }} transition-colors duration-500">
                                        <td class="border border-gray-300 px-4 py-2">{{ $loop->iteration }}</td>
                                        <td class="border border-gray-300 px-4 py-2">{{ $item->item_code }}</td>
                                        <td class="border border-gray-300 px-4 py-2">{{ $item->item_name }}</td>
                                        <td class="border border-gray-300 px-4 py-2">{{ $item->quantity }}</td>
                                        <td class="border border-gray-300 px-4 py-2">{{ $item->packaging_quantity }}</td>
                                        <td class="border border-gray-300 px-4 py-2">{{ number_format($ctn) }}</td>
                                        <td class="border border-gray-300 px-4 py-2"></td>
                                        <td id="scanned-box-desktop-{{ $item->item_code }}" class="border border-gray-300 px-4 py-2">
                                            {{ $item->scannedCount }} / {{ number_format($ctn) }}
                                        </td>
                                        <td id="scanned-qty-desktop-{{ $item->item_code }}" class="border border-gray-300 px-4 py-2">{{ $scannedTotalQuantity }}</td>
                                    </tr>
                                @endforeach

                                <tr class="bg-gray-200 font-semibold">
                                    <td class="border border-gray-300 px-4 py-2" colspan="4"></td>

                                    <!-- Qty/Pack column -->
                                    <td class="border border-gray-300 px-4 py-2 text-right">
                                        Total Box
                                    </td>

                                    <!-- CTN column -->
                                    <td class="border border-gray-300 px-4 py-2">
                                        {{ number_format($totalCtn) }}
                                    </td>

                                    <td class="border border-gray-300 px-4 py-2" colspan="3"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>


                    {{-- ==================== MOBILE CARD VIEW ==================== --}}
                    <div class="space-y-4 sm:hidden">
                        @foreach ($data as $item)
                            @php
                                $scannedTotalQuantity = $item->scannedTotalQuantity;
                                $ctn = ceil($item->quantity / $item->packaging_quantity);
                                $isWarning = $item->scannedCount > $ctn;
                            @endphp

                            <div id="row-mobile-{{ $item->item_code }}" class="p-4 rounded-lg shadow border transition-colors duration-500
                                {{ $isWarning ? 'bg-red-100 border-red-300' : 'bg-white border-gray-200' }}">
                                
                                {{-- Header --}}
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-xs text-gray-500">Model</p>
                                        <p class="font-semibold text-gray-900">{{ $item->item_code }}</p>
                                    </div>
                                    <span class="text-xs px-2 py-1 rounded 
                                        {{ $isWarning ? 'bg-red-500 text-white' : 'bg-green-600 text-white' }}">
                                        {{ $isWarning ? 'Over' : 'OK' }}
                                    </span>
                                </div>

                                <p class="mt-1 text-sm text-gray-600">{{ $item->item_name }}</p>

                                {{-- Details --}}
                                <div class="grid grid-cols-2 gap-3 text-sm mt-4">

                                    <div>
                                        <p class="text-xs text-gray-500">Delivery Qty</p>
                                        <p class="font-semibold">{{ $item->quantity }}</p>
                                    </div>

                                    <div>
                                        <p class="text-xs text-gray-500">Qty / Pack</p>
                                        <p class="font-semibold">{{ $item->packaging_quantity }}</p>
                                    </div>

                                    <div>
                                        <p class="text-xs text-gray-500">CTN</p>
                                        <p class="font-semibold">{{ $ctn }}</p>
                                    </div>

                                    <div>
                                        <p id="scanned-box-mobile-{{ $item->item_code }}" class="font-semibold">
                                            {{ $item->scannedCount }} / {{ $ctn }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-xs text-gray-500">Scanned Qty</p>
                                        <p id="scanned-qty-mobile-{{ $item->item_code }}" class="font-semibold">{{ $scannedTotalQuantity }}</p>
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Tombol Update All / Info --}}
                <div id="update-all-container" class="mt-6">
                    @if ($allFinished && ! $allDone)
                        <a
                            href="{{ route('update.so.data', ['docNum' => $docNum]) }}"
                            class="inline-flex justify-center w-full sm:w-auto px-6 py-3 bg-blue-600 text-white text-sm sm:text-base font-semibold rounded-md shadow hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Update All
                        </a>
                    @elseif (! $allFinished && ! $allDone)
                        <p class="text-red-500 mt-4 text-sm sm:text-base">
                            Not all items are finished yet.
                        </p>
                    @endif
                </div>


                 @if ($errors->any())
                    <div
                        class="bg-red-100 text-red-800 border border-red-300 rounded-md p-3 sm:p-4 mb-4 relative flex items-start sm:items-center justify-between alert-container text-sm sm:text-base"
                    >
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button
                            type="button"
                            class="text-red-800 hover:text-red-900 ml-2 text-xl sm:text-2xl"
                            onclick="this.parentElement.style.display='none';"
                        >
                            &times;
                        </button>
                    </div>
                @endif


                {{-- Packaging Mode Toggle --}}
                <div class="mt-8 flex justify-end">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="packagingToggle" class="sr-only peer" onchange="togglePackagingMode()">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        <span class="ml-3 text-sm font-medium text-gray-700">Scan Packaging Barcode</span>
                    </label>
                </div>

                {{-- Form Scan Barcode --}}
                @if (! $allDone)
                    <form
                        id="barcode-form"
                        method="POST"
                        action="{{ route('so.scanBarcode') }}"
                        class="mt-8 space-y-4"
                    >
                        @csrf
                        <input type="hidden" name="so_number" value="{{ $docNum }}" />
                        <input type="hidden" name="scan_mode" id="scan_mode_input" value="OFF" />

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="spk_code" class="block text-sm font-medium text-gray-700">
                                    SPK Code:
                                </label>
                                <input
                                    type="text"
                                    id="spk_code"
                                    name="spk_code"
                                    required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                />
                            </div>

                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700">
                                    Quantity:
                                </label>
                                <input
                                    type="number"
                                    id="quantity"
                                    name="quantity"
                                    required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                />
                            </div>

                            <div>
                                <label for="warehouse" class="block text-sm font-medium text-gray-700">
                                    Warehouse:
                                </label>
                                <input
                                    type="text"
                                    id="warehouse"
                                    name="warehouse"
                                    required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                />
                            </div>

                            <div>
                                <label for="label" class="block text-sm font-medium text-gray-700">
                                    Label:
                                </label>
                                <input
                                    type="number"
                                    id="label"
                                    name="label"
                                    required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                />
                            </div>

                            {{-- NEW PACKAGING SECTION (Additive) --}}
                            <div id="packaging_barcode_section" class="hidden col-span-full border-t pt-4 mt-2">
                                <h4 class="text-xs font-bold text-blue-600 mb-2 uppercase">Packaging Details</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div>
                                        <label for="packaging_name" class="block text-sm font-medium text-gray-700">Packaging Name / Code:</label>
                                        <input type="text" id="packaging_name" name="packaging_name" class="mt-1 block w-full px-3 py-2 border-2 border-blue-200 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" placeholder="Scan kemasan...">
                                    </div>
                                    <div>
                                        <label for="packaging_label" class="block text-sm font-medium text-gray-700">Packaging Label:</label>
                                        <input type="text" id="packaging_label" name="packaging_label" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    </div>
                                    <div>
                                        <label for="packaging_warehouse" class="block text-sm font-medium text-gray-700">Warehouse (Out):</label>
                                        <input type="text" id="packaging_warehouse" name="packaging_warehouse" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="mt-4 w-full sm:w-auto px-6 py-2 bg-indigo-600 text-white text-sm sm:text-base font-semibold rounded-md shadow hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Scan Barcode
                        </button>
                    </form>
                @else
                    <h1 class="mt-8 text-center text-lg font-semibold text-green-600">
                        DOCUMENT FINISHED
                    </h1>
                @endif

                {{-- Scanned Data --}}
                <h2 class="text-lg sm:text-xl font-bold text-gray-800 mt-10">
                    Scanned Data
                </h2>

                <div id="history-container" class="mt-8">
                    @forelse ($scandatas as $itemCode => $scans)
                    <h3 class="text-base sm:text-lg font-semibold text-gray-700 mt-4">
                        Item Code: {{ $itemCode }}
                    </h3>

                    <div class="mt-2 overflow-x-auto -mx-4 sm:mx-0">
                        <table class="min-w-full bg-white border-collapse border border-gray-200 text-xs sm:text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left">No</th>
                                    <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left">Quantity</th>
                                    <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left">Warehouse</th>
                                    <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left">Label</th>
                                    <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left">Created At</th>
                                    <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left">Action</th>
                                </tr>
                            </thead>
                            <tbody id="scandata-body-{{ $itemCode }}">
                                @foreach ($scans as $scan)
                                    <tr class="hover:bg-gray-50">
                                        <td class="border border-gray-300 px-2 sm:px-4 py-2">
                                            {{ $loop->iteration }}
                                        </td>
                                        <td class="border border-gray-300 px-2 sm:px-4 py-2">
                                            {{ $scan->quantity }}
                                        </td>
                                        <td class="border border-gray-300 px-2 sm:px-4 py-2">
                                            {{ $scan->warehouse }}
                                        </td>
                                        <td class="border border-gray-300 px-2 sm:px-4 py-2">
                                            {{ $scan->label }}
                                        </td>
                                        <td class="border border-gray-300 px-2 sm:px-4 py-2">
                                            {{ $scan->created_at->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s') }}
                                        </td>
                                        <td class="border border-gray-300 px-2 sm:px-4 py-2 space-x-2">
                                            <!-- Edit -->
                                            <button
                                                onclick="openEditModal({{ $scan->id }}, {{ $scan->quantity }})"
                                                class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs">
                                                Edit
                                            </button>

                                            <!-- Delete -->
                                            <form action="{{ route('scan.delete', $scan->id) }}"
                                                method="POST"
                                                class="inline"
                                                onsubmit="return confirm('Yakin mau hapus data ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @empty
                        <p id="no-scandata-msg" class="text-red-500 text-base sm:text-lg mt-2">
                            No scanned data yet for this SO Number.
                        </p>
                    @endforelse
                </div>
            </div>

            <div id="editModal"
                class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 w-80">
                    <h3 class="text-lg font-semibold mb-4">Edit Quantity</h3>

                    <form id="editForm" method="POST">
                        @csrf
                        @method('PUT')

                        <input type="number"
                            name="quantity"
                            id="editQuantity"
                            class="w-full border rounded px-3 py-2 mb-4"
                            required
                            min="1">

                        <div class="flex justify-end space-x-2">
                            <button type="button"
                                    onclick="closeEditModal()"
                                    class="px-4 py-2 bg-gray-400 text-white rounded">
                                Cancel
                            </button>

                            <button type="submit"
                                    class="px-4 py-2 bg-green-600 text-white rounded">
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>



            {{-- Scan Mode Toggle for Mobile --}}
            <div class="sm:hidden mt-6 mb-4 text-center">
                <button id="scanModeBtn"
                    class="px-6 py-3 bg-green-600 text-white font-semibold rounded-lg shadow">
                    Start Scan Mode
                </button>
            </div>

            {{-- Camera for ZXing --}}
            <div id="scanView" class="hidden sm:hidden mt-4">
                <video id="scannerVideo" autoplay muted playsinline class="w-full rounded-lg shadow"></video>
            </div>
        </div>
    </div>


    <div id="scanAlert"
        class="hidden fixed top-4 left-1/2 transform -translate-x-1/2 px-4 py-2 rounded-lg text-white text-sm font-semibold shadow-lg z-50">
    </div>

    
    <script src="https://unpkg.com/@zxing/browser@latest"></script>

    <script>
    // --- KELOMPOK FUNGSI GLOBAL ---
    
    // Fungsi untuk update UI secara parsial (Tanpa Reload)
    function updateUI(data) {
        if (!data.item_code) return;

        // 1. Update Tabel Desktop
        const deskBox = document.getElementById(`scanned-box-desktop-${data.item_code}`);
        const deskQty = document.getElementById(`scanned-qty-desktop-${data.item_code}`);
        const deskRow = document.getElementById(`row-desktop-${data.item_code}`);

        if (deskBox) {
            const parts = deskBox.innerText.split('/');
            const totalCtn = parts[1] ? parts[1].trim() : '?';
            deskBox.innerText = `${data.scannedCount} / ${totalCtn}`;
            deskBox.classList.add('bg-yellow-100');
            setTimeout(() => deskBox.classList.remove('bg-yellow-100'), 1000);
        }
        if (deskQty) {
            deskQty.innerText = data.scannedTotalQuantity;
        }

        // 2. Update Tampilan Mobile
        const mobBox = document.getElementById(`scanned-box-mobile-${data.item_code}`);
        const mobQty = document.getElementById(`scanned-qty-mobile-${data.item_code}`);
        const mobRow = document.getElementById(`row-mobile-${data.item_code}`);

        if (mobBox) {
            const parts = mobBox.innerText.split('/');
            const totalCtn = parts[1] ? parts[1].trim() : '?';
            mobBox.innerText = `${data.scannedCount} / ${totalCtn}`;
        }
        if (mobQty) {
            mobQty.innerText = data.scannedTotalQuantity;
        }

        // 3. Update Tabel Riwayat Scan di Bawah
        let historyBody = document.getElementById(`scandata-body-${data.item_code}`);
        const historyContainer = document.getElementById('history-container');
        const noDataMsg = document.getElementById('no-scandata-msg');

        // Jika tabel belum ada (scan pertama untuk item ini), buat tabelnya
        if (!historyBody && historyContainer && data.newScan) {
            if (noDataMsg) noDataMsg.remove(); // Hapus pesan "No data" jika ada

            const newTableHtml = `
                <h3 class="text-base sm:text-lg font-semibold text-gray-700 mt-4">
                    Item Code: ${data.item_code}
                </h3>
                <div class="mt-2 overflow-x-auto -mx-4 sm:mx-0">
                    <table class="min-w-full bg-white border-collapse border border-gray-200 text-xs sm:text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left">No</th>
                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left">Quantity</th>
                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left">Warehouse</th>
                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left">Label</th>
                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left">Created At</th>
                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody id="scandata-body-${data.item_code}"></tbody>
                    </table>
                </div>
            `;
            historyContainer.insertAdjacentHTML('beforeend', newTableHtml);
            historyBody = document.getElementById(`scandata-body-${data.item_code}`);
        }

        if (historyBody && data.newScan) {
            const rowCount = historyBody.rows.length + 1;
            const newRow = historyBody.insertRow(0); // Prepend
            newRow.classList.add('hover:bg-gray-50', 'bg-yellow-50', 'transition-colors', 'duration-1000');

            const scanDeleteUrl = "{{ route('scan.delete', ':id') }}".replace(':id', data.newScan.id);
            newRow.innerHTML = `
                <td class="border border-gray-300 px-2 sm:px-4 py-2">${rowCount}</td>
                <td class="border border-gray-300 px-2 sm:px-4 py-2">${data.newScan.quantity}</td>
                <td class="border border-gray-300 px-2 sm:px-4 py-2">${data.newScan.warehouse}</td>
                <td class="border border-gray-300 px-2 sm:px-4 py-2">${data.newScan.label}</td>
                <td class="border border-gray-300 px-2 sm:px-4 py-2">${data.newScan.created_at}</td>
                <td class="border border-gray-300 px-2 sm:px-4 py-2 space-x-2">
                    <button onclick="openEditModal(${data.newScan.id}, ${data.newScan.quantity})" 
                            class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs">
                        Edit
                    </button>
                    <form action="${scanDeleteUrl}" method="POST" class="inline" onsubmit="return confirm('Yakin mau hapus data ini?')">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">
                            Delete
                        </button>
                    </form>
                </td>
            `;
            setTimeout(() => newRow.classList.remove('bg-yellow-50'), 2000);
        }

        // 4. Efek Flash pada Row (Feedback visual)
        [deskRow, mobRow].forEach(row => {
            if (row) {
                row.classList.add('bg-blue-100');
                setTimeout(() => row.classList.remove('bg-blue-100'), 2000);
            }
        });

        // 5. Update Tombol "Update All" secara dinamis
        const container = document.getElementById('update-all-container');
        if (container && data.allFinished) {
            const docNum = "{{ $docNum }}";
            const updateUrl = "{{ route('update.so.data', ['docNum' => ':docNum']) }}".replace(':docNum', docNum);
            container.innerHTML = `
                <a href="${updateUrl}" class="inline-flex justify-center w-full sm:w-auto px-6 py-3 bg-blue-600 text-white text-sm sm:text-base font-semibold rounded-md shadow hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Update All
                </a>
            `;
        }
        
        // 6. Vibrasi jika device mendukung
        if (navigator.vibrate) navigator.vibrate(100);
    }

    function openEditModal(id, quantity) {
        const modal = document.getElementById('editModal');
        const form = document.getElementById('editForm');
        const qtyInput = document.getElementById('editQuantity');
        form.action = `/scan/${id}`;
        qtyInput.value = quantity;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeEditModal() {
        const modal = document.getElementById('editModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function showAlert(msg, type = "success") {
        const alertBox = document.getElementById('scanAlert');
        if (!alertBox) return;
        alertBox.innerText = msg;
        alertBox.classList.remove("hidden");
        alertBox.style.backgroundColor = type === "success" ? "#16a34a" : "#dc2626";
        setTimeout(() => alertBox.classList.add("hidden"), 3000);
    }

    // --- MAIN EVENT LISTENERS ---
    
    // --- ADDITIVE PACKAGING FUNCTIONS ---
    function togglePackagingMode() {
        const toggle = document.getElementById('packagingToggle');
        const section = document.getElementById('packaging_barcode_section');
        const modeInput = document.getElementById('scan_mode_input');

        if (toggle.checked) {
            section.classList.remove('hidden');
            if (modeInput) modeInput.value = 'ON';
            localStorage.setItem('packaging_mode', 'ON');
        } else {
            section.classList.add('hidden');
            if (modeInput) modeInput.value = 'OFF';
            localStorage.setItem('packaging_mode', 'OFF');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        let autoSubmitTimer;
        const spkInput = document.getElementById('spk_code');
        const labelInput = document.getElementById('label');
        const pkgNameInput = document.getElementById('packaging_name');
        const pkgLabelInput = document.getElementById('packaging_label');
        const pkgWhseInput = document.getElementById('packaging_warehouse');
        const barcodeForm = document.getElementById('barcode-form');
        const toggle = document.getElementById('packagingToggle');
        const modeInput = document.getElementById('scan_mode_input');

        // Restore preference
        if (localStorage.getItem('packaging_mode') === 'ON') {
            toggle.checked = true;
            if (modeInput) modeInput.value = 'ON';
            togglePackagingMode();
        }

        // --- ADDITIVE FOCUS & PARSING LOGIC ---
        if (spkInput) {
            spkInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    // Jika Mode ON, tetap biarkan submit untuk validasi SPK dulu
                    if (toggle.checked) {
                        // Jangan preventDefault, biar dia hit server untuk validasi SPK
                        console.log("Submitting SPK for validation...");
                    }
                }
            });
        }

        if (pkgNameInput) {
            pkgNameInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const val = this.value.trim();
                    if (val === '') return;

                    const parts = val.split('\t');
                    if (parts.length > 1) {
                        e.preventDefault();
                        this.value = parts[0]; // Set Packaging Name
                        pkgLabelInput.value = parts[1]; // Set Packaging Label
                        labelInput.value = parts[1]; // Sync with main Label field
                        
                        // Auto-focus next field after parsing
                        if (pkgLabelInput) pkgLabelInput.focus();
                    }
                }
            });
        }

        // --- ORIGINAL LOGIC REMAINS ---
        const checkBtn = document.getElementById('check-finish-btn');
        const scanBtn = document.getElementById('scanModeBtn');
        const videoElem = document.getElementById('scannerVideo');

        // Initial Focus
        if (spkInput) spkInput.focus();

        // Refresh Manual (Check Finish)
        if (checkBtn) {
            checkBtn.addEventListener('click', () => location.reload());
        }

        // --- AJAX SUBMIT FUNCTION (Reliable) ---
        function submitScanForm(form) {
            clearTimeout(autoSubmitTimer);
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerText = 'Scanning...';
            }

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerText = 'Scan Barcode';
                }

                if (data.success) {
                    showAlert(data.message || "Berhasil scan!", "success");
                    updateUI(data);

                    if (data.next_step === 'packaging') {
                        // TAHAP 1: SPK Sukses, Pindah ke Packaging
                        if (pkgNameInput) pkgNameInput.focus();
                    } else {
                        // TAHAP 2/ORIGINAL: Reset untuk item berikutnya
                        form.reset();
                        if (spkInput) spkInput.focus();
                    }
                } else {
                    showAlert(data.message || "Unknown error", "error");
                    form.reset();
                    if (spkInput) spkInput.focus();
                }
            })
            .catch(error => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerText = 'Scan Barcode';
                }
                console.error('Error:', error);
                showAlert("Terjadi kesalahan koneksi ke server.", "error");
            });
        }

        // --- INTERCEPT SCAN FORM ---
        if (barcodeForm) {
            // Timer untuk AJAX #1 (Label SPK)
            if (labelInput) {
                labelInput.addEventListener('input', () => {
                    // Hanya AJAX #1 jika Mode ON dan packaging masih kosong
                    // Atau selalu AJAX jika Mode OFF
                    const mode = document.getElementById('scan_mode_input').value;
                    const pkgIsVisible = !document.getElementById('packaging_barcode_section').classList.contains('hidden');
                    
                    if (mode === 'OFF' || (mode === 'ON' && pkgNameInput.value.trim() === '')) {
                        clearTimeout(autoSubmitTimer);
                        autoSubmitTimer = setTimeout(() => {
                            if (labelInput.value.trim() !== '') {
                                submitScanForm(barcodeForm);
                            }
                        }, 1000);
                    }
                });
            }

            // Timer untuk AJAX #2 (Warehouse Packaging)
            if (pkgWhseInput) {
                pkgWhseInput.addEventListener('input', () => {
                    if (pkgNameInput.value.trim() !== '') {
                        clearTimeout(autoSubmitTimer);
                        autoSubmitTimer = setTimeout(() => {
                            if (pkgWhseInput.value.trim() !== '') {
                                submitScanForm(barcodeForm);
                            }
                        }, 1000);
                    }
                });
            }

            barcodeForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitScanForm(this);
            });
        }

        // --- CAMERA SCAN MODE (AJAX) ---
        let scanMode = false;
        let codeReader = null;
        let stream = null;
        let lastScan = "";
        let lastScanTime = 0;
        const throttle = 2000;

        if (scanBtn) {
            scanBtn.addEventListener('click', () => {
                if (!scanMode) startScanMode();
                else stopScanMode();
            });
        }

        async function startScanMode() {
            try {
                scanMode = true;
                scanBtn.innerText = "Stop Scan Mode";
                scanBtn.classList.replace("bg-green-600", "bg-red-600");
                document.getElementById('scanView').classList.remove('hidden');

                const constraints = { video: { facingMode: "environment" }, audio: false };
                stream = await navigator.mediaDevices.getUserMedia(constraints);
                videoElem.srcObject = stream;

                codeReader = new ZXingBrowser.BrowserMultiFormatReader();
                codeReader.decodeFromVideoDevice(undefined, videoElem, (result, err) => {
                    if (result) {
                        const now = Date.now();
                        if (result.text === lastScan && (now - lastScanTime < throttle)) return;
                        lastScan = result.text;
                        lastScanTime = now;
                        
                        // Flash effect on video
                        videoElem.style.border = "5px solid #16a34a";
                        setTimeout(() => videoElem.style.border = "none", 500);

                        sendScanAjax(result.text);
                    }
                });
            } catch (err) {
                showAlert("Camera error: " + err.message, "error");
                stopScanMode();
            }
        }

        function stopScanMode() {
            scanMode = false;
            scanBtn.innerText = "Start Scan Mode";
            scanBtn.classList.replace("bg-red-600", "bg-green-600");
            document.getElementById('scanView').classList.add('hidden');
            if (codeReader) codeReader.reset();
            if (stream) stream.getTracks().forEach(t => t.stop());
        }

        function sendScanAjax(code) {
            let fd = new FormData();
            fd.append("so_number", "{{ $docNum }}");
            fd.append("spk_code", code); // Gunakan spk_code sesuai controller
            fd.append("quantity", 1);
            fd.append("warehouse", "AUTO");
            fd.append("label", 0);
            fd.append("_token", "{{ csrf_token() }}");

            fetch("{{ route('so.scanBarcode') }}", {
                method: "POST",
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                showAlert(data.message, data.success ? "success" : "error");
                if (data.success) {
                    updateUI(data);
                    /* Tampilkan Foto di-disable karena lambat
                    if (data.photo) {
                        Fancybox.show([{ src: data.photo, type: "image" }], {
                            on: { reveal: (fb) => setTimeout(() => fb.close(), 1000) }
                        });
                    }
                    */
                }
            })
            .catch(err => showAlert("Network error: " + err.message, "error"));
        }
    });
    </script>
    </script>
</x-app-layout>
