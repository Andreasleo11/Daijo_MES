<x-app-layout>
    <title>Input Form</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/js/select2.min.js"></script>

    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="bg-white shadow-lg rounded-lg p-8 max-w-lg w-full">
            <h1 class="text-2xl font-bold text-center text-gray-700 mb-6">Input Form</h1>
            <form action="{{ route('generatepackagingbarcode') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="partNo" class="block text-sm font-medium text-gray-700 mb-2">Part No (Select or Type New)</label>
                    <select class="form-control w-full border border-gray-300 p-2 rounded-md" id="partNo"
                        name="partNo" required>
                        <option value="" disabled selected>Select Part No</option>
                        @foreach ($datas as $data)
                            <option value="{{ $data->part_no }}">{{ $data->part_no }} - {{ $data->part_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label for="print_quantity" class="block text-sm font-medium text-gray-700 mb-2">Print Quantity</label>
                    <input type="number" class="w-full border border-gray-300 p-2 rounded-md" id="print_quantity"
                        name="print_quantity" value="1" min="1" required>
                </div>
                <div class="mb-4">
                    <label for="remark" class="block text-sm font-medium text-gray-700 mb-2">Remark (Optional)</label>
                    <textarea class="w-full border border-gray-300 p-2 rounded-md" id="remark" name="remark" rows="2"></textarea>
                </div>
                <div class="flex flex-col gap-3">
                    <button type="submit"
                        class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-md transition duration-200">
                        Generate A4 Labels
                    </button>
                    <button type="submit" formaction="{{ route('generate.zebra.barcode') }}"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md transition duration-200">
                        Generate Zebra (50x35)
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#partNo').select2({
                placeholder: 'Select or type a Part No',
                allowClear: true,
                tags: true // Enable adding new part numbers on the fly
            });
        });
    </script>

</x-app-layout>
