<div class="mb-4">
    <label for="mold_name" class="block font-bold mb-1">Mold Name</label>
    <input type="text" name="mold_name" id="mold_name" class="w-full border border-gray-300 p-2 rounded"
        value="{{ old('mold_name', $waitingPurchaseOrder->mold_name ?? '') }}" required>
    @error('mold_name')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

<div class="mb-4">
    <label for="capture_photo" class="block font-bold mb-1">Capture Photo</label>
    <input type="file" name="capture_photo" id="capture_photo" class="w-full border border-gray-300 p-2 rounded"
        {{ isset($waitingPurchaseOrder) ? '' : 'required' }} accept="image/*" onchange="previewImage(event)">

    <div class="mt-2">
        <img id="photo_preview"
            src="{{ old('capture_photo') ? asset('storage/uploads/' . old('capture_photo')) : (isset($waitingPurchaseOrder) ? asset('storage/uploads/' . $waitingPurchaseOrder->capture_photo_path) : '#') }}"
            alt="Preview"
            class="h-36 object-contain max-w-sm rounded shadow-md {{ old('capture_photo') || isset($waitingPurchaseOrder) ? '' : 'hidden' }}">
    </div>

    @error('capture_photo')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>


<div class="mb-4">
    <label for="process" class="block font-bold mb-1">Process</label>
    <input type="text" name="process" id="process" class="w-full border border-gray-300 p-2 rounded"
        value="{{ old('process', $waitingPurchaseOrder->process ?? '') }}" required>
    @error('process')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

<div class="mb-4">
    <label for="price" class="block font-bold mb-1">Price</label>
    <input type="text" name="price" id="price" class="w-full border border-gray-300 p-2 rounded"
        value="{{ old('price', isset($waitingPurchaseOrder) ? number_format($waitingPurchaseOrder->price, 2) : '') }}"
        required oninput="formatPrice(event)">
    @error('price')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

<div class="mb-4">
    <label for="quotation_no" class="block font-bold mb-1">Quotation No</label>
    <input type="text" name="quotation_no" id="quotation_no" class="w-full border border-gray-300 p-2 rounded"
        value="{{ old('quotation_no', $waitingPurchaseOrder->quotation_no ?? '') }}" required>
    @error('quotation_no')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

<div class="mb-4">
    <label for="remark" class="block font-bold mb-1">Remark</label>
    <textarea name="remark" id="remark" class="w-full border border-gray-300 p-2 rounded">{{ old('remark', $waitingPurchaseOrder->remark ?? '') }}</textarea>
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
