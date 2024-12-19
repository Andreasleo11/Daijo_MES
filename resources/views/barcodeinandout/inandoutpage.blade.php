<x-app-layout>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="bg-white shadow-lg rounded-lg p-8 max-w-lg w-full">
            <h1 class="text-2xl font-bold text-center text-gray-700 mb-6">Insert Warehouse Barcode Form</h1>
            <form action="{{ route('process.in.and.out') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="warehouseType" class="block text-sm font-medium text-gray-700 mb-2">Warehouse Type</label>
                    <select class="form-control w-full border border-gray-300 p-2 rounded-md" id="warehouseType"
                        name="warehouseType" required>
                        <option value="" disabled selected>Select Warehouse Type</option>
                        <option value="in">In</option>
                        <option value="out">Out</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                    <select class="form-control w-full border border-gray-300 p-2 rounded-md" id="location"
                        name="location" required>
                        <option value="" disabled selected>Select Location</option>
                        <option value="jakarta">Jakarta</option>
                        <option value="karawang">Karawang</option>
                    </select>
                </div>
                <button type="submit"
                    class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-md transition duration-200">Submit</button>
            </form>
        </div>
    </div>
</x-app-layout>
