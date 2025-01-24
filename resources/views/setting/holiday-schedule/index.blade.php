<x-app-layout>
    <h1 class="text-3xl font-semibold text-gray-800 mb-6">Holiday Schedule Index</h1>

    <div class="mb-6">
        <a href="{{ route('holiday-schedule.export') }}" class="bg-green-500 text-white py-2 px-6 rounded-lg shadow-md hover:bg-green-600 transition duration-300 ease-in-out">
            Export to Excel
        </a>
    </div>


    <!-- Import Button -->
    <div class="mb-6">
        <button onclick="openModal()" class="bg-green-500 text-white py-2 px-6 rounded-lg shadow-md hover:bg-green-600 transition duration-300 ease-in-out">Import Holiday Schedule</button>
    </div>

    <!-- Modal for File Upload -->
    <div id="importModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center z-50 hidden">
        <div class="bg-white p-6 rounded-lg w-1/3">
            <h2 class="text-2xl font-semibold mb-4">Import Holiday Schedule</h2>
            <form action="{{ route('holiday-schedule.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label for="file" class="block text-sm font-medium text-gray-700">Upload Excel File</label>
                    <input type="file" name="file" id="file" class="mt-1 px-4 py-2 w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-blue-500 text-white py-2 px-6 rounded-md hover:bg-blue-600">Import</button>
                    <button type="button" onclick="closeModal()" class="ml-4 bg-gray-300 text-gray-700 py-2 px-6 rounded-md hover:bg-gray-400">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Button to Add Data -->
    <div class="mb-6">
        <a href="{{ route('holiday-schedule.create') }}" class="bg-blue-500 text-white py-2 px-6 rounded-lg shadow-md hover:bg-blue-600 transition duration-300 ease-in-out">Add New Holiday Schedule</a>
    </div>

    <!-- Table to Display Data -->
    <div class="overflow-x-auto bg-white shadow-md rounded-lg">
        <table class="min-w-full text-sm text-left text-gray-500 border-collapse">
            <thead class="bg-blue-50 text-blue-700">
                <tr>
                    <th class="px-6 py-3 border-b font-medium">Date</th>
                    <th class="px-6 py-3 border-b font-medium">Description</th>
                    <th class="px-6 py-3 border-b font-medium">Injection</th>
                    <th class="px-6 py-3 border-b font-medium">Second Process</th>
                    <th class="px-6 py-3 border-b font-medium">Assembly</th>
                    <th class="px-6 py-3 border-b font-medium">Moulding</th>
                    <th class="px-6 py-3 border-b font-medium">Half Day</th> 
                    <th class="px-6 py-3 border-b font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datas as $data)
                    <tr class="border-b hover:bg-gray-50 transition duration-300 ease-in-out">
                        <td class="px-6 py-4">{{ \Carbon\Carbon::parse($data->date)->format('Y-m-d') }}</td>
                        <td class="px-6 py-4">{{ $data->description }}</td>
                        <td class="px-6 py-4">{{ $data->injection }}</td>
                        <td class="px-6 py-4">{{ $data->second_process }}</td>
                        <td class="px-6 py-4">{{ $data->assembly }}</td>
                        <td class="px-6 py-4">{{ $data->moulding }}</td>
                        <td class="px-6 py-4">
                            {{ $data->half_day == 1 ? 'Yes' : 'No' }} <!-- Displaying Yes/No for Half Day -->
                        </td>
                        <td class="px-6 py-4 text-blue-500 hover:text-blue-700">
                            <!-- Edit Button -->
                            <button 
                                class="bg-yellow-500 text-white py-2 px-4 rounded-md hover:bg-yellow-600" 
                                onclick="openEditModal({{ $data->id }}, '{{ $data->description }}', '{{ $data->injection }}', '{{ $data->second_process }}', '{{ $data->assembly }}', '{{ $data->moulding }}')">
                                Edit
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal for Editing Data -->
    <div id="editModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center z-50 hidden">
        <div class="bg-white p-6 rounded-lg w-1/3">
            <h2 class="text-2xl font-semibold mb-4">Edit Holiday Schedule</h2>
            <form action="{{ route('holiday-schedule.update', 'ID_PLACEHOLDER') }}" method="POST" id="editForm">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <input type="text" id="description" name="description" class="mt-1 px-4 py-2 w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>

                <div class="mb-4">
                    <label for="injection" class="block text-sm font-medium text-gray-700">Injection</label>
                    <select id="injection" name="injection" class="mt-1 px-4 py-2 w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="Full">Full</option>
                        <option value="Half">Half</option>
                        <option value="Off">Off</option>
                        <option value="Unknown">Unknown</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="second_process" class="block text-sm font-medium text-gray-700">Second Process</label>
                    <select id="second_process" name="second_process" class="mt-1 px-4 py-2 w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="Full">Full</option>
                        <option value="Half">Half</option>
                        <option value="Off">Off</option>
                        <option value="Unknown">Unknown</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="assembly" class="block text-sm font-medium text-gray-700">Assembly</label>
                    <select id="assembly" name="assembly" class="mt-1 px-4 py-2 w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="Full">Full</option>
                        <option value="Half">Half</option>
                        <option value="Off">Off</option>
                        <option value="Unknown">Unknown</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="moulding" class="block text-sm font-medium text-gray-700">Moulding</label>
                    <select id="moulding" name="moulding" class="mt-1 px-4 py-2 w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="Full">Full</option>
                        <option value="Half">Half</option>
                        <option value="Off">Off</option>
                        <option value="Unknown">Unknown</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="half_day" class="block text-sm font-medium text-gray-700">Half Day</label>
                    <select id="half_day" name="half_day" class="mt-1 px-4 py-2 w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-blue-500 text-white py-2 px-6 rounded-md hover:bg-blue-600">Save Changes</button>
                    <button type="button" onclick="closeEditModal()" class="ml-4 bg-gray-300 text-gray-700 py-2 px-6 rounded-md hover:bg-gray-400">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Open the modal
        function openEditModal(id, description, injection, second_process, assembly, moulding) {
            document.getElementById("editModal").classList.remove("hidden");
            document.getElementById("editForm").action = "/holiday-schedule/" + id;
            document.getElementById("description").value = description;
            document.getElementById("injection").value = injection;
            document.getElementById("second_process").value = second_process;
            document.getElementById("assembly").value = assembly;
            document.getElementById("moulding").value = moulding;
            document.getElementById("half_day").value = half_day;
        }

        // Close the modal
        function closeEditModal() {
            document.getElementById("editModal").classList.add("hidden");
        }

        // Open the modal
        function openModal() {
            document.getElementById('importModal').classList.remove('hidden');
        }

        // Close the modal
        function closeModal() {
            document.getElementById('importModal').classList.add('hidden');
        }
    </script>

</x-app-layout>
