<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-black text-xl text-gray-800 uppercase tracking-wide">
                    {{ __('First Piece Sample / Inspection Reports') }}
                </h2>
                <p class="text-xs font-semibold text-gray-500 mt-0.5">QC Gate-Check records before production start • Document DI-F-P/PR/07/SP-013</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('second-process.dashboard') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl border border-gray-300 shadow-sm transition uppercase tracking-wider">
                    &larr; Floor Overview Dashboard
                </a>
                <a href="{{ route('first-piece-inspections.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-black text-xs rounded-xl shadow-sm transition uppercase tracking-wider">
                    + New First Piece Inspection
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        <div class="max-w-7xl mx-auto space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-300 text-emerald-900 px-5 py-4 rounded-2xl text-xs font-bold shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-300 text-red-900 px-5 py-4 rounded-2xl text-xs font-bold shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Filter Card --}}
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">Filter & Search Inspections</h3>
                        <p class="text-xs text-gray-500 font-medium">Refine First Piece Inspection records by date, judgement, or keywords</p>
                    </div>
                </div>

                <form method="GET" action="{{ route('first-piece-inspections.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                        <div>
                            <label class="block text-[11px] font-black text-gray-500 uppercase tracking-wider mb-1">From Date</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}"
                                class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold text-gray-800">
                        </div>

                        <div>
                            <label class="block text-[11px] font-black text-gray-500 uppercase tracking-wider mb-1">To Date</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}"
                                class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold text-gray-800">
                        </div>

                        <div>
                            <label class="block text-[11px] font-black text-gray-500 uppercase tracking-wider mb-1">Judgement</label>
                            <select name="overall_judgement" class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold text-gray-800">
                                <option value="">All Judgements</option>
                                <option value="OK" {{ request('overall_judgement') === 'OK' ? 'selected' : '' }}>OK Only</option>
                                <option value="NG" {{ request('overall_judgement') === 'NG' ? 'selected' : '' }}>NG Only</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-black text-gray-500 uppercase tracking-wider mb-1">Search Keyword</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Part No, Name, Model..."
                                class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold text-gray-800">
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                        <a href="{{ route('first-piece-inspections.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl border border-gray-300 shadow-sm transition uppercase tracking-wider">
                            Reset Filters
                        </a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-black text-xs rounded-xl shadow-sm transition uppercase tracking-wider">
                            Apply Filter
                        </button>
                    </div>
                </form>
            </div>

            {{-- Table Card --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <div>
                        <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">Inspection Records Log</h3>
                        <p class="text-xs text-gray-500 font-medium">Historical QC sign-offs and checkpoint evaluations</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3">Model</th>
                                <th class="px-6 py-3">Part Details</th>
                                <th class="px-6 py-3 text-center">Judgement</th>
                                <th class="px-6 py-3 text-center">QC Inspector</th>
                                <th class="px-6 py-3 text-center">Approved By</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs">
                            @forelse($inspections as $insp)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 font-bold text-gray-800 whitespace-nowrap">{{ $insp->date }}</td>
                                    <td class="px-6 py-4 font-semibold text-gray-600">{{ $insp->model ?: '-' }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">{{ $insp->part_name }}</div>
                                        <div class="text-[10px] text-blue-700 font-mono font-bold">{{ $insp->part_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($insp->overall_judgement === 'OK')
                                            <span class="px-3 py-1 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 uppercase tracking-wider border border-emerald-200">
                                                OK
                                            </span>
                                        @else
                                            <span class="px-3 py-1 rounded-full text-[10px] font-black bg-red-100 text-red-800 uppercase tracking-wider border border-red-200">
                                                NG
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($insp->checked_by)
                                            <div class="font-bold text-gray-800">{{ $insp->checked_by }}</div>
                                            <div class="text-[10px] text-gray-400 font-medium">{{ $insp->checked_at ? $insp->checked_at->format('d/m/Y H:i') : '' }}</div>
                                        @else
                                            <span class="text-amber-600 font-bold uppercase tracking-wider text-[10px]">Pending</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($insp->approved_by)
                                            <div class="font-bold text-gray-800">{{ $insp->approved_by }}</div>
                                            <div class="text-[10px] text-gray-400 font-medium">{{ $insp->approved_at ? $insp->approved_at->format('d/m/Y H:i') : '' }}</div>
                                        @else
                                            <span class="text-gray-400 font-medium text-[10px]">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-1 whitespace-nowrap">
                                        <a href="{{ route('first-piece-inspections.show', $insp->id) }}"
                                            class="inline-block px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs rounded-lg border border-blue-200 transition uppercase tracking-wider">
                                            View
                                        </a>
                                        @if(!$insp->checked_at)
                                            <a href="{{ route('first-piece-inspections.edit', $insp->id) }}"
                                                class="inline-block px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold text-xs rounded-lg border border-amber-200 transition uppercase tracking-wider">
                                                Edit
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-400 font-medium">
                                        No First Piece Inspection records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($inspections->hasPages())
                    <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100">
                        {{ $inspections->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
