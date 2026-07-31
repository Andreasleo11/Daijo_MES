<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="font-black text-2xl text-gray-800 leading-tight tracking-tight">
                        {{ $workOrder->wo_number }}
                    </h2>
                    @switch($workOrder->status)
                        @case('planned') <span class="px-3 py-1 text-[10px] font-black rounded-full bg-blue-100 text-blue-800 uppercase tracking-widest">Planned</span> @break
                        @case('in_progress') <span class="px-3 py-1 text-[10px] font-black rounded-full bg-green-100 text-green-800 uppercase tracking-widest animate-pulse shadow-sm shadow-green-200">Running</span> @break
                        @case('completed') <span class="px-3 py-1 text-[10px] font-black rounded-full bg-gray-100 text-gray-600 uppercase tracking-widest">Completed</span> @break
                        @case('cancelled') <span class="px-3 py-1 text-[10px] font-black rounded-full bg-red-100 text-red-800 uppercase tracking-widest">Cancelled</span> @break
                    @endswitch
                </div>
                <p class="text-xs text-gray-500 mt-1 font-semibold">Created by {{ $workOrder->creator?->name ?? 'System' }} on {{ $workOrder->created_at->format('d M Y, H:i') }}</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('sp-work-orders.index') }}" class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold py-2 px-4 rounded-lg shadow-sm text-xs transition">
                    ← Back to List
                </a>
                @if($workOrder->status === 'planned')
                    <a href="{{ route('sp-work-orders.edit', $workOrder->id) }}" class="bg-yellow-50 hover:bg-yellow-100 text-yellow-700 border border-yellow-200 font-bold py-2 px-4 rounded-lg shadow-sm text-xs transition">
                        Edit
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg text-sm font-bold shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Main Dashboard Layout --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- Left Column: Details --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- Progress Hero Card --}}
                    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm relative overflow-hidden">
                        <div class="flex justify-between items-end mb-2 relative z-10">
                            <div>
                                <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Production Progress</h3>
                                <div class="text-3xl font-black text-gray-900 ">
                                    {{ number_format($workOrder->total_good) }} <span class="text-base font-bold text-gray-400">/ {{ number_format($workOrder->target_qty) }} Pcs</span>
                                </div>
                            </div>
                            <div class="text-3xl font-black {{ $workOrder->progress_percentage >= 100 ? 'text-green-500' : 'text-blue-500' }}">
                                {{ $workOrder->progress_percentage }}%
                            </div>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-4 overflow-hidden relative z-10">
                            <div class="{{ $workOrder->progress_percentage >= 100 ? 'bg-green-500' : 'bg-blue-500' }} h-4 rounded-full transition-all duration-1000 ease-out" style="width: {{ $workOrder->progress_percentage }}%"></div>
                        </div>
                        <!-- Decorative background element -->
                        <div class="absolute -right-10 -bottom-10 opacity-5 text-9xl pointer-events-none">📈</div>
                    </div>

                    {{-- Info Cards Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Product Details --}}
                        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                            <div class="mb-4 pb-2 border-b border-gray-100">
                                <h3 class="text-sm font-black text-gray-700 uppercase tracking-widest">Product Info</h3>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <div class="text-[10px] text-gray-400 uppercase font-black tracking-wider">Part Number</div>
                                    <div class="text-sm font-bold text-gray-900 mt-0.5">{{ $workOrder->part_number }}</div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-gray-400 uppercase font-black tracking-wider">Part Name</div>
                                    <div class="text-sm font-bold text-gray-900 mt-0.5">{{ $workOrder->part_name }}</div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <div class="text-[10px] text-gray-400 uppercase font-black tracking-wider">Customer</div>
                                        <div class="text-sm font-bold text-gray-900 mt-0.5">{{ $workOrder->customer }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-gray-400 uppercase font-black tracking-wider">Model Code</div>
                                        <div class="text-sm font-bold text-gray-900 mt-0.5">{{ $workOrder->model ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Manufacturing Setup --}}
                        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                            <div class="mb-4 pb-2 border-b border-gray-100">
                                <h3 class="text-sm font-black text-gray-700 uppercase tracking-widest">Manufacturing Setup</h3>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <div class="text-[10px] text-gray-400 uppercase font-black tracking-wider">Process Type</div>
                                    <div class="text-sm font-bold text-gray-900 mt-0.5">{{ $workOrder->process_prod }}</div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <div class="text-[10px] text-gray-400 uppercase font-black tracking-wider">Production Line</div>
                                        <div class="text-sm font-bold text-gray-900 mt-0.5">{{ $workOrder->unit_line }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-gray-400 uppercase font-black tracking-wider">Shift</div>
                                        <div class="text-sm font-bold text-gray-900 mt-0.5">{{ $workOrder->shift }}</div>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-gray-400 uppercase font-black tracking-wider">Planned Date</div>
                                    <div class="text-sm font-bold text-gray-900 mt-0.5">{{ \Carbon\Carbon::parse($workOrder->planned_date)->format('l, d F Y') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Right Column: Action Center & Stats --}}
                <div class="space-y-6">
                    
                    {{-- Action Center --}}
                    @if(!in_array($workOrder->status, ['completed', 'cancelled']))
                        <div class="bg-blue-50 p-6 rounded-2xl border border-blue-200 shadow-sm text-center">
                            <h3 class="text-xs font-black text-blue-800 uppercase tracking-widest mb-4">Action Center</h3>
                            
                            @php
                                $activeSession = $workOrder->sessions->where('status', 'running')->first();
                                $isFpiApproved = isset($firstPiece) && $firstPiece && $firstPiece->isApproved();
                            @endphp

                            @if($activeSession)
                                <div class="mb-4 text-sm font-bold text-green-700 flex items-center justify-center gap-2">
                                    <span class="relative flex h-3 w-3">
                                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                      <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                                    </span>
                                    Production is running
                                </div>
                                <a href="{{ route('app.sp-sessions.show', $activeSession->id) }}" class="block w-full bg-green-600 hover:bg-green-500 text-white font-black py-4 px-6 rounded-xl shadow-lg transition-transform transform hover:scale-105">
                                    RESUME SESSION
                                </a>
                            @else
                                {{-- First Piece Inspection Status Box --}}
                                <div class="mb-5 p-4 rounded-xl text-left border {{ $isFpiApproved ? 'bg-green-50 border-green-200 text-green-900' : 'bg-yellow-50 border-yellow-200 text-yellow-900' }}">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[10px] font-black uppercase tracking-wider text-gray-500">QC First Piece Inspection</span>
                                        @if($isFpiApproved)
                                            <span class="px-2 py-0.5 text-[10px] font-black rounded-full bg-green-200 text-green-800 uppercase">APPROVED (OK)</span>
                                        @elseif(isset($firstPiece) && $firstPiece)
                                            <span class="px-2 py-0.5 text-[10px] font-black rounded-full bg-yellow-200 text-yellow-800 uppercase">{{ $firstPiece->overall_judgement ?: 'PENDING' }}</span>
                                        @else
                                            <span class="px-2 py-0.5 text-[10px] font-black rounded-full bg-red-200 text-red-800 uppercase">NOT STARTED</span>
                                        @endif
                                    </div>
                                    <p class="text-xs font-bold mt-1">
                                        @if($isFpiApproved)
                                            First Piece inspected & signed by <span class="font-black">{{ $firstPiece->checked_by ?: 'QC Inspector' }}</span>.
                                        @elseif(isset($firstPiece) && $firstPiece)
                                            Inspection logged, awaiting QC verification/signature.
                                        @else
                                            First Piece Inspection must be completed & approved by QC before production can start today.
                                        @endif
                                    </p>
                                    <div class="mt-3 flex items-center gap-2">
                                        @if($isFpiApproved)
                                            <a href="{{ route('first-piece-inspections.show', $firstPiece->id) }}" target="_blank" class="inline-block text-xs font-black underline hover:no-underline text-green-700">
                                                View Inspection Details #{{ $firstPiece->id }} &rarr;
                                            </a>
                                        @elseif(isset($firstPiece) && $firstPiece)
                                            <a href="{{ route('first-piece-inspections.show', $firstPiece->id) }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-black rounded-lg bg-yellow-600 hover:bg-yellow-700 text-white transition shadow-sm">
                                                Sign QC Approval #{{ $firstPiece->id }} &rarr;
                                            </a>
                                        @else
                                            <a href="{{ route('first-piece-inspections.create', ['work_order_id' => $workOrder->id, 'part_number' => $workOrder->part_number, 'part_name' => $workOrder->part_name, 'model' => $workOrder->model]) }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-black rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition shadow-sm">
                                                ➕ Perform First Piece Inspection
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                @if($isFpiApproved)
                                    <p class="text-xs text-blue-600 mb-4 font-semibold">QC Approved! Ready to start production for this work order?</p>
                                    <form action="{{ route('sp-sessions.start', $workOrder->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-4 px-6 rounded-xl shadow-lg transition-transform transform hover:scale-105 flex justify-center items-center gap-2">
                                            START PRODUCTION
                                        </button>
                                    </form>
                                @else
                                    <button type="button" disabled class="w-full bg-gray-300 text-gray-500 cursor-not-allowed font-black py-4 px-6 rounded-xl text-xs uppercase tracking-wider">
                                        START PRODUCTION (QC Approval Required)
                                    </button>
                                @endif
                            @endif
                        </div>
                    @else
                        <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200 text-center">
                            <div class="text-4xl mb-2"></div>
                            <h3 class="text-sm font-black text-gray-700 uppercase tracking-widest mb-1">Work Order {{ $workOrder->status }}</h3>
                            <p class="text-xs text-gray-500 font-semibold">No further production actions can be taken.</p>
                        </div>
                    @endif

                    {{-- Quick Stats --}}
                    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Quality Summary</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                                <span class="text-sm font-bold text-gray-600">Total Good</span>
                                <span class="text-lg font-black text-green-600">{{ number_format($workOrder->total_good) }}</span>
                            </div>
                            <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                                <span class="text-sm font-bold text-gray-600">Total Reject</span>
                                <span class="text-lg font-black text-red-600">{{ number_format($workOrder->total_reject) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-bold text-gray-600">Avg Yield</span>
                                @php
                                    $totalProduced = $workOrder->total_good + $workOrder->total_reject;
                                    $avgYield = $totalProduced > 0 ? round(($workOrder->total_good / $totalProduced) * 100, 1) : 0;
                                @endphp
                                <span class="text-lg font-black {{ $avgYield >= 98 ? 'text-green-600' : ($avgYield >= 90 ? 'text-yellow-600' : 'text-red-600') }}">{{ $avgYield }}%</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Production Sessions List --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mt-8">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <div class="flex items-center gap-2">
                        <h3 class="font-black text-gray-800 text-sm uppercase tracking-widest">Production Sessions</h3>
                    </div>
                    <span class="text-xs font-bold bg-white px-3 py-1 rounded-full border border-gray-200 text-gray-500 shadow-sm">{{ $workOrder->sessions->count() }} Session(s)</span>
                </div>

                {{-- TABLET / MOBILE VIEW (Card Layout) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 md:hidden p-4 gap-4 bg-gray-50">
                    @forelse($workOrder->sessions as $session)
                        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex flex-col gap-3">
                            <div class="flex justify-between items-center border-b pb-2">
                                <div class="font-black text-blue-600">#SESSION-{{ $session->id }}</div>
                                @if($session->status === 'running')
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-green-100 text-green-800 uppercase animate-pulse">Running</span>
                                @elseif($session->approved_by)
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-blue-100 text-blue-800 uppercase">Approved</span>
                                @else
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-gray-100 text-gray-700 uppercase">Completed</span>
                                @endif
                            </div>
                            
                            <div class="text-xs text-gray-500 font-bold">
                                Operator: <span class="text-gray-900">{{ $session->operator?->name ?? 'Unknown' }}</span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-xs bg-gray-50 p-2 rounded-lg">
                                <div><span class="text-gray-400 font-bold uppercase block text-[9px]">Good</span> <span class="font-bold text-green-600">{{ number_format($session->total_good) }}</span></div>
                                <div><span class="text-gray-400 font-bold uppercase block text-[9px]">Reject</span> <span class="font-bold text-red-600">{{ number_format($session->total_reject) }}</span></div>
                                <div><span class="text-gray-400 font-bold uppercase block text-[9px]">Yield</span> <span class="font-bold">{{ $session->yield }}%</span></div>
                                <div><span class="text-gray-400 font-bold uppercase block text-[9px]">Time</span> <span class="font-bold">{{ $session->started_at?->format('H:i') }} - {{ $session->finished_at?->format('H:i') ?? 'Now' }}</span></div>
                            </div>
                            
                            <div class="flex justify-end gap-2 mt-1 pt-2 border-t border-gray-100">
                                @if($session->status === 'completed' && !$session->approved_by)
                                    <form action="{{ route('app.sp-sessions.approve', $session->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-blue-700 font-black bg-blue-50 hover:bg-blue-100 px-4 py-2 rounded-lg text-xs shadow-sm transition w-full">✓ Approve</button>
                                    </form>
                                @endif
                                <a href="{{ route('app.sp-sessions.show', $session->id) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg text-xs font-black shadow-sm flex-1 text-center">Open</a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-400 p-4 font-bold col-span-full">No sessions recorded.</div>
                    @endforelse
                </div>

                {{-- DESKTOP VIEW (Table Layout) --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 ">
                        <thead class="bg-white ">
                            <tr>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Session ID</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Operator</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Time</th>
                                <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Good Qty</th>
                                <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Reject Qty</th>
                                <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Yield %</th>
                                <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                                <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white ">
                            @forelse($workOrder->sessions as $session)
                                <tr class="hover:bg-blue-50/30 :bg-gray-700/50 transition">
                                    <td class="px-6 py-4 text-sm font-black text-blue-600">#SESSION-{{ $session->id }}</td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-800 ">{{ $session->operator?->name ?? 'Operator' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 font-semibold">
                                        {{ $session->started_at ? $session->started_at->format('H:i') : '-' }} 
                                        → 
                                        {{ $session->finished_at ? $session->finished_at->format('H:i') : 'Now' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right font-black text-green-600">{{ number_format($session->total_good) }}</td>
                                    <td class="px-6 py-4 text-sm text-right font-black text-red-600">{{ number_format($session->total_reject) }}</td>
                                    <td class="px-6 py-4 text-sm text-right font-black">{{ $session->yield }}%</td>
                                    <td class="px-6 py-4 text-center">
                                        @if($session->status === 'running')
                                            <span class="px-3 py-1 text-[10px] font-black rounded-full bg-green-100 text-green-800 uppercase tracking-widest animate-pulse">Running</span>
                                        @elseif($session->approved_by)
                                            <span class="px-3 py-1 text-[10px] font-black rounded-full bg-blue-100 text-blue-800 uppercase tracking-widest" title="Approved by {{ $session->approvedBy->name }}">Approved</span>
                                        @else
                                            <span class="px-3 py-1 text-[10px] font-black rounded-full bg-gray-100 text-gray-600 uppercase tracking-widest">Completed</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm flex justify-end gap-3 items-center">
                                        @if($session->status === 'completed' && !$session->approved_by)
                                            <form action="{{ route('app.sp-sessions.approve', $session->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="text-blue-700 font-black bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg text-xs transition">
                                                    ✓ Approve
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('app.sp-sessions.show', $session->id) }}" class="text-gray-600 hover:text-gray-900 font-black px-3 py-1.5 bg-gray-50 hover:bg-gray-100 rounded-lg text-xs transition">
                                            Open
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                        <div class="font-bold text-lg">No sessions recorded</div>
                                        <div class="text-sm mt-1">Click "Start Production" to begin tracking.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
