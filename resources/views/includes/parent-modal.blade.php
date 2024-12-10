<div id="parentModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-lg w-full">
        <h3 class="text-xl font-semibold mb-4">Edit BOM Parent</h3>
        <form action="{{ route('production.bom.update', $bomParent->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-medium">Item Code</label>
                <input type="text" name="code" value="{{ $bomParent->code }}"
                    class="w-full border rounded px-4 py-2" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Item Description</label>
                <input type="text" name="description" value="{{ $bomParent->description }}"
                    class="w-full border rounded px-4 py-2" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Type</label>
                <select name="type" class="w-full border rounded px-4 py-2" required>
                    <option value="moulding" {{ $bomParent->type == 'moulding' ? 'selected' : '' }}>Moulding
                    </option>
                    <option value="production" {{ $bomParent->type == 'production' ? 'selected' : '' }}>Production
                    </option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Customer </label>
                <input type="text" name="customer" value="{{ $bomParent->customer }}"
                    class="w-full border rounded px-4 py-2" required>
            </div>
            <div class="flex justify-end mt-6">
                <button type="button" onclick="toggleModal('parentModal', false)"
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
