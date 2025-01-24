<div x-data="{ open: false }" x-show="open" @keydown.escape.window="open = false" x-ref="editModal" id="edit-line-modal-{{ str_replace(' ', '', $data->line_code) }}" class="modal fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center hidden">
    <div class="modal-content bg-white rounded-lg p-6">
        <form method="POST" action="{{ route('editline', $data->line_code) }}">
            @csrf
            @method('PUT')
            <div class="modal-header flex justify-between items-center">
                <h5 class="modal-title text-lg font-semibold">Edit Line</h5>
                <button type="button" class="text-gray-500" @click="open = false">
                    <i class="bx bx-x text-2xl"></i>
                </button>
            </div>
            <div class="modal-body">
                <!-- Form content -->
                <div class="form-group mt-4">
                    <label for="line_code" class="form-label">Line Code:</label>
                    <input type="text" name="line_code" class="form-control" id="line_code" value="{{ $data->line_code }}">
                </div>
                <div class="form-group mt-4">
                    <label for="line_name" class="form-label">Line Name:</label>
                    <input type="text" name="line_name" class="form-control" id="line_name" value="{{ $data->line_name }}">
                </div>
                <div class="form-group mt-4">
                    <label for="departement" class="form-label">Department:</label>
                    <input type="text" name="departement" class="form-control" id="departement" value="{{ $data->departement }}">
                </div>
                <div class="form-group mt-4">
                    <label for="daily_minutes" class="form-label">Daily Minutes:</label>
                    <input type="text" name="daily_minutes" class="form-control" id="daily_minutes" value="{{ $data->daily_minutes }}">
                </div>
            </div>
            <div class="modal-footer flex justify-end space-x-2">
                <button type="button" class="bg-gray-500 text-white py-2 px-4 rounded-md hover:bg-gray-400" @click="open = false">Close</button>
                <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">Save Changes</button>
            </div>
        </form>
    </div>
</div>
