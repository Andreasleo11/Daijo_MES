<div id="modal-{{ $child->id }}" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-lg w-full">
        <h3 class="text-xl font-semibold mb-4">Edit Child Item</h3>
        <form action="{{ route('production.bom.child.update', $child->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label class="block text-sm font-medium">Item Code</label>
                <input type="text" name="item_code" value="{{ $child->item_code }}"
                    class="w-full border rounded px-4 py-2" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Item
                    Description</label>
                <input type="text" name="item_description" value="{{ $child->item_description }}"
                    class="w-full border rounded px-4 py-2" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Quantity</label>
                <input type="number" step="any" name="quantity" value="{{ $child->quantity }}"
                    class="w-full border rounded px-4 py-2" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Measure</label>
                <input type="text" name="measure" value="{{ $child->measure }}"
                    class="w-full border rounded px-4 py-2" required>
            </div>

            <div class="flex justify-end mt-6">
                <button type="button" onclick="toggleModal('modal-{{ $child->id }}', false)"
                    class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-2">
                    Cancel
                </button>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
