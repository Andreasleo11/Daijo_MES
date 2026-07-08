<x-app-layout>
    <div class="py-6 no-print">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex justify-between">
            <a href="{{ route('second-process-reports.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded transition">
                &larr; Back to List
            </a>
            <div class="space-x-2">
                <button onclick="window.print()" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition">
                    Print Report
                </button>
                @if($report->status === 'draft')
                    <a href="{{ route('second-process-reports.edit', $report->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded transition">
                        Edit Report
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 no-print mb-4">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative text-sm" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 no-print mb-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-sm" role="alert">
                <span class="block sm:inline">{{ $errors->first() }}</span>
            </div>
        </div>
    @endif

    <!-- Workflow Approval Banner -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 no-print mb-6">
        <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
            <div>
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Report Status</h3>
                <div class="flex items-center space-x-2 mt-1">
                    @if($report->status === 'draft')
                        <span class="px-2.5 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-800 uppercase">Draft</span>
                        <span class="text-xs text-gray-500">Only editable by operator. Needs submission.</span>
                    @elseif($report->status === 'submitted')
                        <span class="px-2.5 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-800 uppercase">Submitted</span>
                        <span class="text-xs text-gray-500">Pending Quality Inspection (PQC) sign-off.</span>
                    @elseif($report->status === 'pqc_approved')
                        <span class="px-2.5 py-1 text-xs font-semibold rounded bg-yellow-100 text-yellow-800 uppercase">PQC Approved</span>
                        <span class="text-xs text-gray-500">Pending Team Leader review.</span>
                    @elseif($report->status === 'leader_approved')
                        <span class="px-2.5 py-1 text-xs font-semibold rounded bg-orange-100 text-orange-800 uppercase">Leader Approved</span>
                        <span class="text-xs text-gray-500">Pending Supervisor acknowledgment.</span>
                    @elseif($report->status === 'acknowledged')
                        <span class="px-2.5 py-1 text-xs font-semibold rounded bg-green-100 text-green-800 uppercase">Acknowledged</span>
                        <span class="text-xs text-gray-500">Fully approved and finalized.</span>
                    @endif
                </div>
            </div>
            
            <div class="flex items-center space-x-2">
                @if($report->status === 'draft')
                    <form action="{{ route('second-process-reports.sign', [$report->id, 'checker']) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow transition text-sm">
                            Submit Report
                        </button>
                    </form>
                @elseif($report->status === 'submitted')
                    <form action="{{ route('second-process-reports.sign', [$report->id, 'pqc']) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded shadow transition text-sm mr-2">
                            Sign as PQC
                        </button>
                    </form>
                    <button onclick="document.getElementById('reject-dialog').showModal()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow transition text-sm">
                        Reject
                    </button>
                @elseif($report->status === 'pqc_approved')
                    <form action="{{ route('second-process-reports.sign', [$report->id, 'leader']) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded shadow transition text-sm mr-2">
                            Sign as Leader
                        </button>
                    </form>
                    <button onclick="document.getElementById('reject-dialog').showModal()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow transition text-sm">
                        Reject
                    </button>
                @elseif($report->status === 'leader_approved')
                    <form action="{{ route('second-process-reports.sign', [$report->id, 'acknowledged']) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded shadow transition text-sm mr-2">
                            Acknowledge (Supervisor)
                        </button>
                    </form>
                    <button onclick="document.getElementById('reject-dialog').showModal()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow transition text-sm">
                        Reject
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Rejection Dialog -->
    <dialog id="reject-dialog" class="rounded-lg p-6 shadow-2xl border border-gray-300 w-full max-w-md backdrop:bg-gray-900/50">
        <form action="{{ route('second-process-reports.reject', $report->id) }}" method="POST">
            @csrf
            <h3 class="text-sm font-bold text-gray-900 mb-2">Reject Production Report</h3>
            <p class="text-[11px] text-gray-500 mb-4 leading-normal font-medium">Please provide a brief reason for rejecting this report. The report status will return to Draft and signatures will be cleared.</p>
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Rejection Reason</label>
                <textarea name="rejection_reason" rows="3" required class="w-full text-xs rounded border-gray-300 focus:border-red-500 focus:ring-red-500" placeholder="E.g., NG quantities on hour 4 mismatch..."></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('reject-dialog').close()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-3 py-1.5 rounded text-xs font-bold transition">
                    Cancel
                </button>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded text-xs font-bold shadow-sm transition">
                    Submit Rejection
                </button>
            </div>
        </form>
    </dialog>

    <!-- Printable Area -->
    @php
        $currentHoursCount = max(8, $report->hourlyProductions->count());
    @endphp
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pb-12 print-area">
        <div class="bg-white shadow-xl rounded-lg overflow-hidden border border-gray-200 p-6 space-y-6">
            
            <!-- Header Table style -->
            <div class="border-2 border-black grid grid-cols-1 md:grid-cols-4 divide-y md:divide-y-0 md:divide-x divide-black text-sm">
                <div class="p-4 flex flex-col justify-center items-center col-span-2">
                    <h1 class="text-xl font-black text-center tracking-tight">PT. DAIJO INDUSTRIAL</h1>
                    <p class="text-xs font-bold text-center mt-1">Second Process Departement</p>
                </div>
                <div class="p-2 grid grid-cols-2 gap-x-2 text-[11px] leading-tight col-span-2">
                    <div class="font-bold">No. Dokumen</div><div>: DI-F-P/PR/07/SP-001</div>
                    <div class="font-bold">Tgl. Dikeluarkan</div><div>: 04 Januari 2023</div>
                    <div class="font-bold">Mulai berlaku</div><div>: 08 Desember 2025</div>
                    <div class="font-bold">Revisi / Hal</div><div>: 2 / 1 of 1</div>
                </div>
            </div>

            <div class="text-center mt-4">
                <h2 class="text-xl font-bold uppercase tracking-wider underline">Laporan Produksi Harian</h2>
            </div>

            <!-- Header Fields -->
            <table class="w-full border-collapse border border-black text-xs">
                <tbody>
                    <tr>
                        <td class="border border-black p-2 font-bold bg-gray-50 w-1/6">Tanggal</td>
                        <td class="border border-black p-2 w-1/4">{{ $report->date }}</td>
                        <td class="border border-black p-2 font-bold bg-gray-50 w-1/6">Model</td>
                        <td class="border border-black p-2 w-1/4">{{ $report->model }}</td>
                        <td class="border border-black p-2 font-bold bg-gray-50 w-1/12">Target/Jam</td>
                        <td class="border border-black p-2">{{ $report->target_per_hour }}</td>
                    </tr>
                    <tr>
                        <td class="border border-black p-2 font-bold bg-gray-50">Unit / Line</td>
                        <td class="border border-black p-2">{{ $report->unit_line }}</td>
                        <td class="border border-black p-2 font-bold bg-gray-50">Part Number</td>
                        <td class="border border-black p-2">{{ $report->part_number }}</td>
                        <td class="border border-black p-2 font-bold bg-gray-50">Jml Input WIP</td>
                        <td class="border border-black p-2">{{ $report->jml_input_wip }}</td>
                    </tr>
                    <tr>
                        <td class="border border-black p-2 font-bold bg-gray-50">Shift</td>
                        <td class="border border-black p-2">{{ $report->shift }}</td>
                        <td class="border border-black p-2 font-bold bg-gray-50">Part Name</td>
                        <td class="border border-black p-2">{{ $report->part_name }}</td>
                        <td class="border border-black p-2 font-bold bg-gray-50">Repairan</td>
                        <td class="border border-black p-2">{{ $report->repairan }}</td>
                    </tr>
                    <tr>
                        <td class="border border-black p-2 font-bold bg-gray-50">Proses Prod</td>
                        <td class="border border-black p-2">{{ $report->process_prod }}</td>
                        <td class="border border-black p-2 font-bold bg-gray-50">Customer</td>
                        <td class="border border-black p-2">{{ $report->customer }}</td>
                        <td class="border border-black p-2 font-bold bg-gray-50">Jumlah Output</td>
                        <td class="border border-black p-2 font-semibold">{{ $report->jumlah_output }}</td>
                    </tr>
                    <tr>
                        <td class="border border-black p-2 font-bold bg-gray-50">Status</td>
                        <td class="border border-black p-2 uppercase font-semibold text-blue-700">{{ $report->status ?: 'draft' }}</td>
                        <td class="border border-black p-2 font-bold bg-gray-50">Tujuan Output</td>
                        <td class="border border-black p-2 uppercase font-semibold text-indigo-700">{{ $report->output_destination ?: '-' }}</td>
                        <td class="border border-black p-2 font-bold bg-gray-50">NG Remarks</td>
                        <td class="border border-black p-2">{{ $report->ng_remarks ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="border border-black"></td>
                        <td class="border border-black p-2 font-bold bg-gray-50 text-green-700">Jumlah OK</td>
                        <td class="border border-black p-2 text-green-700 font-bold">{{ $report->jumlah_ok }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="border border-black"></td>
                        <td class="border border-black p-2 font-bold bg-gray-50 text-red-700">Jumlah NG</td>
                        <td class="border border-black p-2 text-red-700 font-bold">{{ $report->jumlah_ng }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="border border-black"></td>
                        <td class="border border-black p-2 font-bold bg-gray-50 text-red-700">NG Prosentase</td>
                        <td class="border border-black p-2 text-red-700 font-bold">{{ $report->ng_prosentase }}%</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="border border-black"></td>
                        <td class="border border-black p-2 font-bold bg-gray-50">Jml NG Lebur</td>
                        <td class="border border-black p-2">{{ $report->jml_ng_lebur }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Materials & Results section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <!-- Materials Column -->
                <div class="space-y-4">
                    <h3 class="text-sm font-bold border-b border-black pb-1">Material</h3>
                    
                    <table class="w-full border-collapse border border-black text-[11px]">
                        <thead class="bg-gray-100 font-bold">
                            <tr>
                                <th class="border border-black p-1 text-left">Item Paint</th>
                                <th class="border border-black p-1">Lot Number</th>
                                <th class="border border-black p-1">Visco</th>
                                <th class="border border-black p-1">Mixing Ratio</th>
                                <th class="border border-black p-1">Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($report->materials->where('type', 'paint') as $material)
                                <tr>
                                    <td class="border border-black p-1 font-semibold">{{ $material->item_name }}</td>
                                    <td class="border border-black p-1 text-center">{{ $material->lot_number }}</td>
                                    <td class="border border-black p-1 text-center">{{ $material->visco }}</td>
                                    <td class="border border-black p-1 text-center font-mono">{{ $material->mixing_ratio ?: '-' }}</td>
                                    <td class="border border-black p-1 text-center">{{ $material->qty }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <table class="w-full border-collapse border border-black text-[11px]">
                        <thead class="bg-gray-100 font-bold">
                            <tr>
                                <th class="border border-black p-1 text-left">Item Parts</th>
                                <th class="border border-black p-1">Lot Number</th>
                                <th class="border border-black p-1">Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($report->materials->where('type', 'part') as $material)
                                <tr>
                                    <td class="border border-black p-1 font-semibold">{{ $material->item_name }}</td>
                                    <td class="border border-black p-1 text-center">{{ $material->lot_number }}</td>
                                    <td class="border border-black p-1 text-center">{{ $material->qty }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Hourly Production Column -->
                <div class="space-y-4">
                    <h3 class="text-sm font-bold border-b border-black pb-1">Hasil Produksi</h3>
                    <table class="w-full border-collapse border border-black text-[11px] text-center">
                        <thead class="bg-gray-100 font-bold">
                            <tr>
                                <th class="border border-black p-1">Jam ke</th>
                                <th class="border border-black p-1">OK</th>
                                <th class="border border-black p-1">Acumulasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for($hour = 1; $hour <= $currentHoursCount; $hour++)
                                @php
                                    $hData = $report->hourlyProductions->where('hour_ke', $hour)->first();
                                @endphp
                                <tr>
                                    <td class="border border-black p-1 font-bold">{{ $hour }}</td>
                                    <td class="border border-black p-1">{{ $hData && $hData->ok_qty !== null ? $hData->ok_qty : '-' }}</td>
                                    <td class="border border-black p-1 font-semibold">{{ $hData && $hData->acumulasi_qty !== null ? $hData->acumulasi_qty : '-' }}</td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>

            </div>

            <!-- Manpower Section -->
            <div>
                <h3 class="text-sm font-bold border-b border-black pb-1 mb-2">Manpower (MP)</h3>
                <div class="grid grid-cols-3 gap-4 text-[11px]">
                    <div class="border border-black">
                        <div class="bg-gray-100 p-1 font-bold text-center border-b border-black">Loading / Input / packing</div>
                        <ul class="divide-y divide-black p-1">
                            @foreach($report->manpowers->where('role', 'loading')->sortBy('no') as $mp)
                                <li class="py-1 px-2">{{ $mp->no }}. {{ $mp->name ?: '-' }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="border border-black">
                        <div class="bg-gray-100 p-1 font-bold text-center border-b border-black">MP Sprayer</div>
                        <ul class="divide-y divide-black p-1">
                            @foreach($report->manpowers->where('role', 'sprayer')->sortBy('no') as $mp)
                                <li class="py-1 px-2">{{ $mp->no }}. {{ $mp->name ?: '-' }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="border border-black">
                        <div class="bg-gray-100 p-1 font-bold text-center border-b border-black">MP Checker</div>
                        <ul class="divide-y divide-black p-1">
                            @foreach($report->manpowers->where('role', 'checker')->sortBy('no') as $mp)
                                <li class="py-1 px-2">{{ $mp->no }}. {{ $mp->name ?: '-' }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- NG Produksi table -->
            <div>
                <h3 class="text-sm font-bold border-b border-black pb-1 mb-2">NG Produksi / Jam</h3>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-black text-[11px] text-center">
                        <thead class="bg-gray-100 font-bold">
                            <tr>
                                <th class="border border-black p-1 text-left">ITEMS NG</th>
                                @for($h = 1; $h <= $currentHoursCount; $h++)
                                    <th class="border border-black p-1 w-8">{{ $h }}</th>
                                @endfor
                                <th class="border border-black p-1 w-16 font-bold text-red-600">Total NG</th>
                                <th class="border border-black p-1 text-left">NG Input (Item NG / Qty)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $defaultNgs = ['SCRATCH', 'DIRTY', 'HAIR MARK', 'DENTED', 'OVER CUT'];
                            @endphp
                            @foreach($defaultNgs as $ngName)
                                @php
                                    $ng = $report->ngRecords->where('ng_name', $ngName)->first();
                                @endphp
                                <tr>
                                    <td class="border border-black p-1 text-left font-bold">{{ $ngName }}</td>
                                    @for($h = 1; $h <= $currentHoursCount; $h++)
                                        @php
                                            $detail = $ng ? $ng->hourlyDetails->where('hour_ke', $h)->first() : null;
                                        @endphp
                                        <td class="border border-black p-1">{{ $detail && $detail->qty !== null ? $detail->qty : '-' }}</td>
                                    @endfor
                                    <td class="border border-black p-1 font-bold text-red-600">{{ $ng && $ng->total_ng ? $ng->total_ng : '-' }}</td>
                                    <td class="border border-black p-1 text-left px-2">
                                        @if($ng && $ng->ng_input_item)
                                            {{ $ng->ng_input_item }} ({{ $ng->ng_input_qty }})
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Troubles section -->
            <div>
                <h3 class="text-sm font-bold border-b border-black pb-1 mb-2">Trouble Report</h3>
                <table class="w-full border-collapse border border-black text-[11px]">
                    <thead class="bg-gray-100 font-bold">
                        <tr>
                            <th class="border border-black p-1 w-1/5">Category</th>
                            <th class="border border-black p-1 w-1/3">Masalah (Problem)</th>
                            <th class="border border-black p-1">Penanganan (Countermeasure)</th>
                            <th class="border border-black p-1 w-1/6">Loss Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $defaultTroubles = ['Man', 'Mesin', 'Part', 'PPS', 'Lingkungan'];
                        @endphp
                        @foreach($defaultTroubles as $causes)
                            @php
                                $trouble = $report->troubles->where('penyebab', $causes)->first();
                            @endphp
                            <tr>
                                <td class="border border-black p-2 font-bold">{{ $causes }}</td>
                                <td class="border border-black p-2">{{ $trouble && $trouble->masalah ? $trouble->masalah : '-' }}</td>
                                <td class="border border-black p-2">{{ $trouble && $trouble->penanganan ? $trouble->penanganan : '-' }}</td>
                                <td class="border border-black p-2 text-center">
                                    @if($trouble)
                                        @if($trouble->loss_time_minutes)
                                            {{ $trouble->loss_time_minutes }} mins
                                        @else
                                            {{ $trouble->loss_time ?: '-' }}
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Notes & Footer signature -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div class="space-y-2 border border-black p-3">
                    <div>
                        <span class="font-bold uppercase">Catatan Produksi:</span>
                        <p class="mt-1 whitespace-pre-wrap">{{ $report->production_notes ?: '-' }}</p>
                    </div>
                    <div class="pt-2 border-t border-gray-300">
                        <span class="font-bold uppercase">Karyawan Tidak Hadir:</span>
                        <p class="mt-1">{{ $report->absent_employees ?: '-' }}</p>
                    </div>
                </div>
                <div class="flex flex-col justify-between border border-black p-3">
                    <div>
                        <span class="font-bold uppercase">Jadwal Produksi Selanjutnya:</span>
                        <div class="mt-1">
                            @if(is_array($report->next_production_schedule))
                                <ol class="list-decimal pl-4 space-y-1">
                                    @foreach($report->next_production_schedule as $sch)
                                        @if(!empty($sch))
                                            <li>{{ $sch }}</li>
                                        @endif
                                    @endforeach
                                </ol>
                            @elseif(is_string($report->next_production_schedule))
                                <pre class="font-sans text-xs whitespace-pre-wrap">{{ $report->next_production_schedule }}</pre>
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    
                    <!-- Signatures display -->
                    <div class="grid grid-cols-4 gap-2 text-center pt-4 border-t border-black mt-4 text-[10px]">
                        <div class="flex flex-col justify-between h-24">
                            <span class="font-bold">Dibuat (Checker)</span>
                            <div>
                                @if($report->created_by_signed_at)
                                    <div class="text-[8px] font-bold text-blue-600 uppercase tracking-wider border border-dashed border-blue-600 rounded px-1 py-0.5 max-w-xs mx-auto mb-1 leading-none">DIGITALLY SIGNED</div>
                                    <span class="underline font-semibold block leading-tight">{{ $report->created_by_name }}</span>
                                    <span class="text-gray-500 text-[9px]">{{ \Carbon\Carbon::parse($report->created_by_signed_at)->format('d/m/Y H:i') }}</span>
                                @else
                                    <span class="underline font-semibold block text-gray-400">Not Signed</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-col justify-between h-24">
                            <span class="font-bold">PQC (Quality Lane)</span>
                            <div>
                                @if($report->pqc_signed_at)
                                    <div class="text-[8px] font-bold text-yellow-600 uppercase tracking-wider border border-dashed border-yellow-600 rounded px-1 py-0.5 max-w-xs mx-auto mb-1 leading-none">DIGITALLY SIGNED</div>
                                    <span class="underline font-semibold block leading-tight">{{ $report->pqc_name }}</span>
                                    <span class="text-gray-500 text-[9px]">{{ \Carbon\Carbon::parse($report->pqc_signed_at)->format('d/m/Y H:i') }}</span>
                                @else
                                    <span class="underline font-semibold block text-gray-400">Not Signed</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-col justify-between h-24">
                            <span class="font-bold">Diperiksa (Leader)</span>
                            <div>
                                @if($report->leader_signed_at)
                                    <div class="text-[8px] font-bold text-orange-600 uppercase tracking-wider border border-dashed border-orange-600 rounded px-1 py-0.5 max-w-xs mx-auto mb-1 leading-none">DIGITALLY SIGNED</div>
                                    <span class="underline font-semibold block leading-tight">{{ $report->leader_name }}</span>
                                    <span class="text-gray-500 text-[9px]">{{ \Carbon\Carbon::parse($report->leader_signed_at)->format('d/m/Y H:i') }}</span>
                                @else
                                    <span class="underline font-semibold block text-gray-400">Not Signed</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-col justify-between h-24">
                            <span class="font-bold">Mengetahui (Supervisor)</span>
                            <div>
                                @if($report->acknowledged_signed_at)
                                    <div class="text-[8px] font-bold text-green-600 uppercase tracking-wider border border-dashed border-green-600 rounded px-1 py-0.5 max-w-xs mx-auto mb-1 leading-none">DIGITALLY SIGNED</div>
                                    <span class="underline font-semibold block leading-tight">{{ $report->acknowledged_by_name }}</span>
                                    <span class="text-gray-500 text-[9px]">{{ \Carbon\Carbon::parse($report->acknowledged_signed_at)->format('d/m/Y H:i') }}</span>
                                @else
                                    <span class="underline font-semibold block text-gray-400">Not Signed</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Embedded Print Stylesheet -->
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                font-family: 'Courier New', Courier, monospace !important;
            }
            .print-area {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
            }
            .bg-white {
                box-shadow: none !important;
                border: none !important;
            }
            header, nav, .lg\:pl-64, .fixed {
                display: none !important;
            }
            .lg\:pl-64 {
                padding-left: 0 !important;
            }
            main {
                padding: 0 !important;
                margin: 0 !important;
            }
        }
    </style>
</x-app-layout>
