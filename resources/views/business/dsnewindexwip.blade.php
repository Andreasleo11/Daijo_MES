<x-app-layout>
    <div class="container justify-center mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <section class="header mb-8">
            <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 mb-4">DELIVERY SCHEDULE (WIP)</h1>
                    
                    <div class="text-sm text-gray-600">
                        <span class="font-medium">Terakhir Diupdate Pada:</span> 
                        {{ $utiDateList?->updated_at ?? 'No data' }}
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('delschedwip.step1') }}"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-500 transition-colors duration-200">
                        <i class="bi bi-pencil mr-2"></i>Update
                    </a>
                </div>
            </div>
        </section>

        <section class="content">
            <!-- Livewire Component -->
            @livewire('wip-table')
        </section>

        <div class="mt-8">
            <a href="{{ route('indexds') }}"
                class="inline-block px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-500 transition-colors duration-200">
                <i class="bi bi-arrow-left mr-2"></i>Back to Delivery Schedule
            </a>
        </div>
    </div>
</x-app-layout>