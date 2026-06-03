<div class="container mx-auto p-6 bg-gray-100">


    <div class="flex items-center mb-4 space-x-2">
    <input wire:model.live.debounce.500ms="search" type="text" placeholder="Search by item code or file name..."
            class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />

    <!-- <button wire:click="toggleShowOnlyNoFiles"
        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">
        {{ $this->showOnlyNoFiles ? 'Tampilkan Semua' : 'Tampilkan yang Belum Upload File' }}
    </button> -->

    <div class="flex space-x-2 mb-4">
        <button wire:click="toggleShowOnlyNoFiles"
            class="px-3 py-1 {{ $showOnlyNoFiles ? 'bg-blue-600 text-white' : 'bg-gray-300' }}">
            Tampilkan Item Hari Ini Tanpa File
        </button>

        <button wire:click="toggleShowAllNoFiles"
            class="px-3 py-1 {{ $showAllNoFiles ? 'bg-green-600 text-white' : 'bg-gray-300' }}">
            Tampilkan Semua Item Tanpa File
        </button>
    </div>
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
                     <tr class="border-b" wire:key="item-{{ $item->item_code }}">
                        <td class="py-4 px-6">{{ $item->item_code }}</td>
                        <td class="py-4 px-6">
                            @if ($item->files->isNotEmpty())
                                <table class="min-w-full bg-gray-100 rounded-lg">
                                    <thead>
                                        <tr class="w-full bg-gray-300 text-gray-800">
                                            <th class="py-3 px-4 text-left">File Name</th>
                                            <th class="py-3 px-4 text-left">Mime Type</th>
                                            <th class="py-3 px-4 text-left">Size</th>
                                            <th class="py-3 px-4 text-left">Uploaded At</th>
                                            <th class="py-3 px-4 text-left">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($item->files as $file)
                                            <tr class="border-b" wire:key="file-{{ $file->id }}">
                                                <td class="py-3 px-4">{{ $file->name }}</td>
                                                <td class="py-3 px-4">{{ $file->mime_type }}</td>
                                                <td class="py-3 px-4">{{ $file->size }}</td>
                                                <td class="py-3 px-4">{{ $file->created_at ? $file->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') : '-' }}</td>
                                                <td class="py-3 px-3 flex items-center space-x-2">
                                                    <button type="button" 
                                                        onclick="openPreviewModal('{{ asset('storage/files/' . $file->name) }}', '{{ $file->mime_type }}', '{{ $file->name }}')"
                                                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg cursor-pointer">
                                                        Preview
                                                    </button>
                                                    <button type="button"
                                                        wire:click="deleteFile({{ $file->id }})"
                                                        wire:confirm="Apakah Anda yakin ingin menghapus berkas ini?"
                                                        class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg cursor-pointer">
                                                        Delete
                                                    </button>
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

    <!-- Preview Modal -->
    <div id="previewModal" class="fixed inset-0 items-center justify-center bg-black bg-opacity-75 hidden z-50 transition-opacity duration-300">
        <div class="relative w-full max-w-4xl mx-auto my-6 h-[85vh] flex flex-col bg-white rounded-lg shadow-2xl overflow-hidden border border-gray-200">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 bg-gray-900 text-white">
                <h3 id="previewTitle" class="text-lg font-bold truncate">File Preview</h3>
                <button onclick="closePreviewModal()" class="text-gray-400 hover:text-white transition-colors duration-150">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="flex-1 p-6 bg-gray-50 flex items-center justify-center overflow-auto" id="previewContent">
                <!-- Content will be dynamically inserted here -->
            </div>
            
            <!-- Modal Footer -->
            <div class="flex justify-end px-6 py-4 bg-gray-100 border-t border-gray-200">
                <a id="previewDownloadLink" href="#" download class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg mr-2 transition-colors duration-150">
                    Download File
                </a>
                <button onclick="closePreviewModal()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded-lg transition-colors duration-150">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        function openPreviewModal(url, mimeType, filename) {
            const modal = document.getElementById('previewModal');
            const title = document.getElementById('previewTitle');
            const content = document.getElementById('previewContent');
            const downloadLink = document.getElementById('previewDownloadLink');
            
            title.textContent = filename;
            downloadLink.href = url;
            downloadLink.setAttribute('download', filename);
            
            content.innerHTML = ''; // Clear previous content
            
            if (mimeType.startsWith('image/')) {
                const img = document.createElement('img');
                img.src = url;
                img.alt = filename;
                img.className = 'max-w-full max-h-full object-contain rounded shadow-lg';
                content.appendChild(img);
            } else if (mimeType === 'application/pdf') {
                const iframe = document.createElement('iframe');
                iframe.src = url;
                iframe.className = 'w-full h-full border-0 rounded shadow-inner';
                content.appendChild(iframe);
            } else {
                const div = document.createElement('div');
                div.className = 'text-center p-8';
                
                let iconSvg = '';
                if (mimeType.includes('excel') || mimeType.includes('spreadsheet') || filename.endsWith('.xlsx') || filename.endsWith('.xls') || filename.endsWith('.csv')) {
                    iconSvg = '<svg class="w-24 h-24 text-green-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>' +
                    '</svg>';
                } else {
                    iconSvg = '<svg class="w-24 h-24 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>' +
                    '</svg>';
                }
                
                div.innerHTML = 
                    iconSvg +
                    '<p class="text-lg font-medium text-gray-700 mb-2">Preview not available for this file type</p>' +
                    '<p class="text-sm text-gray-500 mb-6">Mime Type: ' + mimeType + '</p>' +
                    '<a href="' + url + '" download="' + filename + '" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg transition-colors duration-150">' +
                        '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>' +
                        '</svg>' +
                        'Download to View' +
                    '</a>';
                content.appendChild(div);
            }
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        
        function closePreviewModal() {
            const modal = document.getElementById('previewModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.getElementById('previewContent').innerHTML = '';
        }
    </script>
</div>
