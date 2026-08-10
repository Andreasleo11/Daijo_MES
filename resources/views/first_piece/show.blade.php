<x-app-layout>
    <div class="py-6 no-print">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('first-piece-inspections.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded transition text-sm">
                    Back to List
                </a>
                @if($inspection->workOrder)
                    <a href="{{ route('sp-work-orders.show', $inspection->work_order_id) }}" class="bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold py-2 px-4 rounded border border-blue-200 transition text-sm flex items-center font-mono">
                        Work Order: {{ $inspection->workOrder->wo_number }}
                    </a>
                @endif
            </div>
            <div class="space-x-2">
                <button onclick="window.print()" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition text-sm">
                    Print Form
                </button>
                @if(!$inspection->checked_at)
                    @can('execute-qc-inspections')
                        <a href="{{ route('first-piece-inspections.edit', $inspection->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded transition text-sm">
                            Edit Form
                        </a>
                    @endcan
                @endif
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 no-print mb-4">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded text-sm" role="alert">
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 no-print mb-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded text-sm" role="alert">
                <span>{{ $errors->first() }}</span>
            </div>
        </div>
    @endif

    <!-- Workflow Sign-off Action Banner -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 no-print mb-6">
        <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
            <div>
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">QC Gate Status</h3>
                <div class="flex items-center space-x-2 mt-1">
                    @if($inspection->overall_judgement === 'OK')
                        <span class="px-3 py-1 text-xs font-extrabold rounded bg-green-100 text-green-800 border border-green-300 uppercase">OK — APPROVED FOR PRODUCTION</span>
                    @else
                        <span class="px-3 py-1 text-xs font-extrabold rounded bg-red-100 text-red-800 border border-red-300 uppercase">NG — NOT APPROVED</span>
                    @endif
                </div>
            </div>

            <div class="flex items-center space-x-3">
                @if(!$inspection->prepared_at)
                    <form action="{{ route('first-piece-inspections.sign', [$inspection->id, 'prepared']) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm transition">
                            Sign as Production (Prepared)
                        </button>
                    </form>
                @endif

                @if(!$inspection->checked_at)
                    <form action="{{ route('first-piece-inspections.sign', [$inspection->id, 'checked']) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded text-sm transition">
                            Sign as QC Inspector (Checked)
                        </button>
                    </form>
                @endif

                @if($inspection->checked_at && !$inspection->approved_at)
                    <form action="{{ route('first-piece-inspections.sign', [$inspection->id, 'approved']) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm transition">
                            Sign as QC Leader (Approved)
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Printable Area -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pb-12 print-area">
        <div class="bg-white shadow-xl rounded-lg overflow-hidden border border-gray-200 p-6 space-y-6">

            <!-- Document Header -->
            <div class="border-2 border-black grid grid-cols-1 md:grid-cols-4 divide-y md:divide-y-0 md:divide-x divide-black text-sm">
                <div class="p-4 flex flex-col justify-center items-center col-span-2">
                    <h1 class="text-xl font-black text-center tracking-tight">PT. DAIJO INDUSTRIAL</h1>
                    <p class="text-xs font-bold text-center mt-1">Second Process Department</p>
                </div>
                <div class="p-2 grid grid-cols-2 gap-x-2 text-[11px] leading-tight col-span-2">
                    <div class="font-bold">Dok:</div>
                    <div>DI-F-P/PR/07/SP-013</div>
                    <div class="font-bold">Form:</div>
                    <div>FIRST PIECE SAMPLE / INSPECTION</div>
                    <div class="font-bold">Overall Judgment:</div>
                    <div class="font-extrabold {{ $inspection->overall_judgement === 'OK' ? 'text-green-700' : 'text-red-700' }}">{{ $inspection->overall_judgement }}</div>
                </div>
            </div>

            <div class="text-center mt-4">
                <h2 class="text-xl font-bold uppercase tracking-wider underline">FIRST PIECE SAMPLE / INSPECTION</h2>
            </div>

            <!-- Header Fields Table -->
            <table class="w-full border-collapse border border-black text-xs">
                <tbody>
                    <tr>
                        <td class="border border-black p-2 font-bold bg-gray-50 w-1/6">DATE</td>
                        <td class="border border-black p-2 w-1/3">{{ $inspection->date }}</td>
                        <td class="border border-black p-2 font-bold bg-gray-50 w-1/6">THINNER CODE</td>
                        <td class="border border-black p-2 w-1/3">{{ $inspection->thinner_code ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="border border-black p-2 font-bold bg-gray-50">MODEL</td>
                        <td class="border border-black p-2">{{ $inspection->model }}</td>
                        <td class="border border-black p-2 font-bold bg-gray-50">INK CODE</td>
                        <td class="border border-black p-2">{{ $inspection->ink_code ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="border border-black p-2 font-bold bg-gray-50">PART NAME</td>
                        <td class="border border-black p-2">{{ $inspection->part_name }}</td>
                        <td class="border border-black p-2 font-bold bg-gray-50">VISCOSITY</td>
                        <td class="border border-black p-2">{{ $inspection->viscosity ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="border border-black p-2 font-bold bg-gray-50">PART NO.</td>
                        <td class="border border-black p-2 font-bold font-mono text-blue-800">{{ $inspection->part_number }}</td>
                        <td class="border border-black p-2 font-bold bg-gray-50">CYCLE TIME</td>
                        <td class="border border-black p-2">{{ $inspection->cycle_time ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="border border-black p-2 font-bold bg-gray-50">PAINT CODE / MAT. CODE</td>
                        <td class="border border-black p-2" colspan="3">{{ $inspection->paint_code ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Inspection Check Points Table -->
            <div>
                <h3 class="text-sm font-bold text-center uppercase tracking-wider mb-2 border border-black bg-gray-100 p-2">
                    QUALITY CONTROL ISSUE FOR APPROVED
                </h3>

                <table class="w-full border-collapse border border-black text-xs">
                    <thead>
                        <tr class="bg-gray-100 font-bold text-center">
                            <th class="border border-black p-2 text-left w-1/3">Check Point</th>
                            <th class="border border-black p-2 w-1/4">Methode Check</th>
                            <th class="border border-black p-2 w-1/4">Inspection Result</th>
                            <th class="border border-black p-2 w-1/6">Judgment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($inspection->check_results))
                            @foreach($inspection->check_results as $res)
                                <tr>
                                    <td class="border border-black p-2 font-bold">{{ $res['check_point'] }}</td>
                                    <td class="border border-black p-2 text-center">{{ $res['method'] ?? 'Visual' }}</td>
                                    <td class="border border-black p-2 text-center font-bold">
                                        @if(($res['result'] ?? 'OK') === 'OK')
                                            <span class="text-green-700">✓ OK</span>
                                        @else
                                            <span class="text-red-700">✗ NG</span>
                                        @endif
                                    </td>
                                    <td class="border border-black p-2 text-center font-extrabold">
                                        @if(($res['judgement'] ?? 'OK') === 'OK')
                                            <span class="text-green-700">OK</span>
                                        @else
                                            <span class="text-red-700">NG</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Remarks -->
            @if($inspection->remark)
                <div class="border border-black p-3 text-xs">
                    <span class="font-bold">Remark:</span> {{ $inspection->remark }}
                </div>
            @endif

            <!-- Signatures Table -->
            <div class="pt-4">
                <table class="w-full border-collapse border border-black text-xs text-center">
                    <thead>
                        <tr class="bg-gray-100 font-bold">
                            <td class="border border-black p-2 w-1/3">PREPARED BY:</td>
                            <td class="border border-black p-2 w-1/3">CHECKED BY:</td>
                            <td class="border border-black p-2 w-1/3">APPROVED BY:</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="h-20">
                            <td class="border border-black p-2 align-bottom">
                                @if($inspection->prepared_by)
                                    <div class="font-bold text-gray-900">{{ $inspection->prepared_by }}</div>
                                    <div class="text-[10px] text-gray-500">{{ $inspection->prepared_at ? $inspection->prepared_at->format('d/m/Y H:i') : '' }}</div>
                                    <div class="text-[10px] font-semibold text-blue-700">PRODUCTION</div>
                                @else
                                    <span class="text-gray-400 font-italic">(Pending Sign)</span>
                                @endif
                            </td>
                            <td class="border border-black p-2 align-bottom">
                                @if($inspection->checked_by)
                                    <div class="font-bold text-gray-900">{{ $inspection->checked_by }}</div>
                                    <div class="text-[10px] text-gray-500">{{ $inspection->checked_at ? $inspection->checked_at->format('d/m/Y H:i') : '' }}</div>
                                    <div class="text-[10px] font-semibold text-green-700">QC INSPECTOR</div>
                                @else
                                    <span class="text-gray-400 font-italic">(Pending Sign)</span>
                                @endif
                            </td>
                            <td class="border border-black p-2 align-bottom">
                                @if($inspection->approved_by)
                                    <div class="font-bold text-gray-900">{{ $inspection->approved_by }}</div>
                                    <div class="text-[10px] text-gray-500">{{ $inspection->approved_at ? $inspection->approved_at->format('d/m/Y H:i') : '' }}</div>
                                    <div class="text-[10px] font-semibold text-purple-700">QC LEADER</div>
                                @else
                                    <span class="text-gray-400 font-italic">(Pending Sign)</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Attached Physical Proof Gallery -->
            @if($inspection->attachments->count() > 0)
                <div class="border-t border-black pt-4">
                    <h4 class="text-xs font-bold text-gray-700 uppercase mb-2">Attached Physical Sample Photos:</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($inspection->attachments as $attach)
                            <a href="{{ $attach->url }}" target="_blank" class="block border rounded p-2 text-center hover:shadow transition">
                                <img src="{{ $attach->url }}" alt="{{ $attach->label }}" class="h-32 object-cover mx-auto rounded">
                                <div class="text-[11px] text-gray-700 mt-1 font-semibold truncate">{{ $attach->label ?? $attach->original_name }}</div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
