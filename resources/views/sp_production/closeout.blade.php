<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-xl text-gray-800 leading-tight uppercase tracking-wider">
                Production Session Close-Out
            </h2>
            <span class="text-xs font-bold bg-blue-100 text-blue-800 px-3 py-1 rounded-full border border-blue-200 uppercase tracking-wider">
                Session #{{ $session->id }}
            </span>
        </div>
    </x-slot>

    <div class="py-6 space-y-6 max-w-5xl mx-auto" x-data="closeoutForm()">
        @php
            $wo = $session->workOrder;
            $spLines = config('mes.sp_lines', []);
            $lineSlug = array_search($session->unit_line, $spLines) ?: \Illuminate\Support\Str::slug($session->unit_line);
            $gatewayUrl = route('sp-sessions.line-gateway', [
                'lineSlug' => $lineSlug,
                'date' => $session->started_at?->format('Y-m-d') ?? now()->format('Y-m-d'),
                'shift' => $session->shift ?? 1,
            ]);
        @endphp

        <form action="{{ route('app.sp-sessions.submit-closeout', $session->id) }}" method="POST" class="space-y-6">
            @csrf

            {{-- SECTION 1: Session Summary (Readonly) --}}
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm space-y-4">
                <div class="border-b border-gray-100 pb-3 flex justify-between items-center">
                    <div>
                        <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">1. Session Summary</h3>
                        <p class="text-xs text-gray-500 font-medium">Read-only operational metrics recorded during session</p>
                    </div>
                    <span class="text-[11px] font-black uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                        Completed
                    </span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-200">
                        <span class="block text-[10px] font-black text-gray-400 uppercase tracking-wider">Work Order</span>
                        <span class="font-black text-blue-700">{{ $wo->wo_number ?? 'N/A' }}</span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-200">
                        <span class="block text-[10px] font-black text-gray-400 uppercase tracking-wider">Part Info</span>
                        <span class="font-bold text-gray-900 block truncate">{{ $wo->part_name ?? 'N/A' }}</span>
                        <span class="text-[10px] text-gray-500 font-mono">{{ $wo->part_number ?? '-' }}</span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-200">
                        <span class="block text-[10px] font-black text-gray-400 uppercase tracking-wider">Line & Shift</span>
                        <span class="font-bold text-gray-800">{{ $session->unit_line }} — Shift {{ $session->shift }}</span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-200">
                        <span class="block text-[10px] font-black text-gray-400 uppercase tracking-wider">Operator</span>
                        <span class="font-bold text-gray-800">{{ $session->operator->name ?? 'N/A' }}</span>
                    </div>
                </div>

                @php
                    $unusedWip = max(0, $session->total_input - ($session->total_good + $session->total_reject));
                @endphp
                <div class="grid grid-cols-3 md:grid-cols-7 gap-3 text-center text-xs">
                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                        <span class="block text-[10px] font-black text-slate-500 uppercase tracking-wider">Target Qty</span>
                        <span class="text-base font-black text-slate-800">{{ number_format($wo->target_qty ?? 0) }}</span>
                    </div>
                    <div class="bg-blue-50 p-2.5 rounded-xl border border-blue-200">
                        <span class="block text-[10px] font-black text-blue-600 uppercase tracking-wider">Total Input</span>
                        <span class="text-base font-black text-blue-900">{{ number_format($session->total_input) }}</span>
                    </div>
                    <div class="bg-indigo-50 p-2.5 rounded-xl border border-indigo-200">
                        <span class="block text-[10px] font-black text-indigo-600 uppercase tracking-wider">Leftover WIP</span>
                        <span class="text-base font-black text-indigo-900">{{ number_format($unusedWip) }}</span>
                    </div>
                    <div class="bg-emerald-50 p-2.5 rounded-xl border border-emerald-200">
                        <span class="block text-[10px] font-black text-emerald-600 uppercase tracking-wider">Good (OK)</span>
                        <span class="text-base font-black text-emerald-800">{{ number_format($session->total_good) }}</span>
                    </div>
                    <div class="bg-red-50 p-2.5 rounded-xl border border-red-200">
                        <span class="block text-[10px] font-black text-red-600 uppercase tracking-wider">Reject (NG)</span>
                        <span class="text-base font-black text-red-800">{{ number_format($session->total_reject) }}</span>
                    </div>
                    <div class="bg-purple-50 p-2.5 rounded-xl border border-purple-200">
                        <span class="block text-[10px] font-black text-purple-600 uppercase tracking-wider">Rework Rec</span>
                        <span class="text-base font-black text-purple-800">{{ number_format($session->total_rework_recovered) }}</span>
                    </div>
                    <div class="bg-amber-50 p-2.5 rounded-xl border border-amber-200">
                        <span class="block text-[10px] font-black text-amber-700 uppercase tracking-wider">Yield Rate</span>
                        <span class="text-base font-black text-amber-900">{{ number_format($session->yield, 1) }}%</span>
                    </div>
                </div>
            </div>

            {{-- SECTION 2: Material Consumption (Paint & Parts) --}}
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm space-y-6">
                <div class="border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">2. Material Consumption</h3>
                    <p class="text-xs text-gray-500 font-medium">Record lot numbers, viscosity, mixing ratio, and quantities used during shift</p>
                </div>

                {{-- Paint Materials Sub-table --}}
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-[11px] font-black text-gray-600 uppercase tracking-wider">Paint Items (Viscosity & Mixing Ratio)</span>
                        <button type="button" @click="addPaintRow()" class="px-3 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs rounded-lg border border-blue-200 transition">
                            + Add Paint Item
                        </button>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 rounded-xl">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 text-[10px] font-black text-gray-400 uppercase tracking-wider border-b border-gray-200">
                                <tr>
                                    <th class="px-3 py-2">Item Name</th>
                                    <th class="px-3 py-2">Lot Number</th>
                                    <th class="px-3 py-2 w-24">Viscosity</th>
                                    <th class="px-3 py-2 w-28">Mixing Ratio</th>
                                    <th class="px-3 py-2 w-24">Qty</th>
                                    <th class="px-3 py-2 w-20">UOM</th>
                                    <th class="px-2 py-2 text-center w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <template x-for="(mat, idx) in paintMaterials" :key="'paint-' + idx">
                                    <tr>
                                        <td class="px-3 py-1.5">
                                            <input type="hidden" :name="'materials[' + mat.globalIndex + '][type]'" value="paint">
                                            <input type="text" :name="'materials[' + mat.globalIndex + '][item_name]'" x-model="mat.item_name" placeholder="Item Name" class="w-full text-xs font-bold rounded-lg border-gray-300 py-1 focus:ring-blue-500">
                                        </td>
                                        <td class="px-3 py-1.5">
                                            <input type="text" :name="'materials[' + mat.globalIndex + '][lot_number]'" x-model="mat.lot_number" placeholder="Lot #" class="w-full text-xs font-medium rounded-lg border-gray-300 py-1 focus:ring-blue-500">
                                        </td>
                                        <td class="px-3 py-1.5">
                                            <input type="text" :name="'materials[' + mat.globalIndex + '][visco]'" x-model="mat.visco" placeholder="Visco" class="w-full text-xs font-medium rounded-lg border-gray-300 py-1 focus:ring-blue-500">
                                        </td>
                                        <td class="px-3 py-1.5">
                                            <input type="text" :name="'materials[' + mat.globalIndex + '][mixing_ratio]'" x-model="mat.mixing_ratio" placeholder="1:1.5" class="w-full text-xs font-medium rounded-lg border-gray-300 py-1 focus:ring-blue-500">
                                        </td>
                                        <td class="px-3 py-1.5">
                                            <input type="number" step="any" :name="'materials[' + mat.globalIndex + '][qty]'" x-model="mat.qty" placeholder="0" class="w-full text-xs font-bold rounded-lg border-gray-300 py-1 focus:ring-blue-500">
                                        </td>
                                        <td class="px-3 py-1.5">
                                            <input type="text" :name="'materials[' + mat.globalIndex + '][uom]'" x-model="mat.uom" placeholder="Kg/L" class="w-full text-xs font-medium rounded-lg border-gray-300 py-1 focus:ring-blue-500">
                                        </td>
                                        <td class="px-2 py-1.5 text-center">
                                            <button type="button" @click="removePaintRow(idx)" class="text-red-500 hover:text-red-700 font-black text-base px-1">&times;</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Part / WIP Materials Sub-table --}}
                <div class="space-y-3 pt-2">
                    <div class="flex justify-between items-center">
                        <span class="text-[11px] font-black text-gray-600 uppercase tracking-wider">Item Parts / WIP Lots</span>
                        <button type="button" @click="addPartRow()" class="px-3 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs rounded-lg border border-blue-200 transition">
                            + Add Part / WIP Item
                        </button>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 rounded-xl">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 text-[10px] font-black text-gray-400 uppercase tracking-wider border-b border-gray-200">
                                <tr>
                                    <th class="px-3 py-2">Item Name</th>
                                    <th class="px-3 py-2">Lot Number</th>
                                    <th class="px-3 py-2 w-28">Qty</th>
                                    <th class="px-3 py-2 w-24">UOM</th>
                                    <th class="px-2 py-2 text-center w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <template x-for="(mat, idx) in partMaterials" :key="'part-' + idx">
                                    <tr>
                                        <td class="px-3 py-1.5">
                                            <input type="hidden" :name="'materials[' + mat.globalIndex + '][type]'" value="part">
                                            <input type="text" :name="'materials[' + mat.globalIndex + '][item_name]'" x-model="mat.item_name" placeholder="Item Name" class="w-full text-xs font-bold rounded-lg border-gray-300 py-1 focus:ring-blue-500">
                                        </td>
                                        <td class="px-3 py-1.5">
                                            <input type="text" :name="'materials[' + mat.globalIndex + '][lot_number]'" x-model="mat.lot_number" placeholder="Lot #" class="w-full text-xs font-medium rounded-lg border-gray-300 py-1 focus:ring-blue-500">
                                        </td>
                                        <td class="px-3 py-1.5">
                                            <input type="number" step="any" :name="'materials[' + mat.globalIndex + '][qty]'" x-model="mat.qty" placeholder="0" class="w-full text-xs font-bold rounded-lg border-gray-300 py-1 focus:ring-blue-500">
                                        </td>
                                        <td class="px-3 py-1.5">
                                            <input type="text" :name="'materials[' + mat.globalIndex + '][uom]'" x-model="mat.uom" placeholder="Pcs/Box" class="w-full text-xs font-medium rounded-lg border-gray-300 py-1 focus:ring-blue-500">
                                        </td>
                                        <td class="px-2 py-1.5 text-center">
                                            <button type="button" @click="removePartRow(idx)" class="text-red-500 hover:text-red-700 font-black text-base px-1">&times;</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- SECTION 3: Downtime Enrichment & Shift Handover --}}
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm space-y-6">
                <div class="border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">3. Downtime & Shift Handover</h3>
                    <p class="text-xs text-gray-500 font-medium">Categorize downtime causes, countermeasures, and shift notes</p>
                </div>

                {{-- Trouble / Downtime Enrichment List --}}
                @if($session->downtimeEntries->isNotEmpty())
                    <div class="space-y-3">
                        <span class="text-[11px] font-black text-gray-600 uppercase tracking-wider block">Logged Downtime Events ({{ $session->downtimeEntries->count() }})</span>

                        <div class="border border-gray-200 rounded-xl overflow-hidden divide-y divide-gray-100 bg-white">
                            @foreach($session->downtimeEntries as $idx => $dt)
                                <div class="p-4 space-y-3 bg-gray-50/50">
                                    <input type="hidden" name="troubles[{{ $idx }}][downtime_id]" value="{{ $dt->id }}">
                                    
                                    <div class="flex flex-wrap justify-between items-center text-xs gap-2">
                                        <div class="flex items-center gap-2">
                                            <span class="font-black text-gray-800">{{ $dt->reason }}</span>
                                            <span class="text-[10px] font-bold bg-amber-100 text-amber-800 px-2 py-0.5 rounded border border-amber-200">
                                                {{ $dt->duration_minutes }} mins
                                            </span>
                                        </div>
                                        <div class="text-[10px] font-mono text-gray-400">
                                            {{ $dt->start_time ? $dt->start_time->format('H:i') : '-' }} - {{ $dt->resume_time ? $dt->resume_time->format('H:i') : '-' }}
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                                        <div>
                                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-wider mb-1">Category</label>
                                            <select name="troubles[{{ $idx }}][category]" class="w-full rounded-lg border-gray-300 text-xs font-bold text-gray-800 py-1 focus:ring-blue-500 bg-white">
                                                <option value="">-- Select Category --</option>
                                                @foreach($troubleCategories as $cat)
                                                    <option value="{{ $cat }}" {{ (old("troubles.{$idx}.category", $dt->category) == $cat) ? 'selected' : '' }}>{{ $cat }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="md:col-span-2">
                                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-wider mb-1">Countermeasure (Penanganan)</label>
                                            <input type="text" name="troubles[{{ $idx }}][countermeasure]" value="{{ old("troubles.{$idx}.countermeasure", $dt->countermeasure) }}" placeholder="Describe action taken to resolve..." class="w-full rounded-lg border-gray-300 text-xs font-medium text-gray-800 py-1 focus:ring-blue-500 bg-white">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-xs text-gray-400 font-medium bg-gray-50 p-3 rounded-xl border border-gray-200">
                        No downtime events were logged during this session.
                    </div>
                @endif

                {{-- Notes & Schedule Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-xs">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[11px] font-black text-gray-600 uppercase tracking-wider mb-1">Production Notes</label>
                            <textarea name="production_notes" rows="3" placeholder="General production notes or remarks..." class="w-full rounded-xl border-gray-300 text-xs font-medium focus:ring-blue-500">{{ old('production_notes', $session->production_notes ?? $session->remarks) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-[11px] font-black text-gray-600 uppercase tracking-wider mb-1">NG Remarks</label>
                            <textarea name="ng_remarks" rows="2" placeholder="Remarks regarding defect causes..." class="w-full rounded-xl border-gray-300 text-xs font-medium focus:ring-blue-500">{{ old('ng_remarks', $session->ng_remarks) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-[11px] font-black text-gray-600 uppercase tracking-wider mb-1">Absent Employees</label>
                            <input type="text" name="absent_employees" value="{{ old('absent_employees', $session->absent_employees) }}" placeholder="e.g. John (Sakit), Jane (Izin)..." class="w-full rounded-xl border-gray-300 text-xs font-medium focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-[11px] font-black text-gray-600 uppercase tracking-wider mb-1">Output Destination</label>
                            <select name="output_destination" class="w-full rounded-xl border-gray-300 text-xs font-bold text-gray-800 focus:ring-blue-500">
                                <option value="">-- Select Destination --</option>
                                <option value="fg" {{ old('output_destination', $session->output_destination) == 'fg' ? 'selected' : '' }}>Finished Goods (FG)</option>
                                <option value="buffing" {{ old('output_destination', $session->output_destination) == 'buffing' ? 'selected' : '' }}>Buffing</option>
                                <option value="next_process" {{ old('output_destination', $session->output_destination) == 'next_process' ? 'selected' : '' }}>Next Process Area</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-black text-gray-600 uppercase tracking-wider mb-1">Next Production Schedule</label>
                            <div class="space-y-2">
                                @for($i = 0; $i < 4; $i++)
                                    @php
                                        $schVal = is_array($session->next_production_schedule) ? ($session->next_production_schedule[$i] ?? '') : '';
                                    @endphp
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-400 font-bold w-4 text-right">{{ $i + 1 }}.</span>
                                        <input type="text" name="next_production_schedule[]" value="{{ old("next_production_schedule.{$i}", $schVal) }}" placeholder="Next schedule item {{ $i + 1 }}" class="w-full rounded-xl border-gray-300 text-xs font-medium focus:ring-blue-500 py-1.5">
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                <a href="{{ $gatewayUrl }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl border border-gray-300 transition uppercase tracking-wider">
                    Skip Close-Out
                </a>

                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-black text-xs rounded-xl shadow-md transition uppercase tracking-wider">
                    Submit Close-Out Report
                </button>
            </div>
        </form>
    </div>

    <script>
        function closeoutForm() {
            return {
                nextGlobalIndex: 0,
                paintMaterials: [],
                partMaterials: [],

                init() {
                    const existingMaterials = @json($session->materials);

                    if (existingMaterials && existingMaterials.length > 0) {
                        existingMaterials.forEach(m => {
                            const row = {
                                globalIndex: this.nextGlobalIndex++,
                                item_name: m.item_name || '',
                                lot_number: m.lot_number || '',
                                visco: m.visco || '',
                                mixing_ratio: m.mixing_ratio || '',
                                qty: m.qty || '',
                                uom: m.uom || ''
                            };
                            if (m.type === 'paint') {
                                this.paintMaterials.push(row);
                            } else {
                                this.partMaterials.push(row);
                            }
                        });
                    } else {
                        // Default Paint presets
                        const defaultPaints = [
                            'Paint Primer', 'Hardener',
                            'Paint Basecoat', 'Hardener',
                            'Paint Topcoat', 'Hardener'
                        ];
                        defaultPaints.forEach(name => {
                            this.paintMaterials.push({
                                globalIndex: this.nextGlobalIndex++,
                                item_name: name,
                                lot_number: '',
                                visco: '',
                                mixing_ratio: '',
                                qty: '',
                                uom: ''
                            });
                        });

                        // Default Part presets
                        const defaultParts = ['WIP 1', 'WIP 2', 'WIP 3', 'Repairan 1', 'Repairan 2', 'Repairan 3'];
                        defaultParts.forEach(name => {
                            this.partMaterials.push({
                                globalIndex: this.nextGlobalIndex++,
                                item_name: name,
                                lot_number: '',
                                qty: '',
                                uom: ''
                            });
                        });
                    }
                },

                addPaintRow() {
                    this.paintMaterials.push({
                        globalIndex: this.nextGlobalIndex++,
                        item_name: '',
                        lot_number: '',
                        visco: '',
                        mixing_ratio: '',
                        qty: '',
                        uom: ''
                    });
                },

                removePaintRow(idx) {
                    this.paintMaterials.splice(idx, 1);
                },

                addPartRow() {
                    this.partMaterials.push({
                        globalIndex: this.nextGlobalIndex++,
                        item_name: '',
                        lot_number: '',
                        qty: '',
                        uom: ''
                    });
                },

                removePartRow(idx) {
                    this.partMaterials.splice(idx, 1);
                }
            };
        }
    </script>
</x-app-layout>
