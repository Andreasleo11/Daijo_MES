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
                            <div class="bg-blue-600 h-3" style="width: 100%"></div>
                        </div>
                    </div>

                    <!-- Circle 2 -->
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-600 text-white font-semibold">
                        2
                    </div>
                    <div class="flex-1">
                        <div class="w-full bg-gray-200 h-3">
                            <div class="bg-blue-600 h-3" style="width: 0%"></div>
                        </div>
                    </div>

                    <!-- Circle 3 -->
                    <div class="flex items-center justify-center w-10 h-10 rounded-full border-2 border-blue-600 text-blue-600 font-semibold">
                        3
                    </div>
                </div>

                <!-- Next Button Section -->
                <div class="mt-6 border-t pt-4">
                    <div class="flex justify-between">
                        <a href="{{ route('step2logic') }}" class="bg-gray-800 text-white py-2 px-6 rounded-md hover:bg-gray-700">Mulai Proses 2</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</x-app-layout>