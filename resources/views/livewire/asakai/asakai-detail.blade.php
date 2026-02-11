
    <div class="max-w-5xl mx-auto p-6">
        @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
        @endif

        <div class="bg-white rounded-lg shadow-md p-6">
            {{-- Header --}}
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-2xl font-bold">Detail Asakai #{{ $asakai->id }}</h2>
                    <p class="text-gray-500 text-sm">
                        Dibuat oleh {{ $asakai->creator->name }} pada {{ $asakai->created_at->format('d M Y H:i') }}
                    </p>
                </div>
                <div class="flex gap-2">
                    @if($asakai->status !== 'closed')
                        <a href="{{ route('asakai.edit', $asakai->id) }}" 
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Edit
                        </a>
                        <button wire:click="delete" wire:confirm="Yakin ingin menghapus?"
                                class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                            Hapus
                        </button>
                    @else
                        <div class="bg-gray-300 text-gray-600 px-4 py-2 rounded cursor-not-allowed" title="Status sudah CLOSED">
                            🔒 Data Terkunci
                        </div>
                    @endif
                    
                    <a href="{{ route('asakai.index') }}" 
                    class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                        Kembali
                    </a>
                </div>
            </div>

            {{-- Status Badge --}}
            <div class="mb-6">
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 rounded text-sm font-semibold
                        @if($asakai->status === 'draft') bg-gray-200 text-gray-700
                        @elseif($asakai->status === 'submitted') bg-yellow-200 text-yellow-800
                        @else bg-green-200 text-green-800
                        @endif">
                        Status: {{ ucfirst($asakai->status) }}
                    </span>

                    @if($asakai->status !== 'closed')
                    <select wire:change="changeStatus($event.target.value)" 
                            class="border rounded px-3 py-1 text-sm">
                        <option value="">- Ubah Status -</option>
                        @if($asakai->status === 'draft')
                        <option value="submitted">Submit</option>
                        @endif
                        <option value="closed">Close</option>
                    </select>
                    @endif

                    @if($asakai->is_overdue)
                    <span class="text-red-500 font-semibold">⚠️ OVERDUE!</span>
                    @endif
                </div>

                <div class="mt-2">
                    <div class="bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" 
                            style="width: {{ $asakai->completion_percentage }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Completion: {{ $asakai->completion_percentage }}%
                    </p>
                </div>
            </div>

            {{-- Main Info --}}
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="text-sm font-semibold text-gray-600">Customer</label>
                    <p class="text-gray-800">{{ $asakai->customer }}</p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-600">Part No</label>
                    <p class="text-gray-800">{{ $asakai->part_no }}</p>
                </div>
                <div class="col-span-2">
                    <label class="text-sm font-semibold text-gray-600">Issue</label>
                    <p class="text-gray-800">{{ $asakai->issue }}</p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-600">Quantity</label>
                    <p class="text-gray-800">{{ $asakai->quantity }}</p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-600">Lot Date & Shift</label>
                    <p class="text-gray-800">{{ $asakai->lot_date_formatted }}</p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-600">Date Issue</label>
                    <p class="text-gray-800">{{ $asakai->date_issue->format('d M Y') }}</p>
                </div>
            </div>

            {{-- PICs --}}
            <div class="mb-6 p-4 bg-gray-50 rounded">
                <h3 class="font-bold mb-2">Person In Charge (PIC)</h3>
                <ul class="list-disc list-inside">
                    @foreach($asakai->pics as $pic)
                    <li>{{ $pic->pic_name }}</li>
                    @endforeach
                </ul>
            </div>

            {{-- RCA --}}
            <div class="mb-6 p-4 bg-gray-50 rounded">
                <h3 class="font-bold mb-3">Root Cause Analysis (RCA)</h3>
                @foreach($asakai->rcas as $rca)
                <div class="mb-2">
                    <label class="text-sm font-semibold text-gray-600">{{ $rca->why_label }}</label>
                    <p class="text-gray-800">{{ $rca->description }}</p>
                </div>
                @endforeach
            </div>

            {{-- Corrective Actions --}}
            <div class="mb-6 p-4 bg-gray-50 rounded">
                <h3 class="font-bold mb-3">Corrective Actions</h3>
                <ol class="list-decimal list-inside">
                    @foreach($asakai->correctiveActions as $action)
                    <li class="mb-2">{{ $action->action }}</li>
                    @endforeach
                </ol>
            </div>

            {{-- Additional Info --}}
            <div class="grid grid-cols-2 gap-4">
                @if($asakai->pokayoke)
                <div class="col-span-2">
                    <label class="text-sm font-semibold text-gray-600">Pokayoke</label>
                    <p class="text-gray-800">{{ $asakai->pokayoke }}</p>
                </div>
                @endif

                @if($asakai->audit_date)
                <div>
                    <label class="text-sm font-semibold text-gray-600">Audit Date</label>
                    <p class="text-gray-800">{{ $asakai->audit_date->format('d M Y') }}</p>
                </div>
                @endif

                @if($asakai->reply_date)
                <div>
                    <label class="text-sm font-semibold text-gray-600">Reply Date</label>
                    <p class="text-gray-800">{{ $asakai->reply_date->format('d M Y') }}</p>
                </div>
                @endif

                @if($asakai->verify)
                <div class="col-span-2">
                    <label class="text-sm font-semibold text-gray-600">Verify</label>
                    <p class="text-gray-800">{{ $asakai->verify }}</p>
                </div>
                @endif

                @if($asakai->fmea_cp)
                <div>
                    <label class="text-sm font-semibold text-gray-600">FMEA/CP</label>
                    <p class="text-gray-800">{{ $asakai->fmea_cp }}</p>
                </div>
                @endif

                @if($asakai->std_work)
                <div>
                    <label class="text-sm font-semibold text-gray-600">Std Work</label>
                    <p class="text-gray-800">{{ $asakai->std_work }}</p>
                </div>
                @endif

                @if($asakai->remark)
                <div class="col-span-2">
                    <label class="text-sm font-semibold text-gray-600">Remark</label>
                    <p class="text-gray-800">{{ $asakai->remark }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
