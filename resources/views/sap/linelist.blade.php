<x-app-layout>

<section class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-semibold text-gray-800">Line List View</h1>
        </div>
    </div>
</section>

<section class="content">
    <div class="bg-white shadow-md rounded-lg mt-5">
        <div class="p-4">
            <div class="overflow-x-auto">
                {{ $dataTable->table(['class' => 'min-w-full table-auto text-sm text-left text-gray-500', 'id' => 'invlinelist-table']) }}
            </div>
        </div>
    </div>

    <!-- Add Button for Modal (without href) -->
    <button onclick="openAddModal()" class="bg-gray-800 text-white py-2 px-4 rounded-md hover:bg-gray-700 mt-4 inline-block">Add</button>
</section>

@foreach($datas as $data)
    <!-- Pass dynamic id to the edit modal -->
   
    

    <div id="edit-line-modal{{ str_replace(' ', '', $data->line_code) }}" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-sm w-full">
            <h3 class="text-xl font-semibold">Edit Line</h3>
            <!-- Your edit form goes here -->
            <form action="{{ route('editline', $data->line_code) }}" method="POST">
                @csrf
                @method('PUT')
                <!-- Your input fields -->
                <div class="form-group mt-4">
                    <label for="line_code" class="form-label">Line Code:</label>
                    <input type="text" name="line_code" class="form-control" id="line_name" value="{{ $data->line_code }}">
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
                <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 mt-4">Save</button>
            </form>
            <button onclick="closeModal('edit-line-modal{{ str_replace(' ', '', $data->line_code) }}')" class="bg-gray-800 text-white py-2 px-4 rounded-md hover:bg-gray-700 mt-4">Close</button>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="delete-confirmation-modal-{{ str_replace(' ', '', $data->line_code) }}" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-sm w-full">
            <h3 class="text-xl font-semibold">Delete Line</h3>
            <p>Are you sure you want to delete the line: {{ $data->line_code }}?</p>
            <form action="{{ route('deleteline', $data->line_code) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 text-white py-2 px-4 rounded-md hover:bg-red-600 mt-4">Delete</button>
            </form>
            <button onclick="closeModal('delete-confirmation-modal-{{ str_replace(' ', '', $data->line_code) }}')" class="bg-gray-800 text-white py-2 px-4 rounded-md hover:bg-gray-700 mt-4">Close</button>
        </div>
    </div>
@endforeach

<!-- Pass dynamic id to the add modal -->
@include('includes.add-new-line-modal', ['modal_id' => 'add-new-line'])

{{ $dataTable->scripts() }}

<!-- Modal JS -->
<script>
    // Function to open the add new line modal
    function openAddModal() {
        document.getElementById('add-new-line').classList.remove('hidden');
    }

    // Function to close the add new line modal
    function closeAddModal() {
        document.getElementById('add-new-line').classList.add('hidden');
    }

    function openEditModal(modalId) {
        const modal = document.getElementById(modalId);
        console.log(modal);
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    // Function to open the Delete Modal (via Alpine.js)
    function openDeleteModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    // Function to close modals by hiding them
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
        }
    }
</script>

</x-app-layout>
