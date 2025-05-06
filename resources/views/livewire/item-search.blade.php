<div class="container mx-auto p-6 bg-gray-100">


    <div class="flex items-center mb-4 space-x-2">
    <input wire:model.live="search" type="text" placeholder="Search by item code or file name..."
            class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />

    <button wire:click="toggleShowOnlyNoFiles"
        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">
        {{ $this->showOnlyNoFiles ? 'Tampilkan Semua' : 'Tampilkan yang Belum Upload File' }}
    </button>
</div>
    <!-- Search Results Table -->
    <div class="overflow-x-auto shadow-lg rounded-lg">
        <table class="min-w-full bg-white rounded-lg">
            <thead>
                <tr class="w-full bg-gray-800 text-white">
                    <th class="py-3 px-6 text-left">Item Code</th>
                    <th class="py-3 px-6 text-left">Files</th>
                    <th class="py-3 px-6 text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr class="border-b">
                        <td class="py-4 px-6">{{ $item->item_code }}</td>
                        <td class="py-4 px-6">
                            @if ($item->files->isNotEmpty())
                                <table class="min-w-full bg-gray-100 rounded-lg">
                                    <thead>
                                        <tr class="w-full bg-gray-300 text-gray-800">
                                            <th class="py-3 px-4 text-left">File Name</th>
                                            <th class="py-3 px-4 text-left">Mime Type</th>
                                            <th class="py-3 px-4 text-left">Size</th>
                                            <th class="py-3 px-4 text-left">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($item->files as $file)
                                            <tr class="border-b">
                                                <td class="py-3 px-4">{{ $file->name }}</td>
                                                <td class="py-3 px-4">{{ $file->mime_type }}</td>
                                                <td class="py-3 px-4">{{ $file->size }}</td>
                                                <td class="py-3 px-3">
                                                    <form action="{{ route('file.delete', $file->id) }}" method="post">
                                                        @csrf @method('delete')
                                                        <button type="submit"
                                                            class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg cursor-pointer">
                                                            Delete
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                No files uploaded.
                            @endif
                        </td>
                        <td>
                            <button
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg cursor-pointer"
                                onclick="openModal('{{ $item->item_code }}')">Upload Files</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center py-4 text-gray-500">No items found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination links -->
        <div class="mt-4 mb-2 mx-3">
            {{ $items->links() }}
        </div>
    </div>
</div>
