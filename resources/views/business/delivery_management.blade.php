<x-app-layout>
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-semibold text-center mb-6">
            Delete Delivery Data -    
        Select Month and Year</h1>

        <!-- Form for Month and Year selection -->
        <form action="{{ route('delete.delivery.data') }}" method="GET">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Month Select -->
                <div>
                    <label for="month" class="block text-sm font-medium text-gray-700">Month</label>
                    <select name="month" id="month" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        @foreach(range(1, 12) as $month)
                            <option value="{{ $month }}" {{ old('month') == $month ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($month)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Year Select -->
                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700">Year</label>
                    <select name="year" id="year" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        @php
                            $currentYear = now()->year;
                        @endphp
                        @foreach(range($currentYear - 5, $currentYear + 5) as $year)
                            <option value="{{ $year }}" {{ old('year') == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-between">
                <!-- Submit Button -->
                <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <i class="bx bx-check"></i> Submit
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
