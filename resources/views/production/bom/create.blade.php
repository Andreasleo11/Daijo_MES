<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="text-2xl font-semibold mb-2">{{ __('Create Bill of Materials') }}</h2>
            <!-- Breadcrumb -->
            <nav class="flex text-gray-500 text-sm mb-6" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-500">
                    Dashboard
                </a>
                <span class="mx-2">/</span>
                <a href="{{ route('production.bom.index') }}" class="hover:text-blue-500">
                    Bill of Materials
                </a>
                <span class="mx-2">/</span>
                <span class="text-gray-800 font-semibold">Create</span>
            </nav>
            <form action="{{ route('production.bom.store') }}" method="post">
                @csrf
                <div class="bg-white shadow-md rounded-lg p-6">
                    <!-- Parent Fields -->
                    <div class="mb-6">
                        <h3 class="text-xl font-medium mb-4">Product Information</h3>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="code" class="text-sm font-medium text-gray-700">Product Code</label>
                                <input type="text" name="code" id="code"
                                    class="w-full border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                    required>
                            </div>
                            <div>
                                <label for="description" class="text-sm font-medium text-gray-700">Product
                                    Description</label>
                                <input type="text" name="description" id="description"
                                    class="w-full border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                    required>
                            </div>
                            <div class="mt-4">
                                <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                                <select name="type" id="type"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    required>
                                    <option value="production">Production</option>
                                    <option value="moulding">Moulding</option>
                                </select>
                            </div>
                            <div class="mt-4">
                                <label for="customer" class="block text-sm font-medium text-gray-700">Customer</label>
                                <input type="text" name="customer" id="customer"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white shadow-md rounded-lg p-6 mt-6">
                    <!-- Parent Fields -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xl font-medium">Item Codes Information</h3>
                            <button id="toggle-input-method"
                                class="text-indigo-600 bg-gray-100 px-4 py-2 rounded-lg shadow-sm">Switch to Excel
                                Upload</button>
                        </div>
                        <div class="mt-6">

                            <!-- Manual Input -->
                            <div id="manual-input-section" class="mt-4">
                                <div id="child-rows-container">
                                    <!-- Default Child Row -->
                                </div>
                                <button id="add-child-btn"
                                    class="mt-4 bg-blue-500 text-white px-4 py-2 rounded-md shadow-md">+
                                    Add Another Child</button>
                            </div>

                            <!-- Excel Upload -->
                            <div id="excel-upload-section" class="hidden mt-6">
                                <label for="excel_file" class="text-sm font-medium text-gray-700">Upload Excel
                                    File</label>
                                <input type="file" name="excel_file" id="excel_file" accept=".xlsx, .xls"
                                    class="mt-2 w-full border-gray-300 rounded-md">
                                <p class="text-sm text-gray-500 mt-2">Supported formats: .xls, .xlsx</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Save Button -->
                <div class="mt-6">
                    <button type="submit"
                        class="bg-green-600 text-white px-6 py-3 rounded-lg shadow-md hover:bg-green-700">Save
                        BOM</button>
                </div>
            </form>
        </div>

        <!-- Template for Child Row -->
        <template id="child-row-template">
            <div class="flex space-x-4 mt-4 child-row">
                <div class="w-1/3">
                    <input type="text" name="child_item_code[]" placeholder="Child Item Code"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        required>
                </div>
                <div class="w-1/3">
                    <input type="text" name="child_item_description[]" placeholder="Child Item Description"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        required>
                </div>
                <div class="w-1/3">
                    <input type="text" name="quantity[]" placeholder="Quantity"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        required>
                </div>
                <div class="w-1/3">
                    <input type="text" name="measure[]" placeholder="Measure"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        required>
                </div>
                <button type="button" class="remove-child-btn text-red-500 mt-2">Remove</button>
            </div>
        </template>

        <script>
            // Toggle between manual input and Excel upload
            const toggleInputMethodBtn = document.getElementById('toggle-input-method');
            const manualInputSection = document.getElementById('manual-input-section');
            const excelUploadSection = document.getElementById('excel-upload-section');
            const childRowsContainer = document.getElementById('child-rows-container');
            const childRowTemplate = document.getElementById('child-row-template');

            toggleInputMethodBtn.addEventListener('click', () => {
                manualInputSection.classList.toggle('hidden');
                excelUploadSection.classList.toggle('hidden');

                if (manualInputSection.classList.contains('hidden')) {
                    toggleInputMethodBtn.textContent = 'Switch to Manual Input';
                } else {
                    toggleInputMethodBtn.textContent = 'Switch to Excel Upload';
                }
            });

            // Add new child row
            const addChildBtn = document.getElementById('add-child-btn');
            addChildBtn.addEventListener('click', function() {
                const newChildRow = childRowTemplate.content.cloneNode(true);
                childRowsContainer.appendChild(newChildRow);
            });

            // Remove child row
            childRowsContainer.addEventListener('click', function(event) {
                if (event.target.classList.contains('remove-child-btn')) {
                    event.target.closest('.child-row').remove();
                }
            });

            // Add the first child row on page load
            document.addEventListener('DOMContentLoaded', () => {
                const defaultChildRow = childRowTemplate.content.cloneNode(true);
                childRowsContainer.appendChild(defaultChildRow);
            });
        </script>
</x-app-layout>
