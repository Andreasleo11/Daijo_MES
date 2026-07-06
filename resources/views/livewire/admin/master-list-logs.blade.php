<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4 bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
        <!-- Search logs -->
        <div class="w-full md:w-1/3 relative">
            <input wire:model.live="search" type="text" placeholder="Search logs by item code, action, or user..." 
                   class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm pl-10 py-2">
            <div class="absolute left-3 top-2.5 text-gray-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
        </div>

        <!-- Back Link -->
        <a href="{{ route('admin.master-list-manager') }}" 
           class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded text-sm transition inline-flex items-center">
            &larr; Back to Master List
        </a>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-xs text-left">
                <thead class="bg-gray-50 text-gray-700 font-bold uppercase">
                    <tr>
                        <th class="px-4 py-3">Timestamp</th>
                        <th class="px-4 py-3">Operator User</th>
                        <th class="px-4 py-3">Item Code</th>
                        <th class="px-4 py-3">Action Type</th>
                        <th class="px-4 py-3">Modification Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-gray-700">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-500">
                                {{ $log->created_at->format('d M Y H:i:s') }}
                                <span class="block text-[10px] text-gray-400 font-normal">({{ $log->created_at->diffForHumans() }})</span>
                            </td>
                            <td class="px-4 py-3 font-bold">{{ $log->user->name ?? 'System/API' }}</td>
                            <td class="px-4 py-3 font-semibold text-gray-900 select-all">{{ $log->item_code }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ 
                                    $log->action === 'inline_edit' ? 'bg-indigo-100 text-indigo-800' : (
                                    $log->action === 'csv_import_create' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800') 
                                }}">
                                    {{ str_replace('_', ' ', $log->action) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 max-w-sm space-y-1">
                                @if($log->action === 'csv_import_create')
                                    <span class="text-green-600 font-medium italic">New record created in database</span>
                                @else
                                    @if(is_array($log->new_values))
                                        @foreach($log->new_values as $key => $newVal)
                                            @php $oldVal = $log->old_values[$key] ?? '-'; @endphp
                                            <div class="text-[10px] py-0.5 border-b border-gray-100 last:border-0 leading-tight">
                                                <span class="font-bold text-gray-500">{{ str_replace('_', ' ', $key) }}:</span>
                                                <span class="text-red-500 font-mono">{{ $oldVal }}</span> &rarr;
                                                <span class="text-green-600 font-mono font-bold">{{ $newVal }}</span>
                                            </div>
                                        @endforeach
                                    @else
                                        <span class="text-gray-400 italic">No values changed</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 text-gray-500 italic font-semibold">No activity logs recorded matching search criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Logs Pagination -->
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
            {{ $logs->links() }}
        </div>
    </div>
</div>
