<div id="assign-item-type-{{ $child->id }}"
    class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-lg w-full">
        <h3 class="text-xl font-semibold mb-4">Assign Action Type</h3>
        <form action="{{ route('production.bom.child.assign_type', $child->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-medium">Action Type</label>
                <select name="action_type" class="w-full border rounded px-4 py-2" required>
                    <option value="buyfinish" {{ $child->action_type == 'buyfinish' ? 'selected' : '' }}>
                        Buy Finish</option>
                    <option value="buyprocess" {{ $child->action_type == 'buyprocess' ? 'selected' : '' }}>
                        Buy Process</option>
                    <option value="stockfinish" {{ $child->action_type == 'stockfinish' ? 'selected' : '' }}>
                        Stock Finish</option>
                    <option value="stockprocess" {{ $child->action_type == 'stockprocess' ? 'selected' : '' }}>
                        Stock Process</option>
                </select>
            </div>

            <div class="flex justify-end mt-6">
                <button type="button" onclick="toggleModal('assign-item-type-{{ $child->id }}', false)"
                    class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-2">
                    Cancel
                </button>
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    Assign Type
                </button>
            </div>
        </form>
    </div>
</div>
