<x-app-layout>
    <section class="mb-8">
        <div class="flex justify-start">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">DELIVERY SCHEDULE (RAW)</h1>
            </div>
        </div>
    </section>

    <section>
        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="overflow-x-auto">
                {{ $dataTable->table(['class' => 'min-w-full border-collapse border border-gray-300']) }}
            </div>
        </div>

        <div class="mt-6">
            <a href="{{ route('indexds') }}" class="inline-block px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 focus:ring-2 focus:ring-gray-400 focus:ring-offset-2">
                Back
            </a>
        </div>
    </section>

    {{ $dataTable->scripts() }}
</x-app-layout>
