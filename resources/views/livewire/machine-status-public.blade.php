<div wire:poll.15s="nextZone" class="bg-[#0a0a0b] min-h-screen p-8 text-gray-200 font-sans selection:bg-blue-500/30 overflow-hidden">
    {{-- Header / HUD Stats (Clean version for TV) --}}
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8 mb-12">
        <div class="flex items-center gap-6">
            <div>
                <h1 class="text-3xl font-black italic uppercase tracking-tighter text-white leading-none">
                    Real-time <span class="bg-blue-600 text-white px-3 not-italic rounded-sm">MONITOR</span>
                </h1>
                <div x-data="{ time: '{{ now()->format('H:i:s') }}' }" x-init="setInterval(() => { 
                        let d = new Date();
                        time = d.getHours().toString().padStart(2, '0') + ':' + 
                               d.getMinutes().toString().padStart(2, '0') + ':' + 
                               d.getSeconds().toString().padStart(2, '0');
                    }, 1000)" class="flex items-center gap-2 mt-2">
                    <div class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></div>
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.3em] font-mono" x-text="time"></span>
                </div>
            </div>

            {{-- Sector Header --}}
            <div class="h-14 w-px bg-white/10 mx-2 hidden lg:block"></div>
            
            <div class="bg-white/5 border border-white/10 rounded-2xl px-8 py-3 flex flex-col items-start min-w-[200px]">
                <span class="text-[9px] font-bold text-blue-400 uppercase tracking-widest mb-1 italic">ACTIVE SECTOR DIAGNOSTIC</span>
                <span class="text-4xl font-black text-white tracking-tighter uppercase leading-none">{{ $currentZoneName }}</span>
            </div>
        </div>

        {{-- Global Counters --}}
        <div class="flex gap-4">
            @foreach([
                ['label' => 'RUNNING', 'value' => $machines->where('status', 'RUNNING')->count(), 'color' => 'emerald'],
                ['label' => 'IDLE', 'value' => $machines->where('status', 'IDLE')->count(), 'color' => 'rose'],
                ['label' => 'SETUP', 'value' => $machines->where('status', 'SETUP')->count(), 'color' => 'amber'],
            ] as $stat)
                <div class="bg-[#141416] border border-white/5 rounded-2xl px-6 py-2 flex flex-col items-center min-w-[100px]">
                    <span class="text-[8px] font-black text-gray-600 uppercase tracking-widest">{{ $stat['label'] }}</span>
                    <span class="text-xl font-black text-{{ $stat['color'] }}-400 tracking-tighter">{{ $stat['value'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Cards Grid (Dynamic count for TV) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 xxl:grid-cols-5 gap-8">
        @forelse($machines as $machine)
            @php
                $statusColor = match($machine['status']) {
                    'RUNNING' => 'emerald',
                    'SETUP' => 'amber',
                    default => 'rose'
                };
            @endphp
            <div class="relative group transition-all duration-700 animate-fade-in">
                {{-- Background Glow --}}
                <div class="absolute -inset-0.5 bg-{{ $statusColor }}-500/10 rounded-3xl blur-2xl opacity-100"></div>
                
                <div class="relative bg-[#141416]/90 backdrop-blur-2xl rounded-3xl border border-white/10 shadow-2xl p-8 h-full flex flex-col overflow-hidden ring-1 ring-white/10">
                    
                    {{-- Status Indicator --}}
                    <div class="flex justify-between items-start mb-8">
                        <div class="px-4 py-1.5 rounded-full bg-{{ $statusColor }}-500/10 text-{{ $statusColor }}-500 text-[10px] font-black uppercase tracking-widest ring-1 ring-{{ $statusColor }}-500/20 flex items-center gap-2 shadow-inner">
                            <span class="relative flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-{{ $statusColor }}-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-{{ $statusColor }}-500"></span>
                            </span>
                            {{ $machine['status'] }}
                        </div>
                    </div>

                    {{-- Machine Name / Code --}}
                    <div class="mb-10">
                        <h3 class="text-5xl font-black text-white tracking-tighter leading-none mb-2">{{ $machine['machine_code'] }}</h3>
                        <p class="text-gray-500 text-xs font-bold uppercase tracking-widest">{{ $machine['name'] }}</p>
                    </div>

                    {{-- Production Context --}}
                    <div class="mt-auto space-y-6">
                        @if($machine['part_running'] !== '-')
                            <div class="space-y-1.5">
                                <p class="text-[9px] font-black text-gray-600 uppercase tracking-widest italic">Current Mission</p>
                                <p class="text-xl font-black text-white tracking-tight line-clamp-1 truncate">{{ $machine['part_running'] }}</p>
                            </div>

                            {{-- Progress Meter --}}
                            @if(is_numeric($machine['target_qty']) && $machine['target_qty'] > 0)
                                @php
                                    $pct = min(100, round(($machine['target_achieve'] / $machine['target_qty']) * 100));
                                    $barColorCode = match($machine['status']) {
                                        'RUNNING'  => 'linear-gradient(to right, #16a34a, #4ade80)',
                                        'SETUP'    => 'linear-gradient(to right, #b45309, #fbbf24)',
                                        'IDLE'     => 'linear-gradient(to right, #6b7280, #9ca3af)',
                                        default    => 'linear-gradient(to right, #16a34a, #4ade80)',
                                    };
                                @endphp
                                <div class="space-y-3">
                                    <div class="flex justify-between text-[11px] font-black uppercase font-mono tracking-tight">
                                        <span class="text-gray-500">{{ number_format($machine['target_achieve']) }} / {{ number_format($machine['target_qty']) }}</span>
                                        <span class="text-{{ $statusColor }}-400">{{ $pct }}%</span>
                                    </div>
                                    <div class="w-full bg-white/5 h-2 rounded-full ring-1 ring-white/10 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-1000 shadow-[0_0_15px_rgba(34,197,94,0.4)]"
                                             style="width: {{ $pct }}%; background: {{ $barColorCode }};"></div>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="py-8 border-y border-white/5 flex flex-col items-center justify-center gap-2 opacity-50">
                                <svg class="w-10 h-10 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="text-[10px] font-black text-gray-700 uppercase tracking-widest mt-2">Operational Standby</span>
                            </div>
                        @endif

                        {{-- Footer HUD Data --}}
                        <div class="pt-6 border-t border-white/5 flex justify-between items-center text-[11px] font-mono tracking-tighter">
                            <div class="flex flex-col">
                                <span class="text-gray-600 uppercase mb-0.5 min-w-[60px]">Idle (Min)</span>
                                <span class="text-{{ $machine['total_time_not_running'] > 5 ? 'rose-400' : 'gray-400' }} font-black text-lg leading-none">{{ $machine['total_time_not_running'] ?: '0' }}'</span>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="text-gray-600 uppercase mb-0.5">Next Ready</span>
                                <span class="text-gray-300 font-bold text-sm truncate max-w-[120px]">{{ $machine['next_part_running'] ?: '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-48 flex flex-col items-center justify-center gap-6 opacity-20">
                <svg class="w-32 h-32 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <p class="text-3xl font-black uppercase text-gray-600 tracking-tighter italic">No Operational Feed in This Sector</p>
            </div>
        @endforelse
    </div>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;900&display=swap');
        .font-sans { font-family: 'Outfit', sans-serif; }
        
        .animate-fade-in {
            animation: fadeIn 0.8s ease-out forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Hide scrollbar but allow scrolling */
        body::-webkit-scrollbar {
            display: none;
        }
        body {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    <script>
        document.addEventListener('livewire:navigated', () => {
            startAutoScroll();
        });

        document.addEventListener('livewire:initialized', () => {
            startAutoScroll();
            
            Livewire.on('zone-changed', () => {
                // For a new zone, we always start from the top
                if (scrollInterval) clearInterval(scrollInterval);
                window.scrollTo({ top: 0, behavior: 'smooth' });
                setTimeout(startAutoScroll, 1500);
            });
        });

        let scrollInterval;
        let scrollDirection = 1; // 1 for down, -1 for up

        function startAutoScroll() {
            if (scrollInterval) clearInterval(scrollInterval);
            
            const scrollSpeed = 1; 
            const intervalTime = 40; 
            scrollDirection = 1; // Start by going down
            
            scrollInterval = setInterval(() => {
                const isAtBottom = (window.innerHeight + window.scrollY + 1) >= document.body.offsetHeight;
                const isAtTop = window.scrollY <= 0;

                if (isAtBottom && scrollDirection === 1) {
                    scrollDirection = -1; // Reverse to Up
                } else if (isAtTop && scrollDirection === -1) {
                    scrollDirection = 1; // Reverse to Down
                }

                window.scrollBy(0, scrollDirection * scrollSpeed);
            }, intervalTime);
        }
    </script>
</div>
