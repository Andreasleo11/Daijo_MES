<x-app-layout>
    <section class="header">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">DELIVERY SCHEDULE</h1>
            <div class="flex gap-3">
                <a href="{{ route('delsched.averagemonth') }}" class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded hover:bg-gray-500">
                    Average PerMonth
                </a>
                <a href="{{ route('deslsched.step1') }}" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded hover:bg-blue-500">
                    Update
                </a>
            </div>
        </div>
    </section>

    <section class="content mt-6">
        <div class="bg-white shadow rounded-lg">
            <div class="p-4">
                <div class="overflow-x-auto">
                    {{ $dataTable->table(['class' => 'min-w-full table-auto text-sm text-left text-gray-500']) }}
                </div>
            </div>
        </div>

        <div class="flex justify-between mt-6">
            <a href="{{ route('indexfinalwip') }}" class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded hover:bg-gray-500">
                Delivery Schedule (WIP)
            </a>
            <a href="{{ route('rawdelsched') }}" class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded hover:bg-gray-500">
                Delivery Schedule (RAW)
            </a>
        </div>
    </section>

    <!-- Include DataTables JavaScript -->
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  
     {{ $dataTable->scripts() }}
</x-app-layout>

