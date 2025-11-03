<div id="add-broken-child-modal-{{ $child->id }}"
    class="fixed inset-0 flex items-center justify-center hidden bg-black bg-opacity-50 z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
        <h2 class="text-lg font-bold mb-4">Add Broken Quantity for
            {{ $child->item_code }}</h2>
        <form action="{{ route('production.bom.child.addBrokenQuantity', $child->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="broken_quantity_{{ $child->id }}" class="block text-sm font-medium text-gray-700">Broken
                    Quantity</label>
                <input type="number" name="broken_quantity" id="broken_quantity_{{ $child->id }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    required>
            </div>
            <div class="mb-4">
                <label for="remark_{{ $child->id }}" class="block text-sm font-medium text-gray-700">Remark</label>
                <textarea name="remark" id="remark_{{ $child->id }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="Optional"></textarea>
            </div>
            <div class="flex justify-end">
                <button type="button" onclick="toggleModal('add-broken-child-modal-{{ $child->id }}', false)"
                    class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-2">
                    Cancel
                </button>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>
