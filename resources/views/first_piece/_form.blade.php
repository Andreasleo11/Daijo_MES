<div class="bg-white shadow-xl rounded-lg overflow-hidden mb-6 border border-gray-200">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-700 to-indigo-800 text-white p-6">
        <div class="flex flex-wrap justify-between items-center">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight">PT. DAIJO INDUSTRIAL</h1>
                <p class="text-sm font-semibold opacity-90 mt-1">Second Process Department</p>
            </div>
            <div class="text-right text-xs md:text-sm space-y-1 bg-white/10 p-3 rounded-lg backdrop-blur-sm mt-4 md:mt-0">
                <div><span class="font-bold">Dokumen No:</span> DI-F-P/PR/07/SP-013</div>
                <div><span class="font-bold">Form:</span> FIRST PIECE SAMPLE / INSPECTION</div>
            </div>
        </div>
        <div class="text-center mt-6">
            <h2 class="text-2xl font-bold uppercase tracking-wider">
                {{ $inspection->exists ? 'Edit First Piece Inspection' : 'First Piece Sample / Inspection' }}
            </h2>
        </div>
    </div>

    <div class="p-6 space-y-6">

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded text-sm mb-4">
                <div class="font-bold">Please fix the following errors:</div>
                <ul class="list-disc list-inside mt-1">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- General Info Header Fields -->
        <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Header Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Date <span class="text-red-500">*</span></label>
                    <input type="date" name="date" value="{{ old('date', $inspection->date ?? date('Y-m-d')) }}" class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Model <span class="text-red-500">*</span></label>
                    <input type="text" name="model" value="{{ old('model', $inspection->model) }}" placeholder="e.g. KS PE" class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Part Name <span class="text-red-500">*</span></label>
                    <input type="text" name="part_name" value="{{ old('part_name', $inspection->part_name) }}" placeholder="e.g. Molding Side REF" class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Part No. <span class="text-red-500">*</span></label>
                    <input type="text" name="part_number" value="{{ old('part_number', $inspection->part_number) }}" placeholder="e.g. 401-41019967" class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Paint Code / Mat Code</label>
                    <input type="text" name="paint_code" value="{{ old('paint_code', $inspection->paint_code) }}" placeholder="e.g. DR 249 - 8M8" class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Thinner Code</label>
                    <input type="text" name="thinner_code" value="{{ old('thinner_code', $inspection->thinner_code) }}" placeholder="e.g. T971" class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Ink Code</label>
                    <input type="text" name="ink_code" value="{{ old('ink_code', $inspection->ink_code) }}" class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Viscosity</label>
                    <input type="text" name="viscosity" value="{{ old('viscosity', $inspection->viscosity) }}" class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Cycle Time</label>
                    <input type="text" name="cycle_time" value="{{ old('cycle_time', $inspection->cycle_time) }}" class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Time Submit</label>
                    <input type="time" name="time_submit" value="{{ old('time_submit', $inspection->time_submit) }}" class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>
            </div>
        </div>

        <!-- Quality Control Issue Form / Check Points Table -->
        <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">QUALITY CONTROL ISSUE FOR APPROVED</h3>
            
            @php
                $existingResults = old('check_results', $inspection->check_results ?? []);
                if (empty($existingResults)) {
                    $existingResults = array_map(function($cp) {
                        return ['check_point' => $cp, 'method' => 'Visual', 'result' => 'OK', 'judgement' => 'OK'];
                    }, $defaultCheckPoints);
                }
            @endphp

            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse border border-gray-300 bg-white rounded">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700 uppercase text-xs">
                            <th class="border border-gray-300 px-4 py-2 text-left w-1/3">Check Point</th>
                            <th class="border border-gray-300 px-4 py-2 text-center w-1/4">Method Check</th>
                            <th class="border border-gray-300 px-4 py-2 text-center w-1/4">Inspection Result</th>
                            <th class="border border-gray-300 px-4 py-2 text-center w-1/6">Judgment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($existingResults as $idx => $row)
                            <tr class="hover:bg-gray-50">
                                <td class="border border-gray-300 px-4 py-2 font-bold text-gray-800">
                                    <input type="hidden" name="check_results[{{ $idx }}][check_point]" value="{{ $row['check_point'] }}">
                                    {{ $row['check_point'] }}
                                </td>
                                <td class="border border-gray-300 px-4 py-2 text-center text-gray-600">
                                    <input type="hidden" name="check_results[{{ $idx }}][method]" value="Visual">
                                    Visual
                                </td>
                                <td class="border border-gray-300 px-4 py-2 text-center">
                                    <select name="check_results[{{ $idx }}][result]" class="check-result-select rounded border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500 py-1" data-idx="{{ $idx }}">
                                        <option value="OK" {{ ($row['result'] ?? 'OK') === 'OK' ? 'selected' : '' }}>OK</option>
                                        <option value="NG" {{ ($row['result'] ?? '') === 'NG' ? 'selected' : '' }}>NG</option>
                                    </select>
                                </td>
                                <td class="border border-gray-300 px-4 py-2 text-center font-extrabold">
                                    <input type="hidden" name="check_results[{{ $idx }}][judgement]" id="judgement-input-{{ $idx }}" value="{{ $row['judgement'] ?? 'OK' }}">
                                    <span id="judgement-badge-{{ $idx }}" class="{{ ($row['judgement'] ?? 'OK') === 'OK' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $row['judgement'] ?? 'OK' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Remarks -->
        <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
            <label class="block text-sm font-bold text-gray-700 mb-2">Remark / Notes</label>
            <textarea name="remark" rows="3" class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Any quality issue notes or special instructions...">{{ old('remark', $inspection->remark) }}</textarea>
        </div>

        <!-- Physical Proof & Photos Attachment Section -->
        <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Physical Sample & Proof Photos
            </h3>
            <p class="text-xs text-gray-500 mb-4">Attach photo proof of first piece inspection samples, master color comparison, or defects found.</p>

            @if($inspection->exists && $inspection->attachments->count() > 0)
                <div class="mb-4">
                    <div class="text-xs font-bold text-gray-700 uppercase mb-2">Existing Attachments:</div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        @foreach($inspection->attachments as $attach)
                            <div class="border rounded p-2 bg-white flex flex-col items-center relative group">
                                <a href="{{ $attach->url }}" target="_blank" class="block w-full text-center">
                                    @if(str_contains($attach->mime_type ?? '', 'image'))
                                        <img src="{{ $attach->url }}" alt="{{ $attach->label }}" class="h-24 object-cover mx-auto rounded border">
                                    @else
                                        <div class="h-24 flex items-center justify-center bg-gray-100 rounded text-xs text-gray-500 font-bold">
                                            {{ $attach->original_name }}
                                        </div>
                                    @endif
                                    <div class="text-[11px] text-gray-600 truncate mt-1">{{ $attach->label ?? $attach->original_name }}</div>
                                </a>
                                <label class="mt-2 flex items-center text-xs text-red-600 cursor-pointer">
                                    <input type="checkbox" name="delete_attachments[]" value="{{ $attach->id }}" class="rounded text-red-600 mr-1">
                                    Delete
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-500 transition bg-white" id="file-dropzone">
                <input type="file" name="qc_files[]" multiple accept="image/*" class="hidden" id="qc-files-input">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="mt-2 text-sm text-gray-600">
                    <button type="button" onclick="document.getElementById('qc-files-input').click()" class="font-bold text-blue-600 hover:text-blue-500">
                        Upload inspection photos
                    </button>
                    <span> or drag and drop</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">PNG, JPG, WEBP up to 5MB each</p>
                <div id="file-preview-list" class="mt-4 text-left text-xs space-y-1"></div>
            </div>
        </div>

        <!-- Form Submit Bar -->
        <div class="flex justify-between items-center pt-4 border-t border-gray-200">
            <a href="{{ route('first-piece-inspections.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2.5 px-6 rounded transition text-sm">
                Cancel
            </a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-8 rounded shadow-lg transition text-sm flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                {{ $inspection->exists ? 'Save Changes' : 'Submit Inspection' }}
            </button>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dynamic result to judgement synchronization
    const resultSelects = document.querySelectorAll('.check-result-select');
    resultSelects.forEach(select => {
        select.addEventListener('change', function() {
            const idx = this.dataset.idx;
            const val = this.value;
            const badge = document.getElementById('judgement-badge-' + idx);
            const input = document.getElementById('judgement-input-' + idx);
            
            if (badge && input) {
                badge.textContent = val;
                input.value = val;
                if (val === 'OK') {
                    badge.className = 'text-green-600 font-extrabold';
                } else {
                    badge.className = 'text-red-600 font-extrabold';
                }
            }
        });
    });

    // File preview
    const fileInput = document.getElementById('qc-files-input');
    const previewList = document.getElementById('file-preview-list');

    if (fileInput && previewList) {
        fileInput.addEventListener('change', function() {
            previewList.innerHTML = '';
            Array.from(this.files).forEach((file, index) => {
                const item = document.createElement('div');
                item.className = 'flex items-center space-x-2 text-gray-700 bg-blue-50 p-2 rounded';
                item.innerHTML = `<span class="font-bold">📎 ${file.name}</span> <span class="text-gray-500">(${(file.size/1024/1024).toFixed(2)} MB)</span>`;
                previewList.appendChild(item);
            });
        });
    }
});
</script>
