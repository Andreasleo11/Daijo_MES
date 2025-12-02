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
                                onclick="document.getElementById('modalImage').src=this.src; document.getElementById('imgModal').classList.remove('hidden');">
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

    {{-- Modal Foto Besar --}}
    <div id="imgModal" 
        class="hidden fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 p-4"
        onclick="if(event.target === this) document.getElementById('imgModal').classList.add('hidden')">
        <div class="relative bg-white p-6 rounded-lg shadow-lg">
            {{-- Close Button --}}
            <button onclick="document.getElementById('imgModal').classList.add('hidden')"
                class="absolute -top-4 -right-4 bg-red-600 hover:bg-red-700 text-white rounded-full w-10 h-10 flex items-center justify-center text-2xl font-bold shadow-lg border-2 border-white transition">
                ×
            </button>
            
            {{-- Gambar ukuran sedang --}}
            <img id="modalImage" 
                src="" 
                class="max-w-sm max-h-96 object-contain rounded">
        </div>
    </div>
</x-dashboard-layout>
