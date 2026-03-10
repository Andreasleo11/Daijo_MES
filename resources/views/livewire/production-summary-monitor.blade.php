<div style="font-family:'IBM Plex Sans',sans-serif; background:#0F0E0C; min-height:100vh; padding:28px; color:#E8E4DC;">

    {{-- Header --}}
    <div style="display:flex; align-items:flex-end; justify-content:space-between; margin-bottom:28px; flex-wrap:wrap; gap:12px;">
        <div>
            <div style="font-size:10px; font-weight:700; letter-spacing:.2em; color:#5A554E;
                        text-transform:uppercase; margin-bottom:4px; font-family:'IBM Plex Mono',monospace;">
                STORE MANAGEMENT
            </div>
            <h1 style="font-size:26px; font-weight:800; color:#F5F1EB; margin:0;
                       font-family:'IBM Plex Mono',monospace; letter-spacing:-1px;">
                PRODUCTION SUMMARY
                <span style="color:#C8A97E;">MONITOR</span>
            </h1>
        </div>

        <div style="display:flex; align-items:center; gap:10px;">
            <input wire:model.live="filterDate" type="date"
                   style="padding:8px 12px; background:#1C1A16; border:1px solid #2E2B24;
                          border-radius:3px; color:#E8E4DC; font-size:12px;
                          font-family:'IBM Plex Mono',monospace;">
            <div style="background:#1C1A16; border:1px solid #2E2B24; border-radius:3px;
                        padding:8px 14px; font-size:11px; color:#5A554E;
                        font-family:'IBM Plex Mono',monospace;">
                {{ count($this->summaryLogs) }} batch runs
            </div>
        </div>

        {{-- Di bagian header filters --}}
        <div style="display:flex; align-items:center; gap:10px;">
            <input wire:model.live="filterDate" type="date"
                style="padding:8px 12px; background:#1C1A16; border:1px solid #2E2B24;
                        border-radius:3px; color:#E8E4DC; font-size:12px;
                        font-family:'IBM Plex Mono',monospace;">

            <select wire:model.live="filterWarehouse"
                    style="padding:8px 12px; background:#1C1A16; border:1px solid #2E2B24;
                        border-radius:3px; color:#E8E4DC; font-size:12px;
                        font-family:'IBM Plex Mono',monospace; cursor:pointer;">
                <option value="">ALL WH</option>
                <option value="FFI">FFI</option>
                <option value="FG">FG</option>
                <option value="WIP">WIP</option>
            </select>

            <div style="background:#1C1A16; border:1px solid #2E2B24; border-radius:3px;
                        padding:8px 14px; font-size:11px; color:#5A554E;
                        font-family:'IBM Plex Mono',monospace;">
                {{ count($this->summaryLogs) }} batch runs
            </div>
        </div>
    </div>

    {{-- Logs --}}
    @forelse($this->summaryLogs as $log)
    <div style="margin-bottom:12px; border:1px solid #2E2B24; border-radius:4px; overflow:hidden;
                background:#141210;">

        {{-- Batch header --}}
        <div wire:click="toggleExpand({{ $log['id'] }})"
             style="display:flex; align-items:center; gap:0; cursor:pointer;
                    border-bottom:{{ $expandedLog === $log['id'] ? '1px solid #2E2B24' : 'none' }};">

            {{-- Time badge --}}
            <div style="background:#1C1A16; padding:14px 16px; border-right:1px solid #2E2B24;
                        font-family:'IBM Plex Mono',monospace; font-size:13px; font-weight:700;
                        color:#C8A97E; white-space:nowrap; min-width:80px; text-align:center;">
                {{ $log['created_at'] }}
            </div>

            {{-- Status dot --}}
            <div style="padding:0 14px; border-right:1px solid #2E2B24;">
                <div style="width:8px; height:8px; border-radius:50%;
                            background:{{ $log['status'] === 'success' ? '#4ADE80' : '#F87171' }};
                            box-shadow:0 0 6px {{ $log['status'] === 'success' ? '#4ADE80' : '#F87171' }};">
                </div>
            </div>

            {{-- Stats --}}
            <div style="display:flex; gap:0; flex:1;">
                <div style="padding:14px 20px; border-right:1px solid #1C1A16;">
                    <div style="font-size:9px; color:#5A554E; font-weight:700; letter-spacing:.1em;
                                text-transform:uppercase; margin-bottom:2px;">SPK</div>
                    <div style="font-size:16px; font-weight:800; color:#E8E4DC;
                                font-family:'IBM Plex Mono',monospace;">
                        {{ $log['total_spk'] }}
                    </div>
                </div>
                <div style="padding:14px 20px; border-right:1px solid #1C1A16;">
                    <div style="font-size:9px; color:#5A554E; font-weight:700; letter-spacing:.1em;
                                text-transform:uppercase; margin-bottom:2px;">Records</div>
                    <div style="font-size:16px; font-weight:800; color:#E8E4DC;
                                font-family:'IBM Plex Mono',monospace;">
                        {{ $log['total_records'] }}
                    </div>
                </div>
                <div style="padding:14px 20px; border-right:1px solid #1C1A16;">
                    <div style="font-size:9px; color:#5A554E; font-weight:700; letter-spacing:.1em;
                                text-transform:uppercase; margin-bottom:2px;">Total Qty</div>
                    <div style="font-size:16px; font-weight:800; color:#C8A97E;
                                font-family:'IBM Plex Mono',monospace;">
                        {{ number_format($log['total_qty']) }}
                    </div>
                </div>
                @if(!empty($log['label_used']))
                <div style="padding:14px 20px;">
                    <div style="font-size:9px; color:#5A554E; font-weight:700; letter-spacing:.1em;
                                text-transform:uppercase; margin-bottom:4px;">Labels</div>
                    <div style="display:flex; gap:4px; flex-wrap:wrap;">
                        @foreach($log['label_used'] as $label)
                        <span style="background:#2E2B24; color:#C8A97E; font-size:10px; font-weight:700;
                                     padding:2px 6px; border-radius:2px; font-family:'IBM Plex Mono',monospace;">
                            {{ $label }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Expand toggle --}}
            <div style="padding:0 16px; color:#5A554E; font-size:16px;">
                {{ $expandedLog === $log['id'] ? '▲' : '▼' }}
            </div>
        </div>

        {{-- Detail table --}}
        @if($expandedLog === $log['id'])
        <div style="overflow-x:auto;">
            <table style="border-collapse:collapse; width:100%; min-width:700px;
                          font-family:'IBM Plex Mono',monospace;">
                <thead>
                    <tr style="background:#1C1A16;">
                        <th style="padding:8px 16px; text-align:left; font-size:9px; font-weight:700;
                                   color:#5A554E; letter-spacing:.1em; text-transform:uppercase;
                                   border-bottom:1px solid #2E2B24; white-space:nowrap;">SPK Code</th>
                        <th style="padding:8px 16px; text-align:left; font-size:9px; font-weight:700;
                                    color:#5A554E; letter-spacing:.1em; text-transform:uppercase;
                                    border-bottom:1px solid #2E2B24;">Item Code</th>
                        <th style="padding:8px 16px; text-align:right; font-size:9px; font-weight:700;
                                   color:#5A554E; letter-spacing:.1em; text-transform:uppercase;
                                   border-bottom:1px solid #2E2B24;">Qty</th>
                        <th style="padding:8px 16px; text-align:center; font-size:9px; font-weight:700;
                                   color:#5A554E; letter-spacing:.1em; text-transform:uppercase;
                                   border-bottom:1px solid #2E2B24;">Warehouse</th>
                        <th style="padding:8px 16px; text-align:center; font-size:9px; font-weight:700;
                                   color:#5A554E; letter-spacing:.1em; text-transform:uppercase;
                                   border-bottom:1px solid #2E2B24;">Records</th>
                        <th style="padding:8px 16px; text-align:left; font-size:9px; font-weight:700;
                                   color:#5A554E; letter-spacing:.1em; text-transform:uppercase;
                                   border-bottom:1px solid #2E2B24;">Labels Used</th>
                        <th style="padding:8px 16px; text-align:center; font-size:9px; font-weight:700;
                                   color:#5A554E; letter-spacing:.1em; text-transform:uppercase;
                                   border-bottom:1px solid #2E2B24;">Created Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($log['summaries'] as $i => $summary)
                    <tr style="border-bottom:1px solid #1C1A16;
                               background:{{ $i % 2 === 0 ? '#141210' : '#161410' }};">
                        <td style="padding:10px 16px; font-size:12px; font-weight:700; color:#E8E4DC;">
                            {{ $summary['spk_code'] }}
                        </td>
                        <td style="padding:10px 16px; font-size:11px; color:#9A9590;">
                            {{ $summary['item_code'] ?? '—' }}
                        </td>
                        <td style="padding:10px 16px; font-size:13px; font-weight:800;
                                   color:#C8A97E; text-align:right;">
                            {{ number_format($summary['total_quantity']) }}
                        </td>
                        <td style="padding:10px 16px; text-align:center;">
                            <span style="background:#2E2B24; color:#9A9590; font-size:10px;
                                         font-weight:700; padding:2px 8px; border-radius:2px;">
                                {{ strtoupper($summary['warehouse']) }}
                            </span>
                        </td>
                        <td style="padding:10px 16px; font-size:12px; color:#5A554E;
                                   text-align:center;">
                            {{ $summary['records_count'] }}
                        </td>
                        <td style="padding:10px 16px;">
                            <div style="display:flex; gap:3px; flex-wrap:wrap;">
                                @forelse($summary['used_label'] ?? [] as $lbl)
                                <span style="background:#1C1A16; border:1px solid #2E2B24;
                                            color:#7A9E7E; font-size:9px; font-weight:700;
                                            padding:1px 5px; border-radius:2px;">
                                    {{ $lbl }}
                                </span>
                                @empty
                                <span style="color:#3D3935; font-size:9px;">—</span>
                                @endforelse
                            </div>
                        </td>
                        <td style="padding:10px 16px; font-size:11px; color:#5A554E;
                                   text-align:center;">
                            {{ $summary['created_date'] }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                {{-- Footer total --}}
                <tfoot>
                    <tr style="background:#1C1A16; border-top:2px solid #2E2B24;">
                        <td style="padding:10px 16px; font-size:11px; font-weight:700; color:#5A554E;">
                            TOTAL {{ count($log['summaries']) }} SPK
                        </td>
                        <td style="padding:10px 16px; font-size:14px; font-weight:800;
                                   color:#C8A97E; text-align:right;">
                            {{ number_format($log['total_qty']) }}
                        </td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif

    </div>
    @empty
    <div style="text-align:center; padding:80px 20px; color:#2E2B24;">
        <div style="font-size:40px; margin-bottom:12px;">◈</div>
        <div style="font-size:13px; font-family:'IBM Plex Mono',monospace; letter-spacing:.1em;">
            NO SUMMARY LOGS FOUND
        </div>
        <div style="font-size:11px; color:#1C1A16; margin-top:4px;">
            {{ $filterDate }}
        </div>
    </div>
    @endforelse

</div>