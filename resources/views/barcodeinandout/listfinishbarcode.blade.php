<x-app-layout>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
        <div class="w-full max-w-4xl bg-white shadow-lg rounded-lg p-8">
            <h1 class="text-3xl font-bold text-center text-gray-700 mb-6">Barcode Data</h1>

            <form id="filterForm" class="flex flex-col md:flex-row md:space-x-4 space-y-4 md:space-y-0 mb-6">
                <div class="w-full md:w-1/3">
                    <label for="tipeBarcode" class="block text-sm font-medium text-gray-700">Tipe Barcode:</label>
                    <select name="tipeBarcode" id="tipeBarcode" class="w-full border border-gray-300 rounded-md p-2 mt-1">
                        <option value="">All</option>
                        <option value="IN">IN</option>
                        <option value="OUT">OUT</option>
                    </select>
                </div>

                <div class="w-full md:w-1/3">
                    <label for="location" class="block text-sm font-medium text-gray-700">Location:</label>
                    <select name="location" id="location" class="w-full border border-gray-300 rounded-md p-2 mt-1">
                        <option value="">All</option>
                        <option value="JAKARTA">JAKARTA</option>
                        <option value="KARAWANG">KARAWANG</option>
                    </select>
                </div>

                <div class="w-full md:w-1/3">
                    <label for="dateScan" class="block text-sm font-medium text-gray-700">Date Scan:</label>
                    <input type="date" name="dateScan" id="dateScan"
                        class="w-full border border-gray-300 rounded-md p-2 mt-1" />
                </div>
            </form>

            <div class="flex justify-center">
                <button type="button" id="filterButton"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-md">
                    Filter
                </button>
            </div>

            <div id="barcodeData" class="mt-8">
                @include('barcodeinandout.partials.barcode_table', ['result' => $result])
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#filterButton').on('click', function() {
                var tipeBarcode = $('#tipeBarcode').val();
                var location = $('#location').val();
                var dateScan = $('#dateScan').val();
                console.log(tipeBarcode);
                console.log(location);
                console.log(dateScan);
                $.ajax({
                    url: '{{ route('barcode.filter') }}',
                    method: 'GET',
                    data: {
                        tipeBarcode: tipeBarcode,
                        location: location,
                        dateScan: dateScan
                    },
                    success: function(response) {
                        $('#barcodeData').html(response);
                    }
                });
            });
        });
    </script>
</x-app-layout>
