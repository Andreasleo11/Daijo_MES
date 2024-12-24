<div id="add-remark-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
    <div class="bg-white rounded-lg w-full max-w-lg p-6">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold">Create Remark</h2>
            <button type="button" class="text-gray-600" onclick="closeModal('add-remark-modal')">
                <span class="text-xl">&times;</span>
            </button>
        </div>
        <form action="{{ route('remark.store', ['log_id' => $log->id]) }}" method="POST">
            @csrf
            <div class="mt-4">
                <label for="remark" class="block text-sm font-medium text-gray-700">Remark</label>
                <textarea id="remark" name="remark" class="mt-2 w-full p-2 border border-gray-300 rounded-md" required>{{ old('remark') }}</textarea>
            </div>
            <div class="mt-4 flex justify-end">
                <button type="button" class="px-4 py-2 text-white bg-gray-500 rounded-md mr-2"
                    onclick="closeModal('add-remark-modal')">Cancel</button>
                <button type="submit" class="px-4 py-2 text-white bg-blue-500 rounded-md">Save
                    Remark</button>
            </div>
        </form>
    </div>
</div>
