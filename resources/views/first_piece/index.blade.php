<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">First Piece Sample / Inspection Reports</h2>
                    <p class="text-xs text-gray-500 mt-1">QC Gate-Check records before production start</p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('second-process-reports.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded text-sm transition">
                        &larr; Second Process Reports
                    </a>
                    <a href="{{ route('first-piece-inspections.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow text-sm transition">
                        + New First Piece Inspection
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative text-sm mb-6" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-sm mb-6" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            {{-- Filter Bar --}}
            <form method="GET" action="{{ route('first-piece-inspections.index') }}" class="mb-6">
                <div class="bg-white shadow-sm border border-gray-200 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">From Date</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">To Date</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Judgement</label>
                            <select name="overall_judgement" class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">All Judgements</option>
                                <option value="OK" {{ request('overall_judgement') === 'OK' ? 'selected' : '' }}>OK</option>
                                <option value="NG" {{ request('overall_judgement') === 'NG' ? 'selected' : '' }}>NG</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Search Keyword</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Part No, Name, Model..." class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end space-x-2">
                        <a href="{{ route('first-piece-inspections.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-bold py-2 px-4 rounded transition">
                            Reset Filters
                        </a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2 px-4 rounded transition">
                            Filter
                        </button>
                    </div>
                </div>
            </form>

            {{-- Table --}}
            <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 font-bold text-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-left">Model</th>
                            <th class="px-4 py-3 text-left">Part Number</th>
                            <th class="px-4 py-3 text-left">Part Name</th>
                            <th class="px-4 py-3 text-center">Judgement</th>
                            <th class="px-4 py-3 text-center">QC Inspector</th>
                            <th class="px-4 py-3 text-center">Approved By</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($inspections as $insp)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 font-semibold whitespace-nowrap">{{ $insp->date }}</td>
                                <td class="px-4 py-3">{{ $insp->model }}</td>
                                <td class="px-4 py-3 font-mono font-bold text-blue-700">{{ $insp->part_number }}</td>
                                <td class="px-4 py-3">{{ $insp->part_name }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($insp->overall_judgement === 'OK')
                                        <span class="px-2.5 py-1 text-xs font-extrabold rounded-full bg-green-100 text-green-800 border border-green-300">OK</span>
                                    @else
                                        <span class="px-2.5 py-1 text-xs font-extrabold rounded-full bg-red-100 text-red-800 border border-red-300">NG</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center text-xs">
                                    @if($insp->checked_by)
                                        <span class="font-semibold text-gray-800">{{ $insp->checked_by }}</span>
                                        <div class="text-[10px] text-gray-500">{{ $insp->checked_at ? $insp->checked_at->format('d/m/Y H:i') : '' }}</div>
                                    @else
                                        <span class="text-gray-400 font-italic">Pending</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center text-xs">
                                    @if($insp->approved_by)
                                        <span class="font-semibold text-gray-800">{{ $insp->approved_by }}</span>
                                        <div class="text-[10px] text-gray-500">{{ $insp->approved_at ? $insp->approved_at->format('d/m/Y H:i') : '' }}</div>
                                    @else
                                        <span class="text-gray-400 font-italic">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right space-x-1 whitespace-nowrap">
                                    <a href="{{ route('first-piece-inspections.show', $insp->id) }}" class="text-blue-600 hover:text-blue-900 font-bold text-xs bg-blue-50 hover:bg-blue-100 px-2 py-1 rounded">View</a>
                                    @if(!$insp->checked_at)
                                        <a href="{{ route('first-piece-inspections.edit', $insp->id) }}" class="text-yellow-600 hover:text-yellow-900 font-bold text-xs bg-yellow-50 hover:bg-yellow-100 px-2 py-1 rounded">Edit</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-gray-500 font-medium">
                                    No First Piece Inspection records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="p-4 border-t border-gray-200">
                    {{ $inspections->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
