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

@if (!isset($waitingPurchaseOrder))
    <div class="mb-6">
        <label for="attached_files" class="block font-bold mb-1">Attach Files</label>
        <div id="file_list" class="my-2">
        </div>
        <input type="file" name="attached_files[]" id="attached_files"
            class="w-full border border-gray-300 p-2 rounded" accept="*/*" multiple onchange="previewFiles(event)">
        <span class="text-gray-500 text-sm my-1">
            Maximum file size per file is 4 MB.
        </span>
        @error('attached_files')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </div>
@endif

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
    function previewFiles(event) {
        const files = event.target.files; // Get the files from the input
        const fileList = document.getElementById('file_list'); // Target the file list container
        fileList.innerHTML = ''; // Clear previous file list

        if (files.length > 0) {
            for (const file of files) {
                // Create a list item for each file
                const fileItem = document.createElement('div');
                fileItem.classList.add('mb-2', 'flex', 'items-center', 'gap-2');

                const fileIcon = document.createElement('span');
                fileIcon.textContent = '📄'; // A generic icon for files

                const fileName = document.createElement('span');
                fileName.textContent = file.name;

                const fileSize = document.createElement('span');
                fileSize.textContent = `(${(file.size / 1024 / 1024).toFixed(2)} MB)`;
                fileSize.classList.add('text-gray-500', 'text-sm');

                fileItem.appendChild(fileIcon);
                fileItem.appendChild(fileName);
                fileItem.appendChild(fileSize);

                fileList.appendChild(fileItem);
            }
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
