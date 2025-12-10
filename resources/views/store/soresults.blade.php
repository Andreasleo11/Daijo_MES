<x-app-layout>
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

                {{-- Success Alert --}}
                @if (session('success'))
                    <div
                        class="bg-green-100 text-green-800 border border-green-300 rounded-md p-3 sm:p-4 mb-4 relative flex items-start sm:items-center justify-between alert-container text-sm sm:text-base"
                    >
                        <span>{{ session('success') }}</span>
                        <button
                            type="button"
                            class="text-green-800 hover:text-green-900 ml-2 text-xl sm:text-2xl"
                            onclick="this.parentElement.style.display='none';"
                        >
                            &times;
                        </button>
                    </div>
                @endif

                {{-- Error Alert --}}
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
                                    <th class="border border-gray-300 px-4 py-2 text-left">ID</th>
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
                                @foreach ($data as $item)
                                    @php
                                        $scannedTotalQuantity = $item->scannedData->where('item_code', $item->item_code)->sum('quantity');
                                        $ctn = ceil($item->quantity / $item->packaging_quantity);
                                        $rowClass = $item->scannedCount > $ctn ? 'bg-red-100' : '';
                                    @endphp

                                    <tr class="hover:bg-green-50 {{ $rowClass }}">
                                        <td class="border border-gray-300 px-4 py-2">{{ $item->id }}</td>
                                        <td class="border border-gray-300 px-4 py-2">{{ $item->item_code }}</td>
                                        <td class="border border-gray-300 px-4 py-2">{{ $item->item_name }}</td>
                                        <td class="border border-gray-300 px-4 py-2">{{ $item->quantity }}</td>
                                        <td class="border border-gray-300 px-4 py-2">{{ $item->packaging_quantity }}</td>
                                        <td class="border border-gray-300 px-4 py-2">{{ number_format($ctn) }}</td>
                                        <td class="border border-gray-300 px-4 py-2"></td>
                                        <td class="border border-gray-300 px-4 py-2">
                                            {{ $item->scannedCount }} / {{ number_format($ctn) }}
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2">{{ $scannedTotalQuantity }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>


                    {{-- ==================== MOBILE CARD VIEW ==================== --}}
                    <div class="space-y-4 sm:hidden">
                        @foreach ($data as $item)
                            @php
                                $scannedTotalQuantity = $item->scannedData->where('item_code', $item->item_code)->sum('quantity');
                                $ctn = ceil($item->quantity / $item->packaging_quantity);
                                $isWarning = $item->scannedCount > $ctn;
                            @endphp

                            <div class="p-4 rounded-lg shadow border 
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
                                        <p class="text-xs text-gray-500">Scanned Box</p>
                                        <p class="font-semibold">
                                            {{ $item->scannedCount }} / {{ $ctn }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-xs text-gray-500">Scanned Qty</p>
                                        <p class="font-semibold">{{ $scannedTotalQuantity }}</p>
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Tombol Update All / Info --}}
                <div class="mt-6">
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

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="item_code" class="block text-sm font-medium text-gray-700">
                                    Item Code:
                                </label>
                                <input
                                    type="text"
                                    id="item_code"
                                    name="item_code"
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
                                </tr>
                            </thead>
                            <tbody>
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
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @empty
                    <p class="text-red-500 text-base sm:text-lg mt-2">
                        No scanned data yet for this SO Number.
                    </p>
                @endforelse
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
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('barcode-form');
    const labelInput = document.getElementById('label');

    if (form && labelInput) {
        function submitForm() {
            form.submit();
        }

        labelInput.addEventListener('input', function () {
            submitForm();
        });
    }

    const itemCodeInput = document.getElementById('item_code');
    if (itemCodeInput) {
        itemCodeInput.focus();
    }
});

document.addEventListener('DOMContentLoaded', function () {

    const scanBtn = document.getElementById('scanModeBtn');
    const scanView = document.getElementById('scanView');
    const videoElem = document.getElementById('scannerVideo');
    const alertBox = document.getElementById('scanAlert');

    let scanMode = false;
    let codeReader = null;
    let lastScan = "";
    let lastScanTime = 0;
    const throttle = 1500;
    let stream = null;

    // Check if getUserMedia is supported
    function isGetUserMediaSupported() {
        return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
    }

    // Polyfill for older browsers
    if (!navigator.mediaDevices && navigator.getUserMedia) {
        navigator.mediaDevices = {};
        navigator.mediaDevices.getUserMedia = function(constraints) {
            const getUserMedia = navigator.getUserMedia || 
                               navigator.webkitGetUserMedia || 
                               navigator.mozGetUserMedia;
            
            if (!getUserMedia) {
                return Promise.reject(new Error('getUserMedia is not implemented'));
            }
            
            return new Promise((resolve, reject) => {
                getUserMedia.call(navigator, constraints, resolve, reject);
            });
        };
    }

    function showAlert(msg, type = "success") {
        alertBox.innerText = msg;
        alertBox.classList.remove("hidden");
        alertBox.style.backgroundColor = type === "success" ? "#16a34a" : "#dc2626";

        setTimeout(() => alertBox.classList.add("hidden"), 3000);
    }

    async function startScanMode() {
        try {
            // Check support first
            if (!isGetUserMediaSupported()) {
                showAlert("Browser tidak support kamera. Gunakan Chrome/Safari terbaru dengan HTTPS", "error");
                return;
            }

            // Check if running on HTTPS (required for camera)
            if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                showAlert("Kamera memerlukan HTTPS. Hubungi admin untuk setup SSL", "error");
                return;
            }

            scanMode = true;
            scanBtn.innerText = "Stop Scan Mode";
            scanBtn.classList.remove("bg-green-600");
            scanBtn.classList.add("bg-red-600");
            scanView.classList.remove("hidden");

            console.log("Requesting camera access...");

            // Request camera permission
            const constraints = {
                video: { 
                    facingMode: { ideal: "environment" },
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                },
                audio: false
            };

            stream = await navigator.mediaDevices.getUserMedia(constraints);
            
            console.log("Camera access granted");

            videoElem.srcObject = stream;
            videoElem.setAttribute("playsinline", "true");
            videoElem.setAttribute("autoplay", "true");
            videoElem.setAttribute("muted", "true");
            
            // Wait for video to be ready
            videoElem.onloadedmetadata = () => {
                videoElem.play().then(() => {
                    console.log("Video playing");
                    startDecoding();
                }).catch(err => {
                    console.error("Play error:", err);
                    showAlert("Gagal memulai video: " + err.message, "error");
                });
            };

        } catch (err) {
            console.error("Camera error:", err);
            let errorMsg = "Gagal mengakses kamera: ";
            
            if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                errorMsg += "Permission ditolak. Izinkan akses kamera di browser settings.";
            } else if (err.name === 'NotFoundError') {
                errorMsg += "Kamera tidak ditemukan.";
            } else if (err.name === 'NotReadableError') {
                errorMsg += "Kamera sedang digunakan aplikasi lain.";
            } else {
                errorMsg += err.message;
            }
            
            showAlert(errorMsg, "error");
            stopScanMode();
        }
    }

    function startDecoding() {
        try {
            // Initialize ZXing reader
            codeReader = new ZXingBrowser.BrowserMultiFormatReader();
            
            console.log("Starting barcode detection...");
            
            // Start decoding
            codeReader.decodeFromVideoDevice(
                undefined,
                videoElem,
                (result, err) => {
                    if (result) {
                        const now = Date.now();
                        if (result.text === lastScan && (now - lastScanTime < throttle)) {
                            return;
                        }

                        lastScan = result.text;
                        lastScanTime = now;

                        console.log("Barcode detected:", result.text);
                        
                        // Visual feedback
                        videoElem.style.border = "5px solid #16a34a";
                        setTimeout(() => {
                            videoElem.style.border = "none";
                        }, 500);

                        sendScan(result.text);
                    }
                    if (err && err.name !== 'NotFoundException') {
                        console.error("Decode error:", err);
                    }
                }
            );
        } catch (err) {
            console.error("ZXing error:", err);
            showAlert("Error starting scanner: " + err.message, "error");
        }
    }

    function stopScanMode() {
        scanMode = false;
        scanBtn.innerText = "Start Scan Mode";
        scanBtn.classList.remove("bg-red-600");
        scanBtn.classList.add("bg-green-600");
        scanView.classList.add("hidden");

        // Stop ZXing
        if (codeReader) {
            try {
                codeReader.reset();
            } catch (e) {
                console.error("Error resetting reader:", e);
            }
            codeReader = null;
        }

        // Stop all video tracks
        if (stream) {
            stream.getTracks().forEach(track => {
                track.stop();
                console.log("Track stopped:", track.kind);
            });
            stream = null;
        }

        // Clear video source
        if (videoElem.srcObject) {
            videoElem.srcObject = null;
        }

        console.log("Camera stopped");
    }

    function sendScan(code) {
        let formData = new FormData();
        formData.append("so_number", "{{ $docNum }}");
        formData.append("item_code", code);
        formData.append("quantity", 1);
        formData.append("warehouse", "AUTO");
        formData.append("label", 0);
        formData.append("_token", "{{ csrf_token() }}");

        fetch("{{ route('so.scanBarcode') }}", {
            method: "POST",
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            showAlert(data.message, data.success ? "success" : "error");
            if (data.success) {
                // Reload page after delay
                setTimeout(() => location.reload(), 1500);
            }
        })
        .catch(err => {
            console.error("Fetch error:", err);
            showAlert("Network error: " + err.message, "error");
        });
    }

    // Button click handler
    if (scanBtn) {
        scanBtn.addEventListener("click", () => {
            if (!scanMode) {
                startScanMode();
            } else {
                stopScanMode();
            }
        });
    }

    // Show warning if not HTTPS on page load (except localhost)
    if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
        console.warn("⚠️ Camera requires HTTPS to work properly");
    }

});
</script>
</x-app-layout>
