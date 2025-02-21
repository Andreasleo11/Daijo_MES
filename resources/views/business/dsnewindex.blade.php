<x-app-layout>
    <div class="container justify-center mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <section class="header">
            <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.2/xlsx.full.min.js"></script>
            <link rel="stylesheet"
                href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800">DELIVERY SCHEDULE</h1>
                <div class="flex gap-3">
                    <a href="{{ route('delsched.averagemonth') }}"
                        class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded hover:bg-gray-500">
                        Average PerMonth
                    </a>
                    <a href="{{ route('deslsched.step1') }}"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded hover:bg-blue-500">
                        Update
                    </a>
                </div>
                <div class="flex justify-between mt-6">
                    <!-- Export to Excel Button -->
                    <a href="{{ route('export.delschedfinal') }}"
                        class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded hover:bg-green-500">
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
                <a href="{{ route('indexfinalwip') }}"
                    class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded hover:bg-gray-500">
                    Delivery Schedule (WIP)
                </a>
                <a href="{{ route('rawdelsched') }}"
                    class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded hover:bg-gray-500">
                    Delivery Schedule (RAW)
                </a>
            </div>
        </section>
    </div>

    <!-- Include DataTables JavaScript -->

    {{ $dataTable->scripts() }}
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#deliverynewtable-table').DataTable({
                dom: '<"flex justify-between items-center p-4"<"text-gray-700 font-medium"l><"text-gray-700 font-medium"f>>' +
                    '<"overflow-x-auto"t>' +
                    '<"flex justify-between items-center p-4"<"text-gray-700 font-medium"i><"text-gray-700 font-medium"p>>',
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        next: '<button class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-500">Next</button>',
                        previous: '<button class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-500">Previous</button>',
                    },
                },
                classes: {
                    sWrapper: 'dataTables_wrapper',
                    sFilter: 'dataTables_filter',
                    sLength: 'dataTables_length',
                    sInfo: 'dataTables_info',
                    sPaging: 'dataTables_paginate',
                }
            });

            // Customize table header and rows with Tailwind classes
            $('table.dataTable').addClass('table-auto border-collapse border border-gray-300');
            $('table.dataTable thead').addClass('bg-gray-100 text-gray-700 uppercase text-sm font-medium');
            $('table.dataTable tbody tr').addClass('hover:bg-gray-50');
            $('table.dataTable th, table.dataTable td').addClass('border border-gray-300 px-4 py-2');
        });
    </script>
</x-app-layout>
