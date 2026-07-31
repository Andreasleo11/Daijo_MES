<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                {{ __('Edit Work Order') }}: {{ $workOrder->wo_number }}
            </h2>
            <a href="{{ route('sp-work-orders.show', $workOrder->id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg shadow-sm text-xs transition">
                ← Back to Details
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                
                <form action="{{ route('sp-work-orders.update', $workOrder->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- WO Number --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Work Order No. (Readonly)</label>
                            <input type="text" name="wo_number" value="{{ $workOrder->wo_number }}" readonly
                                class="w-full bg-gray-100 border-gray-300 rounded-lg text-sm font-bold text-blue-600 cursor-not-allowed">
                        </div>

                        {{-- Planned Date --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Planned Date *</label>
                            <input type="date" name="planned_date" value="{{ old('planned_date', $workOrder->planned_date) }}" required
                                class="w-full border-gray-300 rounded-lg text-sm">
                        </div>

                        {{-- Unit / Line --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Production Line *</label>
                            <select name="unit_line" required class="w-full border-gray-300 rounded-lg text-sm">
                                <option value="">-- Select Line --</option>
                                @foreach($lines as $l)
                                    <option value="{{ $l }}" {{ old('unit_line', $workOrder->unit_line) == $l ? 'selected' : '' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Shift --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Shift *</label>
                            <select name="shift" required class="w-full border-gray-300 rounded-lg text-sm">
                                <option value="1" {{ old('shift', $workOrder->shift) == '1' ? 'selected' : '' }}>Shift 1 (07:30 - 15:30)</option>
                                <option value="2" {{ old('shift', $workOrder->shift) == '2' ? 'selected' : '' }}>Shift 2 (15:30 - 23:30)</option>
                                <option value="3" {{ old('shift', $workOrder->shift) == '3' ? 'selected' : '' }}>Shift 3 (23:30 - 07:30)</option>
                            </select>
                        </div>

                        {{-- Process --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Second Process Type *</label>
                            <select name="process_prod" required class="w-full border-gray-300 rounded-lg text-sm">
                                <option value="">-- Select Process --</option>
                                @foreach($processes as $p)
                                    <option value="{{ $p }}" {{ old('process_prod', $workOrder->process_prod) == $p ? 'selected' : '' }}>{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Customer --}}
                        <div class="relative">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Customer Name *</label>
                            <input type="text" name="customer" id="customer" value="{{ old('customer', $workOrder->customer) }}" placeholder="Search or enter Customer..." required autocomplete="off"
                                class="w-full border-gray-300 rounded-lg text-sm">
                            <div id="customer-dropdown" class="absolute left-0 right-0 mt-1 max-h-60 overflow-y-auto bg-white border border-gray-200 rounded-lg shadow-lg z-50 hidden"></div>
                        </div>

                        {{-- Part Number --}}
                        <div class="relative">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Part Number *</label>
                            <input type="text" name="part_number" id="part_number" value="{{ old('part_number', $workOrder->part_number) }}" placeholder="Search Part Number..." required autocomplete="off"
                                class="w-full border-gray-300 rounded-lg text-sm">
                            <div id="part-number-dropdown" class="absolute left-0 right-0 mt-1 max-h-60 overflow-y-auto bg-white border border-gray-200 rounded-lg shadow-lg z-50 hidden"></div>
                        </div>

                        {{-- Part Name --}}
                        <div class="relative">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Part Name *</label>
                            <input type="text" name="part_name" id="part_name" value="{{ old('part_name', $workOrder->part_name) }}" placeholder="Search or enter Part Name..." required autocomplete="off"
                                class="w-full border-gray-300 rounded-lg text-sm">
                            <div id="part-name-dropdown" class="absolute left-0 right-0 mt-1 max-h-60 overflow-y-auto bg-white border border-gray-200 rounded-lg shadow-lg z-50 hidden"></div>
                        </div>

                        {{-- Model --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Model Code (Optional)</label>
                            <input type="text" name="model" id="model" value="{{ old('model', $workOrder->model) }}" placeholder="e.g. D01D"
                                class="w-full border-gray-300 rounded-lg text-sm">
                        </div>

                        {{-- Target Qty --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Target Production Qty (Pcs) *</label>
                            <input type="number" name="target_qty" value="{{ old('target_qty', $workOrder->target_qty) }}" min="1" required
                                class="w-full border-gray-300 rounded-lg text-sm font-bold text-green-600">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-gray-200 pt-4">
                        <a href="{{ route('sp-work-orders.show', $workOrder->id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg text-sm transition">
                            Cancel
                        </a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg text-sm shadow transition">
                            Update Work Order
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    {{-- Autocomplete Suggestions Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function setupAutocomplete(inputId, dropdownId, url, onSelect) {
                const input = document.getElementById(inputId);
                const dropdown = document.getElementById(dropdownId);
                if (!input || !dropdown) return;

                let debounceTimer;
                input.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    const query = this.value.trim();
                    if (query.length < 1) {
                        dropdown.classList.add('hidden');
                        return;
                    }

                    debounceTimer = setTimeout(() => {
                        fetch(`${url}?query=${encodeURIComponent(query)}`)
                            .then(res => res.json())
                            .then(data => {
                                dropdown.innerHTML = '';
                                if (!data || data.length === 0) {
                                    dropdown.classList.add('hidden');
                                    return;
                                }

                                data.forEach(item => {
                                    const div = document.createElement('div');
                                    div.className = 'px-4 py-2.5 hover:bg-blue-50 cursor-pointer text-xs border-b border-gray-100 last:border-b-0 text-gray-800 transition flex justify-between items-center';
                                    if (item.item_code) {
                                        div.innerHTML = `
                                            <div>
                                                <span class="font-bold text-blue-700">${item.item_code}</span> 
                                                <span class="text-gray-600 font-medium ml-1">${item.item_name || item.item_description || ''}</span>
                                            </div>
                                            <span class="text-[10px] text-gray-400 uppercase font-mono">${item.customer_name || ''}</span>
                                        `;
                                    } else if (item.name || item.customer_name) {
                                        div.textContent = item.name || item.customer_name;
                                    }

                                    div.addEventListener('click', () => {
                                        onSelect(item);
                                        dropdown.classList.add('hidden');
                                    });
                                    dropdown.appendChild(div);
                                });
                                dropdown.classList.remove('hidden');
                            })
                            .catch(err => console.error(err));
                    }, 300);
                });

                document.addEventListener('click', function(e) {
                    if (e.target !== input && !dropdown.contains(e.target)) {
                        dropdown.classList.add('hidden');
                    }
                });
            }

            // Populate form when item is selected
            function populateFromItem(item) {
                if (item.item_code) document.getElementById('part_number').value = item.item_code;
                if (item.item_name || item.item_description) {
                    document.getElementById('part_name').value = item.item_name || item.item_description;
                }
                if (item.project_code) {
                    document.getElementById('model').value = item.project_code;
                }
                if (item.customer_name) {
                    document.getElementById('customer').value = item.customer_name;
                }
            }

            // Bind Autocompletes
            setupAutocomplete('part_number', 'part-number-dropdown', '{{ route('second-process-reports.search-items') }}', populateFromItem);
            setupAutocomplete('part_name', 'part-name-dropdown', '{{ route('second-process-reports.search-items') }}', populateFromItem);
            setupAutocomplete('customer', 'customer-dropdown', '{{ route('second-process-reports.search-customers') }}', function(item) {
                document.getElementById('customer').value = item.customer_name || item.name || '';
            });
        });
    </script>
</x-app-layout>
