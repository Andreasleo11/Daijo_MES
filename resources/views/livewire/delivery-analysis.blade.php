<div x-data="{ viewMode: 'horizontal' }" class="min-h-screen" style="background:#F0EEE9; font-family:'IBM Plex Mono',monospace;">

    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@300;400;500;600&family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg:        #F0EEE9;
            --surface:   #FFFFFF;
            --border:    #D8D4CC;
            --border2:   #E8E4DE;
            --text:      #1A1816;
            --muted:     #7A756E;
            --accent:    #E85D04;
            --accent2:   #F48C06;
            --danger:    #D62828;
            --success:   #2D6A4F;
            --info:      #1D3557;
        }

        .ds-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 2px;
        }

        .ds-input {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 2px;
            padding: 6px 10px;
            font-size: 11px;
            font-family: 'IBM Plex Mono', monospace;
            color: var(--text);
            width: 100%;
            outline: none;
            transition: border-color .15s;
        }
        .ds-input:focus { border-color: var(--accent); }

        .ds-label {
            font-size: 9px;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--muted);
            font-family: 'IBM Plex Sans', sans-serif;
            font-weight: 600;
            display: block;
            margin-bottom: 4px;
        }

        /* Table */
        .ds-table { border-collapse: collapse; width: 100%; }
        .ds-table th {
            background: #F7F5F2;
            border-bottom: 2px solid var(--border);
            border-right: 1px solid var(--border2);
            padding: 6px 10px;
            font-size: 9px;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--muted);
            font-family: 'IBM Plex Sans', sans-serif;
            font-weight: 600;
            white-space: nowrap;
            text-align: right;
        }
        .ds-table th:first-child { text-align: left; }

        .ds-table td {
            border-bottom: 1px solid var(--border2);
            border-right: 1px solid var(--border2);
            padding: 5px 10px;
            font-size: 11px;
            white-space: nowrap;
            text-align: right;
        }
        .ds-table td:first-child { text-align: left; }

        .row-seid   td { background: #FAFAF8; }
        .row-actual td { background: #F6FBF8; }
        .row-bdel   td { background: #FFFBF5; }
        .row-bstock td { background: #F5F8FF; }

        .row-seid:hover   td,
        .row-actual:hover td,
        .row-bdel:hover   td,
        .row-bstock:hover td { filter: brightness(.97); }

        .col-sticky-1 {
            position: sticky;
            left: 0;
            z-index: 10;
        }
        .col-sticky-2 {
            position: sticky;
            left: 115px;
            z-index: 10;
        }

        .row-seid   .col-sticky-1,
        .row-seid   .col-sticky-2 { background: #FAFAF8; }
        .row-actual .col-sticky-1,
        .row-actual .col-sticky-2 { background: #F6FBF8; }
        .row-bdel   .col-sticky-1,
        .row-bdel   .col-sticky-2 { background: #FFFBF5; }
        .row-bstock .col-sticky-1,
        .row-bstock .col-sticky-2 { background: #F5F8FF; }

        .head-sticky-1 { position: sticky; left: 0;     z-index: 20; background: #F7F5F2; }
        .head-sticky-2 { position: sticky; left: 115px; z-index: 20; background: #F7F5F2; }

        /* Vertical mode sticky */
        .vertical-sticky-date {
            position: sticky;
            left: 0;
            z-index: 10;
            background: #F7F5F2;
        }

        /* Pill status */
        .pill {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 2px 8px; border-radius: 2px;
            font-size: 9px; font-weight: 700;
            letter-spacing: .08em; text-transform: uppercase;
            font-family: 'IBM Plex Sans', sans-serif;
        }
        .pill-shortfall    { background: #FEE2E2; color: var(--danger);  border: 1px solid #FECACA; }
        .pill-overdelivery { background: #DBEAFE; color: var(--info);    border: 1px solid #BFDBFE; }
        .pill-ontime       { background: #D1FAE5; color: var(--success); border: 1px solid #A7F3D0; }

        /* Item header */
        .item-header {
            background: var(--text);
            padding: 8px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }

        /* Pagination btn */
        .pg-btn {
            padding: 4px 10px;
            font-size: 10px;
            font-family: 'IBM Plex Mono', monospace;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--muted);
            cursor: pointer;
            border-radius: 2px;
            transition: all .15s;
        }
        .pg-btn:hover:not(:disabled) { border-color: var(--accent); color: var(--accent); }
        .pg-btn:disabled { opacity: .3; cursor: not-allowed; }
        .pg-btn.active   { background: var(--accent); border-color: var(--accent); color: #fff; font-weight: 600; }

        /* Summary cards */
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 2px;
            padding: 10px 16px;
            min-width: 120px;
        }

        /* Ticker line */
        .ticker {
            background: var(--text);
            height: 3px;
            width: 40px;
            display: inline-block;
            border-radius: 1px;
        }

        /* value coloring */
        .val-zero     { color: #C5C0B8; }
        .val-pos      { color: #1B4332; font-weight: 600; }
        .val-neg      { color: var(--danger); font-weight: 700; }
        .val-neutral  { color: var(--text); }
        .val-actual   { color: #166534; font-weight: 600; }
        .val-seid     { color: var(--text); font-weight: 500; }

        /* Toggle button */
        .toggle-btn {
            padding: 6px 12px;
            font-size: 10px;
            font-family: 'IBM Plex Sans', sans-serif;
            font-weight: 600;
            letter-spacing: .05em;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--muted);
            cursor: pointer;
            transition: all .15s;
            text-transform: uppercase;
        }
        .toggle-btn:hover { border-color: var(--accent); color: var(--accent); }
        .toggle-btn.active { 
            background: var(--accent); 
            border-color: var(--accent); 
            color: #fff; 
        }
        .toggle-btn:first-child { border-radius: 2px 0 0 2px; }
        .toggle-btn:last-child { border-radius: 0 2px 2px 0; border-left: none; }
    </style>

    {{-- ═══ TOPBAR ═══ --}}
    <div style="background:var(--text); padding:12px 24px; display:flex; align-items:center; justify-content:space-between;">
        <div style="display:flex; align-items:center; gap:16px;">
            <div style="width:3px; height:28px; background:var(--accent); border-radius:1px;"></div>
            <div>
                <div style="font-family:'IBM Plex Sans',sans-serif; font-size:13px; font-weight:700; color:#fff; letter-spacing:.04em;">
                    DELIVERY SCHEDULE ANALYSIS
                </div>
                <div style="font-size:9px; color:#7A756E; letter-spacing:.1em; text-transform:uppercase; margin-top:1px;">
                    Delivery Request · Actual · Balance Del · Balance Stock
                </div>
            </div>
        </div>
        <div style="font-size:10px; color:#5A554E; font-family:'IBM Plex Mono',monospace;">
            {{ now()->format('d M Y · H:i') }}
        </div>
    </div>

    <div class="p-6">

        {{-- ═══ FILTER ROW ═══ --}}
        <div class="ds-card p-4 mb-5">
            <div style="display:grid; grid-template-columns:1.5fr 1.5fr 1fr 1fr 1fr; gap:12px; align-items:end;">

                {{-- Customer filter --}}
                <div>
                    <label class="ds-label">Customer</label>
                    <select wire:model.live="filterCustomer" class="ds-input" style="cursor:pointer;">
                        <option value="">— Semua Customer</option>
                        @foreach($customerList as $cust)
                        <option value="{{ $cust['code'] }}">{{ $cust['name'] . ' - ' . $cust['code'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Item Code --}}
                <div>
                    <label class="ds-label">Item Code / Name</label>
                    <input wire:model.live.debounce.500ms="filterItemCode" type="text"
                        placeholder="Ketik item code..."
                        class="ds-input"/>
                </div>

                {{-- Dari --}}
                <div>
                    <label class="ds-label">Dari Tanggal</label>
                    <input wire:model.live="filterDateFrom" type="date" class="ds-input"/>
                </div>

                {{-- Sampai --}}
                <div>
                    <label class="ds-label">Sampai Tanggal</label>
                    <input wire:model.live="filterDateTo" type="date" class="ds-input"/>
                </div>

                {{-- Status --}}
                <div>
                    <label class="ds-label">Status</label>
                    <select wire:model.live="filterStatus" class="ds-input" style="cursor:pointer;">
                        <option value="">— Semua Status</option>
                        <option value="shortfall">Shortfall</option>
                        <option value="overdelivery">Overdelivery</option>
                        <option value="on-time">On Time</option>
                    </select>
                </div>

                {{-- Cut Off Date --}}
                <div>
                    <label class="ds-label">CutOffDate</label>
                    <input type="date" wire:model.live="filterCutOffDate" class="form-control">
                </div>

            </div>

            <div style="grid-column: span 4; display:flex; justify-content:space-between; align-items:center; margin-top:12px;">
                {{-- Toggle View Mode --}}
                <div style="display:flex; align-items:center; gap:8px;">
                    <span class="ds-label" style="margin-bottom:0;">VIEW:</span>
                    <div style="display:flex;">
                        <button @click="viewMode = 'horizontal'; $wire.call('setExportMode', 'horizontal')" 
                                :class="viewMode === 'horizontal' ? 'active' : ''"
                                class="toggle-btn">
                            ⬌ Horizontal
                        </button>
                        <button @click="viewMode = 'vertical'; $wire.call('setExportMode', 'vertical')" 
                                :class="viewMode === 'vertical' ? 'active' : ''"
                                class="toggle-btn">
                            ⬍ Vertical
                        </button>
                    </div>
                </div>

                {{-- Export Button --}}
                <button wire:click="exportExcel"
                    wire:loading.attr="disabled"
                    wire:target="exportExcel"
                    style="display:inline-flex; align-items:center; gap:8px; background:var(--accent); color:#fff;
                        border:none; padding:7px 18px; font-size:11px; font-family:'IBM Plex Mono',monospace;
                        font-weight:600; letter-spacing:.05em; cursor:pointer; border-radius:2px; transition:opacity .15s;">
                    <svg wire:loading.remove wire:target="exportExcel" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <svg wire:loading wire:target="exportExcel" class="animate-spin" width="14" height="14" fill="none" viewBox="0 0 24 24">
                        <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                        <path style="opacity:.8" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    EXPORT EXCEL
                </button>
            </div>
        </div>

        {{-- ═══ SUMMARY STATS ═══ --}}
        @php
            $shortfallCount = collect($analysisData)->where('status', 'shortfall')->count();
            $overCount      = collect($analysisData)->where('status', 'overdelivery')->count();
            $ontimeCount    = collect($analysisData)->where('status', 'on-time')->count();
        @endphp
        <div style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap; align-items:center;">
            <div class="stat-card">
                <div class="ds-label" style="margin-bottom:2px;">Showing</div>
                <div style="font-size:20px; font-weight:700; color:var(--text); font-family:'IBM Plex Sans',sans-serif; line-height:1;">
                    {{ count($analysisData) }}
                    <span style="font-size:11px; color:var(--muted); font-weight:400;">/ {{ $totalItems }}</span>
                </div>
            </div>
            <div class="stat-card" style="border-left:3px solid var(--danger);">
                <div class="ds-label" style="color:var(--danger); margin-bottom:2px;">Shortfall</div>
                <div style="font-size:20px; font-weight:700; color:var(--danger); font-family:'IBM Plex Sans',sans-serif; line-height:1;">
                    {{ $shortfallCount }}
                </div>
            </div>
            <div class="stat-card" style="border-left:3px solid #1D3557;">
                <div class="ds-label" style="color:#1D3557; margin-bottom:2px;">Overdelivery</div>
                <div style="font-size:20px; font-weight:700; color:#1D3557; font-family:'IBM Plex Sans',sans-serif; line-height:1;">
                    {{ $overCount }}
                </div>
            </div>
            <div class="stat-card" style="border-left:3px solid var(--success);">
                <div class="ds-label" style="color:var(--success); margin-bottom:2px;">On Time</div>
                <div style="font-size:20px; font-weight:700; color:var(--success); font-family:'IBM Plex Sans',sans-serif; line-height:1;">
                    {{ $ontimeCount }}
                </div>
            </div>

            {{-- Per page --}}
            <div style="margin-left:auto; display:flex; align-items:center; gap:8px;">
                <span class="ds-label" style="margin-bottom:0;">Rows:</span>
                <select wire:model.live="perPage" class="ds-input" style="width:70px; cursor:pointer;">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>

        {{-- ═══ ITEM TABLES ═══ --}}
        @forelse($analysisData as $idx => $item)
        @php
            $accentLeft = match($item['status']) {
                'shortfall'    => 'var(--danger)',
                'overdelivery' => '#1D3557',
                default        => 'var(--success)',
            };
        @endphp

        <div class="ds-card mb-4" style="border-left: 3px solid {{ $accentLeft }}; overflow:hidden;">

            {{-- Item Header --}}
            <div class="item-header">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="font-size:9px; color:#5A554E; font-family:'IBM Plex Sans',sans-serif; font-weight:600; background:#2A2522; padding:2px 6px; border-radius:2px;">
                        #{{ $idx + 1 + ($currentPage - 1) * $perPage }}
                    </div>
                    <div>
                        <div style="font-size:13px; font-weight:700; color:#fff; font-family:'IBM Plex Sans',sans-serif; letter-spacing:.02em;">
                            {{ $item['item_code'] }}
                        </div>
                        <div style="font-size:10px; color:#9A9590; margin-top:1px;">
                            {{ $item['item_name'] }}
                        </div>
                        @if($item['customer_name'])
                        <span style="background:#2A2522; color:#9A9590; font-size:9px; padding:2px 8px;
                                    border-radius:2px; font-family:'IBM Plex Sans',sans-serif; letter-spacing:.04em;">
                            {{ $item['customer_name'] }}
                        </span>
                        @endif
                    </div>
                </div>

                <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
                    <div style="text-align:right;">
                        <div style="font-size:8px; color:#5A554E; text-transform:uppercase; letter-spacing:.1em; font-family:'IBM Plex Sans',sans-serif;">In Stock</div>
                        <div style="font-size:14px; font-weight:700; color:#fff; font-family:'IBM Plex Mono',monospace;">{{ number_format($item['in_stock']) }}</div>
                    </div>
                    <div style="width:1px; height:30px; background:#2A2522;"></div>
                    <div style="text-align:right;">
                        <div style="font-size:8px; color:#5A554E; text-transform:uppercase; letter-spacing:.1em; font-family:'IBM Plex Sans',sans-serif;">Scheduled</div>
                        <div style="font-size:14px; font-weight:700; color:#9A9590; font-family:'IBM Plex Mono',monospace;">{{ number_format($item['total_scheduled']) }}</div>
                    </div>
                    <div style="width:1px; height:30px; background:#2A2522;"></div>
                    <div style="text-align:right;">
                        <div style="font-size:8px; color:#5A554E; text-transform:uppercase; letter-spacing:.1em; font-family:'IBM Plex Sans',sans-serif;">Actual</div>
                        <div style="font-size:14px; font-weight:700; color:#4ADE80; font-family:'IBM Plex Mono',monospace;">{{ number_format($item['total_actual']) }}</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:8px; color:#5A554E; text-transform:uppercase; letter-spacing:.1em; font-family:'IBM Plex Sans',sans-serif;">Cycle Time</div>
                        <div style="font-size:14px; font-weight:700; color:#9A9590; font-family:'IBM Plex Mono',monospace;">
                            {{ $item['cycle_time'] ? number_format($item['cycle_time']) . 's' : '—' }}
                        </div>
                    </div>
                    <div style="width:1px; height:30px; background:#2A2522;"></div>

                    @php
                        $pillClass = match($item['status']) {
                            'shortfall'    => 'pill-shortfall',
                            'overdelivery' => 'pill-overdelivery',
                            default        => 'pill-ontime',
                        };
                        $pillIcon = match($item['status']) {
                            'shortfall'    => '↓',
                            'overdelivery' => '↑',
                            default        => '✓',
                        };
                        $pillVal = match($item['status']) {
                            'shortfall'    => number_format($item['total_shortfall']),
                            'overdelivery' => number_format($item['total_overdelivery']),
                            default        => 'On Time',
                        };
                    @endphp
                    <span class="pill {{ $pillClass }}" style="font-size:11px; padding:4px 10px;">
                        {{ $pillIcon }} {{ $pillVal }}
                    </span>
                </div>
            </div>

            {{-- HORIZONTAL VIEW --}}
            <div x-show="viewMode === 'horizontal'" style="overflow-x:auto;">
                <table class="ds-table" style="min-width:max-content;">
                    <thead>
                        <tr>
                            <th class="head-sticky-1" style="min-width:115px; text-align:left; border-right:2px solid var(--border);">
                                Remarks
                            </th>
                            <th class="head-sticky-2" style="min-width:80px; border-right:2px solid var(--border);">
                                Stk Awal
                            </th>
                            @foreach($dateRange as $date)
                            @php
                                $isWeekend = in_array(\Carbon\Carbon::parse($date)->dayOfWeek, [0, 6]);
                                $isToday   = $date === now()->format('Y-m-d');
                            @endphp
                            <th style="min-width:62px; {{ $isToday ? 'background:#FFF3E0; color:var(--accent);' : ($isWeekend ? 'background:#F7F5F0; color:#A09890;' : '') }} position:relative;">
                                {{ \Carbon\Carbon::parse($date)->format('d') }}
                                <br>
                                <span style="font-size:8px; font-weight:400;">{{ \Carbon\Carbon::parse($date)->format('M') }}</span>
                                @if($isToday)
                                <div style="position:absolute; bottom:0; left:0; right:0; height:2px; background:var(--accent);"></div>
                                @endif
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>

                        {{-- Delivery Request --}}
                        <tr class="row-seid">
                            <td class="col-sticky-1" style="min-width:115px; border-right:2px solid var(--border); font-family:'IBM Plex Sans',sans-serif; font-size:10px; font-weight:600; color:var(--text);">
                                Delivery Request
                            </td>
                            <td class="col-sticky-2" style="border-right:2px solid var(--border); color:var(--muted); font-size:10px;">—</td>
                            @foreach($dateRange as $date)
                            @php $val = $item['daily'][$date]['seid_request'] ?? 0; @endphp
                            <td class="{{ $val > 0 ? 'val-seid' : 'val-zero' }}">
                                {{ $val > 0 ? number_format($val) : '' }}
                            </td>
                            @endforeach
                        </tr>

                        {{-- Actual Del --}}
                        <tr class="row-actual">
                            <td class="col-sticky-1" style="min-width:115px; border-right:2px solid var(--border); font-family:'IBM Plex Sans',sans-serif; font-size:10px; font-weight:600; color:#166534;">
                                Actual Del
                            </td>
                            <td class="col-sticky-2" style="border-right:2px solid var(--border); color:var(--muted); font-size:10px;">—</td>
                            @foreach($dateRange as $date)
                            @php $val = $item['daily'][$date]['actual'] ?? 0; @endphp
                            <td class="{{ $val > 0 ? 'val-actual' : 'val-zero' }}">
                                {{ $val > 0 ? number_format($val) : '' }}
                            </td>
                            @endforeach
                        </tr>

                        {{-- Balance Del --}}
                        <tr class="row-bdel">
                            <td class="col-sticky-1" style="min-width:115px; border-right:2px solid var(--border); font-family:'IBM Plex Sans',sans-serif; font-size:10px; font-weight:600; color:#92400E;">
                                Balance Del
                            </td>
                            <td class="col-sticky-2" style="border-right:2px solid var(--border); color:var(--muted); font-size:10px;">—</td>
                            @foreach($dateRange as $date)
                            @php
                                $val = $item['daily'][$date]['balance_del'] ?? 0;
                                $cls = $val < 0 ? 'val-neg' : ($val > 0 ? 'val-pos' : 'val-zero');
                            @endphp
                            <td class="{{ $cls }}">{{ $val != 0 ? number_format($val) : '0' }}</td>
                            @endforeach
                        </tr>

                        {{-- Balance Stock --}}
                        <tr class="row-bstock">
                            <td class="col-sticky-1" style="min-width:115px; border-right:2px solid var(--border); font-family:'IBM Plex Sans',sans-serif; font-size:10px; font-weight:600; color:#1E3A5F;">
                                Balance Stock
                            </td>
                            <td class="col-sticky-2" style="border-right:2px solid var(--border); font-size:11px; font-weight:700; color:var(--info);">
                                {{ number_format($item['in_stock']) }}
                            </td>
                            @foreach($dateRange as $date)
                            @php
                                $val = $item['daily'][$date]['balance_stock'] ?? $item['in_stock'];
                                $cls = $val < 0 ? 'val-neg' : ($val > 0 ? 'val-pos' : 'val-zero');
                            @endphp
                            <td class="{{ $cls }}" style="font-weight:{{ $val < 0 ? '700' : '500' }};">
                                {{ number_format($val) }}
                            </td>
                            @endforeach
                        </tr>

                        {{-- Children (horizontal) --}}
                        @if(!empty($item['children']))
                            @foreach($item['children'] as $child)
                            <tr>
                                <td class="col-sticky-1" colspan="2"
                                    style="background:#F0EEE9; padding:3px 10px 3px {{ 8 + ($child['level'] * 8) }}px;
                                        font-size:8px; font-weight:700; color:#1A1816;
                                        border-right:2px solid var(--border); border-bottom:1px solid var(--border2);">
                                    <span style="color:#7A756E; margin-right:4px;">
                                        @for($l = 1; $l < $child['level']; $l++) └ @endfor
                                    </span>
                                    {{ $child['semi_code'] }}
                                    <span style="color:#7A756E; font-weight:400; margin-left:6px;">{{ $child['semi_name'] }}</span>
                                    <span style="background:#1A1816; color:#fff; font-size:7px; padding:1px 5px;
                                                border-radius:2px; margin-left:6px;">×{{ $child['multiplier'] }}</span>
                                    <span style="color:#7A756E; font-size:8px; margin-left:8px;">
                                        Stk: {{ number_format($child['in_stock']) }}
                                    </span>
                                </td>
                                @foreach($dateRange as $date)
                                <td style="background:#F0EEE9; border-right:1px solid var(--border2);"></td>
                                @endforeach
                            </tr>

                            @php
                                $childRows = [
                                    ['Delivery Req',  'seid_request',  '#FAFAF8', '#1A1816'],
                                    ['Actual',    'actual',        '#F0FAF4', '#166534'],
                                    ['Bal Del',   'balance_del',   '#FFFBF0', '#92400E'],
                                    ['Bal Stock', 'balance_stock', '#F0F5FF', '#1E3A5F'],
                                ];
                            @endphp

                            @foreach($childRows as [$childLabel, $childKey, $childBg, $childColor])
                            <tr>
                                <td class="col-sticky-1"
                                    style="background:{{ $childBg }}; padding:2px 8px 2px {{ 16 + ($child['level'] * 8) }}px;
                                        font-size:8px; font-weight:600; color:{{ $childColor }};
                                        border-right:1px solid var(--border2); white-space:nowrap;">
                                    {{ $childLabel }}
                                </td>
                                <td class="col-sticky-2"
                                    style="background:{{ $childBg }}; font-size:8px; font-weight:700;
                                        text-align:right; padding:2px 8px; border-right:2px solid var(--border);
                                        color:#1E3A5F;">
                                    @if($childKey === 'balance_stock')
                                        {{ number_format($child['in_stock']) }}
                                    @endif
                                </td>
                                @foreach($dateRange as $date)
                                @php
                                    $val  = $child['daily'][$date][$childKey] ?? 0;
                                    $show = !in_array($childKey, ['seid_request','actual']) || $val != 0;
                                    $cls  = $val < 0 ? 'val-neg' : ($val > 0 ? 'val-pos' : 'val-zero');
                                @endphp
                                <td style="background:{{ $childBg }}; text-align:right; padding:2px 6px;
                                        font-size:8px; border-right:1px solid var(--border2);"
                                    class="{{ $show ? $cls : 'val-zero' }}">
                                    {{ $show ? number_format($val) : '' }}
                                </td>
                                @endforeach
                            </tr>
                            @endforeach

                            @endforeach
                        @endif

                    </tbody>
                </table>
            </div>

            {{-- VERTICAL VIEW --}}
            <div x-show="viewMode === 'vertical'" style="overflow-x:auto;">
                <table class="ds-table" style="min-width:max-content;">
                    <thead>
                        <tr>
                            <th class="vertical-sticky-date" style="min-width:100px; text-align:left; border-right:2px solid var(--border);">
                                Tanggal
                            </th>
                            <th style="min-width:80px;">Stk Awal</th>
                            <th style="min-width:90px; background:#FAFAF8;">Delivery Request</th>
                            <th style="min-width:90px; background:#F6FBF8;">Actual Del</th>
                            <th style="min-width:90px; background:#FFFBF5;">Balance Del</th>
                            <th style="min-width:100px; background:#F5F8FF;">Balance Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dateRange as $date)
                        @php
                            $isWeekend = in_array(\Carbon\Carbon::parse($date)->dayOfWeek, [0, 6]);
                            $isToday   = $date === now()->format('Y-m-d');
                            $seidVal   = $item['daily'][$date]['seid_request'] ?? 0;
                            $actualVal = $item['daily'][$date]['actual'] ?? 0;
                            $balDelVal = $item['daily'][$date]['balance_del'] ?? 0;
                            $balStockVal = $item['daily'][$date]['balance_stock'] ?? $item['in_stock'];
                            
                            $balDelCls = $balDelVal < 0 ? 'val-neg' : ($balDelVal > 0 ? 'val-pos' : 'val-zero');
                            $balStockCls = $balStockVal < 0 ? 'val-neg' : ($balStockVal > 0 ? 'val-pos' : 'val-zero');
                        @endphp
                        <tr style="{{ $isToday ? 'background:#FFFEF8;' : '' }}">
                            <td class="vertical-sticky-date" style="font-family:'IBM Plex Sans',sans-serif; font-size:10px; font-weight:600; border-right:2px solid var(--border); {{ $isToday ? 'background:#FFF3E0; color:var(--accent);' : ($isWeekend ? 'color:#A09890;' : '') }}">
                                {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                                @if($isWeekend)
                                <span style="font-size:8px; color:#A09890;">(Weekend)</span>
                                @endif
                            </td>
                            <td style="color:var(--muted); font-size:10px; text-align:center;">
                                @if($loop->first)
                                {{ number_format($item['in_stock']) }}
                                @else
                                —
                                @endif
                            </td>
                            <td class="{{ $seidVal > 0 ? 'val-seid' : 'val-zero' }}" style="background:#FAFAF8;">
                                {{ $seidVal > 0 ? number_format($seidVal) : '' }}
                            </td>
                            <td class="{{ $actualVal > 0 ? 'val-actual' : 'val-zero' }}" style="background:#F6FBF8;">
                                {{ $actualVal > 0 ? number_format($actualVal) : '' }}
                            </td>
                            <td class="{{ $balDelCls }}" style="background:#FFFBF5;">
                                {{ $balDelVal != 0 ? number_format($balDelVal) : '0' }}
                            </td>
                            <td class="{{ $balStockCls }}" style="background:#F5F8FF; font-weight:{{ $balStockVal < 0 ? '700' : '500' }};">
                                {{ number_format($balStockVal) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Children table (vertical) --}}
                @if(!empty($item['children']))
                    @foreach($item['children'] as $child)
                    <div style="margin-top:16px; padding-left:{{ $child['level'] * 12 }}px;">
                        <div style="background:#F0EEE9; padding:6px 12px; border-left:3px solid #7A756E; margin-bottom:8px;">
                            <span style="font-size:9px; font-weight:700; color:#1A1816;">
                                {{ $child['semi_code'] }}
                            </span>
                            <span style="color:#7A756E; font-size:9px; margin-left:6px;">{{ $child['semi_name'] }}</span>
                            <span style="background:#1A1816; color:#fff; font-size:7px; padding:1px 5px;
                                        border-radius:2px; margin-left:6px;">×{{ $child['multiplier'] }}</span>
                            <span style="color:#7A756E; font-size:8px; margin-left:8px;">
                                Stk: {{ number_format($child['in_stock']) }}
                            </span>
                        </div>

                        <table class="ds-table" style="min-width:max-content;">
                            <thead>
                                <tr>
                                    <th class="vertical-sticky-date" style="min-width:100px; text-align:left; border-right:2px solid var(--border);">
                                        Tanggal
                                    </th>
                                    <th style="min-width:80px;">Stk Awal</th>
                                    <th style="min-width:90px; background:#FAFAF8;">Deliver Req</th>
                                    <th style="min-width:90px; background:#F0FAF4;">Actual</th>
                                    <th style="min-width:90px; background:#FFFBF0;">Bal Del</th>
                                    <th style="min-width:100px; background:#F0F5FF;">Bal Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dateRange as $date)
                                @php
                                    $isToday = $date === now()->format('Y-m-d');
                                    $seidVal = $child['daily'][$date]['seid_request'] ?? 0;
                                    $actualVal = $child['daily'][$date]['actual'] ?? 0;
                                    $balDelVal = $child['daily'][$date]['balance_del'] ?? 0;
                                    $balStockVal = $child['daily'][$date]['balance_stock'] ?? 0;
                                    
                                    $balDelCls = $balDelVal < 0 ? 'val-neg' : ($balDelVal > 0 ? 'val-pos' : 'val-zero');
                                    $balStockCls = $balStockVal < 0 ? 'val-neg' : ($balStockVal > 0 ? 'val-pos' : 'val-zero');
                                @endphp
                                <tr>
                                    <td class="vertical-sticky-date" style="font-size:9px; border-right:2px solid var(--border); {{ $isToday ? 'background:#FFF3E0; color:var(--accent);' : '' }}">
                                        {{ \Carbon\Carbon::parse($date)->format('d M') }}
                                    </td>
                                    <td style="font-size:9px; text-align:center;">
                                        @if($loop->first)
                                        {{ number_format($child['in_stock']) }}
                                        @else
                                        —
                                        @endif
                                    </td>
                                    <td class="{{ $seidVal > 0 ? 'val-seid' : 'val-zero' }}" style="background:#FAFAF8; font-size:9px;">
                                        {{ $seidVal > 0 ? number_format($seidVal) : '' }}
                                    </td>
                                    <td class="{{ $actualVal > 0 ? 'val-actual' : 'val-zero' }}" style="background:#F0FAF4; font-size:9px;">
                                        {{ $actualVal > 0 ? number_format($actualVal) : '' }}
                                    </td>
                                    <td class="{{ $balDelCls }}" style="background:#FFFBF0; font-size:9px;">
                                        {{ $balDelVal != 0 ? number_format($balDelVal) : '0' }}
                                    </td>
                                    <td class="{{ $balStockCls }}" style="background:#F0F5FF; font-size:9px;">
                                        {{ number_format($balStockVal) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endforeach
                @endif
            </div>

        </div>
        @empty
        <div class="ds-card" style="padding:60px; text-align:center;">
            <div style="font-size:11px; color:var(--muted); letter-spacing:.05em;">TIDAK ADA DATA</div>
        </div>
        @endforelse

        {{-- ═══ PAGINATION ═══ --}}
        @if($totalPages > 1)
        <div style="display:flex; align-items:center; justify-content:space-between; margin-top:20px; flex-wrap:wrap; gap:12px;">
            <div style="font-size:10px; color:var(--muted); font-family:'IBM Plex Mono',monospace;">
                PAGE <strong style="color:var(--text);">{{ $currentPage }}</strong> / {{ $totalPages }}
                &nbsp;·&nbsp;
                <strong style="color:var(--text);">{{ $totalItems }}</strong> ITEMS
            </div>

            <div style="display:flex; gap:4px; align-items:center;">
                <button wire:click="setPage(1)" @disabled($currentPage == 1) class="pg-btn">«</button>
                <button wire:click="previousPage" @disabled($currentPage == 1) class="pg-btn">‹ Prev</button>

                @php $start = max(1, $currentPage - 2); $end = min($totalPages, $currentPage + 2); @endphp
                @for($p = $start; $p <= $end; $p++)
                <button wire:click="setPage({{ $p }})" class="pg-btn {{ $p == $currentPage ? 'active' : '' }}">
                    {{ $p }}
                </button>
                @endfor

                <button wire:click="nextPage" @disabled($currentPage == $totalPages) class="pg-btn">Next ›</button>
                <button wire:click="setPage({{ $totalPages }})" @disabled($currentPage == $totalPages) class="pg-btn">»</button>
            </div>
        </div>
        @endif

    </div>

    {{-- Loading overlay --}}
    <div wire:loading.flex style="position:fixed; inset:0; background:rgba(240,238,233,.8); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center;">
        <div class="ds-card" style="padding:20px 32px; display:flex; align-items:center; gap:12px; box-shadow:0 4px 24px rgba(0,0,0,.08);">
            <svg style="animation:spin 1s linear infinite; width:18px; height:18px; color:var(--accent);" fill="none" viewBox="0 0 24 24">
                <circle style="opacity:.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                <path style="opacity:.8;" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            <span style="font-size:11px; color:var(--text); letter-spacing:.05em;">MEMUAT DATA...</span>
        </div>
    </div>
    <style>@keyframes spin { to { transform:rotate(360deg); } }</style>

</div>