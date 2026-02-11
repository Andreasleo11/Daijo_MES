<div class="max-w-7xl mx-auto p-6">
    <div class="bg-white rounded-lg shadow-xl overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 px-6 py-6">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold text-white">📅 Delivery Schedule Calendar</h2>
                    <p class="text-indigo-100 text-sm mt-2">Lihat jadwal pengiriman customer dalam tampilan kalender</p>
                </div>
                <a href="{{ route('delivery-schedule.form') }}" 
                   class="bg-white text-indigo-600 px-4 py-2 rounded-lg font-semibold hover:bg-indigo-50 transition flex items-center gap-2 shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Create Schedule
                </a>
            </div>
        </div>

        <div class="p-6">
            <!-- Filter Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Customer Selection -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Filter Customer <span class="text-gray-400 text-xs">(Optional)</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="text" 
                            wire:model.live="customerSearch"
                            wire:focus="$set('showCustomerDropdown', true)"
                            placeholder="Ketik customer code atau nama..."
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                        >
                        
                        @if ($selectedCustomer)
                            <button 
                                wire:click="clearCustomer"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 transition"
                                title="Clear filter"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        @endif
                        
                        @if ($showCustomerDropdown && $this->customers->count() > 0)
                            <div class="absolute z-20 w-full mt-1 bg-white border-2 border-indigo-200 rounded-lg shadow-xl max-h-60 overflow-y-auto">
                                @foreach ($this->customers as $customer)
                                    <div 
                                        wire:click="selectCustomer({{ $customer->id }})"
                                        class="px-4 py-3 hover:bg-indigo-50 cursor-pointer transition border-b border-gray-100 last:border-0"
                                    >
                                        <div class="font-semibold text-gray-900">{{ $customer->customer_code }}</div>
                                        <div class="text-sm text-gray-600">{{ $customer->customer_name }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Item Selection (Optional) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Filter Item <span class="text-gray-400 text-xs">(Optional)</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="text" 
                            wire:model.live="itemSearch"
                            wire:focus="$set('showItemDropdown', true)"
                            placeholder="Ketik item code atau nama..."
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                        >
                        
                        @if ($selectedItemCode)
                            <button 
                                wire:click="clearItem"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 transition"
                                title="Clear filter"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        @endif
                        
                        @if ($showItemDropdown && $this->items->count() > 0)
                            <div class="absolute z-20 w-full mt-1 bg-white border-2 border-indigo-200 rounded-lg shadow-xl max-h-60 overflow-y-auto">
                                @foreach ($this->items as $item)
                                    <div 
                                        wire:click="selectItem('{{ $item->item_code }}')"
                                        class="px-4 py-3 hover:bg-indigo-50 cursor-pointer transition border-b border-gray-100 last:border-0"
                                    >
                                        <div class="font-semibold text-gray-900">{{ $item->item_code }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Month/Year Navigation -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Periode
                    </label>
                    <div class="flex items-center gap-2">
                        <button 
                            wire:click="previousMonth"
                            class="px-4 py-3 bg-gray-200 hover:bg-gray-300 rounded-lg transition font-semibold"
                        >
                            ◀
                        </button>
                        
                        <select 
                            wire:model.live="selectedMonth"
                            class="flex-1 px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 transition"
                        >
                            <option value="1">Januari</option>
                            <option value="2">Februari</option>
                            <option value="3">Maret</option>
                            <option value="4">April</option>
                            <option value="5">Mei</option>
                            <option value="6">Juni</option>
                            <option value="7">Juli</option>
                            <option value="8">Agustus</option>
                            <option value="9">September</option>
                            <option value="10">Oktober</option>
                            <option value="11">November</option>
                            <option value="12">Desember</option>
                        </select>

                        <input 
                            type="number" 
                            wire:model.live="selectedYear"
                            min="2020"
                            class="w-28 px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 transition"
                        >

                        <button 
                            wire:click="nextMonth"
                            class="px-4 py-3 bg-gray-200 hover:bg-gray-300 rounded-lg transition font-semibold"
                        >
                            ▶
                        </button>
                    </div>
                </div>
            </div>

            <!-- Active Filters Info -->
            @if ($selectedCustomer || $selectedItemCode)
                <div class="mb-6 bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                    <div class="flex flex-wrap gap-2 items-center">
                        <span class="text-sm font-semibold text-indigo-700">Active Filters:</span>
                        
                        @if ($selectedCustomer)
                            <span class="bg-indigo-600 text-white px-3 py-1 rounded-full text-sm font-medium flex items-center gap-1">
                                {{ $selectedCustomer->customer_code }}
                                <button wire:click="clearCustomer" class="hover:bg-indigo-700 rounded-full p-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </span>
                        @endif
                        
                        @if ($selectedItemCode)
                            <span class="bg-purple-600 text-white px-3 py-1 rounded-full text-sm font-medium flex items-center gap-1">
                                {{ $selectedItemCode }}
                                <button wire:click="clearItem" class="hover:bg-purple-700 rounded-full p-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </span>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Calendar Grid -->
            @if (!empty($calendarData))
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 shadow-inner">
                    <div class="text-center mb-6">
                        <h3 class="text-2xl font-bold text-gray-800">
                            {{ \Carbon\Carbon::createFromDate($selectedYear, $selectedMonth, 1)->format('F Y') }}
                        </h3>
                        @if ($selectedCustomer)
                            <p class="text-gray-600 mt-1">{{ $selectedCustomer->customer_name }}</p>
                        @endif
                        @if ($selectedItemCode)
                            <p class="text-purple-600 mt-1 font-medium">Item: {{ $selectedItemCode }}</p>
                        @endif
                    </div>

                    <!-- Day Headers -->
                    <div class="grid grid-cols-7 gap-2 mb-2">
                        @foreach(['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $day)
                            <div class="text-center font-bold text-gray-700 py-2">
                                {{ $day }}
                            </div>
                        @endforeach
                    </div>

                    <!-- Calendar Days -->
                    <div class="grid grid-cols-7 gap-2">
                        @php
                            $firstDay = \Carbon\Carbon::createFromDate($selectedYear, $selectedMonth, 1);
                            $startDayOfWeek = $firstDay->dayOfWeek; // 0=Sunday, 1=Monday, etc
                            // Adjust to make Monday = 0
                            $startDayOfWeek = ($startDayOfWeek == 0) ? 6 : $startDayOfWeek - 1;
                        @endphp

                        <!-- Empty cells before first day -->
                        @for ($i = 0; $i < $startDayOfWeek; $i++)
                            <div class="aspect-square"></div>
                        @endfor

                        <!-- Calendar days -->
                        @foreach ($calendarData as $day => $data)
                            @php
                                $hasSchedules = $data['total_items'] > 0;
                                $bgClass = 'bg-white hover:bg-indigo-50';
                                $borderClass = 'border-2 border-gray-200';
                                
                                if ($data['is_today']) {
                                    $borderClass = 'border-2 border-indigo-500 ring-2 ring-indigo-200';
                                }
                                
                                if ($hasSchedules) {
                                    $bgClass = 'bg-gradient-to-br from-indigo-100 to-purple-100 hover:from-indigo-200 hover:to-purple-200';
                                }
                                
                                if ($data['is_past']) {
                                    $bgClass .= ' opacity-60';
                                }
                            @endphp

                            <div 
                                wire:click="viewDate({{ $day }})"
                                class="aspect-square {{ $bgClass }} {{ $borderClass }} rounded-lg p-2 cursor-pointer transition-all duration-200 hover:shadow-lg hover:scale-105 flex flex-col justify-between"
                            >
                                <div class="flex justify-between items-start">
                                    <span class="text-lg font-bold {{ $data['is_today'] ? 'text-indigo-600' : 'text-gray-700' }}">
                                        {{ $day }}
                                    </span>
                                    @if ($hasSchedules)
                                        <span class="bg-indigo-600 text-white text-xs font-bold px-2 py-1 rounded-full">
                                            {{ $data['total_items'] }}
                                        </span>
                                    @endif
                                </div>

                                @if ($hasSchedules)
                                    <div class="mt-auto">
                                        <div class="text-xs font-semibold text-indigo-700 bg-white/70 rounded px-2 py-1 text-center">
                                            {{ number_format($data['total_quantity']) }} pcs
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Legend -->
                    <div class="mt-6 flex flex-wrap gap-4 justify-center text-sm">
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 bg-gradient-to-br from-indigo-100 to-purple-100 border-2 border-gray-300 rounded"></div>
                            <span class="text-gray-700">Ada Schedule</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 bg-white border-2 border-indigo-500 rounded"></div>
                            <span class="text-gray-700">Hari Ini</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 bg-white border-2 border-gray-300 rounded"></div>
                            <span class="text-gray-700">Tidak Ada Schedule</span>
                        </div>
                    </div>
                </div>
            @elseif($selectedCustomer || $selectedItemCode)
                <div class="text-center py-12 bg-gray-50 rounded-lg">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-gray-600 text-lg">Tidak ada schedule untuk filter ini</p>
                </div>
            @else
                <div class="text-center py-12 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg">
                    <svg class="w-20 h-20 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    <p class="text-gray-600 text-lg font-medium mb-2">Pilih filter untuk melihat schedule</p>
                    <p class="text-gray-500 text-sm">Customer, Item Code, atau keduanya</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Detail Schedule -->
    @if ($showModal && $selectedDate)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showModal') }">
            <!-- Backdrop -->
            <div 
                class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity"
                wire:click="closeModal"
            ></div>

            <!-- Modal Content -->
            <div class="flex min-h-screen items-center justify-center p-4">
                <div 
                    class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full transform transition-all"
                    @click.stop
                >
                    <!-- Modal Header -->
                    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 rounded-t-2xl">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-2xl font-bold text-white">
                                    Schedule Detail
                                </h3>
                                <p class="text-indigo-100 text-sm mt-1">
                                    {{ \Carbon\Carbon::createFromDate($selectedYear, $selectedMonth, $selectedDate)->format('l, d F Y') }}
                                </p>
                            </div>
                            <button 
                                wire:click="closeModal"
                                class="text-white hover:bg-white/20 rounded-lg p-2 transition"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 max-h-[60vh] overflow-y-auto">
                        @if (count($selectedDateSchedules) > 0)
                            <div class="space-y-3">
                                @foreach ($selectedDateSchedules as $schedule)
                                    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl p-5 border-2 border-indigo-200 hover:shadow-lg transition-all">
                                        <div class="flex justify-between items-start mb-3">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <span class="bg-indigo-600 text-white text-xs font-bold px-3 py-1 rounded-full">
                                                        {{ $schedule->item_code }}
                                                    </span>
                                                    @if ($schedule->so_num)
                                                        <span class="bg-purple-600 text-white text-xs font-bold px-3 py-1 rounded-full">
                                                            SO: {{ $schedule->so_num }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <p class="text-gray-600 text-sm">
                                                    Customer: <span class="font-semibold text-gray-800">{{ $schedule->customer_code }}</span>
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-3xl font-bold text-indigo-600">
                                                    {{ number_format($schedule->quantity) }}
                                                </div>
                                                <div class="text-xs text-gray-600 font-medium">pieces</div>
                                            </div>
                                        </div>
                                        
                                        <div class="flex gap-2 text-xs text-gray-500 pt-3 border-t border-indigo-200">
                                            <span>Created: {{ $schedule->created_at->format('d M Y H:i') }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Summary -->
                            <div class="mt-6 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl p-4 text-white">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-indigo-100 text-sm">Total Items</p>
                                        <p class="text-2xl font-bold">{{ count($selectedDateSchedules) }} items</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-indigo-100 text-sm">Total Quantity</p>
                                        <p class="text-2xl font-bold">{{ number_format($selectedDateSchedules->sum('quantity')) }} pcs</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <p class="text-gray-500">Tidak ada schedule di tanggal ini</p>
                            </div>
                        @endif
                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end">
                        <button 
                            wire:click="closeModal"
                            class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-2 rounded-lg font-semibold hover:from-indigo-700 hover:to-purple-700 transition"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush