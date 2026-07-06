<div class="max-w-7xl mx-auto p-6">
    <div class="bg-white rounded-lg shadow-lg">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-lg">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-white">Input Delivery Schedule</h2>
                    <p class="text-blue-100 text-sm mt-1">Kelola jadwal pengiriman customer dengan mudah</p>
                </div>
                <a href="{{ route('delivery-schedule.calendar') }}" 
                   class="bg-white text-blue-600 px-4 py-2 rounded-lg font-semibold hover:bg-blue-50 transition flex items-center gap-2 shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    View Calendar
                </a>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session()->has('success'))
            <div class="mx-6 mt-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-green-800 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mx-6 mt-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-red-800 font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <form wire:submit.prevent="submit" class="p-6 space-y-6">
            <!-- Customer Selection -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Customer <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input 
                        type="text" 
                        wire:model.live="customerSearch"
                        wire:focus="$set('showCustomerDropdown', true)"
                        placeholder="Ketik customer code atau nama..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                    >
                    
                    @if ($showCustomerDropdown && $this->customers->count() > 0)
                        <div class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            @foreach ($this->customers as $customer)
                                <div 
                                    wire:click="selectCustomer({{ $customer->id }})"
                                    class="px-4 py-3 hover:bg-blue-50 cursor-pointer transition border-b border-gray-100 last:border-0"
                                >
                                    <div class="font-medium text-gray-900">{{ $customer->customer_code }}</div>
                                    <div class="text-sm text-gray-600">{{ $customer->customer_name }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                @error('selectedCustomer') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Month and Year Selection -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Bulan <span class="text-red-500">*</span>
                    </label>
                    <select 
                        wire:model.live="selectedMonth"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
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
                    @error('selectedMonth') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Tahun <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        wire:model.live="selectedYear"
                        min="2020"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                    >
                    @error('selectedYear') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Item Selection -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Item Code <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input 
                        type="text" 
                        wire:model.live="itemSearch"
                        wire:focus="$set('showItemDropdown', true)"
                        placeholder="Ketik item code atau nama..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                    >
                    
                    @if ($showItemDropdown && $this->items->count() > 0)
                        <div class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            @foreach ($this->items as $item)
                                <div 
                                    wire:click="selectItem({{ $item->id }})"
                                    class="px-4 py-3 hover:bg-blue-50 cursor-pointer transition border-b border-gray-100 last:border-0"
                                >
                                    <div class="font-medium text-gray-900">{{ $item->item_code }}</div>
                                    <div class="text-sm text-gray-600">{{ $item->item_name }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                @error('selectedItem') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- SO Number (Optional) -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    SO Number <span class="text-gray-400 text-xs">(Optional)</span>
                </label>
                <input 
                    type="text" 
                    wire:model="soNum"
                    placeholder="Masukkan nomor SO jika ada..."
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                >
            </div>

            <!-- Schedule Table -->
            @if (!empty($schedules))
                <div class="mt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Jadwal Pengiriman</h3>
                        <span class="text-sm text-gray-600">
                            {{ count($schedules) }} hari
                        </span>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="max-h-96 overflow-y-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky top-0">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                            Tanggal
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                            Quantity
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($schedules as $day => $schedule)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-3 whitespace-nowrap">
                                                <span class="text-sm font-medium text-gray-900">
                                                    {{ $day }}
                                                </span>
                                                <span class="text-sm text-gray-500 ml-2">
                                                    {{ \Carbon\Carbon::createFromDate($selectedYear, $selectedMonth, $day)->format('D') }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap">
                                                <input 
                                                    type="number" 
                                                    wire:model="schedules.{{ $day }}.quantity"
                                                    min="0"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                    placeholder="0"
                                                >
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Submit Button -->
            <div class="flex justify-end pt-4 border-t border-gray-200">
                <button 
                    type="submit"
                    class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-8 py-3 rounded-lg font-semibold hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="submit">
                        <svg class="w-5 h-5 inline mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Submit Delivery Schedule
                    </span>
                    <span wire:loading wire:target="submit" class="flex items-center">
                        <svg class="animate-spin h-5 w-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processing...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>