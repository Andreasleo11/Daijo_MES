<x-dashboard-layout>
    <div class="max-w-md mx-auto mt-10 bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-bold mb-4">Delivery Verification</h2>
        {{-- Form Scan / Input --}}
        <form action="{{ route('delivery.verification.check') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Scan / Enter Item Code</label>
                <input type="text" name="item_code" value="{{ $item_code ?? '' }}"
                       class="w-full border rounded px-3 py-2" placeholder="Item Code" autofocus>
                @error('item_code')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Check
            </button>
        </form>
        @if(isset($item))
            <div class="mt-6 border-t pt-4">
                @if($item)
                    <h3 class="font-bold mb-2">{{ $item->item_code }} - {{ $item->item_description }}</h3>
                    <p>Standard Packaging: {{ $item->standard_packaging }}</p>
                   @if($item->photo_path)
                        <div class="mt-2 inline-block border border-gray-300 rounded overflow-hidden relative">
                            <img src="{{ asset('storage/' . $item->photo_path) }}" 
                                class="w-30 h-30 object-cover cursor-pointer"
                                onclick="openModal('{{ asset('storage/' . $item->photo_path) }}')">
                        </div>
                        <button 
                            onclick="window.location.href='{{ route('delivery.verification') }}'" 
                            class="mt-3 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded w-full">
                            OK
                        </button>
                    @else
                        <p class="text-gray-500 mt-2">No Photo Available</p>
                    @endif
                @else
                    <p class="text-red-500 mt-2">Item not found!</p>
                @endif
            </div>
        @endif
    </div>
    
    {{-- Modal Foto dengan Zoom --}}
    <div id="imgModal" class="hidden fixed inset-0 bg-black bg-opacity-95 z-50">
        
        {{-- Close Button --}}
        <button onclick="closeModal()"
            class="fixed top-8 right-8 bg-red-600 hover:bg-red-700 text-white rounded-full w-20 h-20 flex items-center justify-center text-5xl font-bold shadow-2xl border-4 border-white transition z-20">
            ×
        </button>

        {{-- Reset Zoom Button --}}
        <button id="resetBtn" onclick="resetZoom()"
            class="hidden fixed top-8 left-8 bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-6 py-3 text-xl font-semibold shadow-2xl border-2 border-white transition z-20">
            Reset Zoom
        </button>

        {{-- Zoom Level Indicator & Image Size --}}
        <!-- <div id="zoomIndicator" 
             class="absolute bottom-8 right-8 bg-white bg-opacity-90 text-gray-800 px-6 py-3 rounded-lg font-bold text-xl shadow-2xl z-20">
            <div id="imageSize" class="text-sm font-normal mb-1"></div>
            <div id="zoomText">Klik untuk Zoom In</div>
        </div> -->
        
        {{-- Container Gambar --}}
        <div id="zoomContainer" class="zoom-container w-full h-full flex items-center justify-center p-8"
             onclick="handleZoom(event)">
            <img id="modalImage" 
                src="" 
                class="rounded-lg shadow-2xl"
                style="transform-origin: center center; max-width: 90vw; max-height: 90vh; object-fit: contain;">
        </div>
    </div>

    <style>
        .zoom-container {
            cursor: zoom-in;
            overflow: auto;
        }
        .zoom-container.zoomed {
            cursor: zoom-out;
        }
        .zoom-container img {
            transition: transform 0.3s ease;
        }
    </style>

    <script>
        let zoomLevel = 1;
        let transformOrigin = 'center center';
        const zoomStep = 1.5;
        const maxZoom = 2;
        let imageWidth = 0;
        let imageHeight = 0;

        function openModal(imageSrc) {
            const img = new Image();
            img.onload = function() {
                imageWidth = img.width;
                imageHeight = img.height;
                document.getElementById('modalImage').src = imageSrc;
                document.getElementById('imgModal').classList.remove('hidden');
                displayImageSize();
                resetZoom();
            };
            img.src = imageSrc;
        }

        function displayImageSize() {
            const sizeElement = document.getElementById('imageSize');
            if (imageWidth && imageHeight) {
                sizeElement.textContent = `Ukuran: ${imageWidth}px × ${imageHeight}px`;
            }
        }

        function closeModal() {
            document.getElementById('imgModal').classList.add('hidden');
            resetZoom();
        }

        function handleZoom(event) {
            // Jangan zoom jika klik di luar gambar
            if (event.target.id !== 'modalImage' && event.target.id !== 'zoomContainer') {
                return;
            }

            const img = document.getElementById('modalImage');
            const container = document.getElementById('zoomContainer');
            const rect = img.getBoundingClientRect();

            if (zoomLevel < maxZoom) {
                // Zoom in ke posisi klik
                const x = ((event.clientX - rect.left) / rect.width) * 100;
                const y = ((event.clientY - rect.top) / rect.height) * 100;
                
                transformOrigin = `${x}% ${y}%`;
                zoomLevel *= zoomStep;
                
                img.style.transformOrigin = transformOrigin;
                img.style.transform = `scale(${zoomLevel})`;
                
                container.classList.add('zoomed');
                document.getElementById('resetBtn').classList.remove('hidden');
                updateZoomIndicator();
            } else {
                // Zoom out jika sudah max zoom
                resetZoom();
            }
        }

        function resetZoom() {
            zoomLevel = 1;
            transformOrigin = 'center center';
            
            const img = document.getElementById('modalImage');
            const container = document.getElementById('zoomContainer');
            
            img.style.transformOrigin = transformOrigin;
            img.style.transform = `scale(${zoomLevel})`;
            
            container.classList.remove('zoomed');
            document.getElementById('resetBtn').classList.add('hidden');
            updateZoomIndicator();
        }

        function updateZoomIndicator() {
            const zoomText = document.getElementById('zoomText');
            if (zoomLevel === 1) {
                zoomText.textContent = 'Klik untuk Zoom In';
                document.getElementById('zoomIndicator').classList.remove('bg-green-500', 'text-white');
                document.getElementById('zoomIndicator').classList.add('bg-white', 'bg-opacity-90', 'text-gray-800');
            } else {
                zoomText.textContent = `Zoom: ${Math.round(zoomLevel * 100)}%`;
                document.getElementById('zoomIndicator').classList.remove('bg-white', 'bg-opacity-90', 'text-gray-800');
                document.getElementById('zoomIndicator').classList.add('bg-green-500', 'text-white');
            }
        }

        // Close modal dengan ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</x-dashboard-layout>