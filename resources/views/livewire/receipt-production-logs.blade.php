<div style="font-family:'IBM Plex Sans',sans-serif; background:#F7F5F2; min-height:100vh; padding:24px;">

    {{-- Header --}}
    <div style="margin-bottom:24px; display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:12px;">
        <div>
            <div style="font-size:9px; font-weight:700; letter-spacing:.18em; color:#9A9590;
                        text-transform:uppercase; margin-bottom:4px; font-family:'IBM Plex Mono',monospace;">
                SAP INTEGRATION
            </div>
            <h1 style="font-size:22px; font-weight:800; color:#1A1816; margin:0;
                       font-family:'IBM Plex Mono',monospace; letter-spacing:-.5px;">
                RECEIPT PRODUCTION <span style="color:#B45309;">LOGS</span>
            </h1>
        </div>

        {{-- Stats --}}
        <div style="display:flex; gap:8px;">
            <div style="background:#fff; border:1px solid #E8E4DC; border-radius:3px;
                        padding:10px 16px; text-align:center; min-width:70px;">
                <div style="font-size:20px; font-weight:800; color:#1A1816;
                            font-family:'IBM Plex Mono',monospace;">
                    {{ $this->stats['total'] }}
                </div>
                <div style="font-size:9px; font-weight:700; color:#9A9590; letter-spacing:.1em;
                            text-transform:uppercase;">Total</div>
            </div>
            <div style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:3px;
                        padding:10px 16px; text-align:center; min-width:70px;">
                <div style="font-size:20px; font-weight:800; color:#15803D;
                            font-family:'IBM Plex Mono',monospace;">
                    {{ $this->stats['success'] }}
                </div>
                <div style="font-size:9px; font-weight:700; color:#16A34A; letter-spacing:.1em;
                            text-transform:uppercase;">Success</div>
            </div>
            <div style="background:#FFF7ED; border:1px solid #FED7AA; border-radius:3px;
                        padding:10px 16px; text-align:center; min-width:70px;">
                <div style="font-size:20px; font-weight:800; color:#C2410C;
                            font-family:'IBM Plex Mono',monospace;">
                    {{ $this->stats['failed'] }}
                </div>
                <div style="font-size:9px; font-weight:700; color:#EA580C; letter-spacing:.1em;
                            text-transform:uppercase;">Failed</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div style="display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap;">
        <input wire:model.live="filterDate" type="date"
               style="padding:8px 12px; border:1px solid #D8D4CC; border-radius:3px;
                      font-size:12px; font-family:'IBM Plex Mono',monospace; background:#fff; color:#1A1816;">

        <select wire:model.live="filterStatus"
                style="padding:8px 12px; border:1px solid #D8D4CC; border-radius:3px;
                       font-size:12px; font-family:'IBM Plex Mono',monospace; background:#fff; color:#1A1816;">
            <option value="">— All Status</option>
            <option value="success">Success</option>
            <option value="failed">Failed</option>
        </select>

        <input wire:model.live.debounce.400ms="filterSpk"
               placeholder="Search SPK..."
               style="padding:8px 12px; border:1px solid #D8D4CC; border-radius:3px;
                      font-size:12px; font-family:'IBM Plex Mono',monospace; background:#fff;
                      color:#1A1816; min-width:180px;">

        <select wire:model.live="perPage"
                style="padding:8px 12px; border:1px solid #D8D4CC; border-radius:3px;
                       font-size:12px; font-family:'IBM Plex Mono',monospace; background:#fff; color:#1A1816;">
            <option value="20">20 / page</option>
            <option value="50">50 / page</option>
            <option value="100">100 / page</option>
        </select>
    </div>

    {{-- Table --}}
    <div style="border:1px solid #D8D4CC; border-radius:4px; overflow:hidden; background:#fff;">
        <table style="border-collapse:collapse; width:100%; min-width:900px;
                      font-family:'IBM Plex Mono',monospace;">
            <thead>
                <tr style="background:#1A1816; color:#fff;">
                    <th style="padding:10px 14px; text-align:left; font-size:9px; font-weight:700;
                               letter-spacing:.1em; text-transform:uppercase; white-space:nowrap;
                               border-right:1px solid #333; min-width:150px;">Timestamp</th>
                    <th style="padding:10px 14px; text-align:center; font-size:9px; font-weight:700;
                               letter-spacing:.1em; text-transform:uppercase; white-space:nowrap;
                               border-right:1px solid #333; min-width:80px;">Status</th>
                    <th style="padding:10px 14px; text-align:left; font-size:9px; font-weight:700;
                               letter-spacing:.1em; text-transform:uppercase; white-space:nowrap;
                               border-right:1px solid #333; min-width:110px;">SPK Code</th>
                    <th style="padding:10px 14px; text-align:left; font-size:9px; font-weight:700;
                               letter-spacing:.1em; text-transform:uppercase; white-space:nowrap;
                               border-right:1px solid #333; min-width:140px;">Item Code</th>
                    <th style="padding:10px 14px; text-align:center; font-size:9px; font-weight:700;
                               letter-spacing:.1em; text-transform:uppercase; white-space:nowrap;
                               border-right:1px solid #333; min-width:80px;">WH</th>
                    <th style="padding:10px 14px; text-align:right; font-size:9px; font-weight:700;
                               letter-spacing:.1em; text-transform:uppercase; white-space:nowrap;
                               border-right:1px solid #333; min-width:70px;">Qty</th>
                    <th style="padding:10px 14px; text-align:left; font-size:9px; font-weight:700;
                               letter-spacing:.1em; text-transform:uppercase; white-space:nowrap;
                               border-right:1px solid #333;">Message / Error</th>
                    <th style="padding:10px 14px; text-align:center; font-size:9px; font-weight:700;
                               letter-spacing:.1em; text-transform:uppercase; white-space:nowrap;
                               min-width:70px;">Detail</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->logs as $log)
                @php
                    $isSuccess  = $log['status'] === 'success';
                    $rowBg      = $isSuccess ? '#fff' : '#FFF7ED';
                    $borderLeft = $isSuccess ? '3px solid #22C55E' : '3px solid #F97316';
                @endphp

                {{-- Main row --}}
                <tr style="border-bottom:1px solid #E8E4DC; background:{{ $rowBg }};
                        border-left:{{ $borderLeft }};">
                    <td style="padding:10px 14px; font-size:11px; color:#5A554E; white-space:nowrap;">
                        {{ $log['created_at'] }}
                    </td>
                    <td style="padding:10px 14px; text-align:center;">
                        @if($isSuccess)
                        <span style="background:#DCFCE7; color:#15803D; font-size:9px; font-weight:700;
                                    padding:3px 8px; border-radius:20px; letter-spacing:.05em;">
                            ✓ SUCCESS
                        </span>
                        @else
                        <span style="background:#FEE2E2; color:#B91C1C; font-size:9px; font-weight:700;
                                    padding:3px 8px; border-radius:20px; letter-spacing:.05em;">
                            ✕ FAILED
                        </span>
                        @endif
                    </td>

                    {{-- SPK Code + SAP sent status --}}
                    <td style="padding:10px 14px; font-size:12px; font-weight:700; color:#1A1816;">
                        {{ $log['spk_code'] }}

                        @if($log['is_multi'])
                            <div style="display:flex; gap:4px; margin-top:4px; flex-wrap:wrap;">
                                @if($log['total_sent'] > 0)
                                <span style="background:#DCFCE7; color:#15803D; font-size:9px; font-weight:700;
                                            padding:2px 6px; border-radius:2px; white-space:nowrap;">
                                    ✓ {{ $log['total_sent'] }} terkirim
                                </span>
                                @endif
                                @if($log['total_not_sent'] > 0)
                                <span style="background:#FEE2E2; color:#B91C1C; font-size:9px; font-weight:700;
                                            padding:2px 6px; border-radius:2px; white-space:nowrap;">
                                    ✕ {{ $log['total_not_sent'] }} belum
                                </span>
                                @endif
                            </div>
                        @else
                            @if(($log['sap_sent'] ?? null) == 1)
                            <div style="font-size:9px; color:#15803D; font-weight:700; margin-top:3px;">
                                ✓ Terkirim {{ $log['sap_sent_at'] ? \Carbon\Carbon::parse($log['sap_sent_at'])->timezone('Asia/Jakarta')->format('H:i') : '' }}
                            </div>
                            @elseif(($log['sap_sent'] ?? null) === 0)
                            <div style="font-size:9px; color:#B91C1C; font-weight:700; margin-top:3px;">
                                ✕ Belum terkirim
                            </div>
                            @endif
                        @endif
                    </td>

                    <td style="padding:10px 14px; font-size:11px; color:#3D3935;">
                        {{ $log['item_code'] }}
                    </td>
                    <td style="padding:10px 14px; text-align:center;">
                        <span style="background:#F5F3EF; color:#5A554E; font-size:10px; font-weight:700;
                                    padding:2px 7px; border-radius:2px;">
                            {{ $log['warehouse'] }}
                        </span>
                    </td>
                    <td style="padding:10px 14px; font-size:12px; font-weight:700; color:#1A1816;
                            text-align:right;">
                        {{ is_numeric($log['quantity']) ? number_format($log['quantity']) : $log['quantity'] }}
                    </td>
                    <td style="padding:10px 14px; font-size:11px;
                            color:{{ $isSuccess ? '#3D3935' : '#9A3412' }};
                            max-width:300px;">
                        @if(!$isSuccess && $log['error_msg'])
                            <span style="font-weight:600;">{{ $log['error_msg'] }}</span>
                        @else
                            <span style="color:#9A9590;">Sent to SAP successfully</span>
                        @endif
                    </td>
                    <td style="padding:10px 14px; text-align:center;">
                        <button wire:click="toggleExpand({{ $log['id'] }})"
                                style="background:#F5F3EF; border:1px solid #D8D4CC; color:#5A554E;
                                    padding:4px 10px; border-radius:2px; font-size:10px;
                                    font-weight:700; cursor:pointer; font-family:'IBM Plex Sans',sans-serif;">
                            {{ $expandedId === $log['id'] ? '▲' : '▼' }}
                        </button>
                    </td>
                </tr>

                {{-- Expanded detail row --}}
                @if($expandedId === $log['id'])
                <tr style="background:#FFFBF0; border-bottom:2px solid #FED7AA; border-left:{{ $borderLeft }};">
                    <td colspan="8" style="padding:16px 20px;">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

                            {{-- Request — Info yang dikirim --}}
                            <div style="background:#fff; border:1px solid #E8E4DC; border-radius:4px; padding:16px;">
                                <div style="font-size:10px; font-weight:700; letter-spacing:.12em;
                                            text-transform:uppercase; color:#9A9590; margin-bottom:12px;
                                            padding-bottom:8px; border-bottom:1px solid #E8E4DC;">
                                    📦 Data yang Dikirim ke SAP
                                    @if($log['is_multi'])
                                    <span style="background:#1A1816; color:#fff; font-size:9px; font-weight:700;
                                                padding:2px 7px; border-radius:2px; margin-left:6px;">
                                        {{ $log['total_items'] }} SPK
                                    </span>
                                    @endif
                                </div>

                                @if($log['is_multi'])
                                {{-- Multi item — tampilkan tabel --}}
                                <div style="overflow-x:auto;">
                                    <table style="width:100%; border-collapse:collapse; font-size:11px;
                                                font-family:'IBM Plex Mono',monospace;">
                                        <thead>
                                            <tr style="background:#F5F3EF;">
                                                <th style="padding:6px 10px; text-align:left; font-size:9px; font-weight:700;
                                                        color:#9A9590; letter-spacing:.08em; text-transform:uppercase;
                                                        border-bottom:1px solid #E8E4DC;">SPK</th>
                                                <th style="padding:6px 10px; text-align:left; font-size:9px; font-weight:700;
                                                        color:#9A9590; letter-spacing:.08em; text-transform:uppercase;
                                                        border-bottom:1px solid #E8E4DC;">Item Code</th>
                                                <th style="padding:6px 10px; text-align:center; font-size:9px; font-weight:700;
                                                        color:#9A9590; letter-spacing:.08em; text-transform:uppercase;
                                                        border-bottom:1px solid #E8E4DC;">WH</th>
                                                <th style="padding:6px 10px; text-align:right; font-size:9px; font-weight:700;
                                                        color:#9A9590; letter-spacing:.08em; text-transform:uppercase;
                                                        border-bottom:1px solid #E8E4DC;">Qty</th>
                                                <th style="padding:6px 10px; text-align:center; font-size:9px; font-weight:700;
                                                        color:#9A9590; letter-spacing:.08em; text-transform:uppercase;
                                                        border-bottom:1px solid #E8E4DC;">Label</th>
                                                <th style="padding:6px 10px; text-align:center; font-size:9px; font-weight:700;
                                                        color:#9A9590; letter-spacing:.08em; text-transform:uppercase;
                                                        border-bottom:1px solid #E8E4DC;">SAP Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($log['payloads'] as $i => $p)
                                            <tr style="border-bottom:1px solid #F5F3EF;
                                                    background:{{ $i % 2 === 0 ? '#fff' : '#FAFAF8' }};">
                                                <td style="padding:6px 10px; font-weight:700; color:#1A1816;">
                                                    {{ $p['spk_code'] }}
                                                </td>
                                                <td style="padding:6px 10px; color:#3D3935;">{{ $p['item_code'] }}</td>
                                                <td style="padding:6px 10px; text-align:center; color:#5A554E;">{{ $p['warehouse'] }}</td>
                                                <td style="padding:6px 10px; text-align:right; font-weight:700; color:#1A1816;">
                                                    {{ number_format($p['quantity']) }}
                                                </td>
                                                <td style="padding:6px 10px; text-align:center; color:#5A554E;">{{ $p['label'] }}</td>
                                                <td style="padding:6px 10px; text-align:center;">
                                                    @if(($p['sap_sent'] ?? null) == 1)
                                                    <span style="background:#DCFCE7; color:#15803D; font-size:9px;
                                                                font-weight:700; padding:2px 8px; border-radius:2px;">
                                                        ✓ Terkirim
                                                    </span>
                                                    @elseif(($p['sap_sent'] ?? null) === 0)
                                                    <span style="background:#FEE2E2; color:#B91C1C; font-size:9px;
                                                                font-weight:700; padding:2px 8px; border-radius:2px;">
                                                        ✕ Belum
                                                    </span>
                                                    @else
                                                    <span style="color:#C8C4BC; font-size:9px;">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr style="background:#F5F3EF; border-top:2px solid #E8E4DC;">
                                                <td colspan="3" style="padding:6px 10px; font-size:10px; font-weight:700; color:#9A9590;">
                                                    TOTAL {{ $log['total_items'] }} SPK
                                                </td>
                                                <td style="padding:6px 10px; text-align:right; font-weight:800; color:#1A1816;">
                                                    {{ number_format($log['quantity']) }}
                                                </td>
                                                <td></td>
                                                <td style="padding:6px 10px; text-align:center; font-size:9px;">
                                                    @if($log['total_sent'] > 0)
                                                    <span style="color:#15803D; font-weight:700;">✓ {{ $log['total_sent'] }}</span>
                                                    @endif
                                                    @if($log['total_not_sent'] > 0)
                                                    <span style="color:#B91C1C; font-weight:700; margin-left:4px;">✕ {{ $log['total_not_sent'] }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                @else
                                {{-- Single item --}}
                                <div style="display:flex; flex-direction:column; gap:8px;">
                                    <div style="display:flex; justify-content:space-between; align-items:center;">
                                        <span style="font-size:12px; color:#7A756E;">Nomor SPK</span>
                                        <span style="font-size:13px; font-weight:700; color:#1A1816;
                                                    font-family:'IBM Plex Mono',monospace;">{{ $log['spk_code'] }}</span>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; align-items:center;">
                                        <span style="font-size:12px; color:#7A756E;">Kode Item</span>
                                        <span style="font-size:12px; font-weight:700; color:#1A1816;
                                                    font-family:'IBM Plex Mono',monospace;">{{ $log['item_code'] }}</span>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; align-items:center;">
                                        <span style="font-size:12px; color:#7A756E;">Gudang</span>
                                        <span style="font-size:13px; font-weight:700; color:#1A1816;">{{ $log['warehouse'] }}</span>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; align-items:center;">
                                        <span style="font-size:12px; color:#7A756E;">Jumlah</span>
                                        <span style="font-size:16px; font-weight:800; color:#1A1816;
                                                    font-family:'IBM Plex Mono',monospace;">
                                            {{ number_format($log['quantity']) }} pcs
                                        </span>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; align-items:center;">
                                        <span style="font-size:12px; color:#7A756E;">Label</span>
                                        <span style="font-size:12px; font-weight:700; color:#1A1816;">{{ $log['label'] }}</span>
                                    </div>
                                    {{-- SAP Sent Status untuk single item --}}
                                    <div style="margin-top:4px; padding-top:8px; border-top:1px solid #E8E4DC;">
                                        <div style="display:flex; justify-content:space-between; align-items:center;">
                                            <span style="font-size:12px; color:#7A756E;">Status SAP</span>
                                            @if(($log['sap_sent'] ?? null) == 1)
                                            <span style="background:#DCFCE7; color:#15803D; font-size:10px;
                                                        font-weight:700; padding:3px 10px; border-radius:2px;">
                                                ✓ Terkirim {{ $log['sap_sent_at'] ? '· ' . \Carbon\Carbon::parse($log['sap_sent_at'])->timezone('Asia/Jakarta')->format('H:i') : '' }}
                                            </span>
                                            @elseif(($log['sap_sent'] ?? null) === 0)
                                            <span style="background:#FEE2E2; color:#B91C1C; font-size:10px;
                                                        font-weight:700; padding:3px 10px; border-radius:2px;">
                                                ✕ Belum Terkirim
                                            </span>
                                            @else
                                            <span style="color:#C8C4BC; font-size:11px;">—</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>

                            {{-- Response — Hasil dari SAP --}}
                            <div style="background:#fff; border:1px solid {{ $isSuccess ? '#BBF7D0' : '#FED7AA' }};
                                        border-radius:4px; padding:16px;">
                                <div style="font-size:10px; font-weight:700; letter-spacing:.12em;
                                            text-transform:uppercase; color:#9A9590; margin-bottom:12px;
                                            padding-bottom:8px; border-bottom:1px solid #E8E4DC;">
                                    {{ $isSuccess ? '✅ Hasil dari SAP' : '❌ Keterangan Error' }}
                                </div>

                                @if($isSuccess)
                                <div style="text-align:center; padding:20px 0;">
                                    <div style="font-size:36px; margin-bottom:8px;">✅</div>
                                    <div style="font-size:14px; font-weight:700; color:#15803D;">Berhasil Dikirim</div>
                                    <div style="font-size:12px; color:#9A9590; margin-top:4px;">
                                        Data sudah masuk ke sistem SAP
                                    </div>
                                </div>
                                @else
                                <div style="background:#FEF2F2; border-radius:3px; padding:14px;">
                                    <div style="font-size:11px; font-weight:700; color:#B91C1C; margin-bottom:6px;">
                                        ⚠️ Pesan Error dari SAP:
                                    </div>
                                    <div style="font-size:13px; color:#7F1D1D; line-height:1.6; font-weight:500;">
                                        @php
                                            $cleanMessage = $log['error_msg'];
                                            if (str_contains($cleanMessage, '"message"')) {
                                                $decoded = json_decode(substr($cleanMessage, strpos($cleanMessage, '{')), true);
                                                $cleanMessage = $decoded['message'] ?? $cleanMessage;
                                            }
                                        @endphp
                                        {{ $cleanMessage }}
                                    </div>
                                </div>

                                <div style="margin-top:12px; background:#FFFBF0; border-radius:3px; padding:10px 14px;
                                            border-left:3px solid #F59E0B;">
                                    <div style="font-size:11px; font-weight:700; color:#B45309; margin-bottom:4px;">
                                        💡 Yang perlu dilakukan:
                                    </div>
                                    <div style="font-size:12px; color:#78350F; line-height:1.5;">
                                        @if(str_contains($log['error_msg'], 'melebihi'))
                                            Jumlah yang dikirim melebihi qty SPK. Periksa kembali jumlah produksi untuk SPK <strong>{{ $log['spk_code'] }}</strong>.
                                        @elseif(str_contains($log['error_msg'], 'not found') || str_contains($log['error_msg'], 'tidak ditemukan'))
                                            SPK <strong>{{ $log['spk_code'] }}</strong> tidak ditemukan di SAP. Pastikan nomor SPK sudah benar.
                                        @else
                                            Hubungi tim IT atau admin SAP dengan menyebutkan SPK <strong>{{ $log['spk_code'] }}</strong> dan waktu error: <strong>{{ $log['created_at'] }}</strong>.
                                        @endif
                                    </div>
                                </div>
                                @endif

                                <div style="margin-top:12px; font-size:10px; color:#C8C4BC; text-align:right;">
                                    Dikirim pada: {{ $log['created_at'] }}
                                </div>
                            </div>

                        </div>
                    </td>
                </tr>
                @endif

                @empty
                <tr>
                    <td colspan="8" style="padding:60px; text-align:center; color:#C8C4BC;
                                        font-size:13px; font-family:'IBM Plex Sans',sans-serif;">
                        <div style="font-size:28px; margin-bottom:8px;">◈</div>
                        No logs found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div style="margin-top:12px; display:flex; justify-content:space-between; align-items:center;">
        <span style="font-size:12px; color:#7A756E; font-family:'IBM Plex Mono',monospace;">
            {{ $this->logs->firstItem() }}–{{ $this->logs->lastItem() }} of {{ $this->logs->total() }}
        </span>
        <div style="font-size:12px;">
            {{ $this->logs->links() }}
        </div>
    </div>

</div>