<x-app-layout>

<section class="header py-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-semibold text-gray-800">Capacity By Forecast Periode {{ $time->start_date }}</h1>
        </div>
        <div>
            <a href="{{ route('viewstep1') }}" class="bg-gray-800 text-white py-2 px-4 rounded-md hover:bg-gray-700">Mulai Proses</a>
        </div>
    </div>
</section>

<section class="content py-6">
    <div class="bg-white shadow-md rounded-lg mt-5">
        <div class="p-4">
            <div class="overflow-x-auto">
                <!-- DataTable with Tailwind classes -->
                {{ $dataTable->table(['class' => 'min-w-full table-auto text-sm text-left text-gray-500', 'id' => 'capacity-forecast-table']) }}
            </div>
        </div>
    </div>
    <div class="mt-4 flex justify-end space-x-2">
        <a href="{{ route('capacityforecastline') }}" class="bg-gray-800 text-white py-2 px-4 rounded-md hover:bg-gray-700">Line</a>
        <a href="{{ route('capacityforecastdistribution') }}" class="bg-gray-800 text-white py-2 px-4 rounded-md hover:bg-gray-700">Distribution</a>
        <a href="{{ route('capacityforecastdetail') }}" class="bg-gray-800 text-white py-2 px-4 rounded-md hover:bg-gray-700">Detail</a>
    </div>
</section>
 
{{ $dataTable->scripts() }}



</x-app-layout>