<div>
    {{-- Header & Submit All Button --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 mb-3">
        <div class="font-extrabold text-xs sm:text-sm text-[#92400E]">
            📦 Rincian Box SPK {{ $item->spk_code }} (Gudang: {{ $item->warehouse }})
        </div>
        @if($qcStatus != 1)
            <button onclick="if(confirm('Kirim inspeksi untuk SEMUA box yang diisi pada summary ini? Log hasil inspeksi akan FINAL dan tidak dapat diubah.')) { @this.submitWholeSummary({{ $item->id }}) }"
                    class="w-full sm:w-auto bg-[#D97706] text-white border-none px-3.5 py-2 rounded text-xs font-extrabold cursor-pointer shadow-sm active:scale-95 transition">
                ⚡ Submit Semua Box Summary
            </button>
        @endif
    </div>

    {{-- Desktop Subtable View --}}
    <div class="hidden sm:block overflow-x-auto">
        <table class="w-full border-collapse bg-white border border-[#E8E4DC] rounded text-xs">
            <thead class="bg-[#F3F4F6] text-[10px] font-bold text-[#4B5563] uppercase">
                <tr>
                    <th class="p-2">Label Box</th>
                    <th class="p-2">Item Code</th>
                    <th class="p-2 text-right">Box Qty</th>
                    <th class="p-2 w-[130px] text-center">Qty NG (Input QC)</th>
                    <th class="p-2 text-right">Qty OK</th>
                    <th class="p-2 text-center">Tujuan Transfer</th>
                    <th class="p-2 text-center">Status Transfer SAP</th>
                    <th class="p-2 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rowDetails[$item->id] ?? [] as $box)
                    @php
                        $boxId = $box['id'];
                        $isInspected = $box['is_inspected'];
                        $boxQty = $box['quantity'];
                        $ngVal = (int)($ngInputs[$boxId] ?? 0);
                        $okVal = max(0, $boxQty - $ngVal);
                        $log = $box['log'];
                        $whOk = $item->warehouse === 'KRFFI' ? 'KRFG' : 'FG';
                        $whNg = $item->warehouse === 'KRFFI' ? 'KRRJCT' : 'RJCT';
                    @endphp
                    <tr class="border-b border-[#E5E7EB] {{ $isInspected ? 'bg-gray-50' : 'bg-white' }}">
                        <td class="p-2 font-bold text-gray-800">{{ $box['label'] }}</td>
                        <td class="p-2 font-semibold">{{ $box['item_code'] }}</td>
                        <td class="p-2 text-right font-extrabold text-gray-800">{{ number_format($boxQty) }}</td>
                        <td class="p-2 text-center">
                            @if($isInspected)
                                <span class="font-extrabold text-xs {{ $log['ng_qty'] > 0 ? 'text-red-600' : 'text-gray-500' }}">
                                    {{ $log['ng_qty'] }} PCS
                                </span>
                            @else
                                <input type="number" min="0" max="{{ $boxQty }}" wire:model.live="ngInputs.{{ $boxId }}"
                                       x-data="{ boxId: '{{ $boxId }}' }"
                                       x-init="
                                           let saved = localStorage.getItem('qc_draft_ng_' + boxId);
                                           if (saved !== null && !@json($isInspected)) {
                                               $wire.set('ngInputs.' + boxId, parseInt(saved) || 0);
                                           }
                                       "
                                       @input="localStorage.setItem('qc_draft_ng_' + boxId, $event.target.value)"
                                       class="w-20 text-center p-1 border-2 rounded font-extrabold text-xs {{ $ngVal > 0 ? 'border-red-500 text-red-600 bg-red-50' : 'border-gray-300 text-gray-900' }}">
                            @endif
                        </td>
                        <td class="p-2 text-right">
                            <span class="font-extrabold text-green-600 text-xs">
                                {{ number_format($isInspected ? $log['ok_qty'] : $okVal) }} PCS
                            </span>
                        </td>
                        <td class="p-2 text-center text-[11px]">
                            @if($isInspected)
                                <div class="font-bold">OK: <span class="text-green-600">{{ $log['ok_to_warehouse'] }}</span></div>
                                @if($log['ng_qty'] > 0)
                                    <div class="font-bold">NG: <span class="text-red-600">{{ $log['ng_to_warehouse'] }}</span></div>
                                @endif
                            @else
                                <div class="font-semibold text-gray-600">OK → <span class="text-green-600 font-bold">{{ $whOk }}</span></div>
                                @if($ngVal > 0)
                                    <div class="font-semibold text-gray-600">NG → <span class="text-red-600 font-bold">{{ $whNg }}</span></div>
                                @endif
                            @endif
                        </td>
                        <td class="p-2 text-center">
                            @if($isInspected)
                                <div class="flex flex-col gap-0.5 items-center">
                                    @if($log['ok_qty'] > 0)
                                        @if($log['ok_sap_status'] == 1)
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-green-100 text-green-700">OK: SUKSES</span>
                                        @elseif($log['ok_sap_status'] == 2)
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-red-100 text-red-700">OK: GAGAL ⚠</span>
                                            <div class="text-[9px] text-red-600 font-semibold mt-0.5 max-w-[140px] leading-tight text-center">{{ $log['ok_sap_error'] }}</div>
                                        @else
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-amber-100 text-amber-700">OK: PENDING</span>
                                        @endif
                                    @endif

                                    @if($log['ng_qty'] > 0)
                                        @if($log['ng_sap_status'] == 1)
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-green-100 text-green-700">NG: SUKSES</span>
                                        @elseif($log['ng_sap_status'] == 2)
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-red-100 text-red-700">NG: GAGAL ⚠</span>
                                            <div class="text-[9px] text-red-600 font-semibold mt-0.5 max-w-[140px] leading-tight text-center">{{ $log['ng_sap_error'] }}</div>
                                        @else
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-amber-100 text-amber-700">NG: PENDING</span>
                                        @endif
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-400 text-[11px] italic">Belum kirim</span>
                            @endif
                        </td>
                        <td class="p-2 text-center">
                            @if($isInspected)
                                @if($log['ok_sap_status'] == 2 || $log['ng_sap_status'] == 2)
                                    <button wire:click="retryTransfer({{ $log['id'] }})" class="bg-red-600 text-white border-none px-2 py-1 rounded text-[10px] font-bold cursor-pointer">
                                        Retry SAP
                                    </button>
                                @else
                                    <span class="text-green-600 font-extrabold text-xs">✓ Selesai</span>
                                @endif
                            @else
                                <button onclick="if(confirm('Kirim hasil inspeksi untuk Box Label {{ $box['label'] }}? (OK: {{ $okVal }} pcs, NG: {{ $ngVal }} pcs). Hasil inspeksi akan FINAL dan tidak dapat diubah.')) { @this.submitSingleBox({{ $boxId }}, {{ $item->id }}) }"
                                        class="bg-blue-600 text-white border-none px-2.5 py-1 rounded text-xs font-bold cursor-pointer">
                                    Submit Box
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="p-3 text-center text-gray-400">Tidak ada box ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Box Cards View --}}
    <div class="block sm:hidden space-y-2.5">
        @forelse($rowDetails[$item->id] ?? [] as $box)
            @php
                $boxId = $box['id'];
                $isInspected = $box['is_inspected'];
                $boxQty = $box['quantity'];
                $ngVal = (int)($ngInputs[$boxId] ?? 0);
                $okVal = max(0, $boxQty - $ngVal);
                $log = $box['log'];
                $whOk = $item->warehouse === 'KRFFI' ? 'KRFG' : 'FG';
                $whNg = $item->warehouse === 'KRFFI' ? 'KRRJCT' : 'RJCT';
            @endphp
            <div class="bg-white border border-[#E8E4DC] rounded-lg p-3 shadow-xs {{ $isInspected ? 'bg-gray-50' : 'bg-white' }}">
                <div class="flex justify-between items-start mb-2 pb-2 border-b border-gray-100">
                    <div>
                        <div class="font-extrabold text-xs text-gray-900">Label: {{ $box['label'] }}</div>
                        <div class="text-[11px] font-semibold text-gray-600">{{ $box['item_code'] }}</div>
                    </div>
                    <div class="text-right">
                        <span class="text-[9px] font-bold text-gray-400 block uppercase">BOX QTY</span>
                        <span class="font-black text-xs text-gray-900">{{ number_format($boxQty) }} PCS</span>
                    </div>
                </div>

                {{-- Inspection Qty Inputs / Display --}}
                <div class="grid grid-cols-2 gap-2 mb-3 bg-gray-50 p-2 rounded">
                    <div>
                        <span class="text-[10px] font-bold text-gray-500 block uppercase mb-1">QTY NG (INPUT)</span>
                        @if($isInspected)
                            <span class="font-black text-sm {{ $log['ng_qty'] > 0 ? 'text-red-600' : 'text-gray-700' }}">
                                {{ $log['ng_qty'] }} PCS
                            </span>
                        @else
                            <input type="number" min="0" max="{{ $boxQty }}" wire:model.live="ngInputs.{{ $boxId }}"
                                   x-data="{ boxId: '{{ $boxId }}' }"
                                   x-init="
                                       let saved = localStorage.getItem('qc_draft_ng_' + boxId);
                                       if (saved !== null && !@json($isInspected)) {
                                           $wire.set('ngInputs.' + boxId, parseInt(saved) || 0);
                                       }
                                   "
                                   @input="localStorage.setItem('qc_draft_ng_' + boxId, $event.target.value)"
                                   class="w-full text-center p-1.5 border-2 rounded font-extrabold text-sm {{ $ngVal > 0 ? 'border-red-500 text-red-600 bg-red-50' : 'border-gray-300 text-gray-900' }}">
                        @endif
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] font-bold text-gray-500 block uppercase mb-1">QTY OK</span>
                        <span class="font-black text-sm text-green-600 block mt-1">
                            {{ number_format($isInspected ? $log['ok_qty'] : $okVal) }} PCS
                        </span>
                    </div>
                </div>

                {{-- Warehouse & Status Badges --}}
                <div class="flex justify-between items-center text-[10px] mb-3 px-1">
                    <div class="text-gray-600">
                        @if($isInspected)
                            <span class="font-bold">OK: <span class="text-green-600">{{ $log['ok_to_warehouse'] }}</span></span>
                            @if($log['ng_qty'] > 0)
                                <span class="font-bold ml-2">NG: <span class="text-red-600">{{ $log['ng_to_warehouse'] }}</span></span>
                            @endif
                        @else
                            <span class="font-semibold">OK → <span class="text-green-600 font-bold">{{ $whOk }}</span></span>
                            @if($ngVal > 0)
                                <span class="font-semibold ml-2">NG → <span class="text-red-600 font-bold">{{ $whNg }}</span></span>
                            @endif
                        @endif
                    </div>
                    <div class="text-right">
                        @if($isInspected)
                            @if($log['ok_sap_status'] == 1)
                                <span class="px-1.5 py-0.5 rounded font-extrabold bg-green-100 text-green-700">OK: SUKSES</span>
                            @elseif($log['ok_sap_status'] == 2)
                                <span class="px-1.5 py-0.5 rounded font-extrabold bg-red-100 text-red-700">OK: GAGAL ⚠</span>
                                <div class="text-[9px] text-red-600 font-semibold mt-0.5 max-w-[150px] leading-tight text-right">{{ $log['ok_sap_error'] }}</div>
                            @endif

                            @if($log['ng_sap_status'] == 1)
                                <span class="px-1.5 py-0.5 rounded font-extrabold bg-green-100 text-green-700 ml-1">NG: SUKSES</span>
                            @elseif($log['ng_sap_status'] == 2)
                                <span class="px-1.5 py-0.5 rounded font-extrabold bg-red-100 text-red-700 ml-1">NG: GAGAL ⚠</span>
                                <div class="text-[9px] text-red-600 font-semibold mt-0.5 max-w-[150px] leading-tight text-right">{{ $log['ng_sap_error'] }}</div>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- Submit / Action Button --}}
                <div>
                    @if($isInspected)
                        @if($log['ok_sap_status'] == 2 || $log['ng_sap_status'] == 2)
                            <button wire:click="retryTransfer({{ $log['id'] }})" class="w-full bg-red-600 text-white border-none py-2 rounded text-xs font-bold cursor-pointer">
                                Retry Transfer SAP
                            </button>
                        @else
                            <div class="text-center text-green-600 font-extrabold text-xs py-1">✓ Box Selesai Diinspeksi</div>
                        @endif
                    @else
                        <button onclick="if(confirm('Kirim hasil inspeksi untuk Box Label {{ $box['label'] }}? (OK: {{ $okVal }} pcs, NG: {{ $ngVal }} pcs). Hasil inspeksi akan FINAL dan tidak dapat diubah.')) { @this.submitSingleBox({{ $boxId }}, {{ $item->id }}) }"
                                class="w-full bg-blue-600 text-white border-none py-2 rounded text-xs font-bold cursor-pointer active:scale-98 transition shadow-xs">
                            Submit Box {{ $box['label'] }}
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-3 text-center text-gray-400 text-xs">Tidak ada box ditemukan.</div>
        @endforelse
    </div>
</div>
