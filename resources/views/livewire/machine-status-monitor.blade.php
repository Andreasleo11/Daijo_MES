<div wire:poll.30s class="bg-[#0a0a0b] min-h-screen p-8 text-gray-200 font-sans selection:bg-blue-500/30">
    {{-- Header / HUD Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
        @foreach([
            ['label' => 'RUNNING', 'value' => $machines->where('status', 'RUNNING')->count(), 'color' => 'emerald', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
            ['label' => 'SETUP', 'value' => $machines->where('status', 'SETUP')->count(), 'color' => 'amber', 'icon' => 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m10 0a2 2 0 100-4m0 4a2 2 0 110-4m-4 10a2 2 0 100-4m0 4a2 2 0 110-4m-4 6v2m0-2a2 2 0 100-4m0 4a2 2 0 110-4'],
            ['label' => 'IDLE', 'value' => $machines->where('status', 'IDLE')->count(), 'color' => 'rose', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
            ['label' => 'TOTAL LOAD', 'value' => $machines->count(), 'color' => 'blue', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
        ] as $stat)
            <div class="relative group overflow-hidden">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-{{ $stat['color'] }}-500 to-transparent rounded-2xl blur opacity-20 group-hover:opacity-40 transition duration-1000 group-hover:duration-200"></div>
                <div class="relative flex items-center gap-5 bg-[#141416]/80 backdrop-blur-xl p-6 rounded-2xl border border-white/5 ring-1 ring-white/10 shadow-2xl">
                    <div class="bg-{{ $stat['color'] }}-500/10 p-3.5 rounded-xl text-{{ $stat['color'] }}-400 ring-1 ring-{{ $stat['color'] }}-500/20 shadow-inner">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"></path></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black tracking-[0.2em] uppercase text-gray-500 mb-0.5">{{ $stat['label'] }}</p>
                        <p class="text-3xl font-black text-white leading-none tracking-tighter">{{ $stat['value'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Filter & Search Bar --}}
    <div class="flex flex-col md:flex-row justify-between items-center gap-6 mb-12">
        <div class="flex items-center gap-4">
            <h2 class="text-2xl font-black italic uppercase tracking-tighter text-white">Live <span class="bg-blue-500 text-black px-2 not-italic">HUD</span></h2>
            <div class="h-6 w-px bg-white/10 mx-2"></div>
            <div x-data="{ time: '{{ now()->format('H:i:s') }}' }" x-init="setInterval(() => { 
                    let d = new Date();
                    time = d.getHours().toString().padStart(2, '0') + ':' + 
                           d.getMinutes().toString().padStart(2, '0') + ':' + 
                           d.getSeconds().toString().padStart(2, '0');
                }, 1000)" class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-mono" x-text="'Real-time Feed: ' + time"></span>
            </div>
        </div>

        <div class="flex items-center gap-4 w-full md:w-auto">
            <div class="relative group w-full md:w-64">
                <select wire:model.live="selectedZone" class="w-full bg-[#141416] border-white/5 text-gray-400 text-xs font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 p-3 pr-10 appearance-none cursor-pointer transition-all hover:bg-white/[0.02]">
                    <option value="">All Regions</option>
                    @foreach($zones as $zone)
                        <option value="{{ $zone->id }}">{{ $zone->zone_name }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-gray-600">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" fill-rule="evenodd"></path></svg>
                </div>
            </div>

            <div class="relative group w-full md:w-72">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Scan Machine Code..." class="w-full bg-[#141416] border-white/5 text-gray-300 text-xs font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 pl-11 p-3 transition-all hover:bg-white/[0.02]">
                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
        @forelse($machines as $machine)
            @php
                $statusColor = match($machine['status']) {
                    'RUNNING' => 'emerald',
                    'SETUP' => 'amber',
                    default => 'rose'
                };
            @endphp
            <div @if($interactive) wire:click="selectMachine({{ $machine['id'] }})" @endif 
                 class="relative group @if($interactive) cursor-pointer @endif transition-all duration-500 hover:-translate-y-2">
                
                {{-- Glowing Border / Shadow --}}
                <div class="absolute -inset-0.5 bg-{{ $statusColor }}-500/0 group-hover:bg-{{ $statusColor }}-500/20 rounded-3xl blur-xl transition duration-500 opacity-0 group-hover:opacity-100"></div>
                
                <div class="relative bg-[#141416] rounded-3xl border border-white/5 shadow-2xl p-6 h-full flex flex-col overflow-hidden ring-1 ring-white/10 group-hover:ring-{{ $statusColor }}-500/30">
                    
                    {{-- Status Indicator --}}
                    <div class="flex justify-between items-start mb-6">
                        <div class="px-3 py-1 rounded-full bg-{{ $statusColor }}-500/10 text-{{ $statusColor }}-500 text-[10px] font-black uppercase tracking-widest ring-1 ring-{{ $statusColor }}-500/20 flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-{{ $statusColor }}-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-{{ $statusColor }}-500"></span>
                            </span>
                            {{ $machine['status'] }}
                        </div>
                        <span class="text-gray-700 text-[10px] font-black font-mono tracking-tighter uppercase">{{ $machine['zone'] }}</span>
                    </div>

                    {{-- Machine Name / Code --}}
                    <div class="mb-8">
                        <h3 class="text-4xl font-black text-white tracking-tighter leading-none mb-1 group-hover:text-{{ $statusColor }}-400 transition-colors">{{ $machine['machine_code'] }}</h3>
                        <p class="text-gray-600 text-xs font-bold uppercase tracking-widest">{{ $machine['name'] }}</p>
                    </div>

                    {{-- Production Context --}}
                    <div class="mt-auto space-y-5">
                        @if($machine['part_running'] !== '-')
                            <div class="space-y-1">
                                <p class="text-[9px] font-black text-gray-500 uppercase tracking-widest">Active Production</p>
                                <p class="text-lg font-black text-white tracking-tight line-clamp-1">{{ $machine['part_running'] }}</p>
                            </div>

                            {{-- Progress Meter --}}
                            @if(is_numeric($machine['target_qty']) && $machine['target_qty'] > 0)
                                @php
                                    $pct = min(100, round(($machine['target_achieve'] / $machine['target_qty']) * 100));
                                @endphp
                                <div class="space-y-2">
                                    <div class="flex justify-between text-[10px] font-black uppercase font-mono">
                                        <span class="text-gray-500">{{ number_format($machine['target_achieve']) }} / {{ number_format($machine['target_qty']) }}</span>
                                        <span class="text-{{ $statusColor }}-400">{{ $pct }}%</span>
                                    </div>
                                   @php
                                        $barColor = match($machine['status']) {
                                            'RUNNING'  => 'linear-gradient(to right, #16a34a, #4ade80)',
                                            'SETUP'    => 'linear-gradient(to right, #b45309, #fbbf24)',
                                            'IDLE'     => 'linear-gradient(to right, #6b7280, #9ca3af)',
                                            default    => 'linear-gradient(to right, #16a34a, #4ade80)',
                                        };
                                        $glowColor = match($machine['status']) {
                                            'RUNNING'  => 'rgba(74,222,128,0.5)',
                                            'SETUP'    => 'rgba(251,191,36,0.5)',
                                            'IDLE'     => 'rgba(156,163,175,0.3)',
                                            default    => 'rgba(74,222,128,0.5)',
                                        };
                                    @endphp

                                    <div class="w-full h-2 rounded-full overflow-hidden"
                                        style="background:rgba(255,255,255,0.05); box-shadow:inset 0 0 0 1px rgba(255,255,255,0.1);">
                                        <div class="h-full rounded-full transition-all duration-1000"
                                            style="width:{{ $pct }}%;
                                                    background:{{ $barColor }};
                                                    box-shadow:0 0 10px {{ $glowColor }};">
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="py-6 border-y border-white/5 flex flex-col items-center justify-center gap-1 opacity-50 grayscale group-hover:grayscale-0 group-hover:opacity-100 transition-all duration-500">
                                <svg class="w-8 h-8 text-{{ $statusColor }}-500/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                                <span class="text-[10px] font-black text-gray-600 uppercase tracking-[0.2em] mt-2">Downtime State</span>
                            </div>
                        @endif

                        {{-- Footer HUD Data --}}
                        <div class="pt-4 border-t border-white/5 flex justify-between items-center text-[10px] font-mono tracking-tighter">
                            <div class="flex flex-col">
                                <span class="text-gray-600 uppercase mb-0.5">Idle Min.</span>
                                <span class="text-{{ $machine['total_time_not_running'] > 0 ? 'rose-400' : 'gray-400' }} font-bold text-sm">{{ $machine['total_time_not_running'] ?: '0' }}'</span>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="text-gray-600 uppercase mb-0.5">Next Ready</span>
                                <span class="text-gray-300 font-bold text-sm">{{ $machine['next_part_running'] ?: '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-32 flex flex-col items-center justify-center gap-6 opacity-30">
                <svg class="w-24 h-24 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <p class="text-2xl font-black uppercase text-gray-400 tracking-tighter">No Machines Detected in HUD Sector</p>
            </div>
        @endforelse
    </div>

    {{-- Detail Modal --}}
    @if($selectedMachineId && $machineDetails)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 overflow-hidden">
            {{-- Backdrop --}}
            <div wire:click="closeDetail" class="absolute inset-0 bg-[#070708]/90 backdrop-blur-md transition-opacity duration-300"></div>

            {{-- Modal Content --}}
            <div class="relative w-full max-w-4xl max-h-[85vh] bg-[#141416] rounded-3xl border border-white/10 shadow-3xl overflow-hidden flex flex-col transform transition-all duration-300 scale-100 opacity-100 ring-1 ring-white/10">
                
                {{-- Modal Header --}}
                <div class="p-8 border-b border-white/5 flex justify-between items-center bg-gradient-to-r from-white/[0.02] to-transparent">
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                             <h2 class="text-4xl font-black text-white tracking-tighter uppercase">{{ $machines->firstWhere('id', $selectedMachineId)['machine_code'] }}</h2>
                             <span class="bg-blue-500 text-black px-2 py-0.5 text-[10px] font-black uppercase rounded italic leading-none">Diagnostic Mode</span>
                        </div>
                        <p class="text-gray-500 text-xs font-bold uppercase tracking-[0.2em]">{{ $machines->firstWhere('id', $selectedMachineId)['name'] }} | Sector {{ $machines->firstWhere('id', $selectedMachineId)['zone'] }}</p>
                    </div>
                    <button wire:click="closeDetail" class="p-3 bg-white/5 hover:bg-white/10 rounded-2xl text-gray-500 hover:text-white transition-all ring-1 ring-white/10 group">
                        <svg class="w-6 h-6 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-8 space-y-10 custom-scrollbar">
                    {{-- Grid Sections --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        
                        {{-- Recent Jobs --}}
                        <div class="space-y-6">
                            <div class="flex items-center gap-3">
                                <div class="w-1.5 h-6 bg-blue-500"></div>
                                <h4 class="text-xs font-black uppercase text-gray-200 tracking-widest italic">Production Protocol <span class="text-gray-600 not-italic">(Recent Jobs)</span></h4>
                            </div>
                            <div class="space-y-3">
                                @forelse($machineDetails['recent_jobs'] as $job)
                                    <div class="bg-white/[0.03] rounded-2xl p-4 border border-white/5 hover:border-white/10 transition-colors transition-all group overflow-hidden relative">
                                        <div class="absolute inset-y-0 left-0 w-1 bg-{{ $job['is_done'] ? 'emerald' : 'blue' }}-500 opacity-50"></div>
                                        <div class="flex justify-between items-start mb-2">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-black text-white tracking-tight">{{ $job['item_code'] }}</span>
                                                <span class="text-[8px] font-black text-blue-400 uppercase tracking-widest leading-none mt-1">Shift {{ $job['shift'] }}</span>
                                            </div>
                                            <span class="text-[9px] font-bold text-gray-500 uppercase font-mono">{{ Carbon\Carbon::parse($job['start_date'])->format('M d') }}</span>
                                        </div>
                                        <div class="flex justify-between items-center text-[10px] font-bold text-gray-500 uppercase tracking-tighter">
                                            <span>Target: {{ number_format($job['quantity']) }}</span>
                                            <span class="text-{{ $job['is_done'] ? 'emerald' : 'blue' }}-400">Achv: {{ number_format($job['actual_quantity']) }}</span>
                                            <span class="bg-white/5 px-2 py-0.5 rounded text-[8px] tracking-widest">{{ $job['is_done'] ? 'COMPLETED' : 'ACTIVE' }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs text-gray-600 font-bold italic py-8 text-center border border-dashed border-white/5 rounded-2xl">No recent production cycles found</p>
                                @endforelse
                            </div>
                        </div>

                        {{-- Recent Activity Logs --}}
                        <div class="space-y-6">
                            <div class="flex items-center gap-3">
                                <div class="w-1.5 h-6 bg-amber-500"></div>
                                <h4 class="text-xs font-black uppercase text-gray-200 tracking-widest italic">System Logs <span class="text-gray-600 not-italic">(Adjust / Repair / Mould)</span></h4>
                            </div>
                            <div class="space-y-4">
                                @forelse($machineDetails['recent_logs'] as $log)
                                    @php
                                        $logColor = match($log['log_type']) {
                                            'REPAIR' => 'rose',
                                            'ADJUST' => 'amber',
                                            'MOULD' => 'blue',
                                            default => 'gray'
                                        };
                                    @endphp
                                    <div class="relative pl-6 py-1">
                                        <div class="absolute left-0 top-0 bottom-0 w-px bg-white/10 ml-[3px]"></div>
                                        <div class="absolute left-0 top-2.5 w-2 h-2 rounded-full bg-{{ $logColor }}-500 ring-4 ring-{{ $logColor }}-500/10 border border-[#141416]"></div>
                                        
                                        <div class="flex flex-col gap-1">
                                            <div class="flex justify-between items-center">
                                                <span class="text-[10px] font-black text-{{ $logColor }}-400 uppercase tracking-widest">{{ $log['log_type'] }} OPERATION</span>
                                                <span class="text-[9px] font-bold text-gray-600 uppercase font-mono">
                                                    {{ $log['created_at'] }}
                                                </span>
                                            </div>
                                            <p class="text-xs font-medium text-gray-400 break-words">{{ $log['remark'] ?? $log['problem'] ?? 'Ongoing technical operation...' }}</p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-[8px] font-bold text-gray-600 uppercase tracking-widest">PIC: {{ $log['pic'] ?? 'N/A' }}</span>
                                                @if(isset($log['end_time']) || isset($log['finish_repair']))
                                                    <span class="text-[8px] font-bold text-emerald-500/50 uppercase tracking-widest">● Resoluted</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs text-gray-600 font-bold italic py-8 text-center border border-dashed border-white/5 rounded-2xl">No technical diagnostic entries recorded</p>
                                @endforelse
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="p-8 bg-white/[0.02] border-t border-white/5 flex justify-end gap-3">
                    <button wire:click="closeDetail" class="px-8 py-3 bg-white/5 hover:bg-white/10 rounded-2xl text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-white transition-all ring-1 ring-white/10">Exit Diagnostic</button>
                </div>
            </div>
        </div>
    @endif

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100;300;400;700;900&display=swap');
        
        .font-sans { font-family: 'Outfit', sans-serif; }
        
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.05); border-radius: 10px; }
        .custom-scrollbar:hover::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); }
    </style>
</div>
