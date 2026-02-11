<x-app-layout>
    <div class="container justify-center mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <section class="header mb-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-4">DELIVERY SCHEDULE (RAW)</h1>
        </section>

        <section class="content">
            <!-- Livewire Component -->
            @livewire('raw-delsched-table')
        </section>

        <div class="mt-8">
            <a href="{{ route('indexds') }}"
                class="inline-block px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-500 transition-colors duration-200">
                <i class="bi bi-arrow-left mr-2"></i>Back to Delivery Schedule
            </a>
        </div>
    </div>
</x-app-layout>