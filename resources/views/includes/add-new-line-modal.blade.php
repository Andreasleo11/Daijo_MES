<div class="fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center z-50 hidden" id="add-new-line">
    <div class="bg-white rounded-lg w-full max-w-lg">
        <form method="POST" action="{{ route('addline') }}">
            @csrf
            <div class="p-6">
                <!-- Modal Header -->
                <div class="flex justify-between items-center">
                    <h5 class="text-2xl font-semibold text-gray-800">Add User</h5>
                    <button type="button" class="text-gray-500 hover:text-gray-700" onclick="document.getElementById('add-new-line').classList.add('hidden')">
                        <i class="bx bx-x text-2xl"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="space-y-4 mt-4">
                    <!-- Line Code -->
                    <div class="flex items-center">
                        <label for="line_code" class="w-1/3 text-lg text-gray-700">Line Code:</label>
                        <input type="text" name="line_code" class="w-2/3 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" id="line_code">
                    </div>

                    <!-- Line Name -->
                    <div class="flex items-center">
                        <label for="line_name" class="w-1/3 text-lg text-gray-700">Line Name:</label>
                        <input type="text" name="line_name" class="w-2/3 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" id="line_name">
                    </div>

                    <div class="flex items-center">
                        <label for="category" class="w-1/3 text-lg text-gray-700"> Category(Ganti dengan T di belakang):</label>
                        <input type="text" name="category" class="w-2/3 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" id="category">
                    </div>


                    <div class="flex items-center">
                        <label for="area" class="w-1/3 text-lg text-gray-700">Area :</label>
                        <input type="text" name="area" class="w-2/3 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" id="area">
                    </div>


                    <!-- Department -->
                    <div class="flex items-center">
                        <label for="departement" class="w-1/3 text-lg text-gray-700">Department:</label>
                        <input type="text" name="departement" class="w-2/3 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" id="departement">
                    </div>

                    <!-- Daily Minutes -->
                    <div class="flex items-center">
                        <label for="daily_minutes" class="w-1/3 text-lg text-gray-700">Daily Minutes:</label>
                        <input type="text" name="daily_minutes" class="w-2/3 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" id="daily_minutes">
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="mt-6 flex justify-end space-x-4">
                    <button type="button" class="py-2 px-4 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400" onclick="document.getElementById('add-new-line').classList.add('hidden')">Close</button>
                    <button type="submit" class="py-2 px-4 bg-blue-500 text-white rounded-md hover:bg-blue-600">Add</button>
                </div>
            </div>
        </form>
    </div>
</div>
