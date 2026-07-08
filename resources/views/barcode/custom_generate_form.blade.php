<x-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Custom Barcode Label Generator
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-xl p-8 border border-slate-100">
                <div class="mb-8 border-b pb-4 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800">Generate Custom Barcode Labels</h1>
                        <p class="text-sm text-slate-500 mt-1">Select an item, SPK number, and configure layout options to print labels (12 labels per A4 sheet).</p>
                    </div>
                    <span class="bg-indigo-50 text-indigo-700 text-xs font-bold px-3 py-1.5 rounded-full border border-indigo-100">12 Labels / A4 Sheet</span>
                </div>

                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm text-sm" role="alert">
                        <strong class="font-bold">Please correct the following errors:</strong>
                        <ul class="list-disc list-inside mt-2 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('barcode.custom.print') }}" target="_blank" class="space-y-6">
                    @csrf

                    <!-- Grid Layout for Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- Item Code -->
                        <div>
                            <label for="item_code" class="block text-sm font-semibold text-slate-700 mb-2">Item Code <span class="text-red-500">*</span></label>
                            <select id="item_code" name="item_code" required class="block w-full px-4 py-2 border border-slate-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">-- Select Item Code --</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->item_code }}" data-name="{{ $item->item_name }}">
                                        {{ $item->item_code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Item Name (Auto Filled) -->
                        <div>
                            <label for="item_name" class="block text-sm font-semibold text-slate-700 mb-2">Item Name</label>
                            <input type="text" id="item_name" readonly placeholder="Select an Item Code first" class="block w-full px-4 py-2 border border-slate-200 bg-slate-50 text-slate-500 rounded-lg shadow-sm focus:outline-none">
                        </div>

                        <!-- SPK Number Selection -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label for="spk_select" class="block text-sm font-semibold text-slate-700">SPK Number <span class="text-red-500">*</span></label>
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" id="manual_spk_toggle" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                    <label for="manual_spk_toggle" class="text-xs text-slate-600 cursor-pointer select-none">Input Manual</label>
                                </div>
                            </div>

                            <!-- Dropdown for SPK (Default) -->
                            <div id="spk_select_container">
                                <select id="spk_select" name="spk_number" class="block w-full px-4 py-2 border border-slate-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">-- Select Item Code First --</option>
                                </select>
                            </div>

                            <!-- Manual Text Input (Hidden initially) -->
                            <div id="spk_input_container" class="hidden">
                                <input type="text" id="spk_input" placeholder="Type SPK Number manually" class="block w-full px-4 py-2 border border-slate-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>

                        <!-- Quantity per Label -->
                        <div>
                            <label for="quantity" class="block text-sm font-semibold text-slate-700 mb-2">Quantity per Label <span class="text-red-500">*</span></label>
                            <input type="number" id="quantity" name="quantity" min="1" required placeholder="e.g. 80" class="block w-full px-4 py-2 border border-slate-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <!-- Warehouse -->
                        <div>
                            <label for="warehouse" class="block text-sm font-semibold text-slate-700 mb-2">Warehouse <span class="text-red-500">*</span></label>
                            <input type="text" id="warehouse" name="warehouse" value="WFI" required placeholder="e.g. WFI" class="block w-full px-4 py-2 border border-slate-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <!-- Shift -->
                        <div>
                            <label for="shift" class="block text-sm font-semibold text-slate-700 mb-2">Shift <span class="text-red-500">*</span></label>
                            <select id="shift" name="shift" required class="block w-full px-4 py-2 border border-slate-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="I">Shift I</option>
                                <option value="II">Shift II</option>
                                <option value="III">Shift III</option>
                            </select>
                        </div>

                        <!-- Label Range Start -->
                        <div>
                            <label for="start_label" class="block text-sm font-semibold text-slate-700 mb-2">Start Label Sequence <span class="text-red-500">*</span></label>
                            <input type="number" id="start_label" name="start_label" min="1" value="1" required class="block w-full px-4 py-2 border border-slate-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <!-- Label Range End -->
                        <div>
                            <label for="end_label" class="block text-sm font-semibold text-slate-700 mb-2">End Label Sequence <span class="text-red-500">*</span></label>
                            <input type="number" id="end_label" name="end_label" min="1" value="1" required class="block w-full px-4 py-2 border border-slate-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <!-- Production Date -->
                        <div>
                            <label for="prod_date" class="block text-sm font-semibold text-slate-700 mb-2">Production Date</label>
                            <input type="date" id="prod_date" name="prod_date" value="{{ today()->toDateString() }}" class="block w-full px-4 py-2 border border-slate-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <!-- Operator Name -->
                        <div>
                            <label for="operator" class="block text-sm font-semibold text-slate-700 mb-2">Operator Name</label>
                            <input type="text" id="operator" name="operator" placeholder="Leave empty for blank line" class="block w-full px-4 py-2 border border-slate-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <!-- Customer -->
                        <div class="md:col-span-2">
                            <label for="customer" class="block text-sm font-semibold text-slate-700 mb-2">Customer</label>
                            <input type="text" id="customer" name="customer" placeholder="Leave empty for blank line" class="block w-full px-4 py-2 border border-slate-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                    </div>

                    <!-- Options -->
                    <div class="pt-6 border-t flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="is_trial" name="is_trial" value="1" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                <span class="ml-3 text-sm font-semibold text-slate-700 select-none">TRIAL Label (Adds '\tTRIAL' to QR data)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="pt-6 border-t flex justify-end">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-8 py-3 rounded-lg shadow-md transition duration-150">
                            🖨️ Generate & Print Labels
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const itemCodeSelect = document.getElementById('item_code');
            const itemNameInput = document.getElementById('item_name');
            const spkSelect = document.getElementById('spk_select');
            const manualSpkToggle = document.getElementById('manual_spk_toggle');
            const spkSelectContainer = document.getElementById('spk_select_container');
            const spkInputContainer = document.getElementById('spk_input_container');
            const spkInput = document.getElementById('spk_input');

            // Handle Item Code Change (Auto fill Name & fetch SPKs via AJAX)
            itemCodeSelect.addEventListener('change', function () {
                const selectedOption = itemCodeSelect.options[itemCodeSelect.selectedIndex];
                const itemName = selectedOption.getAttribute('data-name');
                const itemCode = this.value;

                itemNameInput.value = itemName || '';

                if (!itemCode) {
                    spkSelect.innerHTML = '<option value="">-- Select Item Code First --</option>';
                    return;
                }

                spkSelect.innerHTML = '<option value="">Loading SPKs...</option>';

                fetch(`/api/get-spks-by-item?item_code=${encodeURIComponent(itemCode)}`)
                    .then(response => response.json())
                    .then(spks => {
                        spkSelect.innerHTML = '';
                        if (spks.length === 0) {
                            spkSelect.innerHTML = '<option value="">No SPK found in history</option>';
                            // Auto toggle manual input if no SPK is found
                            manualSpkToggle.checked = true;
                            triggerManualToggle(true);
                        } else {
                            spkSelect.innerHTML = '<option value="">-- Select SPK Number --</option>';
                            spks.forEach(spk => {
                                const option = document.createElement('option');
                                option.value = spk;
                                option.textContent = spk;
                                spkSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(err => {
                        console.error('Error fetching SPKs:', err);
                        spkSelect.innerHTML = '<option value="">Error loading SPKs</option>';
                    });
            });

            // Handle Manual SPK Toggle
            manualSpkToggle.addEventListener('change', function () {
                triggerManualToggle(this.checked);
            });

            function triggerManualToggle(isManual) {
                if (isManual) {
                    spkSelectContainer.classList.add('hidden');
                    spkSelect.removeAttribute('name');
                    spkSelect.removeAttribute('required');

                    spkInputContainer.classList.remove('hidden');
                    spkInput.setAttribute('name', 'spk_number');
                    spkInput.setAttribute('required', 'required');
                } else {
                    spkInputContainer.classList.add('hidden');
                    spkInput.removeAttribute('name');
                    spkInput.removeAttribute('required');

                    spkSelectContainer.classList.remove('hidden');
                    spkSelect.setAttribute('name', 'spk_number');
                    spkSelect.setAttribute('required', 'required');
                }
            }
        });
    </script>
</x-dashboard-layout>
