<div x-data="{ open: false }" x-show="open" @keydown.escape.window="open = false" x-ref="deleteModal" id="delete-confirmation-modal-{{ $id }}" class="modal fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center hidden">
    <div class="modal-content bg-white rounded-lg p-6">
        <form action="{{ route($route, $id) }}" method="post">
            @csrf
            @method('DELETE')
            <div class="modal-header flex justify-between items-center">
                <h5 class="modal-title text-lg font-semibold">{{ $title }}</h5>
                <button type="button" class="text-gray-500" @click="open = false">
                    <i class="bx bx-x text-2xl"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>{!! $body !!}</p>
            </div>
            <div class="modal-footer flex justify-end space-x-2">
                <button type="button" class="bg-gray-500 text-white py-2 px-4 rounded-md hover:bg-gray-400" @click="open = false">Close</button>
                <button type="submit" class="bg-red-500 text-white py-2 px-4 rounded-md hover:bg-red-600">Delete</button>
            </div>
        </form>
    </div>
</div>
