<x-app-layout>
    <livewire:alert />

    <div class="container mx-auto p-6 bg-gray-100">

        <livewire:item-search />
        <button onclick="Livewire.emit('showAlert', 'Test success message')">Show Alert</button>

        <!-- Modal -->
        <div id="uploadModal" class="fixed inset-0 items-center justify-center bg-black bg-opacity-50 hidden">
            <div class="w-full max-w-lg mx-auto mt-12">
                <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                    <div class="px-6 py-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Upload Files</h2>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2" for="files">Select Files</label>
                            <div class="flex items-center justify-center w-full">
                                <label
                                    class="flex flex-col items-center w-full h-32 px-4 transition bg-white border-2 border-gray-300 border-dashed rounded-md cursor-pointer hover:border-gray-400 hover:bg-gray-50">
                                    <span class="flex items-center space-x-2">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16V8m10 8V8m-5 8V8m-5 8h10"></path>
                                        </svg>
                                        <span class="font-medium text-gray-600">Drop files here or <span
                                                class="text-blue-600 underline">browse</span></span>
                                    </span>
                                    <form action="{{ route('file.upload') }}" method="post"
                                        enctype="multipart/form-data" id="formUploadFile">
                                        @csrf
                                        <input type="hidden" name="item_code" id="item_code">
                                        <input id="files" type="file" name="files[]" class="hidden" multiple>
                                    </form>
                                </label>
                            </div>
                            <div id="fileList" class="mt-4 text-sm text-gray-600"></div>
                        </div>
                        <div class="flex justify-center">
                            <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full"
                                onclick="document.getElementById('formUploadFile').submit()">
                                Upload
                            </button>
                            <button
                                class="ml-4 bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-full"
                                onclick="closeModal()">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function openModal(item_code) {
                console.log(item_code);
                document.getElementById('item_code').value = item_code;
                document.getElementById('uploadModal').classList.remove('hidden');
            }

            function closeModal() {
                document.getElementById('uploadModal').classList.add('hidden');
            }

            document.getElementById('files').addEventListener('change', function() {
                const fileList = document.getElementById('fileList');
                fileList.innerHTML = '';
                for (let i = 0; i < this.files.length; i++) {
                    const listItem = document.createElement('div');
                    listItem.textContent = this.files[i].name;
                    fileList.appendChild(listItem);
                }
            });
        </script>
    </div>
</x-app-layout>
