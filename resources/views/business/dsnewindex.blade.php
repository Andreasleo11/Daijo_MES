<x-app-layout>
    <section class="header">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.2/xlsx.full.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css">
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
            <div class="flex justify-between mt-6">
            <!-- Export to Excel Button -->
            <a href="{{ route('export.delschedfinal') }}" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded hover:bg-green-500">
                Export to Excel
            </a>
        </div>
        </div>

        <div class="mt-4 text-sm text-gray-600">
            Terakhir Diupdate Pada : {{ $utiDateList->updated_at }}
        </div>
    </section>
  

    <section class="content mt-6">
        <div class="bg-white shadow rounded-lg">
            <div class="p-4">
                <div class="overflow-x-auto">
                    {{ $dataTable->table(['class' => 'min-w-full table-auto text-sm text-left text-gray-500', 'id' => 'deliverynewtable-table']) }}
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

     {{ $dataTable->scripts() }}

</x-app-layout>

