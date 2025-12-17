<x-dashboard-layout>
    <div class="container mx-auto p-6">

        @if(session('success'))
            <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <h3 class="text-xl font-bold mb-4">Master Item Photo Management</h3>

        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 rounded-lg">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 border">Item Code</th>
                        <th class="px-4 py-2 border">Description</th>
                        <th class="px-4 py-2 border">Std Pack</th>
                        <th class="px-4 py-2 border">Status</th>
                        <th class="px-4 py-2 border">Photo</th>
                        <th class="px-4 py-2 border">Upload</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($items as $item)
                        <tr class="border-b">
                            <td class="px-4 py-2">{{ $item->item_code }}</td>
                            <td class="px-4 py-2">{{ $item->item_name }}</td>
                            <td class="px-4 py-2">{{ $item->standart_packaging_list }}</td>

                            {{-- Status O / X --}}
                            <td class="px-4 py-2 text-center">
                                @if($item->photo)
                                    <span class="text-green-600 font-bold text-lg">O</span>
                                @else
                                    <span class="text-red-600 font-bold text-lg">X</span>
                                @endif
                            </td>

                            {{-- Thumbnail --}}
                            <td class="px-4 py-2 text-center">
                                @if($item->photo && $item->photo->photo_path)
                                    <img src="{{ asset('storage/'.$item->photo->photo_path) }}"
                                        class="w-12 h-12 object-cover cursor-pointer rounded border"
                                        onclick="showImage('{{ asset('storage/'.$item->photo->photo_path) }}')">
                                @else
                                    <span class="text-gray-500">No Photo</span>
                                @endif
                            </td>

                            {{-- Upload Form --}}
                            <td class="px-4 py-2">
                                <form action="{{ route('master.items.upload', $item->item_code) }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-2">
                                    @csrf
                                    <input type="file" name="photo" required class="block w-full text-sm text-gray-700 border rounded px-2 py-1">
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                        Upload
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Modal Foto Besar --}}
        <div id="imgModal" class="hidden fixed inset-0 bg-black bg-opacity-70 flex justify-center items-center z-50">
            <img id="modalImage" src="" class="max-w-3/4 max-h-3/4 rounded shadow-lg">
        </div>

    </div>

    <script>
        function showImage(src) {
            const modal = document.getElementById('imgModal');
            const modalImage = document.getElementById('modalImage');
            modalImage.src = src;
            modal.classList.remove('hidden');
        }

        document.getElementById('imgModal').onclick = function () {
            this.classList.add('hidden');
        };
    </script>
</x-dashboard-layout>
