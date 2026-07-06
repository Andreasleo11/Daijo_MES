<x-app-layout>
    <div class="container justify-center mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <section class="header mb-8">
            <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 mb-4">DELIVERY SCHEDULE</h1>
                    
                    <div class="space-y-2">
                        <div class="text-sm text-gray-600">
                            <span class="font-medium">Terakhir API UPDATE:</span> 
                            {{ $latestSyncRejectTimeJakarta ?? 'No data' }}
                        </div>
                        <div class="text-sm text-gray-600">
                            <span class="font-medium">Terakhir Diupdate Pada:</span> 
                            {{ $utiDateList?->updated_at ?? 'No data' }}
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('delsched.averagemonth') }}"
                        class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-500 transition-colors duration-200">
                        <i class="bi bi-graph-up mr-2"></i>Average PerMonth
                    </a>
                    <a href="{{ route('deslsched.step1') }}"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-500 transition-colors duration-200">
                        <i class="bi bi-pencil mr-2"></i>Update
                    </a>
                    <a href="{{ route('export.delschedfinal') }}"
                        class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-500 transition-colors duration-200">
                        <i class="bi bi-download mr-2"></i>Export to Excel
                    </a>
                </div>
            </div>
        </section>

        <section class="content">
            <!-- Livewire Component -->
            @livewire('delivery-table')
        </section>

        <div class="flex flex-col sm:flex-row gap-3 mt-8">
            <a href="{{ route('indexfinalwip') }}"
                class="flex-1 px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-500 transition-colors duration-200 text-center">
                <i class="bi bi-list-ul mr-2"></i>Delivery Schedule (WIP)
            </a>
            <a href="{{ route('rawdelsched') }}"
                class="flex-1 px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-500 transition-colors duration-200 text-center">
                <i class="bi bi-database mr-2"></i>Delivery Schedule (RAW)
            </a>
        </div>
    </div>
</x-app-layout>