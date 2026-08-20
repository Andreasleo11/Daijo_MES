<!-- Main Form Card -->
<div class="bg-white shadow-xl rounded-lg overflow-hidden mb-6 border border-gray-200">

    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-700 to-indigo-800 text-white p-6">
        <div class="flex flex-wrap justify-between items-center">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight">PT. DAIJO INDUSTRIAL</h1>
                <p class="text-sm font-semibold opacity-90 mt-1">Second Process Departement</p>
            </div>
            <div
                class="text-right text-xs md:text-sm space-y-1 bg-white/10 p-3 rounded-lg backdrop-blur-sm mt-4 md:mt-0">
                <div><span class="font-bold">No. Dokumen:</span> DI-F-P/PR/07/SP-001</div>
                <div><span class="font-bold">Tgl. Dikeluarkan:</span> 04 Januari 2023</div>
                <div><span class="font-bold">Mulai berlaku:</span> 08 Desember 2025</div>
                <div><span class="font-bold">Revisi / Halaman:</span> 2 / 1 of 1</div>
            </div>
        </div>
        <div class="text-center mt-6">
            <h2 class="text-2xl font-bold uppercase tracking-wider">
                {{ $report->exists ? 'Edit Laporan Produksi Harian' : 'Laporan Produksi Harian' }}</h2>
        </div>
    </div>

    <!-- Option 2: Sticky Tabbed Navigation -->
    <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
        <nav class="-mb-px flex flex-wrap justify-between sm:justify-start gap-2 md:gap-6" aria-label="Tabs"
            id="form-tabs-navigation">
            <button type="button" data-tab="setup"
                class="tab-btn active border-blue-600 text-blue-600 whitespace-nowrap py-3 px-3 border-b-2 font-bold text-sm flex items-center transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                1. Setup & Manpower
            </button>
            <button type="button" data-tab="materials"
                class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 px-3 border-b-2 font-bold text-sm flex items-center transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z">
                    </path>
                </svg>
                2. Materials
            </button>
            <button type="button" data-tab="production"
                class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 px-3 border-b-2 font-bold text-sm flex items-center transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                    </path>
                </svg>
                3. Production Logs & NG
            </button>
            <button type="button" data-tab="handover"
                class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 px-3 border-b-2 font-bold text-sm flex items-center transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                    </path>
                </svg>
                4. Handover & Signs
            </button>
        </nav>
    </div>

    <!-- Form Content Body -->
    <div class="p-6">

        <!-- Validation Warnings Banner (Chronological Validation) -->
        <div id="totals-validation-message" class="mb-6"></div>

        <!-- TAB 1: SETUP & MANPOWER -->
        <div id="tab-content-setup" class="tab-pane space-y-8">

            <!-- Header Logistics Section -->
            <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Shift Logistics & Setup
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tanggal</label>
                        <input type="date" name="date" value="{{ old('date', $report->date ?? date('Y-m-d')) }}"
                            class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                            required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Unit /
                            Line</label>
                        <select name="unit_line"
                            class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                            required>
                            <option value="">-- Select Unit / Line --</option>
                            @php
                                $unitLineOptions = [
                                    'Line A',
                                    'Line B',
                                    'Line C',
                                    'Line D',
                                    'Buffing 1',
                                    'Buffing 2',
                                    'Buffing 3',
                                    'Buffing 4',
                                    'Buffing 5',
                                    'Buffing 6',
                                    'Buffing 7',
                                    'Buffing 8',
                                    'Area Amplas/Treatment',
                                    'Packing 1',
                                    'Packing 2',
                                    'Packing 3',
                                    'Area Assy',
                                ];
                                $currentUnitLine = old('unit_line', $report->unit_line);
                            @endphp
                            @foreach ($unitLineOptions as $opt)
                                <option value="{{ $opt }}" {{ $currentUnitLine == $opt ? 'selected' : '' }}>
                                    {{ $opt }}</option>
                            @endforeach
                            @if ($currentUnitLine && !in_array($currentUnitLine, $unitLineOptions))
                                <option value="{{ $currentUnitLine }}" selected>{{ $currentUnitLine }}</option>
                            @endif
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Shift</label>
                        <select name="shift"
                            class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                            required>
                            <option value="1" {{ old('shift', $report->shift) == '1' ? 'selected' : '' }}>Shift 1
                            </option>
                            <option value="2" {{ old('shift', $report->shift) == '2' ? 'selected' : '' }}>Shift 2
                            </option>
                            <option value="3" {{ old('shift', $report->shift) == '3' ? 'selected' : '' }}>Shift 3
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Proses
                            Prod</label>
                        <select name="process_prod"
                            class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                            required>
                            <option value="Painting"
                                {{ old('process_prod', $report->process_prod ?? 'Painting') == 'Painting' ? 'selected' : '' }}>
                                Painting</option>
                            <option value="Buffing"
                                {{ old('process_prod', $report->process_prod) == 'Buffing' ? 'selected' : '' }}>
                                Buffing</option>
                            <option value="Amplas"
                                {{ old('process_prod', $report->process_prod) == 'Amplas' ? 'selected' : '' }}>
                                Amplas</option>
                            <option value="Treatment"
                                {{ old('process_prod', $report->process_prod) == 'Treatment' ? 'selected' : '' }}>
                                Treatment</option>
                            <option value="Packing"
                                {{ old('process_prod', $report->process_prod) == 'Packing' ? 'selected' : '' }}>
                                Packing</option>
                            <option value="Rework"
                                {{ old('process_prod', $report->process_prod) == 'Rework' ? 'selected' : '' }}>
                                Rework</option>
                            <option value="Repair"
                                {{ old('process_prod', $report->process_prod) == 'Repair' ? 'selected' : '' }}>
                                Repair</option>
                            <option value="Assy"
                                {{ old('process_prod', $report->process_prod) == 'Assy' ? 'selected' : '' }}>
                                Assy</option>
                        </select>
                    </div>
                    <input type="hidden" name="status" id="status-field"
                        value="{{ old('status', $report->status) }}">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tujuan
                            Output</label>
                        <select name="output_destination"
                            class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value=""
                                {{ empty(old('output_destination', $report->output_destination)) ? 'selected' : '' }}>
                                -- Select Next Step --</option>
                            <option value="fg"
                                {{ old('output_destination', $report->output_destination) == 'fg' ? 'selected' : '' }}>
                                Finished Goods (FG)</option>
                            <option value="buffing"
                                {{ old('output_destination', $report->output_destination) == 'buffing' ? 'selected' : '' }}>
                                Buffing</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Part
                            Number</label>
                        <div class="relative">
                            <input type="text" name="part_number" id="part_number"
                                value="{{ old('part_number', $report->part_number) }}"
                                placeholder="Search Part Number..."
                                class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                                required autocomplete="off">
                            <div id="part-number-dropdown"
                                class="absolute left-0 right-0 mt-1 max-h-60 overflow-y-auto bg-white border border-gray-200 rounded shadow-lg z-50 hidden">
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Part
                            Name</label>
                        <input type="text" name="part_name" value="{{ old('part_name', $report->part_name) }}"
                            placeholder="Auto-filled from Part Number"
                            class="w-full rounded border-gray-300 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 text-sm"
                            >
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Model</label>
                        <input type="text" name="model" value="{{ old('model', $report->model) }}"
                            placeholder="Model"
                            class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                            >
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Customer</label>
                        <div class="relative">
                            <input type="text" name="customer" id="customer"
                                value="{{ old('customer', $report->customer) }}" placeholder="Search Customer..."
                                class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                                autocomplete="off">
                            <div id="customer-dropdown"
                                class="absolute left-0 right-0 mt-1 max-h-60 overflow-y-auto bg-white border border-gray-200 rounded shadow-lg z-50 hidden">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manpower Section -->
            <div class="space-y-4">
                <div class="flex justify-between items-center border-b pb-2">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-teal-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                        Manpower (MP)
                    </h3>
                    <button type="button" onclick="addManpowerRow()"
                        class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-1.5 px-4 rounded text-xs transition shadow-sm">
                        + Add Manpower
                    </button>
                </div>

                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="manpower-table">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 w-12">No</th>
                                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 w-1/3">Role</th>
                                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-600">Name</th>
                                    <th class="px-4 py-2 text-center text-xs font-bold text-gray-600 w-16">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200" id="manpower-tbody">
                                @forelse($report->manpowers->sortBy('no') as $index => $mp)
                                    <tr class="manpower-row">
                                        <td class="px-4 py-2 text-center font-bold text-gray-500 mp-no">
                                            {{ $loop->iteration }}</td>
                                        <td class="px-4 py-2">
                                            <input type="hidden" name="manpower[{{ $index }}][no]"
                                                class="mp-no-input" value="{{ $loop->iteration }}">
                                            @php
                                                $isCustom = !in_array($mp->role, [
                                                    'loading',
                                                    'sprayer',
                                                    'checker',
                                                    'qc',
                                                    'operator',
                                                    'leader',
                                                ]);
                                            @endphp
                                            <select
                                                class="w-full text-xs rounded border-gray-300 py-1 mb-1 role-select"
                                                onchange="toggleCustomRole(this, {{ $index }})">
                                                <option value="loading"
                                                    {{ $mp->role == 'loading' ? 'selected' : '' }}>Loading / Input /
                                                    Packing</option>
                                                <option value="sprayer"
                                                    {{ $mp->role == 'sprayer' ? 'selected' : '' }}>Sprayer</option>
                                                <option value="checker"
                                                    {{ $mp->role == 'checker' ? 'selected' : '' }}>Checker</option>
                                                <option value="qc" {{ $mp->role == 'qc' ? 'selected' : '' }}>QC
                                                </option>
                                                <option value="operator"
                                                    {{ $mp->role == 'operator' ? 'selected' : '' }}>Operator</option>
                                                <option value="leader" {{ $mp->role == 'leader' ? 'selected' : '' }}>
                                                    Leader</option>
                                                <option value="__custom__" {{ $isCustom ? 'selected' : '' }}>Other
                                                    (custom)
                                                    ...</option>
                                            </select>
                                            <input type="text" name="manpower[{{ $index }}][role]"
                                                value="{{ $mp->role }}"
                                                class="w-full text-xs rounded border-gray-300 py-1 role-input {{ $isCustom ? '' : 'hidden' }}"
                                                placeholder="Type custom role..." {{ $isCustom ? '' : 'readonly' }}>
                                        </td>
                                        <td class="px-4 py-2">
                                            <input type="text" name="manpower[{{ $index }}][name]"
                                                value="{{ $mp->name }}" placeholder="Operator Name"
                                                class="w-full text-xs rounded border-gray-300 py-1">
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <button type="button" onclick="removeManpowerRow(this)"
                                                class="text-red-500 hover:text-red-700 p-1">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <!-- Initial empty row if no data -->
                                    <tr class="manpower-row">
                                        <td class="px-4 py-2 text-center font-bold text-gray-500 mp-no">1</td>
                                        <td class="px-4 py-2">
                                            <input type="hidden" name="manpower[0][no]" class="mp-no-input"
                                                value="1">
                                            <select
                                                class="w-full text-xs rounded border-gray-300 py-1 mb-1 role-select"
                                                onchange="toggleCustomRole(this, 0)">
                                                <option value="loading">Loading / Input / Packing</option>
                                                <option value="sprayer">Sprayer</option>
                                                <option value="checker">Checker</option>
                                                <option value="qc">QC</option>
                                                <option value="operator">Operator</option>
                                                <option value="leader">Leader</option>
                                                <option value="__custom__">Other (custom)...</option>
                                            </select>
                                            <input type="text" name="manpower[0][role]" value="loading"
                                                class="w-full text-xs rounded border-gray-300 py-1 role-input hidden"
                                                placeholder="Type custom role..." readonly>
                                        </td>
                                        <td class="px-4 py-2">
                                            <input type="text" name="manpower[0][name]"
                                                placeholder="Operator Name"
                                                class="w-full text-xs rounded border-gray-300 py-1">
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <button type="button" onclick="removeManpowerRow(this)"
                                                class="text-red-500 hover:text-red-700 p-1">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Navigation Tab 1 -->
            <div class="flex justify-end pt-4 border-t border-gray-200 mt-6">
                <button type="button" onclick="switchTab('materials')"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow transition">
                    Next: Materials &rarr;
                </button>
            </div>
        </div> <!-- END TAB 1 -->

        <!-- TAB 2: MATERIALS -->
        <div id="tab-content-materials" class="tab-pane hidden space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                @php
                    $materialGlobalIndex = 0;
                    $paintMaterials = $report->materials ? $report->materials->where('type', 'paint')->values() : collect();
                    if ($paintMaterials->isEmpty() && !$report->exists) {
                        $defaultPaints = [
                            'Paint Primer',
                            'Hardener',
                            'Paint Basecoat',
                            'Hardener',
                            'Paint Topcoat',
                            'Hardener',
                        ];
                        foreach ($defaultPaints as $pName) {
                            $paintMaterials->push((object)[
                                'item_name' => $pName,
                                'lot_number' => '',
                                'visco' => '',
                                'mixing_ratio' => '',
                                'qty' => '',
                            ]);
                        }
                    }
                @endphp

                <!-- Item Paint Table -->
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="bg-gray-100 px-4 py-2 border-b border-gray-200 flex justify-between items-center">
                            <h4 class="text-sm font-bold text-gray-700">Item Paint (Viscosity & Mixing Ratio)</h4>
                            <span class="text-[10px] text-gray-500">Filled during prep stage</span>
                        </div>
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-bold text-gray-600">Item Paint</th>
                                    <th class="px-2 py-2 text-left font-bold text-gray-600 w-1/5">Lot Number</th>
                                    <th class="px-2 py-2 text-left font-bold text-gray-600 w-16">Visco</th>
                                    <th class="px-2 py-2 text-left font-bold text-gray-600 w-20">Mixing Ratio</th>
                                    <th class="px-2 py-2 text-left font-bold text-gray-600 w-20">Qty</th>
                                    <th class="px-2 py-2 text-left font-bold text-gray-600 w-20">UOM</th>
                                    <th class="px-1 py-2 text-center font-bold text-gray-600 w-8"></th>
                                </tr>
                            </thead>
                            <tbody id="paint-materials-tbody" class="divide-y divide-gray-200">
                                @foreach ($paintMaterials as $mat)
                                    @php $currIdx = $materialGlobalIndex++; @endphp
                                    <tr>
                                        <td class="px-3 py-2">
                                            <input type="hidden" name="materials[{{ $currIdx }}][type]" value="paint">
                                            <input type="text" name="materials[{{ $currIdx }}][item_name]"
                                                value="{{ old('materials.' . $currIdx . '.item_name', $mat->item_name ?? '') }}"
                                                placeholder="Paint Item Name"
                                                class="w-full text-xs rounded border-gray-300 py-1 font-semibold">
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="text" name="materials[{{ $currIdx }}][lot_number]"
                                                value="{{ old('materials.' . $currIdx . '.lot_number', $mat->lot_number ?? '') }}"
                                                placeholder="Lot"
                                                class="w-full text-xs rounded border-gray-300 py-1">
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="text" name="materials[{{ $currIdx }}][visco]"
                                                value="{{ old('materials.' . $currIdx . '.visco', $mat->visco ?? '') }}"
                                                placeholder="Visco"
                                                class="w-full text-xs rounded border-gray-300 py-1">
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="text" name="materials[{{ $currIdx }}][mixing_ratio]"
                                                value="{{ old('materials.' . $currIdx . '.mixing_ratio', $mat->mixing_ratio ?? '') }}"
                                                placeholder="Ratio (e.g. 1:1.5)"
                                                class="w-full text-xs rounded border-gray-300 py-1">
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="number" step="any" name="materials[{{ $currIdx }}][qty]"
                                                value="{{ old('materials.' . $currIdx . '.qty', $mat->qty ?? '') }}"
                                                placeholder="Qty"
                                                class="w-full text-xs rounded border-gray-300 py-1">
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="text" name="materials[{{ $currIdx }}][uom]"
                                                value="{{ old('materials.' . $currIdx . '.uom', $mat->uom ?? '') }}"
                                                placeholder="UOM"
                                                class="w-full text-xs rounded border-gray-300 py-1">
                                        </td>
                                        <td class="px-1 py-2 text-center">
                                            <button type="button" onclick="removeMaterialRow(this)"
                                                class="text-red-500 hover:text-red-700 font-bold px-1.5 py-0.5 text-sm rounded hover:bg-red-50 transition"
                                                title="Remove Row">&times;</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 bg-gray-50 border-t border-gray-200">
                        <button type="button" onclick="addPaintMaterialRow()"
                            class="text-xs text-blue-600 hover:text-blue-800 font-bold flex items-center gap-1 py-1 px-2 rounded hover:bg-blue-50 transition">
                            + Add Paint Item
                        </button>
                    </div>
                </div>

                @php
                    $partMaterials = $report->materials ? $report->materials->where('type', 'part')->values() : collect();
                    if ($partMaterials->isEmpty() && !$report->exists) {
                        $defaultParts = ['WIP 1', 'WIP 2', 'WIP 3', 'Repairan 1', 'Repairan 2', 'Repairan 3'];
                        foreach ($defaultParts as $pName) {
                            $partMaterials->push((object)[
                                'item_name' => $pName,
                                'lot_number' => '',
                                'qty' => '',
                                'uom' => '',
                            ]);
                        }
                    }
                @endphp

                <!-- Item Parts Table -->
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="bg-gray-100 px-4 py-2 border-b border-gray-200 flex justify-between items-center">
                            <h4 class="text-sm font-bold text-gray-700">Item Parts / WIP Lots</h4>
                            <span class="text-[10px] text-gray-500">Lot values from Plastic/FG IQC</span>
                        </div>
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-bold text-gray-600">Item Parts</th>
                                    <th class="px-2 py-2 text-left font-bold text-gray-600 w-1/3">Lot Number</th>
                                    <th class="px-2 py-2 text-left font-bold text-gray-600 w-24">Qty</th>
                                    <th class="px-2 py-2 text-left font-bold text-gray-600 w-24">UOM</th>
                                    <th class="px-1 py-2 text-center font-bold text-gray-600 w-8"></th>
                                </tr>
                            </thead>
                            <tbody id="part-materials-tbody" class="divide-y divide-gray-200">
                                @foreach ($partMaterials as $mat)
                                    @php $currIdx = $materialGlobalIndex++; @endphp
                                    <tr>
                                        <td class="px-3 py-2">
                                            <input type="hidden" name="materials[{{ $currIdx }}][type]" value="part">
                                            <input type="text" name="materials[{{ $currIdx }}][item_name]"
                                                value="{{ old('materials.' . $currIdx . '.item_name', $mat->item_name ?? '') }}"
                                                placeholder="Part / WIP Item Name"
                                                class="w-full text-xs rounded border-gray-300 py-1 font-semibold">
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="text" name="materials[{{ $currIdx }}][lot_number]"
                                                value="{{ old('materials.' . $currIdx . '.lot_number', $mat->lot_number ?? '') }}"
                                                placeholder="Lot"
                                                class="w-full text-xs rounded border-gray-300 py-1">
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="number" step="any" name="materials[{{ $currIdx }}][qty]"
                                                value="{{ old('materials.' . $currIdx . '.qty', $mat->qty ?? '') }}"
                                                placeholder="Qty"
                                                class="w-full text-xs rounded border-gray-300 py-1">
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="text" name="materials[{{ $currIdx }}][uom]"
                                                value="{{ old('materials.' . $currIdx . '.uom', $mat->uom ?? '') }}"
                                                placeholder="UOM"
                                                class="w-full text-xs rounded border-gray-300 py-1">
                                        </td>
                                        <td class="px-1 py-2 text-center">
                                            <button type="button" onclick="removeMaterialRow(this)"
                                                class="text-red-500 hover:text-red-700 font-bold px-1.5 py-0.5 text-sm rounded hover:bg-red-50 transition"
                                                title="Remove Row">&times;</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 bg-gray-50 border-t border-gray-200">
                        <button type="button" onclick="addPartMaterialRow()"
                            class="text-xs text-blue-600 hover:text-blue-800 font-bold flex items-center gap-1 py-1 px-2 rounded hover:bg-blue-50 transition">
                            + Add Part / WIP Item
                        </button>
                    </div>
                </div>
            </div>

            <!-- Navigation Tab 2 -->
            <div class="flex justify-between pt-4 border-t border-gray-200 mt-6">
                <button type="button" onclick="switchTab('setup')"
                    class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded shadow transition">
                    &larr; Back to Setup
                </button>
                <button type="button" onclick="switchTab('production')"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow transition">
                    Next: Production Logs &rarr;
                </button>
            </div>
        </div> <!-- END TAB 2 -->

        <!-- TAB 3: PRODUCTION & NG LOGS -->
        <div id="tab-content-production" class="tab-pane hidden space-y-8">

            <!-- Shift Production Calculations Header -->
            <div class="mb-8">
                <h4 class="text-lg font-extrabold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                        </path>
                    </svg>
                    Target & Shift Accumulation
                </h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Input Parameters Group -->
                    <div class="col-span-2 md:col-span-4 lg:col-span-2 grid grid-cols-2 gap-4">
                        <div
                            class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 hover:border-blue-300 transition-colors">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Target
                                Perjam</label>
                            <input type="number" name="target_per_hour" id="target_per_hour"
                                value="{{ $report->target_per_hour }}"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold transition-all"
                                placeholder="0">
                        </div>
                        <div
                            class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 hover:border-blue-300 transition-colors">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Jml
                                Input WIP</label>
                            <input type="number" name="jml_input_wip" id="jml_input_wip"
                                value="{{ $report->jml_input_wip }}"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold transition-all"
                                placeholder="0">
                        </div>
                        <div
                            class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 hover:border-blue-300 transition-colors">
                            <label
                                class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Repairan</label>
                            <input type="number" name="repairan" id="repairan" value="{{ $report->repairan }}"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold transition-all"
                                placeholder="0">
                        </div>
                        <div
                            class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 hover:border-blue-300 transition-colors">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Jml
                                NG Lebur</label>
                            <input type="number" name="jml_ng_lebur" id="jml_ng_lebur"
                                value="{{ $report->jml_ng_lebur }}"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold transition-all"
                                placeholder="0">
                        </div>
                    </div>

                    <!-- Calculated Stats Group -->
                    <div class="col-span-2 md:col-span-4 lg:col-span-2 grid grid-cols-2 gap-4">
                        <div
                            class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-xl shadow-sm border border-blue-200 relative overflow-hidden group">
                            <div
                                class="absolute top-0 right-0 -mr-4 -mt-4 text-blue-200 opacity-50 group-hover:scale-110 transition-transform">
                                <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                    </path>
                                </svg>
                            </div>
                            <label
                                class="block text-xs font-bold text-blue-800 uppercase tracking-wider mb-2 relative z-10">Total
                                Output</label>
                            <input type="number" name="jumlah_output" id="jumlah_output"
                                value="{{ $report->jumlah_output }}"
                                class="w-full bg-transparent border-none text-3xl font-extrabold text-blue-900 p-0 focus:ring-0 relative z-10"
                                readonly>
                        </div>
                        <div
                            class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-xl shadow-sm border border-green-200 relative overflow-hidden group">
                            <div
                                class="absolute top-0 right-0 -mr-4 -mt-4 text-green-200 opacity-50 group-hover:scale-110 transition-transform">
                                <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <label
                                class="block text-xs font-bold text-green-800 uppercase tracking-wider mb-2 relative z-10">Jumlah
                                OK</label>
                            <input type="number" name="jumlah_ok" id="jumlah_ok" value="{{ $report->jumlah_ok }}"
                                class="w-full bg-transparent border-none text-3xl font-extrabold text-green-700 p-0 focus:ring-0 relative z-10"
                                readonly>
                        </div>
                        <div
                            class="bg-gradient-to-br from-red-50 to-red-100 p-4 rounded-xl shadow-sm border border-red-200 relative overflow-hidden group">
                            <div
                                class="absolute top-0 right-0 -mr-4 -mt-4 text-red-200 opacity-50 group-hover:scale-110 transition-transform">
                                <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                            </div>
                            <label
                                class="block text-xs font-bold text-red-800 uppercase tracking-wider mb-2 relative z-10">Jumlah
                                NG</label>
                            <input type="number" name="jumlah_ng" id="jumlah_ng" value="{{ $report->jumlah_ng }}"
                                class="w-full bg-transparent border-none text-3xl font-extrabold text-red-700 p-0 focus:ring-0 relative z-10"
                                readonly>
                        </div>
                        <div
                            class="bg-gradient-to-br from-orange-50 to-orange-100 p-4 rounded-xl shadow-sm border border-orange-200 relative overflow-hidden group">
                            <div
                                class="absolute top-0 right-0 -mr-4 -mt-4 text-orange-200 opacity-50 group-hover:scale-110 transition-transform">
                                <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                                    <path d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                                </svg>
                            </div>
                            <label
                                class="block text-xs font-bold text-orange-800 uppercase tracking-wider mb-2 relative z-10">NG
                                %</label>
                            <div class="flex items-center relative z-10">
                                <input type="number" name="ng_prosentase" id="ng_prosentase" step="0.01"
                                    value="{{ $report->ng_prosentase }}"
                                    class="w-2/3 bg-transparent border-none text-3xl font-extrabold text-orange-700 p-0 focus:ring-0"
                                    readonly>
                                <span class="text-xl font-bold text-orange-600">%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $currentHoursCount = max(1, $report->hourlyProductions->count());
                $defaultNgs = $report->ngRecords->isNotEmpty()
                    ? $report->ngRecords->pluck('ng_name')->unique()->toArray()
                    : ['SCRATCH', 'DIRTY', 'HAIR MARK', 'DENTED', 'OVER CUT'];
            @endphp
            <!-- Unified Production & NG Log Grid -->
            <div class="space-y-6">
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm flex flex-col">
                    <div
                        class="bg-gradient-to-r from-blue-700 to-indigo-800 px-5 py-3 border-b border-gray-200 flex justify-between items-center text-white">
                        <h4 class="text-sm font-bold flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2a2 2 0 00-2-2H5a2 2 0 00-2 2v2m0 0h2a2 2 0 002-2v-3a2 2 0 110-4m0 0V5a2 2 0 012-2h2a2 2 0 012 2v3m0 0a2 2 0 110 4m0 0v3a2 2 0 01-2 2h-2m-4-3H9m4 0h2m-4 0v2m0-4V7">
                                </path>
                            </svg>
                            Production & NG Hourly Spreadsheet
                        </h4>
                        <div class="flex space-x-2">
                            <button type="button" id="remove-hour-btn"
                                class="bg-red-800 hover:bg-red-900 text-white font-bold text-xs px-3 py-1.5 rounded shadow-sm border border-red-900 transition-colors flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 12H4"></path>
                                </svg> Hour
                            </button>
                            <button type="button" id="add-hour-btn"
                                class="bg-green-600 hover:bg-green-500 text-white font-bold text-xs px-3 py-1.5 rounded shadow-sm border border-green-700 transition-colors flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg> Hour
                            </button>
                            <button type="button" id="add-ng-type-btn"
                                class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs px-3 py-1.5 rounded shadow-sm border border-indigo-700 transition-colors flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg> NG Type
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <!-- ponytail: apply minimum width to all spreadsheet number inputs cleanly via CSS -->
                        <style>
                            #unified-production-table tbody input[type="number"] {
                                min-width: 80px;
                            }

                            #ng-remark-modal::backdrop {
                                background-color: rgba(0, 0, 0, 0.5);
                                backdrop-filter: blur(4px);
                            }
                        </style>
                        <!-- ponytail: simplified unified production sheet -->
                        <table class="min-w-full divide-y divide-gray-200 text-sm table-fixed"
                            id="unified-production-table">
                            <thead class="bg-gray-50 text-gray-500 font-semibold text-xs tracking-wider uppercase">
                                <tr id="production-header-row">
                                    <th
                                        class="px-3 py-3 w-16 text-center sticky left-0 bg-gray-50 z-20 shadow-[1px_0_0_0_#e5e7eb]">
                                        Hour</th>
                                    <th
                                        class="px-3 py-3 w-28 text-center bg-green-50 text-green-800 font-bold border-l border-green-100">
                                        OK Qty</th>
                                    <th
                                        class="px-3 py-3 w-28 text-center bg-green-50 text-green-700 border-r border-green-100">
                                        Accum OK</th>
                                    <th
                                        class="px-3 py-3 w-24 text-center bg-red-50 text-red-800 font-bold border-r border-red-100">
                                        Total NG</th>
                                    @foreach ($defaultNgs as $index => $ng)
                                        @php
                                            $ngRecord = $report->ngRecords->where('ng_name', $ng)->first();
                                        @endphp
                                        <th class="group px-3 py-2 w-32 text-center ng-type-header relative select-none border-b border-gray-200"
                                            data-ng="{{ $ng }}">
                                            <div class="flex flex-col space-y-1">
                                                <div class="flex items-center justify-center space-x-1">
                                                    <span
                                                        class="font-bold text-xs uppercase">{{ $ng }}</span>
                                                    <button type="button"
                                                        class="text-red-500 hover:text-red-700 opacity-0 group-hover:opacity-100 transition-opacity delete-ng-col-btn text-[10px] font-bold"
                                                        data-ng="{{ $ng }}"
                                                        title="Delete {{ $ng }}">&times;</button>
                                                </div>
                                                @php
                                                    $hasRemark =
                                                        $ngRecord &&
                                                        (!empty($ngRecord->ng_input_item) ||
                                                            ($ngRecord->ng_input_qty !== null &&
                                                                $ngRecord->ng_input_qty !== ''));
                                                    $btnClass = $hasRemark
                                                        ? 'bg-blue-50 border-blue-200 text-blue-700 hover:bg-blue-100 font-bold'
                                                        : 'bg-gray-50 border-gray-200 text-gray-500 hover:bg-blue-50 hover:text-blue-700 font-semibold';

                                                    $labelText = 'Add Remark';
                                                    if ($hasRemark) {
                                                        $item = $ngRecord->ng_input_item;
                                                        $qty = $ngRecord->ng_input_qty;
                                                        if ($item && $qty !== null) {
                                                            $prettified = preg_replace(
                                                                '/\[(\d+)\]\s*([^\|]+)/',
                                                                '$1x $2',
                                                                $item,
                                                            );
                                                            $prettified = str_replace(' | ', ', ', $prettified);
                                                            $labelText = $prettified;
                                                        } elseif ($item) {
                                                            $labelText = $item;
                                                        } elseif ($qty !== null) {
                                                            $labelText = "Qty: {$qty}";
                                                        }
                                                    }
                                                @endphp
                                                <button type="button"
                                                    class="ng-remark-btn mt-1 flex items-center justify-between w-full px-2 py-1.5 text-[10px] rounded border transition-all select-none {{ $btnClass }}"
                                                    data-ng-name="{{ $ng }}"
                                                    data-ng-index="{{ $index }}">
                                                    <span
                                                        class="truncate remark-preview-label">{{ $labelText }}</span>
                                                    <svg class="w-3 h-3 ml-1 text-gray-400 flex-shrink-0"
                                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                        </path>
                                                    </svg>
                                                </button>
                                                <input type="hidden" name="ngs[{{ $index }}][ng_input_item]"
                                                    value="{{ $ngRecord ? $ngRecord->ng_input_item : '' }}"
                                                    class="ng-input-item-hidden">
                                                <input type="hidden" name="ngs[{{ $index }}][ng_input_qty]"
                                                    value="{{ $ngRecord ? $ngRecord->ng_input_qty : '' }}"
                                                    class="ng-input-qty-hidden">
                                                <input type="hidden" name="ngs[{{ $index }}][ng_name]"
                                                    value="{{ $ng }}">
                                                <input type="hidden" name="ngs[{{ $index }}][total_ng]"
                                                    id="ng-total-hidden-{{ $index }}"
                                                    value="{{ $ngRecord ? $ngRecord->total_ng : 0 }}">
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white" id="production-tbody">
                                @for ($hour = 1; $hour <= $currentHoursCount; $hour++)
                                    @php
                                        $hourlyMatch = $report->hourlyProductions->where('hour_ke', $hour)->first();
                                    @endphp
                                    <tr class="production-row hover:bg-blue-50/50 transition-colors duration-100"
                                        data-hour="{{ $hour }}">
                                        <!-- Hour sticky left -->
                                        <td
                                            class="px-3 py-2 text-center font-bold text-gray-600 bg-gray-50 sticky left-0 z-10 shadow-[1px_0_0_0_#e5e7eb]">
                                            {{ $hour }}</td>

                                        <!-- OK Qty -->
                                        <td class="px-2 py-1.5 bg-green-50/30 border-l border-green-100">
                                            <input type="number" name="hourly[{{ $hour }}][hour_ke]"
                                                value="{{ $hour }}" class="hidden">
                                            <input type="number" inputmode="numeric"
                                                name="hourly[{{ $hour }}][ok_qty]"
                                                value="{{ $hourlyMatch ? $hourlyMatch->ok_qty : '' }}"
                                                placeholder="-"
                                                class="w-full text-center text-sm font-bold text-green-800 rounded-md border-gray-200 focus:border-green-500 focus:ring-green-500 py-2 hourly-ok-input shadow-inner bg-white">
                                        </td>

                                        <!-- Accum OK (Readonly) -->
                                        <td class="px-2 py-1.5 bg-green-50/30 border-r border-green-100">
                                            <input type="number" name="hourly[{{ $hour }}][acumulasi_qty]"
                                                value="{{ $hourlyMatch ? $hourlyMatch->acumulasi_qty : '' }}"
                                                placeholder="0"
                                                class="w-full text-center text-sm font-extrabold text-green-700 bg-transparent border-transparent py-2 hourly-accum-input"
                                                readonly>
                                        </td>

                                        <!-- Total NG (Readonly) -->
                                        <td class="px-2 py-1.5 bg-red-50/30 border-r border-red-100">
                                            <input type="number" name="hourly[{{ $hour }}][ng_qty]"
                                                value="{{ $hourlyMatch ? $hourlyMatch->ng_qty : '' }}"
                                                placeholder="0"
                                                class="w-full text-center text-sm font-extrabold text-red-700 bg-transparent border-transparent py-2 hourly-ng-total-input"
                                                readonly>
                                        </td>

                                        <!-- NG Type Inputs -->
                                        @foreach ($defaultNgs as $index => $ng)
                                            @php
                                                $ngRecord = $report->ngRecords->where('ng_name', $ng)->first();
                                                $ngDetail = $ngRecord
                                                    ? $ngRecord->hourlyDetails->where('hour_ke', $hour)->first()
                                                    : null;
                                                $ngVal = $ngDetail ? $ngDetail->qty : '';
                                            @endphp
                                            <td class="px-1.5 py-1.5 ng-cell transition-colors duration-100"
                                                data-ng="{{ $ng }}">
                                                <input type="number" inputmode="numeric"
                                                    name="ngs[{{ $index }}][hours][{{ $hour }}]"
                                                    value="{{ $ngVal }}"
                                                    class="w-full text-center text-sm rounded-md border-transparent hover:border-gray-300 focus:border-red-500 focus:ring-red-500 py-2 bg-transparent hover:bg-white focus:bg-white transition-all ng-hourly-input {{ $ngVal && $ngVal > 0 ? 'bg-red-50 text-red-700 font-bold shadow-inner' : '' }}"
                                                    data-hour="{{ $hour }}"
                                                    data-ng-index="{{ $index }}" placeholder="-">
                                            </td>
                                        @endforeach
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Navigation Tab 3 -->
            <div class="flex justify-between pt-4 border-t border-gray-200 mt-6">
                <button type="button" onclick="switchTab('materials')"
                    class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded shadow transition">
                    &larr; Back to Materials
                </button>
                <button type="button" onclick="switchTab('handover')"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow transition">
                    Next: Handover &rarr;
                </button>
            </div>
        </div> <!-- END TAB 3 -->

        <!-- TAB 4: HANDOVER & SIGNATURES -->
        <div id="tab-content-handover" class="tab-pane hidden space-y-8">

            <!-- Troubles Section -->
            <div class="space-y-4">
                <h3 class="text-lg font-bold text-gray-800 border-b pb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                    Trouble / Downtime Report
                </h3>
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 font-bold text-gray-600">
                            <tr>
                                <th class="px-4 py-2 text-left w-1/5">Penyebab (Category)</th>
                                <th class="px-4 py-2 text-left w-1/3">Masalah (Problem)</th>
                                <th class="px-4 py-2 text-left">Penanganan (Countermeasure)</th>
                                <th class="px-4 py-2 text-left w-1/4">Loss Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @php
                                $defaultTroubles = ['Man', 'Mesin', 'Part', 'PPS', 'Lingkungan'];
                            @endphp
                            @foreach ($defaultTroubles as $index => $trouble)
                                @php
                                    $match = $report->troubles->where('penyebab', $trouble)->first();
                                @endphp
                                <tr>
                                    <td class="px-4 py-2 font-semibold text-gray-700">
                                        {{ $trouble }}
                                        <input type="hidden" name="troubles[{{ $index }}][penyebab]"
                                            value="{{ $trouble }}">
                                    </td>
                                    <td class="px-4 py-2">
                                        <textarea name="troubles[{{ $index }}][masalah]" rows="1"
                                            class="w-full rounded border-gray-300 text-xs py-1" placeholder="Problem description...">{{ $match ? $match->masalah : '' }}</textarea>
                                    </td>
                                    <td class="px-4 py-2">
                                        <textarea name="troubles[{{ $index }}][penanganan]" rows="1"
                                            class="w-full rounded border-gray-300 text-xs py-1" placeholder="Describe actions...">{{ $match ? $match->penanganan : '' }}</textarea>
                                    </td>
                                    <td class="px-4 py-2 flex space-x-1 items-center">
                                        <input type="number"
                                            name="troubles[{{ $index }}][loss_time_minutes]"
                                            value="{{ $match ? $match->loss_time_minutes : '' }}" placeholder="Mins"
                                            class="w-1/2 text-xs rounded border-gray-300 py-1">
                                        <input type="text" name="troubles[{{ $index }}][loss_time]"
                                            value="{{ $match ? $match->loss_time : '' }}" placeholder="e.g. 15 mins"
                                            class="w-1/2 text-xs rounded border-gray-300 py-1">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Notes, Attendance, & Schedule Section -->
            <div
                class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-5 rounded-lg border border-gray-200 text-sm">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Catatan
                            Produksi</label>
                        <textarea name="production_notes" rows="4"
                            class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-xs"
                            placeholder="General production notes...">{{ $report->production_notes }}</textarea>
                    </div>
                    <div class="mt-3">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Catatan /
                            Remarks NG</label>
                        <textarea name="ng_remarks" rows="2"
                            class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-xs"
                            placeholder="Remarks for NG causes...">{{ $report->ng_remarks }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Karyawan
                            Tidak Hadir</label>
                        <input type="text" name="absent_employees" value="{{ $report->absent_employees }}"
                            placeholder="Absent employees list..."
                            class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-xs">
                    </div>
                </div>
                <div class="space-y-4">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Jadwal Produksi
                        Selanjutnya</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @for ($i = 0; $i < 4; $i++)
                            @php
                                $schVal = $report->next_production_schedule[$i] ?? '';
                                if (empty($schVal) && is_string($report->next_production_schedule)) {
                                    $schedules = explode("\n", $report->next_production_schedule);
                                    foreach ($schedules as $s) {
                                        if (strpos($s, $i + 1 . ': ') === 0) {
                                            $schVal = substr($s, 3);
                                        }
                                    }
                                }
                            @endphp
                            <div class="flex items-center space-x-2">
                                <span class="text-xs font-bold text-gray-500 w-4">{{ $i + 1 }}.</span>
                                <input type="text" name="next_production_schedule[]" value="{{ $schVal }}"
                                    placeholder="Next schedule item"
                                    class="w-full text-xs rounded border-gray-300 py-1 schedule-input">
                            </div>
                        @endfor
                    </div>

                    <!-- Signature / Approvals placeholder -->
                    <div class="pt-4 border-t border-gray-200">
                        <div
                            class="p-3 bg-gray-100 border border-gray-200 rounded text-center text-xs text-gray-500 font-semibold">
                            Signatures will be digitally recorded upon report submission and role-based
                            approval.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Tab 4 -->
            <div class="flex justify-between pt-4 border-t border-gray-200 mt-6">
                <button type="button" onclick="switchTab('production')"
                    class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded shadow transition">
                    &larr; Back to Production
                </button>
                <div>
                    <button type="button" onclick="submitProductionReport()" id="submit-btn"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow-md transition">
                        Submit Production Report
                    </button>
                </div>
            </div>
        </div> <!-- END TAB 4 -->


    </div>

    <!-- Sticky Footer (For Global Save Draft utility) -->
    <div class="bg-gray-100 px-6 py-4 flex justify-between items-center border-t border-gray-200">
        <a href="{{ $report->exists ? route('second-process-reports.show', $report->id) : route('second-process-reports.index') }}"
            class="text-gray-600 hover:text-gray-800 text-sm font-semibold transition">Cancel</a>
        <button type="button" onclick="saveAsDraft()"
            class="bg-gray-600 hover:bg-gray-700 text-white text-xs font-bold py-1.5 px-4 rounded shadow transition">
            Save Draft
        </button>
    </div>

</div>

<!-- Defect Remark Drilldown Dialog -->
<dialog id="ng-remark-modal"
    class="rounded-xl shadow-2xl w-full max-w-md p-0 overflow-hidden backdrop:bg-black/50 backdrop:backdrop-blur-sm">
    <div class="flex flex-col bg-white">
        <div
            class="bg-gradient-to-r from-blue-700 to-indigo-800 px-6 py-4 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg" id="modal-ng-title">Defect Detail</h3>
            <button type="button"
                class="text-white hover:text-gray-200 text-xl font-bold close-modal-btn">&times;</button>
        </div>
        <div class="p-6 space-y-4">
            <div id="modal-rows-container" class="space-y-3 max-h-60 overflow-y-auto">
                <!-- Dynamic rows injected here -->
            </div>
            <button type="button" id="add-modal-row-btn"
                class="w-full py-1.5 border border-dashed border-blue-400 hover:border-blue-600 text-blue-600 hover:text-blue-800 text-xs font-bold rounded flex items-center justify-center transition select-none">
                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg> Add Detail Row
            </button>
        </div>
        <div class="bg-gray-50 px-6 py-3 flex justify-end space-x-2 border-t border-gray-100">
            <button type="button"
                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-semibold rounded transition close-modal-btn">Cancel</button>
            <button type="button" id="save-modal-remark-btn"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded shadow transition">Save
                Details</button>
        </div>
    </div>
</dialog>

<!-- Script calculations & interactions -->
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const form = document.getElementById('production-report-form');
        const submitBtn = document.getElementById('submit-btn');

        // 1. Double Submission Protection
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg> Saving...`;
        });

        // Tab Navigation Logic
        window.switchTab = function(tabId) {
            // Hide all tab panes
            document.querySelectorAll('.tab-pane').forEach(el => {
                el.classList.add('hidden');
            });
            // Show requested tab pane
            document.getElementById('tab-content-' + tabId).classList.remove('hidden');

            // Update tab buttons style
            document.querySelectorAll('.tab-btn').forEach(btn => {
                const isActive = btn.getAttribute('data-tab') === tabId;
                if (isActive) {
                    btn.classList.add('border-blue-600', 'text-blue-600', 'active');
                    btn.classList.remove('border-transparent', 'text-gray-500');
                } else {
                    btn.classList.remove('border-blue-600', 'text-blue-600', 'active');
                    btn.classList.add('border-transparent', 'text-gray-500');
                }
            });

            // Highlight active warnings on tab shift
            validateTotals();

            // Scroll tabs into view smoothly
            document.getElementById('form-tabs-navigation').scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        };

        // Direct tab button event listeners
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                switchTab(this.getAttribute('data-tab'));
            });
        });

        // Save Draft helper function
        window.saveAsDraft = function() {
            const statusField = document.getElementById('status-field');
            if (statusField) {
                statusField.value = 'draft';
            }

            // Highlight checking of required inputs
            const formInputs = form.querySelectorAll('input[required], select[required]');
            let valid = true;

            formInputs.forEach(input => {
                if (!input.value) {
                    valid = false;
                    input.classList.add('border-red-500');
                } else {
                    input.classList.remove('border-red-500');
                }
            });

            if (!valid) {
                switchTab('setup');
                alert('Please fill out all required shift details on Setup tab to save a draft.');
                return;
            }

            form.submit();
        };

        // Submit Production Report helper function
        window.submitProductionReport = function() {
            const statusField = document.getElementById('status-field');
            if (statusField) {
                statusField.value = 'submitted';
            }

            // Highlight checking of inputs before submit
            const formInputs = form.querySelectorAll('input[required], select[required]');
            let valid = true;

            formInputs.forEach(input => {
                if (!input.value) {
                    valid = false;
                    input.classList.add('border-red-500');
                } else {
                    input.classList.remove('border-red-500');
                }
            });

            if (!valid) {
                switchTab('setup');
                alert('Please fill out all required details on the Setup tab to submit the report.');
                return;
            }

            form.submit();
        };

        // 2. Autocomplete helper
        function setupAutocomplete(inputId, dropdownId, url, onSelect) {
            const input = document.getElementById(inputId);
            const dropdown = document.getElementById(dropdownId);
            let debounceTimer;

            input.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                const query = input.value.trim();

                if (query.length < 2) {
                    dropdown.innerHTML = '';
                    dropdown.classList.add('hidden');
                    return;
                }

                debounceTimer = setTimeout(() => {
                    fetch(`${url}?query=${encodeURIComponent(query)}`)
                        .then(res => res.json())
                        .then(data => {
                            dropdown.innerHTML = '';
                            if (data.length === 0) {
                                dropdown.classList.add('hidden');
                                return;
                            }

                            data.forEach(item => {
                                const div = document.createElement('div');
                                div.className =
                                    'px-4 py-2 hover:bg-blue-50 cursor-pointer text-xs border-b border-gray-100 last:border-b-0 text-gray-800 transition';

                                if (item.item_code) {
                                    div.innerHTML =
                                        `<span class="font-bold text-blue-700">${item.item_code}</span> - <span class="text-gray-500">${item.item_description || ''}</span>`;
                                } else if (item.name) {
                                    div.textContent = item.name;
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
                if (e.target !== input && e.target !== dropdown) {
                    dropdown.classList.add('hidden');
                }
            });
        }

        // Initialize Autocompletes
        setupAutocomplete('part_number', 'part-number-dropdown',
            '{{ route('second-process-reports.search-items') }}',
            function(item) {
                document.getElementById('part_number').value = item.item_code;
                document.querySelector('input[name="part_name"]').value = item.item_name || item
                    .item_description || '';
                if (item.project_code) {
                    document.querySelector('input[name="model"]').value = item.project_code;
                }
                document.getElementById('customer').value = item.customer_name || '';
            });
        setupAutocomplete('customer', 'customer-dropdown',
            '{{ route('second-process-reports.search-customers') }}',
            function(item) {
                document.getElementById('customer').value = item.customer_name || item.name || '';
            });

        // 3. Dynamic Hour Management (Unified Production Table Sync)
        const addHourBtn = document.getElementById('add-hour-btn');
        const removeHourBtn = document.getElementById('remove-hour-btn');
        const addNgTypeBtn = document.getElementById('add-ng-type-btn');

        // ponytail: active NG types list stored in state
        let activeNgs = {!! json_encode($defaultNgs) !!};

        addHourBtn.addEventListener('click', function() {
            const currentHours = document.querySelectorAll('.production-row').length;
            if (currentHours >= 8) {
                const proceed = confirm(
                    `Peringatan: Jumlah waktu kerja telah melebihi 8 jam. Apakah Anda yakin ingin menambah jam ke-${currentHours + 1}?`
                );
                if (!proceed) return;
            }

            const newHour = currentHours + 1;

            // ponytail: dynamically render cells based on current activeNgs state
            let ngCells = '';
            activeNgs.forEach((ng, index) => {
                ngCells += `
                    <td class="px-1.5 py-1.5 ng-cell transition-colors duration-100" data-ng="${ng}">
                        <input type="number" inputmode="numeric" name="ngs[${index}][hours][${newHour}]" class="w-full text-center text-sm rounded-md border-transparent hover:border-gray-300 focus:border-red-500 focus:ring-red-500 py-2 bg-transparent hover:bg-white focus:bg-white transition-all ng-hourly-input" data-hour="${newHour}" data-ng-index="${index}" placeholder="-">
                    </td>
                `;
            });

            const newRow = `
    <tr class="production-row hover:bg-blue-50/50 transition-colors duration-100" data-hour="${newHour}">
        <td class="px-3 py-2 text-center font-bold text-gray-600 bg-gray-50 sticky left-0 z-10 shadow-[1px_0_0_0_#e5e7eb]">${newHour}</td>
        <td class="px-2 py-1.5 bg-green-50/30 border-l border-green-100">
            <input type="number" name="hourly[${newHour}][hour_ke]" value="${newHour}" class="hidden">
            <input type="number" inputmode="numeric" name="hourly[${newHour}][ok_qty]" placeholder="-" class="w-full text-center text-sm font-bold text-green-800 rounded-md border-gray-200 focus:border-green-500 focus:ring-green-500 py-2 hourly-ok-input shadow-inner bg-white">
        </td>
        <td class="px-2 py-1.5 bg-green-50/30 border-r border-green-100">
            <input type="number" name="hourly[${newHour}][acumulasi_qty]" placeholder="0" class="w-full text-center text-sm font-extrabold text-green-700 bg-transparent border-transparent py-2 hourly-accum-input" readonly>
        </td>
        <td class="px-2 py-1.5 bg-red-50/30 border-r border-red-100">
            <input type="number" name="hourly[${newHour}][ng_qty]" placeholder="0" class="w-full text-center text-sm font-extrabold text-red-700 bg-transparent border-transparent py-2 hourly-ng-total-input" readonly>
        </td>
        ${ngCells}
    </tr>`;
            document.getElementById('production-tbody').insertAdjacentHTML('beforeend', newRow);

            calculateHourlyAccumulation();
            calculateNgTotals();
        });

        removeHourBtn.addEventListener('click', function() {
            const currentHours = document.querySelectorAll('.production-row').length;
            if (currentHours <= 1) {
                alert('At least 1 hour of production is required.');
                return;
            }

            document.querySelector(`.production-row[data-hour="${currentHours}"]`).remove();

            calculateHourlyAccumulation();
            calculateNgTotals();
        });

        // ponytail: dynamic NG Type Add & Delete
        addNgTypeBtn.addEventListener('click', function() {
            const name = prompt("Masukkan nama Tipe NG Baru (contoh: PAINT RUN, BUBBLE):");
            if (!name) return;
            const uppercaseName = name.trim().toUpperCase();
            if (uppercaseName === '') return;
            if (activeNgs.includes(uppercaseName)) {
                alert('Tipe NG tersebut sudah ada!');
                return;
            }

            activeNgs.push(uppercaseName);
            const newNgIndex = activeNgs.length - 1;

            // 1. Add column to table header
            const headerRow = document.getElementById('production-header-row');
            const th = document.createElement('th');
            th.className =
                'group px-3 py-2 w-32 text-center ng-type-header relative select-none border-b border-gray-200';
            th.dataset.ng = uppercaseName;
            th.innerHTML = `
                <div class="flex flex-col space-y-1">
                    <div class="flex items-center justify-center space-x-1">
                        <span class="font-bold text-xs uppercase">${uppercaseName}</span>
                        <button type="button" class="text-red-500 hover:text-red-700 opacity-0 group-hover:opacity-100 transition-opacity delete-ng-col-btn text-[10px] font-bold" data-ng="${uppercaseName}" title="Delete ${uppercaseName}">&times;</button>
                    </div>
                    <button type="button" 
                        class="ng-remark-btn mt-1 flex items-center justify-between w-full px-2 py-1.5 text-[10px] font-semibold bg-gray-50 border border-gray-200 text-gray-500 hover:bg-blue-50 hover:text-blue-700 rounded transition-all select-none"
                        data-ng-name="${uppercaseName}"
                        data-ng-index="${newNgIndex}">
                        <span class="truncate remark-preview-label">Add Remark</span>
                        <svg class="w-3 h-3 ml-1 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                    <input type="hidden" name="ngs[${newNgIndex}][ng_input_item]" value="" class="ng-input-item-hidden">
                    <input type="hidden" name="ngs[${newNgIndex}][ng_input_qty]" value="" class="ng-input-qty-hidden">
                    <input type="hidden" name="ngs[${newNgIndex}][ng_name]" value="${uppercaseName}">
                    <input type="hidden" name="ngs[${newNgIndex}][total_ng]" id="ng-total-hidden-${newNgIndex}" value="0">
                </div>
            `;
            headerRow.appendChild(th);

            // 2. Add columns to rows
            const rows = document.querySelectorAll('.production-row');
            rows.forEach(row => {
                const hour = row.dataset.hour;
                const td = document.createElement('td');
                td.className = 'px-1.5 py-1.5 ng-cell transition-colors duration-100';
                td.dataset.ng = uppercaseName;
                td.innerHTML = `
                    <input type="number" inputmode="numeric" name="ngs[${newNgIndex}][hours][${hour}]" class="w-full text-center text-sm rounded-md border-transparent hover:border-gray-300 focus:border-red-500 focus:ring-red-500 py-2 bg-transparent hover:bg-white focus:bg-white transition-all ng-hourly-input" data-hour="${hour}" data-ng-index="${newNgIndex}" placeholder="-">
                `;
                row.appendChild(td);
            });

            calculateNgTotals();
        });

        // Event delegation for deleting NG columns
        document.getElementById('production-header-row').addEventListener('click', function(e) {
            const deleteBtn = e.target.closest('.delete-ng-col-btn');
            if (deleteBtn) {
                const ngName = deleteBtn.dataset.ng;
                deleteNgType(ngName);
            }
        });


        function deleteNgType(ngName) {
            const countWithValues = Array.from(document.querySelectorAll(
                    `.ng-cell[data-ng="${ngName}"] input.ng-hourly-input`))
                .reduce((sum, input) => sum + (parseInt(input.value) || 0), 0);

            const confirmMsg = countWithValues > 0 ?
                `Tipe NG "${ngName}" memiliki total input sebanyak ${countWithValues}. Apakah Anda yakin ingin menghapus tipe NG ini beserta seluruh datanya?` :
                `Apakah Anda yakin ingin menghapus Tipe NG "${ngName}"?`;

            if (!confirm(confirmMsg)) return;

            // 1. Remove from activeNgs array
            const index = activeNgs.indexOf(ngName);
            if (index > -1) {
                activeNgs.splice(index, 1);
            }

            // 2. Remove header element
            const th = document.querySelector(`.ng-type-header[data-ng="${ngName}"]`);
            if (th) th.remove();

            // 3. Remove cells from rows
            document.querySelectorAll(`.ng-cell[data-ng="${ngName}"]`).forEach(td => td.remove());

            // 5. Re-index and calculate
            reindexNgs();
            calculateNgTotals();
        }

        function reindexNgs() {
            activeNgs.forEach((ng, newIndex) => {
                document.querySelectorAll(`.ng-cell[data-ng="${ng}"] input.ng-hourly-input`).forEach(
                    input => {
                        const hour = input.dataset.hour;
                        input.name = `ngs[${newIndex}][hours][${hour}]`;
                        input.dataset.ngIndex = newIndex;
                    });

                const th = document.querySelector(`.ng-type-header[data-ng="${ng}"]`);
                if (th) {
                    const nameInput = th.querySelector('input[name$="[ng_name]"]');
                    if (nameInput) nameInput.name = `ngs[${newIndex}][ng_name]`;

                    const totalHidden = th.querySelector('input[id^="ng-total-hidden-"]');
                    if (totalHidden) {
                        totalHidden.name = `ngs[${newIndex}][total_ng]`;
                        totalHidden.id = `ng-total-hidden-${newIndex}`;
                    }

                    const itemInput = th.querySelector('input[name$="[ng_input_item]"]');
                    if (itemInput) itemInput.name = `ngs[${newIndex}][ng_input_item]`;

                    const qtyInput = th.querySelector('input[name$="[ng_input_qty]"]');
                    if (qtyInput) qtyInput.name = `ngs[${newIndex}][ng_input_qty]`;

                    const remarkBtn = th.querySelector('.ng-remark-btn');
                    if (remarkBtn) remarkBtn.setAttribute('data-ng-index', newIndex);
                }
            });
        }

        // 4. Calculations
        const totalOkField = document.getElementById('jumlah_ok');
        const totalNgField = document.getElementById('jumlah_ng');
        const totalOutputField = document.getElementById('jumlah_output');
        const ngPercentageField = document.getElementById('ng_prosentase');

        // ponytail: simple column and row summation
        function calculateHourlyAccumulation() {
            const rows = document.querySelectorAll('.production-row');
            let accumulated = 0;
            rows.forEach(row => {
                const okInput = row.querySelector('.hourly-ok-input');
                const accumInput = row.querySelector('.hourly-accum-input');
                const val = parseInt(okInput.value) || 0;
                accumulated += val;
                if (accumInput) {
                    accumInput.value = accumulated > 0 ? accumulated : '';
                }
            });
            totalOkField.value = accumulated;
            calculateSummaryTotals();
        }

        function calculateNgTotals() {
            const rows = document.querySelectorAll('.production-row');
            let overallNg = 0;

            // Sum by row (hourly total)
            rows.forEach(row => {
                const rowInputs = row.querySelectorAll('.ng-hourly-input');
                const rowTotalField = row.querySelector('.hourly-ng-total-input');
                let rowTotal = 0;
                rowInputs.forEach(input => {
                    rowTotal += parseInt(input.value) || 0;
                });
                if (rowTotalField) {
                    rowTotalField.value = rowTotal > 0 ? rowTotal : '';
                }
                overallNg += rowTotal;
            });

            // Sum by column (NG type total) to update hidden fields
            activeNgs.forEach((ng, index) => {
                const colInputs = document.querySelectorAll(
                    `.ng-hourly-input[data-ng-index="${index}"]`);
                let colTotal = 0;
                colInputs.forEach(input => {
                    colTotal += parseInt(input.value) || 0;
                });
                const hiddenTotal = document.getElementById(`ng-total-hidden-${index}`);
                if (hiddenTotal) {
                    hiddenTotal.value = colTotal;
                }
            });

            totalNgField.value = overallNg;
            calculateSummaryTotals();
        }

        function calculateSummaryTotals() {
            const totalOk = parseInt(totalOkField.value) || 0;
            const totalNg = parseInt(totalNgField.value) || 0;
            const totalOutput = totalOk + totalNg;

            totalOutputField.value = totalOutput;

            if (totalOutput > 0) {
                const pct = (totalNg / totalOutput) * 100;
                ngPercentageField.value = pct.toFixed(2);
            } else {
                ngPercentageField.value = '0.00';
            }

            validateTotals();
        }

        function validateTotals() {
            const totalOk = parseInt(totalOkField.value) || 0;
            const totalNg = parseInt(totalNgField.value) || 0;
            const expectedOutput = totalOk + totalNg;
            const inputOutput = parseInt(totalOutputField.value) || 0;
            const validationMessage = document.getElementById('totals-validation-message');

            if (inputOutput > 0 && (totalOk + totalNg !== inputOutput)) {
                validationMessage.innerHTML = `
        <div class="p-3 bg-yellow-50 text-yellow-800 border border-yellow-200 rounded text-xs flex items-center shadow-sm">
            <svg class="w-4 h-4 mr-2 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <span><strong>Peringatan Validasi:</strong> Total OK (${totalOk}) + Total NG (${totalNg}) = ${totalOk + totalNg}, tidak sama dengan Jumlah Output (${inputOutput}). Silakan periksa kembali input hasil produksi Anda.</span>
        </div>
    `;
            } else {
                validationMessage.innerHTML = '';
            }
        }

        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('hourly-ok-input')) {
                calculateHourlyAccumulation();
            }
            if (e.target.classList.contains('ng-hourly-input')) {
                calculateNgTotals();
                if (e.target.value && e.target.value > 0) {
                    e.target.classList.add('bg-red-50', 'text-red-700', 'font-bold', 'shadow-inner');
                } else {
                    e.target.classList.remove('bg-red-50', 'text-red-700', 'font-bold', 'shadow-inner');
                }
            }
        });

        // Grid Navigation and Crosshair Highlight for Unified Table
        const prodTbody = document.getElementById('production-tbody');
        if (prodTbody) {
            prodTbody.addEventListener('keydown', function(e) {
                if (!e.target.classList.contains('ng-hourly-input') && !e.target.classList.contains(
                        'hourly-ok-input')) return;

                const currentTd = e.target.closest('td');
                const currentRow = e.target.closest('tr');
                let nextInput = null;

                if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    const prevRow = currentRow.previousElementSibling;
                    if (prevRow) {
                        const colIndex = Array.from(currentTd.parentNode.children).indexOf(currentTd);
                        const targetTd = prevRow.children[colIndex];
                        if (targetTd) nextInput = targetTd.querySelector('input:not([type="hidden"])');
                    }
                } else if (e.key === 'ArrowDown' || e.key === 'Enter') {
                    e.preventDefault();
                    const nextRow = currentRow.nextElementSibling;
                    if (nextRow) {
                        const colIndex = Array.from(currentTd.parentNode.children).indexOf(currentTd);
                        const targetTd = nextRow.children[colIndex];
                        if (targetTd) nextInput = targetTd.querySelector('input:not([type="hidden"])');
                    }
                } else if (e.key === 'ArrowLeft' && e.target.selectionStart === 0) {
                    let prevTd = currentTd.previousElementSibling;
                    while (prevTd && (prevTd.querySelector('input[readonly]') || !prevTd.querySelector(
                            'input'))) {
                        prevTd = prevTd.previousElementSibling;
                    }
                    if (prevTd) {
                        e.preventDefault();
                        nextInput = prevTd.querySelector('input');
                    }
                } else if (e.key === 'ArrowRight' && e.target.selectionStart === e.target.value
                    .length) {
                    let nextTd = currentTd.nextElementSibling;
                    while (nextTd && (nextTd.querySelector('input[readonly]') || !nextTd.querySelector(
                            'input'))) {
                        nextTd = nextTd.nextElementSibling;
                    }
                    if (nextTd) {
                        e.preventDefault();
                        nextInput = nextTd.querySelector('input');
                    }
                }

                if (nextInput) {
                    nextInput.focus();
                    nextInput.select();
                }
            });

            prodTbody.addEventListener('focusin', function(e) {
                const isNg = e.target.classList.contains('ng-hourly-input');
                const isOk = e.target.classList.contains('hourly-ok-input');
                if (!isNg && !isOk) return;

                const currentTd = e.target.closest('td');
                const colIndex = Array.from(currentTd.parentNode.children).indexOf(currentTd);

                // Highlight header
                const headers = document.querySelectorAll('#production-header-row th');
                headers.forEach(th => th.classList.remove('bg-red-100', 'text-red-800', 'border-b-2',
                    'border-red-500'));

                const header = headers[colIndex];
                if (header && isNg) {
                    header.classList.add('bg-red-100', 'text-red-800', 'border-b-2', 'border-red-500');
                }

                // Highlight column cells
                document.querySelectorAll(`.production-row`).forEach(row => {
                    const cell = row.children[colIndex];
                    if (cell && isNg) cell.classList.add('bg-red-50');
                });

                e.target.closest('tr').classList.add('bg-blue-100/50');
            });

            prodTbody.addEventListener('focusout', function(e) {
                const isNg = e.target.classList.contains('ng-hourly-input');
                const isOk = e.target.classList.contains('hourly-ok-input');
                if (!isNg && !isOk) return;

                const currentTd = e.target.closest('td');
                const colIndex = Array.from(currentTd.parentNode.children).indexOf(currentTd);

                const headers = document.querySelectorAll('#production-header-row th');
                const header = headers[colIndex];
                if (header) {
                    header.classList.remove('bg-red-100', 'text-red-800', 'border-b-2',
                        'border-red-500');
                }

                document.querySelectorAll(`.production-row`).forEach(row => {
                    const cell = row.children[colIndex];
                    if (cell) cell.classList.remove('bg-red-50');
                });

                e.target.closest('tr').classList.remove('bg-blue-100/50');
            });
        }

        // Defect Remark Modal Handling
        const remarkModal = document.getElementById('ng-remark-modal');
        const modalTitle = document.getElementById('modal-ng-title');
        const saveModalBtn = document.getElementById('save-modal-remark-btn');
        let activeRemarkButton = null;

        // Function to add a row to the modal dynamically
        function addModalRow(item = '', qty = '') {
            const container = document.getElementById('modal-rows-container');
            const div = document.createElement('div');
            div.className = 'flex items-center space-x-2 modal-row';
            div.innerHTML = `
                <input type="text" class="flex-1 rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-xs py-1.5 px-2 modal-row-item" placeholder="Remark detail..." value="${escapeHtml(item)}">
                <input type="number" class="w-20 rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-xs py-1.5 px-2 modal-row-qty" placeholder="Qty" value="${qty}">
                <button type="button" class="text-red-500 hover:text-red-700 font-bold delete-row-btn text-base px-1" title="Delete row">&times;</button>
            `;
            container.appendChild(div);
        }

        // Helper to escape HTML tags in strings
        function escapeHtml(str) {
            return str
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Manage row removal using event delegation
        document.getElementById('modal-rows-container').addEventListener('click', function(e) {
            const deleteBtn = e.target.closest('.delete-row-btn');
            if (deleteBtn) {
                const row = deleteBtn.closest('.modal-row');
                const allRows = document.querySelectorAll('.modal-row');
                // Keep at least one row
                if (allRows.length > 1) {
                    row.remove();
                } else {
                    row.querySelector('.modal-row-item').value = '';
                    row.querySelector('.modal-row-qty').value = '';
                }
            }
        });

        // Add row button listener
        document.getElementById('add-modal-row-btn').addEventListener('click', function() {
            addModalRow('', '');
        });

        // Click event using event delegation for dynamic headers
        document.getElementById('production-header-row').addEventListener('click', function(e) {
            const btn = e.target.closest('.ng-remark-btn');
            if (!btn) return;

            activeRemarkButton = btn;
            const ngName = btn.getAttribute('data-ng-name');
            const th = btn.closest('th');

            const itemHidden = th.querySelector('.ng-input-item-hidden');
            const qtyHidden = th.querySelector('.ng-input-qty-hidden');

            modalTitle.textContent = `Defect Detail: ${ngName}`;

            // Clear existing rows
            document.getElementById('modal-rows-container').innerHTML = '';

            const rawItem = itemHidden.value.trim();
            const rawQty = qtyHidden.value.trim();

            if (rawItem) {
                // Try to parse structured items like [8] X | [3] Y
                const parts = rawItem.split(' | ');
                let parsedAny = false;

                parts.forEach(part => {
                    const match = part.match(/^\[(\d+)\]\s*(.*)$/);
                    if (match) {
                        addModalRow(match[2], match[1]);
                        parsedAny = true;
                    }
                });

                // If parsing failed or it's legacy data, load as a single row
                if (!parsedAny) {
                    addModalRow(rawItem, rawQty);
                }
            } else {
                // Default to one empty row
                addModalRow('', '');
            }

            remarkModal.showModal();
        });

        // Close handlers
        const closeModal = () => {
            remarkModal.close();
            activeRemarkButton = null;
        };

        remarkModal.querySelectorAll('.close-modal-btn').forEach(btn => {
            btn.addEventListener('click', closeModal);
        });

        remarkModal.addEventListener('click', function(e) {
            if (e.target === remarkModal) {
                closeModal();
            }
        });

        // Save details
        saveModalBtn.addEventListener('click', function() {
            if (!activeRemarkButton) return;

            const th = activeRemarkButton.closest('th');
            const itemHidden = th.querySelector('.ng-input-item-hidden');
            const qtyHidden = th.querySelector('.ng-input-qty-hidden');

            const rows = document.querySelectorAll('.modal-row');
            let serializedParts = [];
            let totalQty = 0;

            rows.forEach(row => {
                const itemVal = row.querySelector('.modal-row-item').value.trim();
                const qtyVal = parseInt(row.querySelector('.modal-row-qty').value) || 0;

                if (itemVal || qtyVal > 0) {
                    // Serialize as [qty] item or just item if qty is 0
                    if (qtyVal > 0) {
                        serializedParts.push(`[${qtyVal}] ${itemVal}`);
                        totalQty += qtyVal;
                    } else {
                        serializedParts.push(itemVal);
                    }
                }
            });

            const finalItemVal = serializedParts.join(' | ');

            // Save values back to hidden inputs
            itemHidden.value = finalItemVal;
            qtyHidden.value = totalQty > 0 ? totalQty : '';

            // Update button UI & Label
            const previewLabel = activeRemarkButton.querySelector('.remark-preview-label');
            const hasVal = finalItemVal !== '';

            if (hasVal) {
                activeRemarkButton.classList.remove('bg-gray-50', 'border-gray-200', 'text-gray-500',
                    'hover:bg-blue-50', 'hover:text-blue-700', 'font-semibold');
                activeRemarkButton.classList.add('bg-blue-50', 'border-blue-200', 'text-blue-700',
                    'hover:bg-blue-100', 'font-bold');

                // Format label prettily for preview: e.g. 8x X, 3x Y
                let prettyLabel = finalItemVal
                    .replace(/\[(\d+)\]\s*([^\|]+)/g, '$1x $2')
                    .replace(/\s*\|\s*/g, ', ');
                previewLabel.textContent = prettyLabel;
            } else {
                activeRemarkButton.classList.remove('bg-blue-50', 'border-blue-200', 'text-blue-700',
                    'hover:bg-blue-100', 'font-bold');
                activeRemarkButton.classList.add('bg-gray-50', 'border-gray-200', 'text-gray-500',
                    'hover:bg-blue-50', 'hover:text-blue-700', 'font-semibold');
                previewLabel.textContent = 'Add Remark';
            }

            closeModal();
        });

        // Initial Calculations
        calculateHourlyAccumulation();
        calculateNgTotals();
    });
</script>

<!-- Manpower Script -->
<script>
    let manpowerIndex = document.querySelectorAll('.manpower-row').length;

    function toggleCustomRole(select, index) {
        const input = select.nextElementSibling;
        if (select.value === '__custom__') {
            input.classList.remove('hidden');
            input.removeAttribute('readonly');
            input.value = ''; // clear previous value
            input.focus();
        } else {
            input.classList.add('hidden');
            input.setAttribute('readonly', 'readonly');
            input.value = select.value;
        }
    }

    function addManpowerRow() {
        const tbody = document.getElementById('manpower-tbody');
        const rows = tbody.querySelectorAll('.manpower-row');
        const nextNo = rows.length + 1;

        const tr = document.createElement('tr');
        tr.className = 'manpower-row';
        tr.innerHTML = `
            <td class="px-4 py-2 text-center font-bold text-gray-500 mp-no">${nextNo}</td>
            <td class="px-4 py-2">
                <input type="hidden" name="manpower[${manpowerIndex}][no]" class="mp-no-input" value="${nextNo}">
                <select class="w-full text-xs rounded border-gray-300 py-1 mb-1 role-select" onchange="toggleCustomRole(this, ${manpowerIndex})">
                    <option value="loading">Loading / Input / Packing</option>
                    <option value="sprayer">Sprayer</option>
                    <option value="checker">Checker</option>
                    <option value="qc">QC</option>
                    <option value="operator">Operator</option>
                    <option value="leader">Leader</option>
                    <option value="__custom__">Other (custom)...</option>
                </select>
                <input type="text" name="manpower[${manpowerIndex}][role]" value="loading" class="w-full text-xs rounded border-gray-300 py-1 role-input hidden" placeholder="Type custom role..." readonly>
            </td>
            <td class="px-4 py-2">
                <input type="text" name="manpower[${manpowerIndex}][name]" placeholder="Operator Name" class="w-full text-xs rounded border-gray-300 py-1">
            </td>
            <td class="px-4 py-2 text-center">
                <button type="button" onclick="removeManpowerRow(this)" class="text-red-500 hover:text-red-700 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        manpowerIndex++;
        updateManpowerNumbers();
    }

    function removeManpowerRow(btn) {
        const tr = btn.closest('tr');
        tr.remove();
        updateManpowerNumbers();
    }

    function updateManpowerNumbers() {
        const rows = document.querySelectorAll('.manpower-row');
        rows.forEach((row, index) => {
            const no = index + 1;
            row.querySelector('.mp-no').textContent = no;
            row.querySelector('.mp-no-input').value = no;
        });
    }

    // IPQC Measurement Toggles
    document.querySelectorAll('.meas-toggle-cb').forEach(cb => {
        cb.addEventListener('change', function() {
            const key = this.dataset.key;
            const cols = document.querySelectorAll('.meas-col-' + key);
            cols.forEach(col => {
                if (this.checked) {
                    col.classList.remove('hidden');
                } else {
                    col.classList.add('hidden');
                }
            });
        });
    });

    // IPQC Reject Rate Auto Calculation
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('ipqc-sample-input') || e.target.classList.contains(
                'ipqc-rejsample-input')) {
            const tr = e.target.closest('tr');
            if (tr) {
                const sample = parseFloat(tr.querySelector('.ipqc-sample-input')?.value || 0);
                const rejSample = parseFloat(tr.querySelector('.ipqc-rejsample-input')?.value || 0);
                const rejRateCell = tr.querySelector('.ipqc-rejrate-cell');
                if (rejRateCell) {
                    const rate = sample > 0 ? ((rejSample / sample) * 100).toFixed(2) : 0;
                    rejRateCell.textContent = rate + '%';
                }
            }
        }
    });

    // First Piece Live Gate Check
    function checkFirstPieceGate() {
        const partInput = document.querySelector('input[name="part_number"]');
        const dateInput = document.querySelector('input[name="date"]');
        const partNameInput = document.querySelector('input[name="part_name"]');
        const modelInput = document.querySelector('input[name="model"]');

        const banner = document.getElementById('first-piece-gate-banner');
        const icon = document.getElementById('first-piece-gate-icon');
        const text = document.getElementById('first-piece-gate-text');
        const action = document.getElementById('first-piece-gate-action');

        if (!partInput || !dateInput || !text) return;

        const partNumber = partInput.value.trim();
        const date = dateInput.value.trim();
        const partName = partNameInput ? encodeURIComponent(partNameInput.value.trim()) : '';
        const model = modelInput ? encodeURIComponent(modelInput.value.trim()) : '';

        if (!partNumber || !date) {
            text.innerHTML =
                '<span class="text-gray-500">Please enter Date and Part Number in Tab 1 to check First Piece gate status.</span>';
            action.innerHTML = '';
            return;
        }

        fetch(
                `/first-piece-inspections/check-approval?part_number=${encodeURIComponent(partNumber)}&date=${encodeURIComponent(date)}`
            )
            .then(res => res.json())
            .then(data => {
                if (data.approved) {
                    banner.className =
                        'bg-green-50 border border-green-300 p-4 rounded-lg flex flex-col md:flex-row justify-between items-center gap-4';
                    icon.className = 'p-2 rounded-full bg-green-200 text-green-700';
                    text.innerHTML =
                        `<span class="text-green-800 font-bold">✅ APPROVED</span> — Checked by <strong>${data.inspection.checked_by || 'QC'}</strong> on ${data.inspection.checked_at || ''}`;
                    action.innerHTML =
                        `<a href="/first-piece-inspections/${data.inspection.id}" target="_blank" class="bg-green-700 hover:bg-green-800 text-white text-xs font-bold py-1.5 px-4 rounded shadow transition inline-block">View First Piece Inspection &rarr;</a>`;
                } else {
                    banner.className =
                        'bg-amber-50 border border-amber-300 p-4 rounded-lg flex flex-col md:flex-row justify-between items-center gap-4';
                    icon.className = 'p-2 rounded-full bg-amber-200 text-amber-700';
                    text.innerHTML =
                        `<span class="text-amber-800 font-bold">⚠️ NOT YET APPROVED</span> — First Piece Inspection for part <strong>${partNumber}</strong> on ${date} is pending or missing.`;
                    action.innerHTML =
                        `<a href="/first-piece-inspections/create?part_number=${encodeURIComponent(partNumber)}&date=${encodeURIComponent(date)}&part_name=${partName}&model=${model}" target="_blank" class="bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold py-1.5 px-4 rounded shadow transition inline-block">+ Create First Piece Inspection &rarr;</a>`;
                }
            })
            .catch(err => {
                console.error('Error checking First Piece gate:', err);
            });
    }

    const partInputEl = document.querySelector('input[name="part_number"]');
    const dateInputEl = document.querySelector('input[name="date"]');
    if (partInputEl) partInputEl.addEventListener('change', checkFirstPieceGate);
    if (dateInputEl) dateInputEl.addEventListener('change', checkFirstPieceGate);

    // Initial check on load
    setTimeout(checkFirstPieceGate, 300);

    // Tab 2 Materials Dynamic Rows Logic
    let materialRowIndex = {{ $materialGlobalIndex ?? 100 }};

    window.addPaintMaterialRow = function() {
        const tbody = document.getElementById('paint-materials-tbody');
        if (!tbody) return;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="px-3 py-2">
                <input type="hidden" name="materials[${materialRowIndex}][type]" value="paint">
                <input type="text" name="materials[${materialRowIndex}][item_name]" value="" placeholder="Paint Item Name" class="w-full text-xs rounded border-gray-300 py-1 font-semibold">
            </td>
            <td class="px-2 py-2">
                <input type="text" name="materials[${materialRowIndex}][lot_number]" value="" placeholder="Lot" class="w-full text-xs rounded border-gray-300 py-1">
            </td>
            <td class="px-2 py-2">
                <input type="text" name="materials[${materialRowIndex}][visco]" value="" placeholder="Visco" class="w-full text-xs rounded border-gray-300 py-1">
            </td>
            <td class="px-2 py-2">
                <input type="text" name="materials[${materialRowIndex}][mixing_ratio]" value="" placeholder="Ratio (e.g. 1:1.5)" class="w-full text-xs rounded border-gray-300 py-1">
            </td>
            <td class="px-2 py-2">
                <input type="number" step="any" name="materials[${materialRowIndex}][qty]" value="" placeholder="Qty" class="w-full text-xs rounded border-gray-300 py-1">
            </td>
            <td class="px-2 py-2">
                <input type="text" name="materials[${materialRowIndex}][uom]" value="" placeholder="UOM" class="w-full text-xs rounded border-gray-300 py-1">
            </td>
            <td class="px-1 py-2 text-center">
                <button type="button" onclick="removeMaterialRow(this)" class="text-red-500 hover:text-red-700 font-bold px-1.5 py-0.5 text-sm rounded hover:bg-red-50 transition" title="Remove Row">&times;</button>
            </td>
        `;
        tbody.appendChild(tr);
        materialRowIndex++;
    };

    window.addPartMaterialRow = function() {
        const tbody = document.getElementById('part-materials-tbody');
        if (!tbody) return;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="px-3 py-2">
                <input type="hidden" name="materials[${materialRowIndex}][type]" value="part">
                <input type="text" name="materials[${materialRowIndex}][item_name]" value="" placeholder="Part / WIP Item Name" class="w-full text-xs rounded border-gray-300 py-1 font-semibold">
            </td>
            <td class="px-2 py-2">
                <input type="text" name="materials[${materialRowIndex}][lot_number]" value="" placeholder="Lot" class="w-full text-xs rounded border-gray-300 py-1">
            </td>
            <td class="px-2 py-2">
                <input type="number" step="any" name="materials[${materialRowIndex}][qty]" value="" placeholder="Qty" class="w-full text-xs rounded border-gray-300 py-1">
            </td>
            <td class="px-2 py-2">
                <input type="text" name="materials[${materialRowIndex}][uom]" value="" placeholder="UOM" class="w-full text-xs rounded border-gray-300 py-1">
            </td>
            <td class="px-1 py-2 text-center">
                <button type="button" onclick="removeMaterialRow(this)" class="text-red-500 hover:text-red-700 font-bold px-1.5 py-0.5 text-sm rounded hover:bg-red-50 transition" title="Remove Row">&times;</button>
            </td>
        `;
        tbody.appendChild(tr);
        materialRowIndex++;
    };

    window.removeMaterialRow = function(btn) {
        const tr = btn.closest('tr');
        if (tr) {
            tr.remove();
        }
    };
</script>
