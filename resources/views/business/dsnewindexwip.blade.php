<x-app-layout>
    <section class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">DELIVERY SCHEDULE (WIP)</h1>
            </div>
            <div>
                <a href="{{ route('delschedwip.step1') }}" class="inline-block px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Update
                </a>
            </div>
        </div>
    </section>

    <div class="mt-4 text-sm text-gray-600">
                Terakhir Diupdate Pada : {{ $utiDateList->updated_at }}
            </div>
    
    <section>
        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="overflow-x-auto">
                {{ $dataTable->table(['class' => 'min-w-full border-collapse border border-gray-300']) }}
            </div>
        </div>

        <div class="mt-6 text-right">
            <a href="{{ route('indexds') }}" class="inline-block px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 focus:ring-2 focus:ring-gray-400 focus:ring-offset-2">
                Delivery Schedule
            </a>
        </div>
    </section>

    {{ $dataTable->scripts() }}
</x-app-layout>
