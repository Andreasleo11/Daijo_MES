<div class="space-y-6">
    <!-- Notifications & Warnings -->
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative shadow-sm" role="alert">
            <span class="block sm:inline font-semibold">{{ session('message') }}</span>
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative shadow-sm" role="alert">
            <span class="block sm:inline font-semibold">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Main Form & Toolbar -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Add Customer Card (Tambah per satuan) -->
        <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm space-y-4">
            <h3 class="font-bold text-gray-800 text-sm border-b pb-2">Tambah Customer Baru</h3>
            <form wire:submit.prevent="createCustomer" class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Customer Code</label>
                    <input type="text" wire:model="newCustomerCode" placeholder="Contoh: CUST001" 
                           class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2">
                    @error('newCustomerCode') <span class="text-red-500 text-xs font-semibold">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Customer Name</label>
                    <input type="text" wire:model="newCustomerName" placeholder="Contoh: PT. ABC Indonesia" 
                           class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2">
                    @error('newCustomerName') <span class="text-red-500 text-xs font-semibold">{{ $message }}</span> @enderror
                </div>
                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm shadow transition inline-flex justify-center items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Customer
                </button>
            </form>
        </div>

        <!-- Master List View -->
        <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm space-y-4 lg:col-span-2">
            <div class="flex items-center justify-between border-b pb-2">
                <div class="flex items-center space-x-3">
                    <h3 class="font-bold text-gray-800 text-sm">Daftar Customer</h3>
                    <a href="{{ route('admin.customer-delivery-logs') }}" 
                       class="text-[10px] bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-1 px-3 rounded shadow transition inline-flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Lihat Log
                    </a>
                </div>
                <!-- Search -->
                <div class="w-1/2 relative">
                    <input wire:model.live="search" type="text" placeholder="Cari code atau name..." 
                           class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-xs pl-8 py-1.5">
                    <div class="absolute left-2.5 top-2 text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Spreadsheet-like Master Table -->
            <div class="overflow-x-auto rounded border border-gray-200 bg-white">
                <table class="min-w-full divide-y divide-gray-200 text-xs text-left">
                    <thead class="bg-gray-50 text-gray-700 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3">Customer Code</th>
                            <th class="px-4 py-3">Customer Name</th>
                            <th class="px-4 py-3 text-center" style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-gray-800">
                        @forelse ($customers as $cust)
                            <tr class="hover:bg-gray-50/50 transition">
                                <!-- Customer Code -->
                                <td class="px-4 py-3 font-semibold text-gray-900 select-all" wire:dblclick="startEdit({{ $cust->id }}, 'customer_code')">
                                    @if($editingItemId === $cust->id && $editingField === 'customer_code')
                                        <input type="text" wire:model.defer="editingValue" wire:keydown.enter="saveEdit" wire:keydown.escape="cancelEdit" 
                                               class="w-full text-left rounded border-gray-300 p-1 text-xs focus:ring-1 focus:ring-blue-500 focus:border-blue-500 font-semibold" autofocus>
                                    @else
                                        <span class="cursor-pointer border-b border-dashed border-gray-400 hover:text-blue-600" title="Double click to edit">
                                            {{ $cust->customer_code }}
                                        </span>
                                    @endif
                                </td>
                                
                                <!-- Customer Name -->
                                <td class="px-4 py-3 font-semibold text-gray-700 select-all" wire:dblclick="startEdit({{ $cust->id }}, 'customer_name')">
                                    @if($editingItemId === $cust->id && $editingField === 'customer_name')
                                        <input type="text" wire:model.defer="editingValue" wire:keydown.enter="saveEdit" wire:keydown.escape="cancelEdit" 
                                               class="w-full text-left rounded border-gray-300 p-1 text-xs focus:ring-1 focus:ring-blue-500 focus:border-blue-500 font-semibold" autofocus>
                                    @else
                                        <span class="cursor-pointer border-b border-dashed border-gray-400 hover:text-blue-600" title="Double click to edit">
                                            {{ $cust->customer_name }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Action Buttons -->
                                <td class="px-4 py-3 text-center">
                                    <button wire:click="deleteCustomer({{ $cust->id }})" 
                                            wire:confirm="Yakin ingin menghapus customer {{ $cust->customer_name }}?"
                                            class="text-red-600 hover:text-red-950 font-bold hover:underline transition">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-6 text-gray-500 font-semibold">Tidak ada customer ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $customers->links() }}
            </div>
        </div>
    </div>
</div>
