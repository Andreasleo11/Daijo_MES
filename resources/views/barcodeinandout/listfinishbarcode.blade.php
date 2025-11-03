<x-app-layout>
    <div class="min-h-screen bg-gray-100 py-8">
        <div class="w-full max-w-7xl mx-auto px-4">
            <div class="bg-white shadow-lg rounded-lg p-8">
                <h1 class="text-3xl font-bold text-center text-gray-800 mb-8">Barcode Data</h1>
                
                {{-- Filter Form --}}
                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <form id="filterForm" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="tipeBarcode" class="block text-sm font-medium text-gray-700 mb-2">
                                Tipe Barcode:
                            </label>
                            <select name="tipeBarcode" id="tipeBarcode" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <option value="">Semua</option>
                                <option value="IN">IN</option>
                                <option value="OUT">OUT</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700 mb-2">
                                Location:
                            </label>
                            <select name="location" id="location" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <option value="">Semua</option>
                                <option value="JAKARTA">JAKARTA</option>
                                <option value="KARAWANG">KARAWANG</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="dateScan" class="block text-sm font-medium text-gray-700 mb-2">
                                Date Scan:
                            </label>
                            <input type="date" name="dateScan" id="dateScan" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" />
                        </div>
                    </form>
                    
                    {{-- Filter & Reset Button --}}
                    <div class="flex justify-center gap-3 mt-6">
                        <button type="button" id="filterButton" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2.5 px-6 rounded-lg transition-colors shadow-sm">
                            <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            Filter Data
                        </button>
                        <button type="button" id="resetButton" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2.5 px-6 rounded-lg transition-colors shadow-sm">
                            <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Reset
                        </button>
                    </div>
                </div>

                {{-- Loading Indicator --}}
                <div id="loadingIndicator" class="hidden text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
                    <p class="mt-4 text-gray-600">Loading data...</p>
                </div>

                {{-- Data Table --}}
                <div id="barcodeData" class="mt-6">
                    @include('barcodeinandout.partials.barcode_table', ['result' => $result])
                </div>
            </div>
        </div>
    </div>

    {{-- jQuery --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(function() {
            // Filter button handler
            $('#filterButton').on('click', function() {
                filterData();
            });

            // Reset button handler
            $('#resetButton').on('click', function() {
                $('#filterForm')[0].reset();
                filterData();
            });

            // Enter key handler
            $('#filterForm input, #filterForm select').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    filterData();
                }
            });

            function filterData() {
                let tipeBarcode = $('#tipeBarcode').val();
                let location = $('#location').val();
                let dateScan = $('#dateScan').val();

                // Show loading
                $('#loadingIndicator').removeClass('hidden');
                $('#barcodeData').addClass('opacity-50');

                $.ajax({
                    url: "{{ route('barcode.filter') }}",
                    type: "GET",
                    data: {
                        tipeBarcode: tipeBarcode,
                        location: location,
                        dateScan: dateScan
                    },
                    success: function(response) {
                        $('#barcodeData').html(response);
                        $('#loadingIndicator').addClass('hidden');
                        $('#barcodeData').removeClass('opacity-50');
                    },
                    error: function(xhr) {
                        $('#loadingIndicator').addClass('hidden');
                        $('#barcodeData').removeClass('opacity-50');
                        
                        let errorMessage = "Terjadi kesalahan saat memuat data.";
                        if (xhr.status === 500) {
                            errorMessage = "Server error. Silakan cek log atau hubungi administrator.";
                        } else if (xhr.status === 404) {
                            errorMessage = "Route tidak ditemukan.";
                        }
                        
                        $('#barcodeData').html(
                            '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">' +
                            '<strong class="font-bold">Error!</strong> ' +
                            '<span class="block sm:inline">' + errorMessage + '</span>' +
                            '<div class="mt-2 text-sm">Status: ' + xhr.status + '</div>' +
                            '</div>'
                        );
                        
                        console.error("Ajax Error:", xhr);
                    }
                });
            }
        });
    </script>
</x-app-layout>