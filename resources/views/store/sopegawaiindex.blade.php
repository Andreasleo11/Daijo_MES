<x-app-layout>
    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-4">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-8">

                {{-- Header --}}
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800">
                    Scan SO Number
                </h1>
                <p class="text-gray-600 mb-6">Gunakan kamera untuk scan QR SO.</p>

                {{-- Warning HTTPS --}}
                <div id="httpsWarning" class="hidden mb-4 p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700">
                    <p class="font-bold">⚠️ Kamera Tidak Tersedia</p>
                    <p class="text-sm mt-1">Browser memerlukan HTTPS untuk akses kamera.</p>
                    <p class="text-sm mt-2">
                        <strong>Solusi:</strong> 
                        <a href="#manualInput" class="underline">Gunakan input manual di bawah</a> 
                        atau hubungi admin untuk setup HTTPS.
                    </p>
                </div>

                {{-- Mobile Scan Mode --}}
                <div id="scanModeSection" class="sm:hidden text-center mb-4">
                    <button id="scanModeBtn"
                        class="px-6 py-3 bg-green-600 text-white font-semibold rounded-lg shadow hover:bg-green-700 transition">
                        Start Scan Mode
                    </button>
                </div>

                {{-- Camera --}}
                <div id="scanView" class="hidden sm:hidden mt-4">
                    <video id="scannerVideo" autoplay muted playsinline
                        class="w-full rounded-lg shadow border border-gray-300"></video>
                </div>

                {{-- Manual Input (Fallback) --}}
                <div id="manualInput" class="mt-8 p-6 bg-gray-50 rounded-lg border border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">
                        📝 Manual Input
                    </h2>
                    <form id="manualForm" onsubmit="return goToSO()" class="flex gap-2">
                        <input 
                            type="text" 
                            id="manualInputField"
                            placeholder="Masukkan SO Number (contoh: SO-12345)"
                            required
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg"
                        />
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg">Submit</button>
                    </form>
                    <p class="text-xs text-gray-500 mt-2">
                        * Jika kamera tidak berfungsi, gunakan form ini
                    </p>
                </div>

            </div>
        </div>
    </div>

    {{-- Floating Alert --}}
    <div id="scanAlert"
        class="hidden fixed top-4 left-1/2 transform -translate-x-1/2 px-4 py-2 rounded-lg text-white text-sm font-semibold shadow-lg z-50">
    </div>


    {{-- ZXing --}}
    <script src="https://unpkg.com/@zxing/browser@latest"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const scanBtn = document.getElementById('scanModeBtn');
            const scanView = document.getElementById('scanView');
            const videoElem = document.getElementById('scannerVideo');
            const alertBox = document.getElementById('scanAlert');
            const httpsWarning = document.getElementById('httpsWarning');
            const scanModeSection = document.getElementById('scanModeSection');

            let scanMode = false;
            let codeReader = null;
            let lastScan = "";
            let lastScanTime = 0;
            let stream = null;
            const throttle = 2000;

            // CHECK: Apakah kamera support?
            const cameraSupported = checkCameraSupport();

            function checkCameraSupport() {
                // Check 1: Apakah navigator.mediaDevices ada?
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    return false;
                }

                // Check 2: Apakah HTTPS atau localhost?
                const isSecure = location.protocol === 'https:' || 
                                 location.hostname === 'localhost' || 
                                 location.hostname === '127.0.0.1';

                return isSecure;
            }

            // Tampilkan warning jika tidak support
            if (!cameraSupported) {
                httpsWarning.classList.remove('hidden');
                scanModeSection.classList.add('hidden'); // Sembunyikan tombol scan
                console.warn('⚠️ Camera not supported. Reason: HTTP protocol or mediaDevices not available');
            }

            function showAlert(msg, type = "success") {
                alertBox.innerText = msg;
                alertBox.classList.remove("hidden");
                alertBox.style.backgroundColor = type === "success" ? "#16a34a" : "#dc2626";
                alertBox.style.fontSize = "14px";
                alertBox.style.padding = "12px 20px";

                setTimeout(() => alertBox.classList.add("hidden"), 2500);
            }

            async function startScanMode() {
                // Double check sebelum start
                if (!cameraSupported) {
                    showAlert("❌ Kamera tidak tersedia. Gunakan manual input.", "error");
                    return;
                }

                scanMode = true;
                scanBtn.innerText = "Stop Scan Mode";
                scanBtn.classList.remove("bg-green-600");
                scanBtn.classList.add("bg-red-600");
                scanView.classList.remove("hidden");

                try {
                    // Request camera
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: { ideal: "environment" },
                            width: { ideal: 1280 },
                            height: { ideal: 720 }
                        }
                    });

                    videoElem.srcObject = stream;
                    await videoElem.play();
                    
                    showAlert("✅ Kamera aktif! Arahkan ke QR code", "success");
                    startDecoding();

                } catch (err) {
                    console.error("Camera error:", err);
                    
                    let errorMsg = "❌ Gagal akses kamera: ";
                    
                    if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                        errorMsg += "Permission ditolak. Izinkan akses kamera di browser settings.";
                    } else if (err.name === 'NotFoundError') {
                        errorMsg += "Kamera tidak ditemukan.";
                    } else if (err.name === 'NotReadableError') {
                        errorMsg += "Kamera sedang digunakan aplikasi lain.";
                    } else if (err.name === 'SecurityError' || err.name === 'NotSupportedError') {
                        errorMsg += "Browser tidak support atau harus pakai HTTPS.";
                    } else {
                        errorMsg += err.message || "Unknown error";
                    }
                    
                    showAlert(errorMsg, "error");
                    stopScanMode();
                }
            }

            function startDecoding() {
                try {
                    codeReader = new ZXingBrowser.BrowserMultiFormatReader();

                    codeReader.decodeFromVideoDevice(null, videoElem, (result, err) => {
                        if (result) {
                            const now = Date.now();
                            if (result.text === lastScan && now - lastScanTime < throttle) return;

                            lastScan = result.text;
                            lastScanTime = now;

                            // Visual feedback
                            videoElem.style.border = "5px solid #16a34a";
                            setTimeout(() => {
                                videoElem.style.border = "1px solid #d1d5db";
                            }, 500);

                            showAlert("✅ Scanned: " + result.text, "success");

                            // Redirect ke SO
                            setTimeout(() => {
                                window.location.href = "/so/process/" + result.text;
                            }, 800);
                        }

                        // Abaikan error NotFoundException (normal saat scan)
                        if (err && err.name !== 'NotFoundException') {
                            console.error("Decode error:", err);
                        }
                    });
                } catch (err) {
                    console.error("ZXing error:", err);
                    showAlert("❌ Error starting scanner: " + err.message, "error");
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

                // Stop camera stream
                if (stream) {
                    stream.getTracks().forEach(track => {
                        track.stop();
                    });
                    stream = null;
                }

                videoElem.srcObject = null;
            }

            // Event listener
            if (scanBtn) {
                scanBtn.addEventListener("click", () => {
                    if (!scanMode) {
                        startScanMode();
                    } else {
                        stopScanMode();
                    }
                });
            }

            // Console warning
            if (!cameraSupported) {
                console.warn("⚠️ Camera requires HTTPS or localhost to work");
            }

        });

        function goToSO() {
            let value = document.getElementById('manualInputField').value.trim();
            if (value === "") return false;

            window.location.href = "/so/process/" + value;

            return false; // prevent form submission
        }
    </script>

</x-app-layout>