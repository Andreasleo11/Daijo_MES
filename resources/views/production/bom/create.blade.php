<x-app-layout>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2>{{ __("Create Bill of Materials") }}</h2>
                    <form action="{{ route('production.bom.store') }}" method="POST">
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

                        <h2 class="mt-6">Child Information</h2>
                        <div id="child-rows-container">
                            <!-- Child Rows Will Be Added Here -->
                        </div>

                        <button type="button" id="add-child-btn" class="mt-4 bg-blue-500 text-grey px-4 py-2 rounded-md">Add Another Child</button>

                        <div class="mt-4">
                            <button type="submit" class="bg-green-500 text-grey px-4 py-2 rounded-md">Save BOM</button>
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
        // Get the button and container for child rows
        const addChildBtn = document.getElementById('add-child-btn');
        const childRowsContainer = document.getElementById('child-rows-container');
        const childRowTemplate = document.getElementById('child-row-template');

        // Add event listener to the "Add Another Child" button
        addChildBtn.addEventListener('click', function() {
            // Clone the child row template and append to the container
            const newChildRow = childRowTemplate.content.cloneNode(true);
            childRowsContainer.appendChild(newChildRow);
        });

        // Event delegation for removing a child row
        childRowsContainer.addEventListener('click', function(event) {
            if (event.target.classList.contains('remove-child-btn')) {
                // Remove the parent row when the "Remove" button is clicked
                event.target.closest('.child-row').remove();
            }
        });
    </script>

</x-app-layout>
