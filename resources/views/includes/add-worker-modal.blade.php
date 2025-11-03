<div id="add-worker-modal" class="fixed inset-0 flex justify-center items-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
        <h3 class="text-xl font-semibold mb-4">Add Involved Worker</h3>
        <form action="{{ route('workshop.add.worker') }}" method="POST">
            @csrf
            <input type="hidden" name="log_id" value="{{ $log->id }}">

            <div class="mb-4">
                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Worker
                    Name</label>
                <input type="text" name="username" id="username"
                    class="block w-full p-2 border rounded-lg focus:ring focus:ring-blue-200 focus:outline-none"
                    required />
            </div>

            @if ($log->process_name === 'MANUAL')
                <div class="mb-4">
                    <label for="job" class="block text-sm font-medium text-gray-700 mb-2">Job</label>
                    <select name="job" id="job"
                        class="job-input block w-full p-2 border rounded-lg focus:ring focus:ring-blue-200 focus:outline-none">
                        <option value="">Select Job</option>
                        <option value="Grinding">Grinding</option>
                        <option value="Miling">Miling</option>
                        <option value="Bor">Bor</option>
                        <option value="Bubut">Bubut</option>
                        <option value="Spoting">Spoting</option>
                        <option value="Matching">Matching</option>
                        <option value="Weilding">Weilding</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            @endif
            <!-- Remark Worker -->
            <div class="mb-4">
                <label for="remark_worker" class="block text-sm font-medium text-gray-700 mb-2">Remark
                    (Optional)</label>
                <textarea name="remark_worker" id="remark_worker"
                    class="block w-full p-2 border rounded-lg focus:ring focus:ring-blue-200 focus:outline-none" rows="4"></textarea>
            </div>


            <div class="flex justify-between items-center">
                <button type="button" class="px-4 py-2 bg-gray-500 text-white rounded-lg"
                    onclick="document.getElementById('add-worker-modal').classList.add('hidden')">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg">
                    Add Worker
                </button>
            </div>
        </form>
    </div>
</div>
