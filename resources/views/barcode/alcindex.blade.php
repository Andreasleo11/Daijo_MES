<div class="max-w-xl mx-auto mt-10 bg-white p-6 rounded-lg shadow">
    <h2 class="text-xl font-bold mb-4">Generate Barcode</h2>

    <form action="{{ route('generate.label.yanfeng') }}" method="POST" target="_blank">
        @csrf

        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Pilih Part Code</label>
            <select name="part_code" class="w-full border rounded px-3 py-2">
                <option value="">-- Pilih Part --</option>
                @foreach($items as $item)
                    <option value="{{ $item->part_code }}">
                        {{ $item->part_code }} - {{ $item->part_name }} || {{ $item->ukuran_label }} mm
                    </option>
                @endforeach
            </select>
            @error('part_code') <small class="text-red-500">{{ $message }}</small> @enderror
        </div>

        <div class="flex gap-4 mb-3">
            <div class="flex-1">
                <label class="block text-sm font-medium mb-1">Label Start</label>
                <input type="number" name="label_start" class="w-full border rounded px-3 py-2" placeholder="1">
                @error('label_start') <small class="text-red-500">{{ $message }}</small> @enderror
            </div>

            <div class="flex-1">
                <label class="block text-sm font-medium mb-1">Label End</label>
                <input type="number" name="label_end" class="w-full border rounded px-3 py-2" placeholder="50">
                @error('label_end') <small class="text-red-500">{{ $message }}</small> @enderror
            </div>
        </div>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
            Generate Barcode
        </button>
    </form>
</div>