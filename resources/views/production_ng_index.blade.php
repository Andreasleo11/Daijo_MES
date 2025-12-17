<x-dashboard-layout>    
    <div class="p-6">
        <h1 class="text-xl font-bold mb-4">Production NG Types</h1>

        @if(session('success'))
            <div class="bg-green-200 text-green-800 px-4 py-2 mb-4 rounded">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('ngtypes.store') }}" method="POST" class="mb-6">
            @csrf
            <div class="flex gap-3">
                <input 
                    type="text" 
                    name="ng_type" 
                    placeholder="Nama NG Type baru..."
                    class="border rounded px-3 py-2 flex-grow"
                    required
                >
                <button 
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded"
                >
                    Tambah
                </button>
            </div>

            @error('ng_type')
                <p class="text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </form>

        <table class="w-full border rounded">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-3 py-2">No</th>
                    <th class="border px-3 py-2">NG Type</th>
                    <th class="border px-3 py-2">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ngTypes as $index => $type)
                    <tr>
                        <td class="border px-3 py-2">{{ $index + 1 }}</td>
                        <td class="border px-3 py-2">{{ $type->ng_type }}</td>
                        <td class="border px-3 py-2 text-center">
                            <form action="{{ route('ngtypes.delete', $type->id) }}" method="POST" onsubmit="return confirm('Yakin hapus?')">
                                @csrf
                                @method('DELETE')
                                <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center py-4">Belum ada NG Type</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-dashboard-layout>