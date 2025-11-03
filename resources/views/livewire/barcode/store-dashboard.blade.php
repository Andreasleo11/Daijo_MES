    <div class="p-6 space-y-6" x-data="storeDashboard({{ json_encode($summaryData) }})">

        <h1 class="text-3xl font-bold mb-4">Store Dashboard</h1>

        {{-- Summary Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 rounded-lg">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 border">Part No</th>
                        <th class="px-4 py-2 border">Daijo</th>
                        <th class="px-4 py-2 border">KIIC</th>
                        <th class="px-4 py-2 border">Customer</th>
                        <th class="px-4 py-2 border">Total</th>
                        <th class="px-4 py-2 border">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="summary in summaryData" :key="summary.part_no">
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border" x-text="summary.part_no"></td>
                            <td class="px-4 py-2 border text-center" x-text="summary.quantity_daijo"></td>
                            <td class="px-4 py-2 border text-center" x-text="summary.quantity_kiic"></td>
                            <td class="px-4 py-2 border text-center" x-text="summary.quantity_customer"></td>
                            <td class="px-4 py-2 border text-center font-semibold" x-text="summary.total"></td>
                            <td class="px-4 py-2 border text-center">
                                <button @click="openDetail(summary)" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded">
                                    Detail
                                </button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="summaryData.length === 0">
                        <td colspan="6" class="text-center py-4">No data available</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Detail Modal --}}
        <div x-show="detailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" x-cloak>
            <div class="bg-white rounded-lg w-11/12 max-w-4xl p-6 space-y-4 overflow-y-auto max-h-[90vh]">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold">Detail Part No: <span x-text="selectedPartNo"></span></h2>
                    <button @click="closeDetail()" class="text-red-500 font-bold text-2xl">&times;</button>
                </div>

                {{-- Filter by Date --}}
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <label class="block mb-2 font-semibold text-gray-700">Filter by Last Transaction Date</label>
                    <div class="flex gap-2 items-center">
                        <input 
                            type="date" 
                            x-model="detailFilterDate" 
                            @change="applyDetailFilter()" 
                            class="border border-gray-300 rounded px-3 py-2 flex-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                        <button 
                            x-show="detailFilterDate" 
                            @click="resetDetailFilter()" 
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded whitespace-nowrap"
                        >
                            Clear Filter
                        </button>
                    </div>
                    <p x-show="detailFilterDate" class="text-sm text-gray-600 mt-2">
                        Showing labels with last transaction on: <span class="font-semibold" x-text="formatDate(detailFilterDate)"></span>
                    </p>
                </div>

                {{-- Detail Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 rounded-lg">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 border">Label</th>
                                <th class="px-4 py-2 border">Position</th>
                                <th class="px-4 py-2 border">Last Transaction</th>
                                <th class="px-4 py-2 border">History</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="detail in detailData" :key="detail.label">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border" x-text="detail.label"></td>
                                    <td class="px-4 py-2 border">
                                        <span x-text="detail.position"></span>
                                        <template x-if="detail.customer">
                                            <span class="text-gray-500 text-sm"> (<span x-text="detail.customer"></span>)</span>
                                        </template> 
                                    </td>
                                    <td class="px-4 py-2 border" x-text="formatDateTime(detail.last_transaction)"></td> 
                                    <td class="px-4 py-2 border text-center">
                                        <button @click="openHistory(detail.history)" class="text-blue-500 hover:text-blue-700 underline text-sm">
                                            View (<span x-text="detail.history.length"></span>)
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="detailData.length === 0">
                                <td colspan="5" class="text-center py-4 text-gray-500">
                                    <span x-show="!detailFilterDate">No details available</span>
                                    <span x-show="detailFilterDate">No data found for selected date</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- History Modal --}}
        <div x-show="historyModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" x-cloak>
            <div class="bg-white rounded-lg w-11/12 max-w-3xl p-6 space-y-4 overflow-y-auto max-h-[80vh]">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold">Transaction History</h2>
                    <button @click="closeHistory()" class="text-red-500 font-bold text-2xl">&times;</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 rounded-lg">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 border">Scantime</th>
                                <th class="px-4 py-2 border">Position</th>
                                <th class="px-4 py-2 border">Label</th>
                                <th class="px-4 py-2 border">No Dokumen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(h, index) in historyData" :key="index">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border" x-text="formatDateTime(h.scantime)"></td>
                                    <td class="px-4 py-2 border" x-text="h.position"></td>
                                    <td class="px-4 py-2 border" x-text="h.label"></td>
                                    <td class="px-4 py-2 border" x-text="h.no_dokumen"></td>
                                </tr>
                            </template>
                            <tr x-show="historyData.length === 0">
                                <td colspan="4" class="text-center py-4 text-gray-500">No history available</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script>
    function storeDashboard(summaryData) {
        return {
            summaryData: summaryData,
            detailModal: false,
            historyModal: false,
            selectedPartNo: '',
            detailData: [],
            originalDetailData: [],
            detailFilterDate: null,
            historyData: [],

            openDetail(summary) {
                this.selectedPartNo = summary.part_no;
                this.detailData = summary.details;
                this.originalDetailData = summary.details;
                this.detailModal = true;
                this.detailFilterDate = null;
            },

            closeDetail() {
                this.detailModal = false;
                this.selectedPartNo = '';
                this.detailData = [];
                this.originalDetailData = [];
                this.detailFilterDate = null;
            },

            applyDetailFilter() {
                if(!this.detailFilterDate) {
                    this.resetDetailFilter();
                    return;
                }

                const filterDate = this.detailFilterDate;
                
                this.detailData = this.originalDetailData.filter(detail => {
                    // Extract date from last_transaction (format: YYYY-MM-DD HH:MM:SS or ISO)
                    const lastTransactionDate = detail.last_transaction.split(' ')[0].split('T')[0];
                    return lastTransactionDate === filterDate;
                });
            },

            resetDetailFilter() {
                this.detailFilterDate = null;
                this.detailData = this.originalDetailData;
            },

            openHistory(history) {
                this.historyData = history;
                this.historyModal = true;
            },

            closeHistory() {
                this.historyModal = false;
                this.historyData = [];
            },

            formatDate(dateString) {
                if (!dateString) return '';
                const date = new Date(dateString);
                return date.toLocaleDateString('id-ID', { 
                    day: '2-digit', 
                    month: 'long', 
                    year: 'numeric' 
                });
            },

            formatDateTime(dateTimeString) {
                if (!dateTimeString) return '';
                const date = new Date(dateTimeString);
                return date.toLocaleString('id-ID', { 
                    day: '2-digit', 
                    month: 'short', 
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        }
    }
    </script>