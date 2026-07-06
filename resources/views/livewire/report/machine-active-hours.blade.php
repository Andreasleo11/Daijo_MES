<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">📊 Machine Active Hours</h1>
                <p class="text-gray-500">Laporan akumulasi jam aktif mesin berdasarkan rentang bulan.</p>
            </div>
            <div class="flex space-x-3">
                <button wire:click="exportExcel" 
                    class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl shadow-lg shadow-green-100 transition-all flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    EXPORT EXCEL
                </button>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2 uppercase tracking-wider">Bulan Awal</label>
                    <input type="month" wire:model="startMonth" 
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2 uppercase tracking-wider">Bulan Akhir</label>
                    <input type="month" wire:model="endMonth" 
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                </div>
                <div>
                    <button wire:click="calculate" 
                        class="w-full px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-100 transition-all flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        GENERATE REPORT
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            @if(count($reportData) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse table-fixed">
                        <thead class="bg-gray-50 text-gray-500 text-xs font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-4 w-20 border-b border-gray-100">No</th>
                                <th class="px-6 py-4 border-b border-gray-100">Machine Name</th>
                                <th class="px-6 py-4 text-center w-48 border-b border-gray-100">Total Active Hours</th>
                                <th class="px-6 py-4 text-right w-40 border-b border-gray-100">Breakdown</th>
                            </tr>
                        </thead>
                        @php $no = 1; @endphp
                        @foreach($reportData as $machineName => $data)
                            <tbody x-data="{ open: false }" class="divide-y divide-gray-50 border-b border-gray-100">
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 text-sm text-gray-400 font-mono">{{ $no++ }}</td>
                                    <td class="px-6 py-4 font-bold text-gray-800">{{ $machineName }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-50 text-blue-700">
                                            {{ $data['total'] }} Jam
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button @click="open = !open" 
                                            class="inline-flex items-center text-blue-600 hover:text-blue-800 font-bold text-sm transition-all focus:outline-none">
                                            <span x-text="open ? 'Hide' : 'Detail'"></span>
                                            <svg class="w-4 h-4 ml-1 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        </button>
                                    </td>
                                </tr>
                                <!-- Breakdown Row -->
                                <tr x-show="open" x-cloak x-transition.opacity class="bg-gray-50">
                                    <td colspan="4" class="px-6 py-4">
                                        <div class="bg-white rounded-xl shadow-inner border border-gray-200 overflow-hidden max-h-80 overflow-y-auto">
                                            <table class="w-full text-sm">
                                                <thead class="bg-gray-100 text-gray-600 font-bold">
                                                    <tr>
                                                        <th class="px-4 py-2 text-left">Date</th>
                                                        <th class="px-4 py-2 text-right">Active Hours</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100">
                                                    @foreach($data['days'] as $date => $dayHours)
                                                        <tr class="hover:bg-blue-50 transition-colors">
                                                            <td class="px-4 py-2 text-gray-700">
                                                                {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                                                                <span class="text-xs text-gray-400 ml-2">({{ \Carbon\Carbon::parse($date)->format('l') }})</span>
                                                            </td>
                                                            <td class="px-4 py-2 text-right font-bold text-blue-600">
                                                                {{ $dayHours }} Jam
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        @endforeach
                    </table>
                </div>
            @else
                <div class="px-6 py-20 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Belum ada data</h3>
                    <p class="text-gray-500">Pilih rentang bulan dan klik "Generate Report" untuk melihat data.</p>
                </div>
            @endif
        </div>

    </div>
</div>
