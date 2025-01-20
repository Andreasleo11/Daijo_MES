<x-app-layout>
    <h1 class="text-3xl font-semibold text-gray-800 mb-6">Add New Holiday Schedule</h1>

    <form action="{{ route('holiday-schedule.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Date Field -->
        <div class="flex flex-col">
            <label for="date" class="text-lg font-medium text-gray-700">Date</label>
            <input type="datetime-local" name="date" id="date" required class="mt-2 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Description Field -->
        <div class="flex flex-col">
            <label for="description" class="text-lg font-medium text-gray-700">Description</label>
            <input type="text" name="description" id="description" required class="mt-2 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Injection Field -->
        <div class="flex flex-col">
            <label for="injection" class="text-lg font-medium text-gray-700">Injection</label>
            <select name="injection" id="injection" required class="mt-2 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="Full">Full</option>
                <option value="Half">Half</option>
                <option value="Off">Off</option>
                <option value="Unknown">Unknown</option>
            </select>
        </div>

        <!-- Second Process Field -->
        <div class="flex flex-col">
            <label for="second_process" class="text-lg font-medium text-gray-700">Second Process</label>
            <select name="second_process" id="second_process" required class="mt-2 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="Full">Full</option>
                <option value="Half">Half</option>
                <option value="Off">Off</option>
                <option value="Unknown">Unknown</option>
            </select>
        </div>

        <!-- Assembly Field -->
        <div class="flex flex-col">
            <label for="assembly" class="text-lg font-medium text-gray-700">Assembly</label>
            <select name="assembly" id="assembly" required class="mt-2 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="Full">Full</option>
                <option value="Half">Half</option>
                <option value="Off">Off</option>
                <option value="Unknown">Unknown</option>
            </select>
        </div>

        <!-- Moulding Field -->
        <div class="flex flex-col">
            <label for="moulding" class="text-lg font-medium text-gray-700">Moulding</label>
            <select name="moulding" id="moulding" required class="mt-2 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="Full">Full</option>
                <option value="Half">Half</option>
                <option value="Off">Off</option>
                <option value="Unknown">Unknown</option>
            </select>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="w-full py-3 mt-6 bg-blue-500 text-white font-semibold rounded-lg shadow-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
            Add Holiday Schedule
        </button>
    </form>
</x-app-layout>
