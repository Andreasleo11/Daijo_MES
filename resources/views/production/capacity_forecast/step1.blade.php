<x-app-layout>

<div class="container mx-auto mt-6">
    <div class="flex justify-center">
        <div class="w-full max-w-4xl">
            <div class="bg-white rounded-lg shadow-md p-6">
                <!-- Progress Tracker Section -->
                <div class="flex items-center justify-center space-x-6">
                    <!-- Circle 1 -->
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-600 text-white font-semibold">
                        1
                    </div>
                    <div class="flex-1">
                        <div class="w-full bg-gray-200 h-3">
                            <div class="bg-blue-600 h-3" style="width: 50%"></div>
                        </div>
                    </div>

                    <!-- Circle 2 -->
                    <div class="flex items-center justify-center w-10 h-10 rounded-full border-2 border-blue-600 text-blue-600 font-semibold">
                        2
                    </div>
                    <div class="flex-1">
                        <div class="w-full bg-gray-200 h-3">
                            <div class="bg-transparent h-3" style="width: 0%"></div>
                        </div>
                    </div>

                    <!-- Circle 3 -->
                    <div class="flex items-center justify-center w-10 h-10 rounded-full border-2 border-blue-600 text-blue-600 font-semibold">
                        3
                    </div>
                </div>

                <!-- Form Section -->
                <div class="mt-6">
                    <h1 class="text-xl font-semibold text-gray-700">Pilih Tanggal 1 Di Bulan Yang Ingin Dipilih</h1>

                    <form action="{{ route('step1') }}" method="GET" class="mt-4">
                        <div class="mb-4">
                            <label for="start_date" class="block text-gray-700 font-medium">Pilih Tanggal:</label>
                            <input type="date" id="start_date" name="start_date" class="mt-2 p-2 border border-gray-300 rounded-md w-full" required>
                        </div>
                        
                        <div class="flex justify-between border-t pt-4">
                            <button type="submit" class="bg-gray-800 text-white py-2 px-6 rounded-md hover:bg-gray-700">Mulai Proses 1</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


</x-app-layout>