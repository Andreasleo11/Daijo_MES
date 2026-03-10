<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between px-6 py-4">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Stock Health Dashboard</h1>
                <p class="text-sm text-gray-500 mt-0.5">Finished Goods stock status vs safety stock</p>
            </div>
            <span class="text-xs text-gray-400 bg-gray-100 px-3 py-1 rounded-full">
                Last synced from SAP
            </span>
        </div>
    </x-slot>

    <div class="p-6 space-y-6">

        {{-- ============================================================
             SUMMARY CARDS
        ================================================================ --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- Total --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-.375a1.125 1.125 0 00-1.125-1.125H3.375A1.125 1.125 0 002.25 6v.375c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $summary['total'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Total Items</p>
                </div>
            </div>

            {{-- Healthy --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-green-700">{{ $summary['healthy'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Healthy</p>
                </div>
            </div>

            {{-- At Risk --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-yellow-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-yellow-700">{{ $summary['at_risk'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">At Risk</p>
                </div>
            </div>

            {{-- Critical --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-red-700">{{ $summary['critical'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Critical</p>
                </div>
            </div>
        </div>

        {{-- ============================================================
             PROGRESS BAR OVERVIEW
        ================================================================ --}}
        @if($summary['total'] > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex justify-between text-xs text-gray-500 mb-2">
                <span>Stock Health Distribution</span>
                <span>{{ $summary['total'] }} items</span>
            </div>
            <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden flex gap-0.5">
                @php
                    $healthyPct  = $summary['total'] > 0 ? ($summary['healthy']  / $summary['total']) * 100 : 0;
                    $atRiskPct   = $summary['total'] > 0 ? ($summary['at_risk']  / $summary['total']) * 100 : 0;
                    $criticalPct = $summary['total'] > 0 ? ($summary['critical'] / $summary['total']) * 100 : 0;
                @endphp
                @if($healthyPct  > 0)<div class="h-full bg-green-400  rounded-l-full transition-all" style="width: {{ $healthyPct  }}%"></div>@endif
                @if($atRiskPct   > 0)<div class="h-full bg-yellow-400 transition-all"                 style="width: {{ $atRiskPct   }}%"></div>@endif
                @if($criticalPct > 0)<div class="h-full bg-red-400    rounded-r-full transition-all" style="width: {{ $criticalPct }}%"></div>@endif
            </div>
            <div class="flex gap-6 mt-3 text-xs">
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-green-400 inline-block"></span>Healthy {{ number_format($healthyPct, 1) }}%</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-yellow-400 inline-block"></span>At Risk {{ number_format($atRiskPct, 1) }}%</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-400 inline-block"></span>Critical {{ number_format($criticalPct, 1) }}%</span>
            </div>
        </div>
        @endif

        {{-- ============================================================
             FILTER BAR
        ================================================================ --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <form method="GET" action="{{ route('inventory.stock-health') }}" class="flex flex-wrap gap-3 items-end">

                {{-- Keyword search --}}
                <div class="flex-1 min-w-48">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Search Item</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </span>
                        <input
                            id="search"
                            type="text"
                            name="search"
                            value="{{ $filter->search }}"
                            placeholder="Item code or name…"
                            class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-transparent transition"
                        >
                    </div>
                </div>

                {{-- Process Owner --}}
                <div class="min-w-44">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Process Owner</label>
                    <select
                        id="process_owner"
                        name="process_owner"
                        class="w-full py-2 px-3 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-transparent transition bg-white"
                    >
                        <option value="">All Owners</option>
                        @foreach($processOwners as $owner)
                            <option value="{{ $owner }}" {{ $filter->processOwner === $owner ? 'selected' : '' }}>
                                {{ $owner }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Family --}}
                <div class="min-w-40">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Family</label>
                    <select
                        id="family"
                        name="family"
                        class="w-full py-2 px-3 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-transparent transition bg-white"
                    >
                        <option value="">All Families</option>
                        @foreach($families as $fam)
                            <option value="{{ $fam }}" {{ $filter->family === $fam ? 'selected' : '' }}>
                                {{ $fam }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        Apply
                    </button>
                    <a href="{{ route('inventory.stock-health') }}" class="px-4 py-2 text-sm font-medium bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- ============================================================
             ITEM TABLE
        ================================================================ --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

            @if(count($items) === 0)
                <div class="flex flex-col items-center justify-center py-20 text-gray-400">
                    <svg class="w-12 h-12 mb-3 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-.375a1.125 1.125 0 00-1.125-1.125H3.375A1.125 1.125 0 002.25 6v.375c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                    <p class="text-sm font-medium">No items found</p>
                    <p class="text-xs mt-1">Try adjusting your filters</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Item Code</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Item Name</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Process Owner</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Family</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Safety Stock</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Reject Stock</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($items as $item)
                                <tr class="hover:bg-gray-50 transition-colors duration-100">
                                    <td class="px-5 py-3.5 font-mono text-xs text-gray-700 whitespace-nowrap">{{ $item->itemCode }}</td>
                                    <td class="px-5 py-3.5 text-gray-800 font-medium max-w-xs truncate">{{ $item->itemName }}</td>
                                    <td class="px-5 py-3.5 text-gray-600 whitespace-nowrap">{{ $item->processOwner ?: '—' }}</td>
                                    <td class="px-5 py-3.5 text-gray-600 whitespace-nowrap">{{ $item->family ?: '—' }}</td>

                                    {{-- Stock with visual indicator --}}
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        <span class="font-semibold {{ $item->status === \App\Domain\Inventory\ValueObjects\StockStatus::Critical ? 'text-red-600' : ($item->status === \App\Domain\Inventory\ValueObjects\StockStatus::AtRisk ? 'text-yellow-600' : 'text-green-700') }}">
                                            {{ number_format($item->stock) }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-3.5 text-right text-gray-500 whitespace-nowrap">{{ number_format($item->safetyStock) }}</td>

                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        @if($item->rejectStock > 0)
                                            <span class="text-orange-600 font-medium">{{ number_format($item->rejectStock) }}</span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>

                                    {{-- Status Badge --}}
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $item->status->badgeClasses() }}">
                                            {{ $item->status->label() }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Row count footer --}}
                <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 text-xs text-gray-400">
                    Showing {{ count($items) }} item{{ count($items) !== 1 ? 's' : '' }}
                    @if($filter->search || $filter->processOwner || $filter->family)
                        <span class="text-indigo-500 ml-1">— filters active</span>
                    @endif
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
