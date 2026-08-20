<x-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Custom Barcode Label Generator
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-xl p-8 border border-slate-100">
                <div class="mb-8 border-b pb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800">Generate Custom Barcode Labels</h1>
                        <p class="text-sm text-slate-500 mt-1">Select an item, SPK number, and configure layout options to print labels (12 labels per A4 sheet).</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('barcode.custom.logs') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-sm font-bold rounded-lg border border-indigo-200 transition shadow-sm">
                            <span>📋</span> Lihat History / Log Print
                        </a>
                        <span class="bg-slate-100 text-slate-700 text-xs font-bold px-3 py-2 rounded-lg border border-slate-200">12 Labels / A4 Sheet</span>
                    </div>
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

                        <!-- Remark -->
                        <div class="md:col-span-2">
                            <label for="remark" class="block text-sm font-semibold text-slate-700 mb-2">Remark / Catatan <span class="text-xs font-normal text-slate-500">(Akan dicatat di log tracking)</span></label>
                            <textarea id="remark" name="remark" rows="2" placeholder="Contoh: Print label tambahan sample / rework / penggantian sticker rusak..." class="block w-full px-4 py-2 border border-slate-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
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
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-8 py-3 rounded-lg shadow-md transition duration-150 flex items-center gap-2">
                            <span>🖨️</span> Generate & Print Labels
                        </button>
                    </div>
                </form>

            </div>

            <!-- Print History & Log Tracking -->
            <div class="mt-10 bg-white overflow-hidden shadow-xl sm:rounded-xl p-8 border border-slate-100">
                <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b pb-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                            <span>📋</span> History / Log Tracking Print Barcode
                        </h2>
                        <p class="text-sm text-slate-500 mt-1">Daftar riwayat cetak label barcode custom yang pernah digenerate.</p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 self-start sm:self-auto">
                        Total Riwayat: {{ isset($logs) ? count($logs) : 0 }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-bold text-slate-600 uppercase text-xs">Waktu Print</th>
                                <th class="px-4 py-3 text-left font-bold text-slate-600 uppercase text-xs">User</th>
                                <th class="px-4 py-3 text-left font-bold text-slate-600 uppercase text-xs">Item & SPK</th>
                                <th class="px-4 py-3 text-left font-bold text-slate-600 uppercase text-xs">Label Seq / Total</th>
                                <th class="px-4 py-3 text-left font-bold text-slate-600 uppercase text-xs">Qty/Box</th>
                                <th class="px-4 py-3 text-left font-bold text-slate-600 uppercase text-xs">Shift / WH</th>
                                <th class="px-4 py-3 text-left font-bold text-slate-600 uppercase text-xs">Type</th>
                                <th class="px-4 py-3 text-left font-bold text-slate-600 uppercase text-xs">Remark</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($logs ?? [] as $log)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-4 py-3.5 whitespace-nowrap text-slate-700">
                                        <div class="font-medium text-slate-900">{{ $log->created_at->format('Y-m-d H:i:s') }}</div>
                                        <div class="text-xs text-slate-400">{{ $log->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap text-slate-700">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800">
                                            {{ $log->user_name ?? ($log->user->name ?? 'Guest') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-700">
                                        <div class="font-bold text-indigo-700">{{ $log->item_code }}</div>
                                        <div class="text-xs text-slate-500">{{ $log->item_name }}</div>
                                        <div class="text-xs text-slate-600 font-medium mt-0.5">SPK: <span class="font-mono">{{ $log->spk_number }}</span></div>
                                        @if($log->customer && $log->customer !== '-')
                                            <div class="text-xs text-slate-400">Cust: {{ $log->customer }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap text-slate-700">
                                        <div class="font-semibold text-slate-800">
                                            Label #{{ $log->start_label }} - #{{ $log->end_label }}
                                        </div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 mt-0.5">
                                            {{ $log->total_labels }} label diprint
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap text-slate-700 font-semibold">
                                        {{ number_format($log->quantity) }}
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap text-slate-700 text-xs">
                                        <div>Shift <span class="font-bold">{{ $log->shift }}</span></div>
                                        <div class="text-slate-400">WH: {{ $log->warehouse }}</div>
                                        @if($log->operator && $log->operator !== '-')
                                            <div class="text-slate-400">Op: {{ $log->operator }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        @if($log->is_trial)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-700 border border-red-200">
                                                TRIAL
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                Standard
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-700 text-xs max-w-xs break-words">
                                        @if($log->remark)
                                            <span class="text-slate-800 bg-amber-50 border border-amber-200 px-2 py-1 rounded inline-block">
                                                {{ $log->remark }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 italic">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                        Belum ada data riwayat print barcode.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
