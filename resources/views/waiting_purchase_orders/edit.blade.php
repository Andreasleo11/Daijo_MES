<x-app-layout>
    <div class="container mx-auto pt-10 px-10">
        <!-- Breadcrumb -->
        <nav class="flex mb-4 text-gray-700 text-sm font-medium" aria-label="Breadcrumb">
            <ol class="list-reset flex">
                <li>
                    <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline">Dashboard</a>
                </li>
                <li>
                    <span class="mx-2 text-gray-500">/</span>
                </li>
                <li>
                    <a href="{{ route('waiting_purchase_orders.index') }}" class="text-blue-600 hover:underline">
                        Waiting Purchase Orders
                    </a>
                </li>
                <li>
                    <span class="mx-2 text-gray-500">/</span>
                </li>
                <li class="text-gray-800">Edit</li>
            </ol>
        </nav>

        <h1 class="text-3xl font-bold mb-5">Edit Waiting Purchase Order</h1>

        <form action="{{ route('waiting_purchase_orders.update', $waitingPurchaseOrder->id) }}" method="POST"
            class="bg-white p-6 rounded shadow-md" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('waiting_purchase_orders.form', ['waitingPurchaseOrder' => $waitingPurchaseOrder])

            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Submit
            </button>
        </form>

        <div class="my-6 bg-white p-6 rounded shadow-md">
            <label class="block font-bold mb-4 text-lg">Attached Files</label>

            <div id="attached_list" class="grid gap-4">
                <!-- Show existing files -->
                @if (isset($waitingPurchaseOrder) && $waitingPurchaseOrder->files->count() > 0)
                    @foreach ($waitingPurchaseOrder->files as $file)
                        <div class="file-item flex items-center justify-between gap-4 bg-gray-50 p-4 rounded shadow"
                            data-file-id="{{ $file->id }}">
                            <div class="flex items-center gap-2">
                                <span>📄</span>
                                <a href="{{ asset('storage/files/' . $file->name) }}" target="_blank"
                                    class="text-blue-500 underline">{{ $file->name }}</a>
                                <span class="text-gray-500 text-sm">
                                    ({{ number_format($file->size / 1024 / 1024, 2) }} MB)
                                </span>
                            </div>
                            <button type="button"
                                class="delete-file-btn bg-red-500 text-white font-bold py-1 px-3 rounded hover:bg-red-700"
                                data-file-id="{{ $file->id }}">
                                Delete
                            </button>
                        </div>
                    @endforeach
                @else
                    <p class="text-gray-500">No files attached.</p>
                @endif
            </div>

            <!-- Button to trigger modal -->
            <button id="uploadModalButton"
                class="mt-4 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                Upload New Files
            </button>
        </div>
    </div>

    <!-- Upload Modal -->
    <div id="uploadModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex justify-center items-center">
        <div class="bg-white w-full max-w-lg p-6 rounded shadow-md">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold">Upload New Files</h2>
                <button id="closeModalButton" class="text-gray-500 hover:text-gray-700">✖</button>
            </div>
            <form action="{{ route('file.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="item_code" value="{{ $waitingPurchaseOrder->doc_num }}">
                <label for="new_files" class="block font-bold mb-2">Select Files</label>
                <input type="file" name="files[]" id="new_files" multiple
                    class="w-full border border-gray-300 p-2 rounded mb-4">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Upload
                </button>
            </form>
        </div>
    </div>

    <script>
        // Modal open/close logic
        document.addEventListener('DOMContentLoaded', function() {
            const uploadModal = document.getElementById('uploadModal');
            const uploadModalButton = document.getElementById('uploadModalButton');
            const closeModalButton = document.getElementById('closeModalButton');

            uploadModalButton.addEventListener('click', function() {
                uploadModal.classList.remove('hidden');
            });

            closeModalButton.addEventListener('click', function() {
                uploadModal.classList.add('hidden');
            });

            // Close modal when clicking outside the modal content
            uploadModal.addEventListener('click', function(event) {
                if (event.target === uploadModal) {
                    uploadModal.classList.add('hidden');
                }
            });

            const attachedList = document.getElementById('attached_list');

            attachedList.addEventListener('click', function(event) {
                if (event.target.classList.contains('delete-file-btn')) {
                    const fileId = event.target.getAttribute('data-file-id');

                    if (confirm('Are you sure you want to delete this file?')) {
                        fetch(`/file/${fileId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                        .getAttribute('content'),
                                    'Accept': 'application/json',
                                },
                            })
                            .then(response => response.json())
                            .then(data => {
                                location.reload();
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('An error occurred while deleting the file.');
                            });
                    }
                }
            });
        });
    </script>
</x-app-layout>
