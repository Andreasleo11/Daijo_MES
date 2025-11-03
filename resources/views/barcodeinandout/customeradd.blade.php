<x-app-layout>

<div class="max-w-xl mx-auto mt-10 p-6 bg-white shadow-lg rounded-xl">

    <h2 class="text-2xl font-bold mb-4 text-gray-800">Tambah Customer</h2>

    {{-- Notifikasi sukses --}}
    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Form Tambah --}}
    <form action="{{ route('customer.store') }}" method="POST" class="mb-6">
        @csrf
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700">Nama Customer</label>
            <input type="text" id="name" name="name" required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2 text-sm">
        </div>
        <button type="submit"
            class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700 transition">
            Tambah
        </button>
    </form>

    {{-- Daftar Customer --}}
    <div class="mt-8">
    <h3 class="text-lg font-semibold mb-4 text-gray-700">Daftar Customer</h3>
    @if($customers->count())
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-700 border border-gray-200">
                <thead class="bg-gray-100 text-gray-800">
                    <tr>
                        <th class="px-4 py-2 border">#</th>
                        <th class="px-4 py-2 border">Nama Customer</th>
                        <th class="px-4 py-2 border text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $index => $customer)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border">{{ $index + 1 }}</td>
                            <td class="px-4 py-2 border">{{ $customer->name }}</td>
                            <td class="px-4 py-2 border text-center">
                                <form action="{{ route('customer.destroy', $customer->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus customer ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-sm text-gray-500">Belum ada customer.</p>
    @endif
</div>

</div>

</x-app-layout>