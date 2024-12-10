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
              <input type="text" name="child_item_code[]" class="child-item-code mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Child Item Code" autocomplete="off" required>
                <div class="dropdown hidden absolute z-10 bg-white border border-gray-300 w-full rounded-md shadow-md max-h-40 overflow-auto"></div>
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

        document.addEventListener("DOMContentLoaded", function () {

        const childRowsContainer = document.getElementById("child-rows-container");

            // Event delegation to attach event listener to dynamically added inputs
            childRowsContainer.addEventListener("input", function (event) {
                if (event.target.classList.contains("child-item-code")) {
                    const inputField = event.target;
                    const dropdown = inputField.nextElementSibling; // Find the sibling dropdown element
                    const query = inputField.value;

                    if (query.length > 1) { // Only make a request if query length is > 1
                        fetchFilteredItemCodes(query, dropdown);
                    } else {
                        dropdown.classList.add("hidden");
                    }
                }
            });

            // Fetch filtered item codes using AJAX
            function fetchFilteredItemCodes(query, dropdown) {
                fetch(`/get-item-codes?query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        updateDropdown(data, dropdown);
                    })
                    .catch(error => {
                        console.error("Error fetching item codes:", error);
                    });
            }

            // Update the dropdown with fetched results
            function updateDropdown(items, dropdown) {
                dropdown.innerHTML = ""; // Clear existing items
                if (items.length > 0) {
                    items.forEach(item => {
                        const option = document.createElement("div");
                        option.className = "dropdown-item p-2 hover:bg-gray-200 cursor-pointer";
                        option.textContent = `${item.item_code} - ${item.item_description}`;
                        option.dataset.itemCode = item.item_code;
                        option.dataset.itemDescription = item.item_description;
                        option.dataset.itemUom = item.uom; // Add UOM data
                        dropdown.appendChild(option);
                    });
                    dropdown.classList.remove("hidden");
                } else {
                    dropdown.classList.add("hidden");
                }
            }

            // Handle dropdown item click to populate the input field
            childRowsContainer.addEventListener("click", function (event) {
                if (event.target.classList.contains("dropdown-item")) {
                    const selectedItem = event.target;
                    const inputField = selectedItem.parentElement.previousElementSibling; // Get the input field
                    const childRow = selectedItem.closest(".child-row"); // Find the parent child-row
                    const descriptionField = childRow.querySelector("input[name='child_item_description[]']");
                    const measureField = childRow.querySelector("input[name='measure[]']");
                    // Populate fields with selected item
                    inputField.value = selectedItem.dataset.itemCode;
                    descriptionField.value = selectedItem.dataset.itemDescription;
                    measureField.value = selectedItem.dataset.itemUom;
                    // Hide dropdown
                    selectedItem.parentElement.classList.add("hidden");
                }
            });
        }); 
       

    </script>
</x-app-layout>
