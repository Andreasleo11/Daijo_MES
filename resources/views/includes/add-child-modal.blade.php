<div id="addChildModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-lg w-full">
        <h3 class="text-xl font-semibold mb-4">Add Child Items</h3>
        <form action="{{ route('production.bom.child.store', $bomParent) }}" method="POST">
            @csrf

            <!-- Manual Child Form Fields -->
            <div class="mb-4">
                <label class="block text-sm font-medium">Item Code</label>
                <input type="text" name="child[0][item_code]" class="w-full border rounded px-4 py-2" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Item Description</label>
                <input type="text" name="child[0][item_description]" class="w-full border rounded px-4 py-2"
                    required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Quantity</label>
                <input type="number" name="child[0][quantity]" class="w-full border rounded px-4 py-2" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Measure</label>
                <input type="text" name="child[0][measure]" class="w-full border rounded px-4 py-2" required>
            </div>

            <div class="flex justify-end mt-6">
                <button type="button" onclick="toggleModal('addChildModal', false)"
                    class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-2">
                    Cancel
                </button>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Add Child
                </button>
            </div>
        </form>

        <!-- Excel File Upload Form -->
        <form action="{{ route('production.bom.child.upload', $bomParent) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <div class="mt-6">
                <label class="block text-sm font-medium">Upload Excel File</label>
                <input type="file" name="excel_file" class="w-full border rounded px-4 py-2" accept=".xlsx,.xls,.csv"
                    required>
            </div>

            <div class="flex justify-end mt-6">
                <button type="button" onclick="toggleModal('addChildModal', false)"
                    class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-2">
                    Cancel
                </button>
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    Upload Excel
                </button>
            </div>
        </form>
    </div>
</div>
