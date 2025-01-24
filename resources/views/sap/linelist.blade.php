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
    @include('includes.edit-line-modal', ['id' => str_replace(' ', '', $data->line_code)])

    <!-- Pass dynamic id to the delete modal -->
    @include('includes.delete-confirmation-modal', [
        'id' => str_replace(' ', '',$data->line_code),
        'route' => 'deleteline',
        'title' => 'Delete Line confirmation',
        'body' => 'Are you sure want to delete ' . $data->line_code . '?',
    ])
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

    function openAddModal() {
        document.getElementById('add-new-line').classList.remove('hidden');
    }

    function openEditModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal && modal.__x) {
            modal.__x.$data.open = true; // Access Alpine's data and set open to true
        }
    }

    // Function to open the Delete Modal (via Alpine.js)
    function openDeleteModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal && modal.__x) {
            modal.__x.$data.open = true; // Access Alpine's data and set open to true
        }
    }

    // Function to close modals by hiding them
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal && modal.__x) {
            modal.__x.$data.open = false; // Access Alpine's data and set open to false
        }
    }
</script>

</x-app-layout>
