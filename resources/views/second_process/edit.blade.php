<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('second-process-reports.update', $report->id) }}" method="POST" id="production-report-form">
                @csrf
                @method('PUT')
                
                <!-- Main Form Card -->
                <div class="bg-white shadow-xl rounded-lg overflow-hidden mb-6 border border-gray-200">
                    
                    <!-- Header Section -->
                    <div class="bg-gradient-to-r from-blue-700 to-indigo-800 text-white p-6">
                        <div class="flex flex-wrap justify-between items-center">
                            <div>
                                <h1 class="text-3xl font-extrabold tracking-tight">PT. DAIJO INDUSTRIAL</h1>
                                <p class="text-sm font-semibold opacity-90 mt-1">Second Process Departement</p>
                            </div>
                            <div class="text-right text-xs md:text-sm space-y-1 bg-white/10 p-3 rounded-lg backdrop-blur-sm mt-4 md:mt-0">
                                <div><span class="font-bold">No. Dokumen:</span> DI-F-P/PR/07/SP-001</div>
                                <div><span class="font-bold">Tgl. Dikeluarkan:</span> 04 Januari 2023</div>
                                <div><span class="font-bold">Mulai berlaku:</span> 08 Desember 2025</div>
                                <div><span class="font-bold">Revisi / Halaman:</span> 2 / 1 of 1</div>
                            </div>
                        </div>
                        <div class="text-center mt-6">
                            <h2 class="text-2xl font-bold uppercase tracking-wider">Edit Laporan Produksi Harian</h2>
                        </div>
                    </div>

                    <div class="p-6 space-y-8">
                        
                        <!-- 1. Header Information Section -->
                        <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Detail Produksi
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tanggal</label>
                                    <input type="date" name="date" value="{{ $report->date }}" class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Unit / Line</label>
                                    <input type="text" name="unit_line" value="{{ $report->unit_line }}" class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Shift</label>
                                    <select name="shift" class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                                        <option value="1" {{ $report->shift == '1' ? 'selected' : '' }}>Shift 1</option>
                                        <option value="2" {{ $report->shift == '2' ? 'selected' : '' }}>Shift 2</option>
                                        <option value="3" {{ $report->shift == '3' ? 'selected' : '' }}>Shift 3</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Proses Prod</label>
                                    <input type="text" name="process_prod" value="{{ $report->process_prod }}" class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Part Number</label>
                                    <div class="relative">
                                        <input type="text" name="part_number" id="part_number" value="{{ $report->part_number }}" placeholder="Search Part Number..." class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required autocomplete="off">
                                        <div id="part-number-dropdown" class="absolute left-0 right-0 mt-1 max-h-60 overflow-y-auto bg-white border border-gray-200 rounded shadow-lg z-50 hidden"></div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Part Name</label>
                                    <input type="text" name="part_name" value="{{ $report->part_name }}" placeholder="Auto-filled from Part Number" class="w-full rounded border-gray-300 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Model</label>
                                    <input type="text" name="model" value="{{ $report->model }}" class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Customer</label>
                                    <div class="relative">
                                        <input type="text" name="customer" id="customer" value="{{ $report->customer }}" placeholder="Search Customer..." class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required autocomplete="off">
                                        <div id="customer-dropdown" class="absolute left-0 right-0 mt-1 max-h-60 overflow-y-auto bg-white border border-gray-200 rounded shadow-lg z-50 hidden"></div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Target Perjam</label>
                                    <input type="number" name="target_per_hour" id="target_per_hour" value="{{ $report->target_per_hour }}" class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Jml Input WIP</label>
                                    <input type="number" name="jml_input_wip" id="jml_input_wip" value="{{ $report->jml_input_wip }}" class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Repairan</label>
                                    <input type="number" name="repairan" id="repairan" value="{{ $report->repairan }}" class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Jumlah Output</label>
                                    <input type="number" name="jumlah_output" id="jumlah_output" value="{{ $report->jumlah_output }}" class="w-full rounded border-gray-300 bg-gray-100 text-sm font-semibold" readonly>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1 text-green-700 font-extrabold">Jumlah OK</label>
                                    <input type="number" name="jumlah_ok" id="jumlah_ok" value="{{ $report->jumlah_ok }}" class="w-full rounded border-gray-300 bg-gray-100 text-sm font-semibold text-green-700" readonly>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1 text-red-700 font-extrabold">Jumlah NG</label>
                                    <input type="number" name="jumlah_ng" id="jumlah_ng" value="{{ $report->jumlah_ng }}" class="w-full rounded border-gray-300 bg-gray-100 text-sm font-semibold text-red-700" readonly>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1 text-red-700">NG Prosentase (%)</label>
                                    <input type="number" name="ng_prosentase" id="ng_prosentase" step="0.01" value="{{ $report->ng_prosentase }}" class="w-full rounded border-gray-300 bg-gray-100 text-sm font-semibold text-red-700" readonly>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Jml NG Lebur</label>
                                    <input type="number" name="jml_ng_lebur" id="jml_ng_lebur" value="{{ $report->jml_ng_lebur }}" class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                                </div>
                            </div>
                        </div>

                        <!-- 2. Materials & Production Results Section -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            
                            <!-- Material Tables Section -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-bold text-gray-800 border-b pb-2 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                                    Material
                                </h3>

                                <!-- Item Paint Table -->
                                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                                    <div class="bg-gray-100 px-4 py-2 border-b border-gray-200">
                                        <h4 class="text-sm font-bold text-gray-700">Item Paint</h4>
                                    </div>
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left font-bold text-gray-600">Item Paint</th>
                                                <th class="px-4 py-2 text-left font-bold text-gray-600 w-1/3">Lot Number</th>
                                                <th class="px-4 py-2 text-left font-bold text-gray-600">Visco</th>
                                                <th class="px-4 py-2 text-left font-bold text-gray-600">Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @php
                                                $defaultPaints = ['Paint Primer', 'Hardener', 'Paint Basecoat', 'Hardener', 'Paint Topcoat', 'Hardener'];
                                            @endphp
                                            @foreach($defaultPaints as $index => $paint)
                                                @php
                                                    $match = $report->materials->where('type', 'paint')->where('item_name', $paint)->values()->get($index) ?? $report->materials->where('type', 'paint')->where('item_name', $paint)->first();
                                                    if ($paint == 'Hardener') {
                                                        $hardeners = $report->materials->where('type', 'paint')->where('item_name', 'Hardener')->values();
                                                        if ($index == 1) $match = $hardeners->get(0);
                                                        if ($index == 3) $match = $hardeners->get(1);
                                                        if ($index == 5) $match = $hardeners->get(2);
                                                    }
                                                @endphp
                                                <tr>
                                                    <td class="px-4 py-2">
                                                        <span class="text-gray-700 font-semibold">{{ $paint }}</span>
                                                        <input type="hidden" name="materials[{{ $index }}][type]" value="paint">
                                                        <input type="hidden" name="materials[{{ $index }}][item_name]" value="{{ $paint }}">
                                                    </td>
                                                    <td class="px-4 py-2">
                                                        <input type="text" name="materials[{{ $index }}][lot_number]" value="{{ $match ? $match->lot_number : '' }}" class="w-full text-xs rounded border-gray-300 py-1">
                                                    </td>
                                                    <td class="px-4 py-2">
                                                        <input type="text" name="materials[{{ $index }}][visco]" value="{{ $match ? $match->visco : '' }}" class="w-full text-xs rounded border-gray-300 py-1">
                                                    </td>
                                                    <td class="px-4 py-2">
                                                        <input type="number" name="materials[{{ $index }}][qty]" value="{{ $match ? $match->qty : '' }}" class="w-full text-xs rounded border-gray-300 py-1">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Item Parts Table -->
                                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                                    <div class="bg-gray-100 px-4 py-2 border-b border-gray-200">
                                        <h4 class="text-sm font-bold text-gray-700">Item Parts</h4>
                                    </div>
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left font-bold text-gray-600">Item Parts</th>
                                                <th class="px-4 py-2 text-left font-bold text-gray-600 w-1/3">Lot Number</th>
                                                <th class="px-4 py-2 text-left font-bold text-gray-600">Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @php
                                                $defaultParts = ['WIP 1', 'WIP 2', 'WIP 3', 'Repairan 1', 'Repairan 2', 'Repairan 3'];
                                            @endphp
                                            @foreach($defaultParts as $index => $part)
                                                @php 
                                                    $ptIndex = count($defaultPaints) + $index; 
                                                    $match = $report->materials->where('type', 'part')->where('item_name', $part)->first();
                                                @endphp
                                                <tr>
                                                    <td class="px-4 py-2">
                                                        <span class="text-gray-700 font-semibold">{{ $part }}</span>
                                                        <input type="hidden" name="materials[{{ $ptIndex }}][type]" value="part">
                                                        <input type="hidden" name="materials[{{ $ptIndex }}][item_name]" value="{{ $part }}">
                                                    </td>
                                                    <td class="px-4 py-2">
                                                        <input type="text" name="materials[{{ $ptIndex }}][lot_number]" value="{{ $match ? $match->lot_number : '' }}" class="w-full text-xs rounded border-gray-300 py-1">
                                                    </td>
                                                    <td class="px-4 py-2">
                                                        <input type="number" name="materials[{{ $ptIndex }}][qty]" value="{{ $match ? $match->qty : '' }}" class="w-full text-xs rounded border-gray-300 py-1">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Hasil Produksi Section -->
                            @php
                                $currentHoursCount = max(8, $report->hourlyProductions->count());
                            @endphp
                            <div class="space-y-6">
                                <h3 class="text-lg font-bold text-gray-800 border-b pb-2 flex items-center justify-between">
                                    <span class="flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                        Hasil Produksi
                                    </span>
                                    <div class="flex space-x-1">
                                        <button type="button" id="remove-hour-btn" class="bg-red-500 hover:bg-red-600 text-white font-bold text-xs px-2 py-1 rounded shadow transition">
                                            - Hour
                                        </button>
                                        <button type="button" id="add-hour-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-2 py-1 rounded shadow transition">
                                            + Hour
                                        </button>
                                    </div>
                                </h3>

                                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-center font-bold text-gray-600">Jam Ke</th>
                                                <th class="px-4 py-2 text-center font-bold text-gray-600 w-1/3">OK</th>
                                                <th class="px-4 py-2 text-center font-bold text-gray-600 w-1/3">Acumulasi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200" id="hourly-ok-tbody">
                                            @for($hour = 1; $hour <= $currentHoursCount; $hour++)
                                                @php
                                                    $match = $report->hourlyProductions->where('hour_ke', $hour)->first();
                                                @endphp
                                                <tr class="hourly-ok-row" data-hour="{{ $hour }}">
                                                    <td class="px-4 py-2 text-center font-bold text-gray-700">{{ $hour }}</td>
                                                    <td class="px-4 py-2">
                                                        <input type="number" name="hourly[{{ $hour }}][hour_ke]" value="{{ $hour }}" class="hidden">
                                                        <input type="number" name="hourly[{{ $hour }}][ok_qty]" value="{{ $match ? $match->ok_qty : '' }}" placeholder="Qty OK" class="w-full text-xs rounded border-gray-300 py-1 text-center hourly-ok-input">
                                                    </td>
                                                    <td class="px-4 py-2">
                                                        <input type="number" name="hourly[{{ $hour }}][acumulasi_qty]" value="{{ $match ? $match->acumulasi_qty : '' }}" placeholder="Acumulasi" class="w-full text-xs rounded border-gray-300 bg-gray-100 py-1 text-center font-semibold hourly-accum-input" readonly>
                                                    </td>
                                                </tr>
                                            @endfor
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>

                        <!-- 3. Manpower Section -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-bold text-gray-800 border-b pb-2 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                Manpower (MP)
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                
                                <!-- MP Loading -->
                                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                                    <div class="bg-gray-100 px-4 py-2 border-b border-gray-200">
                                        <h4 class="text-sm font-bold text-gray-700">MP Loding / Input / Blow / Packing</h4>
                                    </div>
                                    <div class="p-3 space-y-2">
                                        @for($i = 1; $i <= 4; $i++)
                                            @php
                                                $match = $report->manpowers->where('role', 'loading')->where('no', $i)->first();
                                            @endphp
                                            <div class="flex items-center space-x-2">
                                                <span class="text-xs font-bold text-gray-500 w-6 text-right">{{ $i }}</span>
                                                <input type="hidden" name="manpower[{{ $i }}][role]" value="loading">
                                                <input type="hidden" name="manpower[{{ $i }}][no]" value="{{ $i }}">
                                                <input type="text" name="manpower[{{ $i }}][name]" value="{{ $match ? $match->name : '' }}" placeholder="Operator Name" class="w-full text-xs rounded border-gray-300 py-1">
                                            </div>
                                        @endfor
                                    </div>
                                </div>

                                <!-- MP Sprayer -->
                                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                                    <div class="bg-gray-100 px-4 py-2 border-b border-gray-200">
                                        <h4 class="text-sm font-bold text-gray-700">MP Sprayer</h4>
                                    </div>
                                    <div class="p-3 space-y-2">
                                        @for($i = 1; $i <= 4; $i++)
                                            @php 
                                                $sprIndex = 4 + $i; 
                                                $match = $report->manpowers->where('role', 'sprayer')->where('no', $i)->first();
                                            @endphp
                                            <div class="flex items-center space-x-2">
                                                <span class="text-xs font-bold text-gray-500 w-6 text-right">{{ $i }}</span>
                                                <input type="hidden" name="manpower[{{ $sprIndex }}][role]" value="sprayer">
                                                <input type="hidden" name="manpower[{{ $sprIndex }}][no]" value="{{ $i }}">
                                                <input type="text" name="manpower[{{ $sprIndex }}][name]" value="{{ $match ? $match->name : '' }}" placeholder="Sprayer Name" class="w-full text-xs rounded border-gray-300 py-1">
                                            </div>
                                        @endfor
                                    </div>
                                </div>

                                <!-- MP Checker -->
                                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                                    <div class="bg-gray-100 px-4 py-2 border-b border-gray-200">
                                        <h4 class="text-sm font-bold text-gray-700">MP Checker</h4>
                                    </div>
                                    <div class="p-3 space-y-2">
                                        @for($i = 1; $i <= 4; $i++)
                                            @php 
                                                $chkIndex = 8 + $i; 
                                                $match = $report->manpowers->where('role', 'checker')->where('no', $i)->first();
                                            @endphp
                                            <div class="flex items-center space-x-2">
                                                <span class="text-xs font-bold text-gray-500 w-6 text-right">{{ $i }}</span>
                                                <input type="hidden" name="manpower[{{ $chkIndex }}][role]" value="checker">
                                                <input type="hidden" name="manpower[{{ $chkIndex }}][no]" value="{{ $i }}">
                                                <input type="text" name="manpower[{{ $chkIndex }}][name]" value="{{ $match ? $match->name : '' }}" placeholder="Checker Name" class="w-full text-xs rounded border-gray-300 py-1">
                                            </div>
                                        @endfor
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- 4. NG Produksi Section -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-bold text-gray-800 border-b pb-2 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                NG Produksi / Jam
                            </h3>
                            <div class="bg-white rounded-lg border border-gray-200 overflow-x-auto shadow-sm">
                                <table class="min-w-full divide-y divide-gray-200 text-xs text-center">
                                    <thead class="bg-gray-50 font-bold text-gray-600">
                                        <tr id="ng-header-row">
                                            <th class="px-2 py-2 text-left">ITEMS NG</th>
                                            @for($h = 1; $h <= $currentHoursCount; $h++)
                                                <th class="px-2 py-2 w-10 ng-hour-header" data-hour="{{ $h }}">{{ $h }}</th>
                                            @endfor
                                            <th class="px-2 py-2 w-20 font-extrabold text-red-600">Total NG</th>
                                            <th class="px-2 py-2 text-left w-1/4">NG Input (Item NG / Qty)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200" id="ng-tbody">
                                        @php
                                            $defaultNgs = ['SCRATCH', 'DIRTY', 'HAIR MARK', 'DENTED', 'OVER CUT'];
                                        @endphp
                                        @foreach($defaultNgs as $index => $ng)
                                            @php
                                                $match = $report->ngRecords->where('ng_name', $ng)->first();
                                            @endphp
                                            <tr class="ng-row">
                                                <td class="px-2 py-2 text-left font-semibold text-gray-700">
                                                    {{ $ng }}
                                                    <input type="hidden" name="ngs[{{ $index }}][ng_name]" value="{{ $ng }}">
                                                </td>
                                                @for($h = 1; $h <= $currentHoursCount; $h++)
                                                    @php $hField = 'hour_'.$h; @endphp
                                                    <td class="px-1 py-2 ng-hour-cell" data-hour="{{ $h }}">
                                                        <input type="number" name="ngs[{{ $index }}][hour_{{ $h }}]" value="{{ $match ? $match->$hField : '' }}" class="w-full text-center rounded border-gray-300 p-1 text-xs ng-hourly-input" data-hour="{{ $h }}">
                                                    </td>
                                                @endfor
                                                <td class="px-2 py-2 font-extrabold text-red-600">
                                                    <input type="number" name="ngs[{{ $index }}][total_ng]" value="{{ $match ? $match->total_ng : 0 }}" class="w-full text-center rounded border-gray-300 bg-gray-100 p-1 text-xs font-bold text-red-600 ng-total-output" readonly>
                                                </td>
                                                <td class="px-2 py-2 flex space-x-1 items-center">
                                                    <input type="text" name="ngs[{{ $index }}][ng_input_item]" value="{{ $match ? $match->ng_input_item : '' }}" placeholder="Item NG" class="w-2/3 rounded border-gray-300 p-1 text-xs">
                                                    <input type="number" name="ngs[{{ $index }}][ng_input_qty]" value="{{ $match ? $match->ng_input_qty : '' }}" placeholder="Qty" class="w-1/3 rounded border-gray-300 p-1 text-xs">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- 5. Troubles Section -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-bold text-gray-800 border-b pb-2 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                Trouble Report
                            </h3>
                            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50 font-bold text-gray-600">
                                        <tr>
                                            <th class="px-4 py-2 text-left w-1/4">Penyebab</th>
                                            <th class="px-4 py-2 text-left">Penanganan</th>
                                            <th class="px-4 py-2 text-left w-1/5">Loss Time</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @php
                                            $defaultTroubles = ['Man', 'Mesin', 'Part', 'PPS', 'Lingkungan'];
                                        @endphp
                                        @foreach($defaultTroubles as $index => $trouble)
                                            @php
                                                $match = $report->troubles->where('penyebab', $trouble)->first();
                                            @endphp
                                            <tr>
                                                <td class="px-4 py-2 font-semibold text-gray-700">
                                                    {{ $trouble }}
                                                    <input type="hidden" name="troubles[{{ $index }}][penyebab]" value="{{ $trouble }}">
                                                </td>
                                                <td class="px-4 py-2">
                                                    <textarea name="troubles[{{ $index }}][penanganan]" rows="1" class="w-full rounded border-gray-300 text-xs py-1" placeholder="Describe actions...">{{ $match ? $match->penanganan : '' }}</textarea>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <input type="text" name="troubles[{{ $index }}][loss_time]" value="{{ $match ? $match->loss_time : '' }}" placeholder="e.g. 15 mins" class="w-full text-xs rounded border-gray-300 py-1">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- 6. Bottom Notes & Schedule Section -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-5 rounded-lg border border-gray-200 text-sm">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Catatan Produksi</label>
                                    <textarea name="production_notes" rows="4" class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-xs" placeholder="General production notes...">{{ $report->production_notes }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Karyawan Tidak Hadir</label>
                                    <input type="text" name="absent_employees" value="{{ $report->absent_employees }}" placeholder="Absent employees list..." class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-xs">
                                </div>
                            </div>
                            <div class="space-y-4">
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Jadwal Produksi Selanjutnya</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @for($i = 0; $i < 4; $i++)
                                        @php
                                            $schVal = $report->next_production_schedule[$i] ?? '';
                                            // Handle fallback if schedule was stored as combined string
                                            if (empty($schVal) && is_string($report->next_production_schedule)) {
                                                $schedules = explode("\n", $report->next_production_schedule);
                                                foreach($schedules as $s) {
                                                    if(strpos($s, ($i+1) . ': ') === 0) $schVal = substr($s, 3);
                                                }
                                            }
                                        @endphp
                                        <div class="flex items-center space-x-2">
                                            <span class="text-xs font-bold text-gray-500 w-4">{{ $i + 1 }}.</span>
                                            <input type="text" name="next_production_schedule[]" value="{{ $schVal }}" placeholder="Next schedule item" class="w-full text-xs rounded border-gray-300 py-1 schedule-input">
                                        </div>
                                    @endfor
                                </div>

                                <!-- Signature / Approvals Input -->
                                <div class="grid grid-cols-3 gap-2 pt-4 border-t border-gray-200">
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Dibuat</label>
                                        <input type="text" name="created_by_name" value="{{ $report->created_by_name }}" placeholder="Operator Name" class="w-full text-xs rounded border-gray-300 py-1">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">PQC</label>
                                        <input type="text" name="pqc_name" value="{{ $report->pqc_name }}" placeholder="PQC Inspector" class="w-full text-xs rounded border-gray-300 py-1">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Mengetahui</label>
                                        <input type="text" name="acknowledged_by_name" value="{{ $report->acknowledged_by_name }}" placeholder="Supervisor" class="w-full text-xs rounded border-gray-300 py-1">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Actions Footer -->
                    <div class="bg-gray-100 px-6 py-4 flex justify-between items-center border-t border-gray-200">
                        <a href="{{ route('second-process-reports.show', $report->id) }}" class="text-gray-600 hover:text-gray-800 text-sm font-semibold transition">Cancel</a>
                        <button type="submit" id="submit-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow-md transition">
                            Save Changes
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- Script calculations & interactions -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            const form = document.getElementById('production-report-form');
            const submitBtn = document.getElementById('submit-btn');

            // 1. Double Submission Protection
            form.addEventListener('submit', function () {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg> Saving...`;
            });

            // 2. Autocomplete helper
            function setupAutocomplete(inputId, dropdownId, url, onSelect) {
                const input = document.getElementById(inputId);
                const dropdown = document.getElementById(dropdownId);
                let debounceTimer;

                input.addEventListener('input', function () {
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
                                    div.className = 'px-4 py-2 hover:bg-blue-50 cursor-pointer text-xs border-b border-gray-100 last:border-b-0 text-gray-800 transition';
                                    
                                    if (item.item_code) {
                                        div.innerHTML = `<span class="font-bold text-blue-700">${item.item_code}</span> - <span class="text-gray-500">${item.item_description || ''}</span>`;
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

                document.addEventListener('click', function (e) {
                    if (e.target !== input && e.target !== dropdown) {
                        dropdown.classList.add('hidden');
                    }
                });
            }

            // Initialize Autocompletes
            setupAutocomplete('part_number', 'part-number-dropdown', '{{ route("second-process-reports.search-items") }}', function (item) {
                document.getElementById('part_number').value = item.item_code;
                document.querySelector('input[name="part_name"]').value = item.item_description || '';
            });

            setupAutocomplete('customer', 'customer-dropdown', '{{ route("second-process-reports.search-customers") }}', function (item) {
                document.getElementById('customer').value = item.name;
            });

            // 3. Dynamic Hour Management (OK and NG Tables Sync)
            const addHourBtn = document.getElementById('add-hour-btn');
            const removeHourBtn = document.getElementById('remove-hour-btn');
            const okTbody = document.getElementById('hourly-ok-tbody');
            const ngHeaderRow = document.getElementById('ng-header-row');
            const ngTbody = document.getElementById('ng-tbody');

            addHourBtn.addEventListener('click', function () {
                const currentHours = document.querySelectorAll('.hourly-ok-row').length;
                if (currentHours >= 12) {
                    alert('Maximum shift limit is 12 hours.');
                    return;
                }

                const newHour = currentHours + 1;

                // Add OK Row
                const okRow = `
                    <tr class="hourly-ok-row" data-hour="${newHour}">
                        <td class="px-4 py-2 text-center font-bold text-gray-700">${newHour}</td>
                        <td class="px-4 py-2">
                            <input type="number" name="hourly[${newHour}][hour_ke]" value="${newHour}" class="hidden">
                            <input type="number" name="hourly[${newHour}][ok_qty]" placeholder="Qty OK" class="w-full text-xs rounded border-gray-300 py-1 text-center hourly-ok-input">
                        </td>
                        <td class="px-4 py-2">
                            <input type="number" name="hourly[${newHour}][acumulasi_qty]" placeholder="Acumulasi" class="w-full text-xs rounded border-gray-300 bg-gray-100 py-1 text-center font-semibold hourly-accum-input" readonly>
                        </td>
                    </tr>`;
                okTbody.insertAdjacentHTML('beforeend', okRow);

                // Add NG Header column
                const totalNgHeader = ngHeaderRow.querySelector('th.text-red-600');
                const th = `<th class="px-2 py-2 w-10 ng-hour-header" data-hour="${newHour}">${newHour}</th>`;
                totalNgHeader.insertAdjacentHTML('beforebegin', th);

                // Add NG Inputs to each row
                const ngRows = document.querySelectorAll('.ng-row');
                ngRows.forEach((row, index) => {
                    const totalNgCell = row.querySelector('.ng-total-output').closest('td');
                    const td = `
                        <td class="px-1 py-2 ng-hour-cell" data-hour="${newHour}">
                            <input type="number" name="ngs[${index}][hour_${newHour}]" class="w-full text-center rounded border-gray-300 p-1 text-xs ng-hourly-input" data-hour="${newHour}">
                        </td>`;
                    totalNgCell.insertAdjacentHTML('beforebegin', td);
                });

                calculateHourlyAccumulation();
                calculateNgTotals();
            });

            removeHourBtn.addEventListener('click', function () {
                const currentHours = document.querySelectorAll('.hourly-ok-row').length;
                if (currentHours <= 1) {
                    alert('At least 1 hour of production is required.');
                    return;
                }

                // Remove OK Row
                document.querySelector(`.hourly-ok-row[data-hour="${currentHours}"]`).remove();

                // Remove NG Header Column
                document.querySelector(`.ng-hour-header[data-hour="${currentHours}"]`).remove();

                // Remove NG Cells
                document.querySelectorAll(`.ng-hour-cell[data-hour="${currentHours}"]`).forEach(el => el.remove());

                calculateHourlyAccumulation();
                calculateNgTotals();
            });

            // 4. Calculations (using Event Delegation)
            const totalOkField = document.getElementById('jumlah_ok');
            const totalNgField = document.getElementById('jumlah_ng');
            const totalOutputField = document.getElementById('jumlah_output');
            const ngPercentageField = document.getElementById('ng_prosentase');

            function calculateHourlyAccumulation() {
                const hourlyOkInputs = document.querySelectorAll('.hourly-ok-input');
                const hourlyAccumInputs = document.querySelectorAll('.hourly-accum-input');
                
                let accumulated = 0;
                hourlyOkInputs.forEach((input, index) => {
                    const val = parseInt(input.value) || 0;
                    accumulated += val;
                    if (hourlyAccumInputs[index]) {
                        hourlyAccumInputs[index].value = accumulated > 0 ? accumulated : '';
                    }
                });
                totalOkField.value = accumulated;
                calculateSummaryTotals();
            }

            function calculateNgTotals() {
                const ngRows = document.querySelectorAll('.ng-row');
                let overallNg = 0;

                ngRows.forEach(row => {
                    const rowInputs = row.querySelectorAll('.ng-hourly-input');
                    const rowTotalField = row.querySelector('.ng-total-output');
                    let rowTotal = 0;
                    rowInputs.forEach(input => {
                        rowTotal += parseInt(input.value) || 0;
                    });
                    rowTotalField.value = rowTotal;
                    overallNg += rowTotal;
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
            }

            // Event delegation on document level to auto-capture inputs
            document.addEventListener('input', function (e) {
                if (e.target.classList.contains('hourly-ok-input')) {
                    calculateHourlyAccumulation();
                }
                if (e.target.classList.contains('ng-hourly-input')) {
                    calculateNgTotals();
                }
            });

            // Initial Calculations
            calculateHourlyAccumulation();
            calculateNgTotals();
        });
    </script>
</x-app-layout>
