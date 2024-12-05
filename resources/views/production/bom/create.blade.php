<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2>{{ __("Create Bill of Materials") }}</h2>
                    <form action="{{ route('production.bom.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Parent Fields -->
                        <div>
                            <label for="item_code" class="block text-sm font-medium text-gray-700">Item Code</label>
                            <input type="text" name="item_code" id="item_code" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        </div>

                        <div class="mt-4">
                            <label for="item_description" class="block text-sm font-medium text-gray-700">Item Description</label>
                            <input type="text" name="item_description" id="item_description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        </div>

                        <div class="mt-4">
                            <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                            <select name="type" id="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                <option value="production">Production</option>
                                <option value="moulding">Moulding</option>
                            </select>
                        </div>

                        <div class="mt-4">
                            <label for="customer" class="block text-sm font-medium text-gray-700">Customer </label>
                            <input type="text" name="customer" id="customer" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        </div>

                        <!-- Toggle Section for Child Information -->
                        <div class="mt-6 flex justify-between items-center">
                            <h2>Child Information</h2>
                            <button type="button" id="toggle-input-method" class="bg-blue-500 text-white px-4 py-2 rounded-md">
                                Switch to Excel Upload
                            </button>
                        </div>

                        <!-- Child Information Input Section -->
                        <div id="manual-input-section">
                            <div id="child-rows-container">
                                <!-- Child Rows Will Be Added Here -->
                            </div>
                            <button type="button" id="add-child-btn" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded-md">Add Another Child</button>
                        </div>

                        <!-- Excel Upload Section (Initially Hidden) -->
                        <div id="excel-upload-section" class="hidden mt-6">
                            <label for="excel_file" class="block text-sm font-medium text-gray-700">Upload Excel File</label>
                            <input type="file" name="excel_file" id="excel_file" accept=".xlsx, .xls" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <p class="text-sm text-gray-500 mt-2">Upload a valid Excel file containing child items.</p>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-md">Save BOM</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Template for Child Row -->
    <template id="child-row-template">
        <div class="flex space-x-4 mt-4 child-row">
            <div class="w-1/3">
                <input type="text" name="child_item_code[]" placeholder="Child Item Code" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
            </div>
            <div class="w-1/3">
                <input type="text" name="child_item_description[]" placeholder="Child Item Description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
            </div>
            <div class="w-1/3">
                <input type="text" name="quantity[]" placeholder="Quantity" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
            </div>
            <div class="w-1/3">
                <input type="text" name="measure[]" placeholder="Measure" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
            </div>
            <button type="button" class="remove-child-btn text-red-500 mt-2">Remove</button>
        </div>
    </template>

    <script>
        // Toggle between manual input and Excel upload
        const toggleInputMethodBtn = document.getElementById('toggle-input-method');
        const manualInputSection = document.getElementById('manual-input-section');
        const excelUploadSection = document.getElementById('excel-upload-section');

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
        const childRowsContainer = document.getElementById('child-rows-container');
        const childRowTemplate = document.getElementById('child-row-template');

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
    </script>
</x-app-layout>
