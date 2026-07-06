<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Barcode</title>

    <!-- Tailwind (kalau sudah ada di project, boleh dihapus) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-100">

<div class="max-w-xl mx-auto mt-10 bg-white p-6 rounded-lg shadow">
    <h2 class="text-xl font-bold mb-4">Generate Barcode</h2>

    <form id="generateForm" method="POST" target="_blank">
        @csrf

        <!-- PART CODE (SEARCHABLE) -->
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Pilih Part Code</label>
            <select name="part_code" class="w-full select-part">
                <option value="">-- Pilih Part --</option>
                @foreach($items as $item)
                    <option value="{{ $item->part_code }}">
                        {{ $item->part_code }} - {{ $item->part_name }} || {{ $item->ukuran_label }} mm
                    </option>
                @endforeach
            </select>
            @error('part_code')
                <small class="text-red-500">{{ $message }}</small>
            @enderror
        </div>

        <!-- LABEL RANGE -->
        <div class="flex gap-4 mb-3">
            <div class="flex-1">
                <label class="block text-sm font-medium mb-1">Label Start</label>
                <input type="number" name="label_start"
                    class="w-full border rounded px-3 py-2"
                    placeholder="1">
                @error('label_start')
                    <small class="text-red-500">{{ $message }}</small>
                @enderror
            </div>

            <div class="flex-1">
                <label class="block text-sm font-medium mb-1">Label End</label>
                <input type="number" name="label_end"
                    class="w-full border rounded px-3 py-2"
                    placeholder="50">
                @error('label_end')
                    <small class="text-red-500">{{ $message }}</small>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">
                Tanggal Produksi <span class="text-gray-400">(optional)</span>
            </label>
            <input type="date"
                name="production_date"
                class="w-full border rounded px-3 py-2"
                value="{{ old('production_date') }}">
            @error('production_date')
                <small class="text-red-500">{{ $message }}</small>
            @enderror
        </div>

        <!-- BUTTONS -->
        <div class="flex gap-3">
            <button type="button"
                onclick="submitTo('{{ route('generate.label.yanfeng40x15') }}')"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Generate 40x15
            </button>

            <button type="button"
                onclick="submitTo('{{ route('generate.label.yanfeng25x10') }}')"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                Generate 25x10
            </button>

            <button type="button"
                onclick="submitTo('{{ route('generate.label.yanfeng50x20') }}')"
                class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded">
                Generate 50x20
            </button>

            <button type="button"
                onclick="submitTo('{{ route('generate.label.yanfeng50x35') }}')"
                class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded">
                Generate 50x35
            </button>
        </div>
    </form>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    function submitTo(route) {
        let form = document.getElementById('generateForm');
        form.action = route;
        form.submit();
    }

    $(document).ready(function () {
        $('.select-part').select2({
            placeholder: "-- Pilih Part --",
            allowClear: true,
            width: '100%'
        });
    });
</script>

</body>
</html>
