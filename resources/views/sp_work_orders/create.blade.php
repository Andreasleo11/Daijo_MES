<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                {{ __('Create Second Process Work Order') }}
            </h2>
            <a href="{{ route('sp-work-orders.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg shadow-sm text-xs transition">
                ← Back to List
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                
                <form action="{{ route('sp-work-orders.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- WO Number --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Work Order No. (Auto)</label>
                            <input type="text" name="wo_number" value="{{ old('wo_number', $woNumber) }}" readonly
                                class="w-full bg-gray-100 border-gray-300 rounded-lg text-sm font-bold text-blue-600">
                        </div>

                        {{-- Planned Date --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Planned Date *</label>
                            <input type="date" name="planned_date" value="{{ old('planned_date', date('Y-m-d')) }}" required
                                class="w-full border-gray-300 rounded-lg text-sm">
                        </div>

                        {{-- Unit / Line --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Production Line *</label>
                            <select name="unit_line" required class="w-full border-gray-300 rounded-lg text-sm">
                                <option value="">-- Select Line --</option>
                                @foreach($lines as $l)
                                    <option value="{{ $l }}" {{ old('unit_line') == $l ? 'selected' : '' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Shift --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Shift *</label>
                            <select name="shift" required class="w-full border-gray-300 rounded-lg text-sm">
                                <option value="1" {{ old('shift') == '1' ? 'selected' : '' }}>Shift 1 (07:30 - 15:30)</option>
                                <option value="2" {{ old('shift') == '2' ? 'selected' : '' }}>Shift 2 (15:30 - 23:30)</option>
                                <option value="3" {{ old('shift') == '3' ? 'selected' : '' }}>Shift 3 (23:30 - 07:30)</option>
                            </select>
                        </div>

                        {{-- Process --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Second Process Type *</label>
                            <select name="process_prod" required class="w-full border-gray-300 rounded-lg text-sm">
                                <option value="">-- Select Process --</option>
                                @foreach($processes as $p)
                                    <option value="{{ $p }}" {{ old('process_prod') == $p ? 'selected' : '' }}>{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Customer --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Customer Name *</label>
                            <input type="text" name="customer" value="{{ old('customer') }}" placeholder="e.g. Toyota, Daihatsu" required
                                class="w-full border-gray-300 rounded-lg text-sm">
                        </div>

                        {{-- Part Number --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Part Number *</label>
                            <input type="text" name="part_number" id="part_number" value="{{ old('part_number') }}" placeholder="e.g. ABC-123" required
                                class="w-full border-gray-300 rounded-lg text-sm">
                        </div>

                        {{-- Part Name --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Part Name *</label>
                            <input type="text" name="part_name" id="part_name" value="{{ old('part_name') }}" placeholder="e.g. Panel Assembly Right" required
                                class="w-full border-gray-300 rounded-lg text-sm">
                        </div>

                        {{-- Model --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Model Code (Optional)</label>
                            <input type="text" name="model" value="{{ old('model') }}" placeholder="e.g. D01D"
                                class="w-full border-gray-300 rounded-lg text-sm">
                        </div>

                        {{-- Target Qty --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Target Production Qty (Pcs) *</label>
                            <input type="number" name="target_qty" value="{{ old('target_qty', 1000) }}" min="1" required
                                class="w-full border-gray-300 rounded-lg text-sm font-bold text-green-600">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-gray-200 pt-4">
                        <a href="{{ route('sp-work-orders.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg text-sm transition">
                            Cancel
                        </a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg text-sm shadow transition">
                            Save Work Order
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>
