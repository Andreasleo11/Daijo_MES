<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Master Box Individual Details') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ 
        editOpen: false, 
        editId: null, 
        editPart: '', 
        editLabel: '', 
        editStatus: '', 
        editRemark: '',
        openEdit(id, part, label, status, remark) {
            this.editId = id;
            this.editPart = part;
            this.editLabel = label;
            this.editStatus = status;
            this.editRemark = remark || '';
            this.editOpen = true;
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 shadow-sm" role="alert">
                    <p class="font-bold">Success</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <!-- Filter Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6 border-l-4 border-blue-500">
                <form action="{{ route('barcode.box_detail.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-bold text-gray-700 mb-1 uppercase tracking-wider">Search Part No</label>
                        <input type="text" name="part_no" value="{{ request('part_no') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Filter by Part No...">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 rounded-md font-bold transition shadow-sm">
                            FILTER
                        </button>
                        <a href="{{ route('barcode.box_detail.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-8 py-2 rounded-md font-bold transition text-center shadow-sm">
                            CLEAR
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 uppercase tracking-widest text-[10px]">
                            <tr>
                                <th class="px-6 py-4 text-left font-bold text-gray-500">ID</th>
                                <th class="px-6 py-4 text-left font-bold text-gray-500">Part No</th>
                                <th class="px-6 py-4 text-left font-bold text-gray-500">Label No</th>
                                <th class="px-6 py-4 text-left font-bold text-gray-500">Status</th>
                                <th class="px-6 py-4 text-left font-bold text-gray-500">Remark</th>
                                <th class="px-6 py-4 text-left font-bold text-gray-500">Created At</th>
                                <th class="px-6 py-4 text-center font-bold text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($details as $detail)
                                <tr class="hover:bg-blue-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">#{{ $detail->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 uppercase tracking-tight">{{ $detail->part_no }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded font-mono font-bold border">
                                            #{{ str_pad($detail->label, 4, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold border
                                            {{ $detail->status == 'active' ? 'bg-green-100 text-green-800 border-green-200' : 'bg-red-100 text-red-800 border-red-200' }}
                                        ">
                                            {{ strtoupper($detail->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 italic max-w-xs truncate">{{ $detail->remark ?: '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-medium">{{ $detail->created_at->format('M d, Y H:i') }}</td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <button 
                                            @click="openEdit({{ $detail->id }}, '{{ $detail->part_no }}', '{{ $detail->label }}', '{{ $detail->status }}', '{{ $detail->remark }}')"
                                            class="text-blue-600 hover:text-blue-900 font-bold text-xs uppercase tracking-tighter bg-blue-50 px-3 py-1 rounded border border-blue-200 hover:bg-blue-100 transition shadow-sm"
                                        >
                                            Edit Menu
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-400 italic">No individual box records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-8">
                    {{ $details->links() }}
                </div>
            </div>
        </div>

        <!-- Professional Edit Modal -->
        <div 
            x-show="editOpen" 
            class="fixed inset-0 z-50 overflow-y-auto" 
            style="display: none;"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="editOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div 
                    class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border-t-8 border-blue-600"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                >
                    <form :action="'{{ url('barcode/box-detail') }}/' + editId" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="px-6 py-6 bg-white">
                            <div class="flex items-center justify-between mb-6 pb-4 border-b">
                                <h3 class="text-xl font-bold text-gray-900 uppercase tracking-tighter">Modify Box record</h3>
                                <button type="button" @click="editOpen = false" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>

                            <div class="grid grid-cols-2 gap-4 mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200 shadow-inner">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest">Part Number</label>
                                    <div class="text-sm font-black text-gray-800" x-text="editPart"></div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest">Label ID</label>
                                    <div class="text-sm font-black text-gray-800" x-text="'#' + editLabel"></div>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2 tracking-widest">Master Status</label>
                                    <select name="status" x-model="editStatus" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-600 focus:border-blue-600 font-bold text-sm tracking-wide">
                                        <option value="active">🟢 ACTIVE (READY)</option>
                                        <option value="non-active">🔴 NON-ACTIVE</option>
                                    </select>
                                    <p class="mt-2 text-[10px] text-gray-500 italic">Non-active boxes will be hidden or flagged in production reports.</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2 tracking-widest">Remark / Justification</label>
                                    <textarea name="remark" x-model="editRemark" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-600 focus:border-blue-600 text-sm" placeholder="Add a reason for this change..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 text-right space-x-3 border-t">
                            <button type="button" @click="editOpen = false" class="px-4 py-2 text-sm font-bold text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-100 shadow-sm transition uppercase">
                                DISCARD
                            </button>
                            <button type="submit" class="px-10 py-2 text-sm font-bold text-white bg-blue-600 rounded-md hover:bg-blue-700 shadow-md transition uppercase tracking-widest">
                                UPDATE RECORD
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
