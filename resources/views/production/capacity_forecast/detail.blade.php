<x-app-layout>

<section class="header py-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-semibold text-gray-800">Capacity By Forecast (Detail SECTION)</h1>
        </div>
    </div>
</section>

<section class="content py-6">
    <div class="bg-white shadow-md rounded-lg mt-5">
        <div class="p-4">
            <div class="overflow-x-auto">
                <!-- DataTable with Tailwind classes -->
                {{ $dataTable->table(['class' => 'min-w-full table-auto text-sm text-left text-gray-500', 'id' => 'capitem-table']) }}
            </div>
        </div>
    </div>
    <div class="mt-4 flex justify-end">
        <a href="{{ route('capacityforecastindex') }}" class="bg-gray-800 text-white py-2 px-4 rounded-md hover:bg-gray-700">Back</a>
    </div>
</section>

{{ $dataTable->scripts() }}

</x-app-layout>