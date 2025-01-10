<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div>
        <label for="mold_name" class="block font-bold mb-1">Mold Name</label>
        <input type="text" name="mold_name" id="mold_name" class="w-full border border-gray-300 p-2 rounded"
            value="{{ old('mold_name', $waitingPurchaseOrder->mold_name ?? '') }}" required
            placeholder="MOLD - DISS &amp; DAIJOMESS&reg;2025">
        @error('mold_name')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="quotation_no" class="block font-bold mb-1">Quotation No</label>
        <input type="text" name="quotation_no" id="quotation_no" class="w-full border border-gray-300 p-2 rounded"
            value="{{ old('quotation_no', $waitingPurchaseOrder->quotation_no ?? '') }}" required
            placeholder="QK-01102025-MONLAY">
        @error('quotation_no')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="mb-6">
    <label for="process" class="block font-bold mb-1">Process</label>
    <textarea name="process" id="process" class="w-full border border-gray-300 p-2 rounded" required
        placeholder="MAKE NEW WEBSITE EVERYDAY">{{ old('process', $waitingPurchaseOrder->process ?? '') }}</textarea>
    @error('process')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

<div class="mb-6">
    <label for="capture_photo" class="block font-bold mb-1">Capture Photo</label>
    <div class="my-2">
        <img id="photo_preview"
            src="{{ old('capture_photo') ? asset('storage/uploads/' . old('capture_photo')) : (isset($waitingPurchaseOrder) ? asset('storage/uploads/' . $waitingPurchaseOrder->capture_photo_path) : '#') }}"
            alt="Preview"
            class="h-36 object-contain max-w-sm rounded shadow-md {{ old('capture_photo') || isset($waitingPurchaseOrder) ? '' : 'hidden' }}">
    </div>
    <input type="file" name="capture_photo" id="capture_photo" class="w-full border border-gray-300 p-2 rounded"
        {{ isset($waitingPurchaseOrder) ? '' : 'required' }} accept="image/*" onchange="previewImage(event)">
    <span class="text-gray-500 text-sm my-1">
        Maximum file size 4 MB.
    </span>
    @error('capture_photo')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

<div class="mb-6">
    <label for="price" class="block font-bold mb-1">Price</label>
    <div class="relative">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 font-bold">Rp</span>
        <input type="text" name="price" id="price" class="w-full border border-gray-300 p-2 rounded pl-10"
            value="{{ old('price', isset($waitingPurchaseOrder) ? number_format($waitingPurchaseOrder->price, 2) : '') }}"
            required oninput="formatPrice(event)" placeholder="85,000,000">
    </div>
    @error('price')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

<div class="mb-6">
    <label for="remark" class="block font-bold mb-1">Remark <span class="text-gray-400">(Optional)</span></label>
    <textarea name="remark" id="remark" class="w-full border border-gray-300 p-2 rounded"
        placeholder="Info tanggal xx-xx-xxxx po baru akan dikirim">{{ old('remark', $waitingPurchaseOrder->remark ?? '') }}</textarea>
    @error('remark')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

<script>
    function previewImage(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('photo_preview');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
            }
            reader.readAsDataURL(file);
        }
    }

    function formatPrice(event) {
        let input = event.target.value.replace(/,/g, ''); // Remove existing commas
        if (!isNaN(input)) {
            event.target.value = parseFloat(input).toLocaleString('en-US'); // Format with thousand separators
        } else {
            event.target.value = '';
        }
    }
</script>
