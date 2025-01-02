<div id="edit-worker-modal-{{ $worker->id }}"
    class="fixed inset-0 flex justify-center items-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
        <h3 class="text-xl font-semibold mb-4">Edit Worker</h3>
        <form action="{{ route('workshop.update.worker') }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" id="worker-id" name="worker_id" value="{{ $worker->id }}">

            <!-- Worker Name -->
            <div class="mb-4">
                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Worker
                    Name</label>
                <input type="text" name="username" id="username"
                    class="block w-full p-2 border rounded-lg focus:ring focus:ring-blue-200 focus:outline-none"
                    value = "{{ $worker->username }}" required />
            </div>
            @if ($log->process_name === 'MANUAL')
                <!-- Job (if applicable) -->
                <div class="mb-4">
                    <label for="job" class="block text-sm font-medium text-gray-700 mb-2">Job</label>
                    <select name="job" id="job"
                        class="job-input block w-full p-2 border rounded-lg focus:ring focus:ring-blue-200 focus:outline-none">
                        <option value="">Select Job</option>
                        <option value="Grinding" {{ $worker->jobs === 'Grinding' ? 'selected' : '' }}>
                            Grinding</option>
                        <option value="Miling" {{ $worker->jobs === 'Miling' ? 'selected' : '' }}>
                            Miling</option>
                        <option value="Bor" {{ $worker->jobs === 'Bor' ? 'selected' : '' }}>Bor
                        </option>
                        <option value="Bubut" {{ $worker->jobs === 'Bubut' ? 'selected' : '' }}>
                            Bubut
                        </option>
                        <option value="Spoting" {{ $worker->jobs === 'Spoting' ? 'selected' : '' }}>
                            Spoting</option>
                        <option value="Matching" {{ $worker->jobs === 'Matching' ? 'selected' : '' }}>
                            Matching</option>
                        <option value="Weilding" {{ $worker->jobs === 'Weilding' ? 'selected' : '' }}>
                            Weilding</option>
                    </select>
                </div>
            @endif

            <!-- Remark -->
            <div class="mb-4">
                <label for="remark" class="block text-sm font-medium text-gray-700 mb-2">Remark</label>
                <input type="text" name="remark" id="remark" value = "{{ $worker->remark }}"
                    class="block w-full p-2 border rounded-lg focus:ring focus:ring-blue-200 focus:outline-none" />
            </div>

            <!-- Buttons -->
            <div class="flex justify-between items-center">
                <button type="button" class="px-4 py-2 bg-gray-500 text-white rounded-lg"
                    onclick="closeEditModal({{ $worker->id }})">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(workerId) {
        // Show the modal
        document.getElementById(`edit-worker-modal-${workerId}`).classList.remove('hidden');
    }

    // Close the modal
    function closeEditModal(workerId) {
        document.getElementById(`edit-worker-modal-${workerId}`).classList.add('hidden');
    }
</script>
