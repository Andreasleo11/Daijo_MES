<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('Production Approvals') }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">Review and approve completed production sessions</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded-r shadow-sm">
                    <p class="text-green-700 font-bold text-sm">{{ session('success') }}</p>
                </div>
            @endif
            @if(session('warning'))
                <div class="mb-4 bg-orange-50 border-l-4 border-orange-500 p-4 rounded-r shadow-sm">
                    <p class="text-orange-700 font-bold text-sm">{{ session('warning') }}</p>
                </div>
            @endif

            {{-- Tabs --}}
            <div class="flex gap-4 border-b border-gray-200 mb-6">
                <a href="{{ route('sp-approvals.index', ['tab' => 'pending']) }}" class="py-2 px-1 text-sm font-bold border-b-2 {{ $tab === 'pending' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    Pending Approvals
                </a>
                <a href="{{ route('sp-approvals.index', ['tab' => 'approved']) }}" class="py-2 px-1 text-sm font-bold border-b-2 {{ $tab === 'approved' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    Approved History
                </a>
            </div>

            {{-- Table --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-black">Date / Time</th>
                                <th scope="col" class="px-6 py-4 font-black">Work Order</th>
                                <th scope="col" class="px-6 py-4 font-black">Part Number</th>
                                <th scope="col" class="px-6 py-4 font-black">Operator</th>
                                <th scope="col" class="px-6 py-4 font-black">Output</th>
                                <th scope="col" class="px-6 py-4 font-black">Yield</th>
                                <th scope="col" class="px-6 py-4 font-black text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($sessions as $session)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-bold text-gray-800">{{ optional($session->finished_at)->format('Y-m-d') ?: '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ optional($session->finished_at)->format('H:i') ?: '-' }} (Shift: {{ $session->shift }})</div>
                                    </td>
                                    <td class="px-6 py-4 font-mono font-bold text-gray-800">
                                        {{ $session->workOrder->wo_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-bold text-gray-800">{{ $session->workOrder->part_number }}</div>
                                        <div class="text-xs text-gray-500 max-w-xs truncate">{{ $session->workOrder->part_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-700">
                                        {{ $session->operator->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-black text-green-600">{{ number_format($session->total_good) }}</span>
                                        <span class="text-xs text-gray-400">/ {{ number_format($session->total_reject) }} NG</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $total = $session->total_good + $session->total_reject;
                                            $yield = $total > 0 ? ($session->total_good / $total) * 100 : 0;
                                        @endphp
                                        <div class="font-bold {{ $yield >= 98 ? 'text-green-600' : ($yield >= 95 ? 'text-orange-500' : 'text-red-600') }}">
                                            {{ number_format($yield, 2) }}%
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if($tab === 'pending')
                                            <a href="{{ route('sp-approvals.show', $session->id) }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition shadow-sm text-xs uppercase tracking-wider">
                                                Review
                                            </a>
                                        @else
                                            <a href="{{ route('sp-approvals.show', $session->id) }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-100 text-gray-700 font-bold rounded-lg hover:bg-gray-200 transition shadow-sm text-xs uppercase tracking-wider">
                                                View
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                        <div class="font-bold text-lg mb-1">No sessions found</div>
                                        <div class="text-sm">There are no {{ $tab }} approvals at this time.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($sessions->hasPages())
                    <div class="p-4 border-t border-gray-200">
                        {{ $sessions->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
