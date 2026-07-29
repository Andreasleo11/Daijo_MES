<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Real-Time Floor Dashboard - Second Process') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6" x-data="{ 
            init() { 
                setInterval(() => { 
                    if(!document.hidden) window.location.reload(); 
                }, 60000); // 60 seconds auto-refresh
            } 
        }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Filters / Global Header --}}
            <div class="bg-white p-4 shadow rounded-lg mb-6 flex flex-col md:flex-row justify-between items-center gap-4 border-l-4 border-blue-500">
                <form action="{{ route('second-process.dashboard') }}" method="GET" class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2">
                        <label class="font-bold text-gray-700 text-sm">Date:</label>
                        <input type="date" name="date" value="{{ $date }}" class="rounded border-gray-300 text-sm py-1.5 focus:ring-blue-500" onchange="this.form.submit()">
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="font-bold text-gray-700 text-sm">Shift:</label>
                        <select name="shift" class="rounded border-gray-300 text-sm py-1.5 focus:ring-blue-500" onchange="this.form.submit()">
                            @foreach(config('mes.shifts', []) as $sId => $sConf)
                                <option value="{{ $sId }}" {{ $shift == $sId ? 'selected' : '' }}>{{ $sConf['name'] }} ({{ $sConf['start'] }} - {{ $sConf['end'] }})</option>
                            @endforeach
                            @if(empty(config('mes.shifts', [])))
                                <option value="1" {{ $shift == 1 ? 'selected' : '' }}>Shift 1</option>
                                <option value="2" {{ $shift == 2 ? 'selected' : '' }}>Shift 2</option>
                                <option value="3" {{ $shift == 3 ? 'selected' : '' }}>Shift 3</option>
                            @endif
                        </select>
                    </div>
                    <noscript><button type="submit" class="bg-gray-200 px-3 py-1 text-sm rounded hover:bg-gray-300">Filter</button></noscript>
                </form>

                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <svg class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Live (60s refresh)
                </div>
            </div>

            {{-- Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($lines as $lineName)
                    @php
                        $report = $reports->get($lineName);
                    @endphp

                    @if($report)
                        {{-- RUNNING CARD --}}
                        <div class="bg-white rounded-lg shadow-md border-t-4 border-green-500 overflow-hidden flex flex-col transition hover:shadow-lg">
                            <div class="bg-green-50 px-4 py-3 border-b border-green-100 flex justify-between items-center">
                                <h3 class="font-black text-green-900 tracking-tight">{{ $lineName }}</h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-200 text-green-800 uppercase animate-pulse">Running</span>
                            </div>
                            <div class="p-4 flex-grow flex flex-col gap-2">
                                <div>
                                    <div class="text-[10px] font-bold text-gray-500 uppercase">Part Number</div>
                                    <div class="font-bold text-sm text-gray-800">{{ $report->part_number ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-[10px] font-bold text-gray-500 uppercase">Part Name</div>
                                    <div class="text-xs text-gray-700 truncate" title="{{ $report->part_name }}">{{ $report->part_name ?? '-' }}</div>
                                </div>
                                <div class="grid grid-cols-2 gap-2 mt-2 pt-2 border-t border-gray-100">
                                    <div>
                                        <div class="text-[10px] font-bold text-gray-500 uppercase">Total Output</div>
                                        <div class="font-black text-lg text-blue-600">{{ number_format($report->jumlah_output) }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] font-bold text-gray-500 uppercase">NG Rate</div>
                                        <div class="font-bold text-sm {{ $report->ng_prosentase > 0 ? 'text-red-500' : 'text-green-500' }}">{{ $report->ng_prosentase }}%</div>
                                    </div>
                                </div>
                            </div>
                            <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 flex gap-2">
                                <a href="{{ route('second-process-reports.edit', $report->id) }}" class="w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded shadow text-xs transition">
                                    Log Hourly Production
                                </a>
                            </div>
                        </div>
                    @else
                        {{-- IDLE CARD --}}
                        <div class="bg-gray-50 rounded-lg shadow-sm border border-gray-200 border-t-4 border-gray-300 overflow-hidden flex flex-col transition hover:shadow-md">
                            <div class="bg-gray-100 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                                <h3 class="font-bold text-gray-600 tracking-tight">{{ $lineName }}</h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-200 text-gray-600 uppercase">IDLE</span>
                            </div>
                            <div class="p-4 flex-grow flex flex-col items-center justify-center text-center opacity-50 py-8">
                                <svg class="w-10 h-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                <p class="text-sm font-semibold text-gray-500">No active production</p>
                                <p class="text-[10px] text-gray-400">for this shift</p>
                            </div>
                            <div class="px-4 py-3 bg-white border-t border-gray-100 flex gap-2">
                                <a href="{{ route('second-process-reports.create', ['unit_line' => $lineName, 'shift' => $shift]) }}" class="w-full text-center bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-3 rounded shadow text-xs transition">
                                    Start Production
                                </a>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>
