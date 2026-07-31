<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('Second Process Work Orders') }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">Manage, release, and track production Work Orders</p>
            </div>
            <a href="{{ route('sp-work-orders.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm text-sm transition flex items-center gap-1">
                + New Work Order
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg text-sm" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg text-sm" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Filter Bar --}}
            <form method="GET" action="{{ route('sp-work-orders.index') }}" class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">From Date</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full border-gray-300 rounded-lg text-xs focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">To Date</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full border-gray-300 rounded-lg text-xs focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Line / Area</label>
                        <select name="unit_line" class="w-full border-gray-300 rounded-lg text-xs focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Lines</option>
                            @foreach($lines as $l)
                                <option value="{{ $l }}" {{ request('unit_line') == $l ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Status</label>
                        <select name="status" class="w-full border-gray-300 rounded-lg text-xs focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Status</option>
                            <option value="planned" {{ request('status') == 'planned' ? 'selected' : '' }}>Planned</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="WO#, part..." class="w-full border-gray-300 rounded-lg text-xs focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded-lg text-xs shadow transition">Filter</button>
                        <a href="{{ route('sp-work-orders.index') }}" class="w-full text-center bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-3 rounded-lg text-xs transition">Reset</a>
                    </div>
                </div>
            </form>

            {{-- Data Display --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                
                {{-- TABLET / MOBILE VIEW (Card Layout) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 lg:hidden bg-gray-50">
                    @forelse($workOrders as $wo)
                        <div class="bg-white border border-gray-200 p-4 rounded-xl shadow-sm flex flex-col gap-3">
                            <div class="flex justify-between items-start">
                                <div>
                                    <a href="{{ route('sp-work-orders.show', $wo->id) }}" class="text-base font-black text-blue-600 hover:underline">
                                        {{ $wo->wo_number }}
                                    </a>
                                    <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($wo->planned_date)->format('d M Y') }}</div>
                                </div>
                                @switch($wo->status)
                                    @case('planned') <span class="px-2 py-1 text-[10px] font-bold rounded bg-blue-100 text-blue-800 uppercase">Planned</span> @break
                                    @case('in_progress') <span class="px-2 py-1 text-[10px] font-bold rounded bg-green-100 text-green-800 uppercase animate-pulse">Running</span> @break
                                    @case('completed') <span class="px-2 py-1 text-[10px] font-bold rounded bg-gray-100 text-gray-800 uppercase">Completed</span> @break
                                    @case('cancelled') <span class="px-2 py-1 text-[10px] font-bold rounded bg-red-100 text-red-800 uppercase">Cancelled</span> @break
                                @endswitch
                            </div>
                            
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div><span class="text-gray-400 font-bold uppercase block text-[9px]">Part No</span> <span class="font-bold">{{ $wo->part_number }}</span></div>
                                <div><span class="text-gray-400 font-bold uppercase block text-[9px]">Line/Shift</span> <span class="font-bold">{{ $wo->unit_line }} (S{{ $wo->shift }})</span></div>
                            </div>

                            <div>
                                <div class="flex justify-between text-[10px] font-bold mb-1">
                                    <span class="text-gray-500">PROGRESS</span>
                                    <span class="{{ $wo->progress_percentage >= 100 ? 'text-green-600' : 'text-blue-600' }}">{{ $wo->progress_percentage }}% ({{ number_format($wo->total_good) }} / {{ number_format($wo->target_qty) }})</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                    <div class="{{ $wo->progress_percentage >= 100 ? 'bg-green-500' : 'bg-blue-500' }} h-2 rounded-full" style="width: {{ $wo->progress_percentage }}%"></div>
                                </div>
                            </div>

                            <div class="flex justify-end gap-2 mt-1 pt-3 border-t border-gray-100">
                                @if($wo->status === 'planned')
                                    <a href="{{ route('sp-work-orders.edit', $wo->id) }}" class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-gray-200">Edit</a>
                                @endif
                                <a href="{{ route('sp-work-orders.show', $wo->id) }}" class="bg-blue-600 text-white px-4 py-1.5 rounded-lg text-xs font-bold shadow-sm hover:bg-blue-700">Open Details</a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-400 p-6 w-full col-span-full font-bold">
                            <div class="text-4xl mb-2">📄</div>
                            No Work Orders found.
                        </div>
                    @endforelse
                </div>

                {{-- DESKTOP VIEW (Table Layout) --}}
                <div class="hidden lg:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 ">
                        <thead class="bg-gray-50 ">
                            <tr>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">WO Number</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Planned Date</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Line / Shift</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Part Info</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Production Progress</th>
                                <th class="px-5 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-5 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white ">
                            @forelse($workOrders as $wo)
                                <tr class="hover:bg-blue-50/50 :bg-gray-700/50 transition cursor-pointer" onclick="window.location='{{ route('sp-work-orders.show', $wo->id) }}'">
                                    <td class="px-5 py-3 text-sm font-black text-blue-600 whitespace-nowrap">
                                        {{ $wo->wo_number }}
                                    </td>
                                    <td class="px-5 py-3 text-sm font-bold text-gray-600 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($wo->planned_date)->format('d M Y') }}
                                    </td>
                                    <td class="px-5 py-3 text-sm whitespace-nowrap">
                                        <div class="font-bold text-gray-800">{{ $wo->unit_line }}</div>
                                        <div class="text-xs font-semibold text-gray-400">Shift {{ $wo->shift }}</div>
                                    </td>
                                    <td class="px-5 py-3 text-sm">
                                        <div class="font-bold text-gray-900">{{ $wo->part_number }}</div>
                                        <div class="text-xs text-gray-500 truncate max-w-[200px]">{{ $wo->part_name }}</div>
                                    </td>
                                    <td class="px-5 py-3 whitespace-nowrap w-48">
                                        <div class="flex justify-between text-[10px] font-bold mb-1">
                                            <span class="text-gray-500">{{ number_format($wo->total_good) }} / {{ number_format($wo->target_qty) }}</span>
                                            <span class="{{ $wo->progress_percentage >= 100 ? 'text-green-600' : 'text-blue-600' }}">{{ $wo->progress_percentage }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                            <div class="{{ $wo->progress_percentage >= 100 ? 'bg-green-500' : 'bg-blue-500' }} h-1.5 rounded-full" style="width: {{ $wo->progress_percentage }}%"></div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-center whitespace-nowrap">
                                        @switch($wo->status)
                                            @case('planned') <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-blue-100 text-blue-800 uppercase">Planned</span> @break
                                            @case('in_progress') <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-green-100 text-green-800 uppercase animate-pulse shadow-sm shadow-green-200">Running</span> @break
                                            @case('completed') <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-gray-100 text-gray-600 uppercase">Completed</span> @break
                                            @case('cancelled') <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-red-100 text-red-800 uppercase">Cancelled</span> @break
                                        @endswitch
                                    </td>
                                    <td class="px-5 py-3 text-right text-sm font-bold whitespace-nowrap space-x-3">
                                        <a href="{{ route('sp-work-orders.show', $wo->id) }}" class="text-blue-600 hover:text-blue-800" onclick="event.stopPropagation()">View</a>
                                        @if($wo->status === 'planned')
                                            <a href="{{ route('sp-work-orders.edit', $wo->id) }}" class="text-gray-400 hover:text-gray-800" onclick="event.stopPropagation()">Edit</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                                        <div class="text-5xl mb-3">📄</div>
                                        <div class="font-bold text-lg">No Work Orders Found</div>
                                        <div class="text-sm mt-1 mb-4">Create a new work order to get started.</div>
                                        <a href="{{ route('sp-work-orders.create') }}" class="bg-blue-600 text-white font-bold py-2 px-4 rounded-lg shadow-sm text-sm">
                                            + New Work Order
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $workOrders->links() }}
            </div>

        </div>
    </div>
</x-app-layout>
