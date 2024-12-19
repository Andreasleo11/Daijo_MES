<x-app-layout>
    <div class="max-w-md mx-auto mt-10 bg-white p-8 rounded-lg shadow-lg">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Select Tipe Mesin</h1>

        <form action="{{ route('barcode.generate') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label for="tipe_mesin" class="block text-sm font-medium text-gray-700">Select Tipe Mesin:</label>
                <select name="tipe_mesin" id="tipe_mesin" required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm bg-white">
                    <option value="" disabled selected>Select a tipe mesin</option>
                    @foreach ($tipeMesins as $tipeMesin)
                        <option value="{{ $tipeMesin }}">{{ $tipeMesin }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-center">
                <button type="submit"
                    class="w-full py-2 px-4 bg-indigo-600 text-white font-semibold rounded-md shadow hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Generate Barcodes
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
