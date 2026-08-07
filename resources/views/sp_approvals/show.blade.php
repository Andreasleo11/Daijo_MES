<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-gray-500 font-semibold mb-1">
                    <a href="{{ route('sp-approvals.index') }}" class="hover:text-blue-600 transition">Production Approvals</a>
                    <span>/</span>
                    <span class="text-gray-800">Session #{{ $session->id }}</span>
                </div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('Daily Production Report Review') }}
                </h2>
            </div>
            
            @if($session->approved_at)
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-2 rounded-lg flex items-center gap-2 shadow-sm">
                    <div class="w-2.5 h-2.5 bg-green-500 rounded-full"></div>
                    <span class="font-bold text-xs uppercase tracking-wide">Approved on {{ $session->approved_at->format('M d, Y H:i') }}</span>
                </div>
            @else
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-2 rounded-lg flex items-center gap-2 shadow-sm">
                    <div class="w-2.5 h-2.5 bg-yellow-500 rounded-full animate-pulse"></div>
                    <span class="font-bold text-xs uppercase tracking-wide">Pending Approval</span>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r shadow-sm">
                    <p class="text-red-700 font-bold text-sm">{{ session('error') }}</p>
                </div>
            @endif

            @if($session->is_qc_bypassed)
                <div class="mb-6 bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-xl shadow-sm flex items-start justify-between gap-4">
                    <div>
                        <h4 class="font-black text-amber-950 text-sm uppercase tracking-wide flex items-center gap-2">
                            <span>⚠️ Emergency QC Gate Bypassed</span>
                        </h4>
                        <p class="text-xs text-amber-900 font-medium mt-1">
                            This production session was started without a pre-approved First Piece Inspection.
                        </p>
                        <div class="text-xs text-amber-950 font-bold mt-2 bg-amber-100/80 px-3 py-1.5 rounded-lg inline-block border border-amber-200">
                            Reason: "{{ $session->qc_bypass_reason }}"
                        </div>
                    </div>
                    @if($session->qcBypassedBy)
                        <div class="text-right text-xs text-amber-800 shrink-0">
                            <div>Bypassed by: <strong>{{ $session->qcBypassedBy->name }}</strong></div>
                            <div class="font-mono text-[10px] text-amber-700">{{ $session->qc_bypassed_at?->format('Y-m-d H:i') }}</div>
                        </div>
                    @endif
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- Left Column: Data & Stats --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- Work Order Info Card --}}
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                            <h3 class="font-black text-gray-800 text-sm uppercase tracking-widest">Work Order Details</h3>
                        </div>
                        <div class="p-6 grid grid-cols-2 md:grid-cols-3 gap-6">
                            <div>
                                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Work Order No</div>
                                <div class="font-mono font-black text-lg text-gray-800">{{ $session->workOrder->wo_number }}</div>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Part Number</div>
                                <div class="font-bold text-gray-800">{{ $session->workOrder->part_number }}</div>
                            </div>
                            <div class="col-span-2 md:col-span-1">
                                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Part Name</div>
                                <div class="font-bold text-gray-800">{{ $session->workOrder->part_name }}</div>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Unit Line</div>
                                <div class="font-bold text-gray-800">{{ $session->workOrder->unit_line }}</div>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Shift</div>
                                <div class="font-bold text-gray-800">{{ $session->workOrder->shift }}</div>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Operator</div>
                                <div class="font-bold text-gray-800">{{ $session->operator->name ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Production KPIs --}}
                    @php
                        $totalOutput = $session->total_good + $session->total_reject;
                        $yield = $totalOutput > 0 ? ($session->total_good / $totalOutput) * 100 : 0;
                        $target = $session->workOrder->target_qty;
                        $progress = $target > 0 ? ($session->total_good / $target) * 100 : 0;
                        
                        $totalDowntimeMinutes = 0;
                        foreach($session->downtimeEntries as $dt) {
                            if ($dt->start_time && $dt->resume_time) {
                                $start = \Carbon\Carbon::parse($dt->start_time);
                                $resume = \Carbon\Carbon::parse($dt->resume_time);
                                $totalDowntimeMinutes += $start->diffInMinutes($resume);
                            }
                        }
                    @endphp
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 text-center">
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Total Good</div>
                            <div class="font-black text-3xl text-green-600">{{ number_format($session->total_good) }}</div>
                            <div class="text-xs font-bold text-gray-500 mt-1">/ {{ number_format($target) }} Target</div>
                        </div>
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 text-center">
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Total Reject</div>
                            <div class="font-black text-3xl text-red-600">{{ number_format($session->total_reject) }}</div>
                            <div class="text-xs font-bold text-gray-500 mt-1">Pcs</div>
                        </div>
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 text-center">
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Yield</div>
                            <div class="font-black text-3xl {{ $yield >= 98 ? 'text-green-600' : ($yield >= 95 ? 'text-orange-500' : 'text-red-600') }}">
                                {{ number_format($yield, 1) }}%
                            </div>
                            <div class="text-xs font-bold text-gray-500 mt-1">Quality Rate</div>
                        </div>
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 text-center">
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Downtime</div>
                            <div class="font-black text-3xl text-orange-500">{{ $totalDowntimeMinutes }}</div>
                            <div class="text-xs font-bold text-gray-500 mt-1">Minutes</div>
                        </div>
                    </div>

                    {{-- Data Tables --}}
                    <div x-data="{ tab: 'defects' }" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="border-b border-gray-200 bg-gray-50 px-6 py-3 flex gap-6">
                            <button @click="tab = 'defects'" :class="tab === 'defects' ? 'text-blue-600 border-blue-600 font-black' : 'text-gray-500 border-transparent hover:text-gray-800'" class="py-3 text-sm border-b-2 tracking-wide uppercase transition-colors">
                                Defects ({{ $session->rejectEntries->count() }})
                            </button>
                            <button @click="tab = 'downtime'" :class="tab === 'downtime' ? 'text-blue-600 border-blue-600 font-black' : 'text-gray-500 border-transparent hover:text-gray-800'" class="py-3 text-sm border-b-2 tracking-wide uppercase transition-colors">
                                Downtime ({{ $session->downtimeEntries->count() }})
                            </button>
                            <button @click="tab = 'rework'" :class="tab === 'rework' ? 'text-blue-600 border-blue-600 font-black' : 'text-gray-500 border-transparent hover:text-gray-800'" class="py-3 text-sm border-b-2 tracking-wide uppercase transition-colors">
                                Rework ({{ $session->reworkEntries->count() }})
                            </button>
                            <button @click="tab = 'input'" :class="tab === 'input' ? 'text-blue-600 border-blue-600 font-black' : 'text-gray-500 border-transparent hover:text-gray-800'" class="py-3 text-sm border-b-2 tracking-wide uppercase transition-colors">
                                Input WIP ({{ $session->inputEntries->count() }})
                            </button>
                            <button @click="tab = 'manpower'" :class="tab === 'manpower' ? 'text-blue-600 border-blue-600 font-black' : 'text-gray-500 border-transparent hover:text-gray-800'" class="py-3 text-sm border-b-2 tracking-wide uppercase transition-colors">
                                Line Team ({{ $session->manpowerEntries->count() }})
                            </button>
                        </div>

                        {{-- Tab: Defects --}}
                        <div x-show="tab === 'defects'" class="p-0">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-400 uppercase bg-gray-50 border-b border-gray-100">
                                    <tr>
                                        <th class="px-6 py-3">Time</th>
                                        <th class="px-6 py-3">Defect Type</th>
                                        <th class="px-6 py-3">Quantity</th>
                                        <th class="px-6 py-3">Cause / Remarks</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($session->rejectEntries as $entry)
                                        <tr>
                                            <td class="px-6 py-4 font-mono text-gray-500">{{ $entry->created_at->format('H:i') }}</td>
                                            <td class="px-6 py-4 font-bold text-gray-800">{{ $entry->defect_type }}</td>
                                            <td class="px-6 py-4 font-black text-red-600">{{ $entry->quantity }} Pcs</td>
                                            <td class="px-6 py-4 text-gray-600">{{ $entry->cause ?: '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-8 text-center text-gray-400">No defects recorded.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Tab: Downtime --}}
                        <div x-show="tab === 'downtime'" style="display: none;" class="p-0">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-400 uppercase bg-gray-50 border-b border-gray-100">
                                    <tr>
                                        <th class="px-6 py-3">Reason</th>
                                        <th class="px-6 py-3">Duration</th>
                                        <th class="px-6 py-3">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($session->downtimeEntries as $entry)
                                        @php
                                            $start = \Carbon\Carbon::parse($entry->start_time);
                                            $resume = \Carbon\Carbon::parse($entry->resume_time);
                                            $duration = $start->diffInMinutes($resume);
                                        @endphp
                                        <tr>
                                            <td class="px-6 py-4 font-bold text-gray-800">{{ $entry->reason }}</td>
                                            <td class="px-6 py-4 font-mono text-orange-600 font-bold">
                                                {{ $entry->start_time }} - {{ $entry->resume_time }} ({{ $duration }} min)
                                            </td>
                                            <td class="px-6 py-4 text-gray-600">{{ $entry->remarks ?: '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-6 py-8 text-center text-gray-400">No downtime recorded.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Tab: Rework --}}
                        <div x-show="tab === 'rework'" style="display: none;" class="p-0">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-400 uppercase bg-gray-50 border-b border-gray-100">
                                    <tr>
                                        <th class="px-6 py-3">Time</th>
                                        <th class="px-6 py-3">Input</th>
                                        <th class="px-6 py-3">Recovered</th>
                                        <th class="px-6 py-3">Scrapped</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($session->reworkEntries as $entry)
                                        <tr>
                                            <td class="px-6 py-4 font-mono text-gray-500">{{ $entry->created_at->format('H:i') }}</td>
                                            <td class="px-6 py-4 font-bold text-gray-800">{{ $entry->input_qty }} Pcs</td>
                                            <td class="px-6 py-4 font-bold text-green-600">{{ $entry->recovered_qty }} Pcs</td>
                                            <td class="px-6 py-4 font-bold text-red-600">{{ $entry->scrapped_qty }} Pcs</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-8 text-center text-gray-400">No rework recorded.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Tab: Input WIP --}}
                        <div x-show="tab === 'input'" style="display: none;" class="p-0">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-400 uppercase bg-gray-50 border-b border-gray-100">
                                    <tr>
                                        <th class="px-6 py-3">Time</th>
                                        <th class="px-6 py-3">Quantity</th>
                                        <th class="px-6 py-3">Source</th>
                                        <th class="px-6 py-3">Pallet Number</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($session->inputEntries as $entry)
                                        <tr>
                                            <td class="px-6 py-4 font-mono text-gray-500">{{ $entry->created_at->format('H:i') }}</td>
                                            <td class="px-6 py-4 font-bold text-blue-600">+{{ $entry->quantity }} Pcs</td>
                                            <td class="px-6 py-4 font-bold text-gray-700 uppercase">{{ $entry->source }}</td>
                                            <td class="px-6 py-4 font-mono text-gray-600">{{ $entry->pallet_number ?: '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-8 text-center text-gray-400">No input WIP recorded.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Tab: Line Team / Manpower --}}
                        <div x-show="tab === 'manpower'" style="display: none;" class="p-0">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-400 uppercase bg-gray-50 border-b border-gray-100">
                                    <tr>
                                        <th class="px-6 py-3">Role / Position</th>
                                        <th class="px-6 py-3">Operator Name</th>
                                        <th class="px-6 py-3">Employee NIK</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($session->manpowerEntries as $mp)
                                        <tr>
                                            <td class="px-6 py-4 font-bold text-purple-700 uppercase tracking-wide">{{ $mp->role }}</td>
                                            <td class="px-6 py-4 font-bold text-gray-800">{{ $mp->operator_name }}</td>
                                            <td class="px-6 py-4 font-mono text-gray-600">{{ $mp->employee_no ?: '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-6 py-8 text-center text-gray-400">No additional line team members recorded.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Approval Action --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden sticky top-6">
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                            <h3 class="font-black text-gray-800 text-sm uppercase tracking-widest">Supervisor Action</h3>
                        </div>
                        <div class="p-6">
                            @if(is_null($session->approved_at))
                                <div class="mb-6">
                                    <p class="text-sm text-gray-600 font-semibold mb-4">Please verify the production data. By approving this report, it becomes the official production record for this shift.</p>
                                    
                                    <form action="{{ route('sp-approvals.approve', $session->id) }}" method="POST" class="mb-3">
                                        @csrf
                                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-black py-4 px-6 rounded-xl shadow-lg transition uppercase tracking-widest text-sm">
                                            Approve Report
                                        </button>
                                    </form>

                                    <form action="{{ route('sp-approvals.reject', $session->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Are you sure you want to return this report to the operator for correction?')" class="w-full bg-white hover:bg-red-50 text-red-600 border-2 border-red-200 font-bold py-3 px-6 rounded-xl transition uppercase tracking-widest text-sm">
                                            Return for Correction
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="text-center py-6">
                                    <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    <h4 class="font-black text-gray-800 text-lg mb-1">Report Approved</h4>
                                    <p class="text-sm text-gray-500 font-semibold">Approved by {{ $session->approvedBy->name ?? 'User #' . $session->approved_by }} on {{ $session->approved_at->format('M d, Y') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
