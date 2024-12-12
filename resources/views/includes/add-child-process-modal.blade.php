<div id="add-child-process-modal-{{ $child->id }}"
    class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
        <h2 class="text-xl font-bold mb-4">Assign Process for Material</h2>

        <!-- Repeatable Process List -->
        <form id="process-form-{{ $child->id }}" method="POST"
            action="{{ route('production.bom.child.assign_process', $child->id) }}">
            @csrf
            <!-- Include CSRF token for security -->
            <div id="process-list-{{ $child->id }}" class="space-y-3">
                <div class="flex space-x-3 process-item">
                    <select name="all_process[]" class="process-select bg-gray-100 rounded p-2 w-full">
                        <option value="CNC">CNC</option>
                        <option value="EDM">EDM</option>
                        <option value="WIRECUT">WIRECUT</option>
                        <option value="GUNDRILL">GUNDRILL</option>
                        <option value="QC">QC</option>
                        <option value="POLISH">POLISH</option>
                        <option value="ASSEMBLY">ASSEMBLY</option>
                        <option value="MANUAL">MANUAL</option>
                    </select>
                    <button type="button" onclick="removeProcess(this)" class="bg-red-500 text-white px-2 rounded">
                        Remove
                    </button>
                </div>
            </div>

            <!-- Add New Process Button -->
            <button type="button" onclick="addProcess('{{ $child->id }}')"
                class="bg-green-500 text-white mt-4 px-3 py-1 rounded hover:bg-green-600">
                Add Another Process
            </button>

            <!-- Modal Actions -->
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="toggleModal('add-child-process-modal-{{ $child->id }}', false)"
                    class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Cancel
                </button>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>
