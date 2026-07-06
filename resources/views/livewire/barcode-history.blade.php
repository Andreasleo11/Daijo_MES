<div x-data="{ 
    selected: @entangle('selected'), 
    rangeSelecting: false 
}">
    <!-- Filter Section (Livewire-powered) -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6 border-l-4 border-blue-500">
        <div class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-bold text-gray-700 mb-1 uppercase tracking-wider">Part No</label>
                <input type="text" wire:model.live.debounce.300ms="part_no" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Filter by Part No...">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-bold text-gray-700 mb-1 uppercase tracking-wider">Remark</label>
                <input type="text" wire:model.live.debounce.300ms="remark" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Filter by Remark...">
            </div>
            <div class="w-44">
                <label class="block text-sm font-bold text-gray-700 mb-1 uppercase tracking-wider">Date From</label>
                <input type="date" wire:model.live="date_from" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="w-44">
                <label class="block text-sm font-bold text-gray-700 mb-1 uppercase tracking-wider">Date To</label>
                <input type="date" wire:model.live="date_to" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex gap-2">
                <button wire:click="clearFilters" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-md transition font-bold uppercase text-xs">
                    Clear
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Selection & Actions Toolbar -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-4 border border-blue-100 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="h-8 w-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold">1</div>
            <span class="text-sm font-bold text-gray-700">RANGE SELECT:</span>
            <input type="number" wire:model="rangeFrom" placeholder="Start #" class="w-24 text-sm border-gray-300 rounded-md">
            <span class="text-gray-400">to</span>
            <input type="number" wire:model="rangeTo" placeholder="End #" class="w-24 text-sm border-gray-300 rounded-md">
            <button wire:click="selectRange" class="bg-blue-600 text-white px-4 py-1.5 rounded-md text-sm font-bold hover:bg-blue-700 shadow-sm transition">
                Apply Range
            </button>
        </div>

        <div class="flex items-center gap-4">
            <div class="text-sm font-bold text-gray-600 mr-2" x-show="selected.length > 0">
                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-bold" x-text="selected.length + ' labels selected'"></span>
            </div>
            <form action="{{ route('barcode.reprint') }}" method="POST" target="_blank" class="flex gap-2">
                @csrf
                <template x-for="id in selected" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                <div class="flex gap-2" x-show="selected.length > 0">
                    <button type="submit" name="format" value="a4" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md shadow-lg transition font-bold flex items-center gap-2">
                        🖨️ PRINT (A4)
                    </button>
                    <button type="submit" name="format" value="zebra" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded-md shadow-lg transition font-bold flex items-center gap-2">
                        🦓 PRINT (ZEBRA)
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 w-5 h-5">
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Part No</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Label No</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Remark</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Generated At</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-widest">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($history as $item)
                        <tr wire:key="row-{{ $item->id }}-{{ $item->label }}" class="hover:bg-blue-50 transition-colors" :class="selected.includes('{{ $item->id }}') ? 'bg-blue-50' : ''">
                            <td class="px-6 py-4">
                                <input type="checkbox" wire:model.live="selected" value="{{ $item->id }}" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 w-5 h-5">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900 uppercase">{{ $item->part_no }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded text-sm font-mono font-bold">
                                    #{{ str_pad($item->label, 4, '0', STR_PAD_LEFT) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 italic max-w-xs truncate">
                                {{ $item->remark ?: '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-medium">
                                {{ $item->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full {{ $item->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800 border' }}">
                                    {{ strtoupper($item->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="h-12 w-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <span class="text-gray-500 font-medium italic text-lg">No history records found matching your current filters.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            {{ $history->links() }}
        </div>
    </div>
    
    <div wire:loading class="fixed bottom-10 right-10 z-50">
        <div class="bg-gray-900 text-white px-6 py-3 rounded-full shadow-2xl flex items-center gap-3 animate-bounce">
            <svg class="animate-spin h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm font-bold tracking-widest">UPDATING RESULTS...</span>
        </div>
    </div>
</div>
