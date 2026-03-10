<div style="font-family:'IBM Plex Sans',sans-serif; background:#F5F3EF; min-height:100vh; padding:24px;">

    {{-- Header --}}
    <div style="margin-bottom:20px;">
        <div style="font-size:11px; font-weight:700; letter-spacing:.15em; text-transform:uppercase;
                    color:#9A9590; margin-bottom:4px;">Production Summary</div>
        <div style="font-size:22px; font-weight:800; color:#1A1816;">SAP Receipt Monitor</div>
    </div>

    {{-- Stats --}}
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:20px;">
        <div style="background:#fff; border:1px solid #E8E4DC; border-radius:4px; padding:16px;">
            <div style="font-size:10px; font-weight:700; color:#9A9590; letter-spacing:.1em;
                        text-transform:uppercase; margin-bottom:6px;">Total SPK</div>
            <div style="font-size:24px; font-weight:800; color:#1A1816;">{{ number_format($this->stats['total']) }}</div>
        </div>
        <div style="background:#fff; border:1px solid #E8E4DC; border-radius:4px; padding:16px;
                    border-left:3px solid #22C55E;">
            <div style="font-size:10px; font-weight:700; color:#9A9590; letter-spacing:.1em;
                        text-transform:uppercase; margin-bottom:6px;">Terkirim ke SAP</div>
            <div style="font-size:24px; font-weight:800; color:#15803D;">{{ number_format($this->stats['sent']) }}</div>
        </div>
        <div style="background:#fff; border:1px solid #E8E4DC; border-radius:4px; padding:16px;
                    border-left:3px solid #F97316;">
            <div style="font-size:10px; font-weight:700; color:#9A9590; letter-spacing:.1em;
                        text-transform:uppercase; margin-bottom:6px;">Belum Terkirim</div>
            <div style="font-size:24px; font-weight:800; color:#C2410C;">{{ number_format($this->stats['pending']) }}</div>
        </div>
        <div style="background:#fff; border:1px solid #E8E4DC; border-radius:4px; padding:16px;">
            <div style="font-size:10px; font-weight:700; color:#9A9590; letter-spacing:.1em;
                        text-transform:uppercase; margin-bottom:6px;">Total Qty</div>
            <div style="font-size:24px; font-weight:800; color:#1A1816;">{{ number_format($this->stats['total_qty']) }}</div>
        </div>
    </div>

    {{-- Filters --}}
    <div style="background:#fff; border:1px solid #E8E4DC; border-radius:4px; padding:14px 16px;
                margin-bottom:16px; display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
        <div>
            <label style="font-size:10px; font-weight:700; color:#9A9590; display:block;
                          text-transform:uppercase; letter-spacing:.08em; margin-bottom:4px;">Tanggal</label>
            <input type="date" wire:model.live="filterDate"
                   style="border:1px solid #D8D4CC; border-radius:2px; padding:6px 10px;
                          font-size:12px; font-family:'IBM Plex Sans',sans-serif; color:#1A1816;">
        </div>
        <div>
            <label style="font-size:10px; font-weight:700; color:#9A9590; display:block;
                          text-transform:uppercase; letter-spacing:.08em; margin-bottom:4px;">Cari SPK</label>
            <input type="text" wire:model.live.debounce.400ms="filterSpk"
                   placeholder="No. SPK..."
                   style="border:1px solid #D8D4CC; border-radius:2px; padding:6px 10px;
                          font-size:12px; font-family:'IBM Plex Sans',sans-serif; color:#1A1816; width:160px;">
        </div>
        <div>
            <label style="font-size:10px; font-weight:700; color:#9A9590; display:block;
                          text-transform:uppercase; letter-spacing:.08em; margin-bottom:4px;">Status SAP</label>
            <select wire:model.live="filterStatus"
                    style="border:1px solid #D8D4CC; border-radius:2px; padding:6px 10px;
                           font-size:12px; font-family:'IBM Plex Sans',sans-serif; color:#1A1816;">
                <option value="">Semua</option>
                <option value="sent">✓ Terkirim</option>
                <option value="pending">✕ Belum</option>
            </select>
        </div>
    </div>

    {{-- Table --}}
    <div style="background:#fff; border:1px solid #E8E4DC; border-radius:4px; overflow:hidden;">
        <table style="width:100%; border-collapse:collapse; font-size:12px;">
            <thead>
                <tr style="background:#1A1816; color:#fff;">
                    <th style="padding:10px 14px; text-align:left; font-size:9px; font-weight:700;
                                letter-spacing:.12em; text-transform:uppercase;">Tanggal</th>
                    <th style="padding:10px 14px; text-align:left; font-size:9px; font-weight:700;
                                letter-spacing:.12em; text-transform:uppercase;">No. SPK</th>
                    <th style="padding:10px 14px; text-align:left; font-size:9px; font-weight:700;
                                letter-spacing:.12em; text-transform:uppercase;">Item Code</th>
                    <th style="padding:10px 14px; text-align:center; font-size:9px; font-weight:700;
                                letter-spacing:.12em; text-transform:uppercase;">Gudang</th>
                    <th style="padding:10px 14px; text-align:right; font-size:9px; font-weight:700;
                                letter-spacing:.12em; text-transform:uppercase;">Qty</th>
                    <th style="padding:10px 14px; text-align:center; font-size:9px; font-weight:700;
                                letter-spacing:.12em; text-transform:uppercase;">Label</th>
                    <th style="padding:10px 14px; text-align:center; font-size:9px; font-weight:700;
                                letter-spacing:.12em; text-transform:uppercase;">Status SAP</th>
                    <th style="padding:10px 14px; text-align:left; font-size:9px; font-weight:700;
                                letter-spacing:.12em; text-transform:uppercase;">Waktu Kirim</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->logs as $i => $row)
                @php
                    $sent = $row->sap_sent == 1;
                    $bg   = $i % 2 === 0 ? '#fff' : '#FAFAF8';
                    $bl   = $sent ? '3px solid #22C55E' : '3px solid #F97316';
                @endphp
                <tr style="border-bottom:1px solid #F0EDE8; background:{{ $bg }}; border-left:{{ $bl }};">
                    <td style="padding:10px 14px; color:#5A554E; font-size:11px;">
                        @php
                            $createdDate = Carbon\Carbon::parse($row->created_date);
                        @endphp
                        <div style="font-weight:600; color:#1A1816; font-size:12px;">
                            {{ $createdDate->format('d M Y') }}
                        </div>
                        <div style="font-size:10px; color:#9A9590; margin-top:1px;">
                            {{ $createdDate->translatedFormat('l') }}
                        </div>
                    </td>
                    <td style="padding:10px 14px; font-weight:700; color:#1A1816;
                               font-family:'IBM Plex Mono',monospace;">
                        {{ $row->spk_code }}
                    </td>
                    <td style="padding:10px 14px; font-size:11px; color:#3D3935;
                                font-family:'IBM Plex Mono',monospace;">
                        {{ $row->item_code ?? '—' }}
                    </td>
                    <td style="padding:10px 14px; text-align:center;">
                        <span style="background:#F5F3EF; color:#5A554E; font-size:10px;
                                     font-weight:700; padding:2px 8px; border-radius:2px;">
                            {{ $row->warehouse }}
                        </span>
                    </td>
                    <td style="padding:10px 14px; text-align:right; font-weight:700;
                               color:#1A1816; font-family:'IBM Plex Mono',monospace;">
                        {{ number_format($row->total_quantity) }}
                    </td>
                    <td style="padding:10px 14px; text-align:center; color:#5A554E;">
                        {{ $row->label }}
                    </td>
                    <td style="padding:10px 14px; text-align:center;">
                        @if($sent)
                        <span style="background:#DCFCE7; color:#15803D; font-size:10px;
                                     font-weight:700; padding:3px 10px; border-radius:20px;">
                            ✓ Terkirim
                        </span>
                        @else
                        <span style="background:#FEE2E2; color:#B91C1C; font-size:10px;
                                     font-weight:700; padding:3px 10px; border-radius:20px;">
                            ✕ Belum
                        </span>
                        @endif
                    </td>
                    <td style="padding:10px 14px; font-size:11px; color:#5A554E;">
                        @if($row->sap_sent_at)
                        @php
                            $sentAt = Carbon\Carbon::parse($row->sap_sent_at)->timezone('Asia/Jakarta');
                        @endphp
                        <div style="font-weight:700; color:#15803D; font-size:13px;">
                            {{ $sentAt->format('H:i') }} <span style="font-size:10px; font-weight:400;">WIB</span>
                        </div>
                        <div style="font-size:10px; color:#9A9590; margin-top:2px;">
                            {{ $sentAt->format('d M Y') }}
                        </div>
                        @else
                        <div style="display:flex; flex-direction:column; gap:2px;">
                            <span style="background:#FEF3C7; color:#B45309; font-size:10px;
                                        font-weight:700; padding:2px 8px; border-radius:2px;
                                        display:inline-block; width:fit-content;">
                                ⏳ Menunggu
                            </span>
                            <span style="font-size:10px; color:#C8C4BC;">Belum dikirim ke SAP</span>
                        </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="padding:60px; text-align:center; color:#C8C4BC;">
                        <div style="font-size:28px; margin-bottom:8px;">◈</div>
                        Tidak ada data
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        <div style="padding:12px 16px; border-top:1px solid #E8E4DC;">
            {{ $this->logs->links() }}
        </div>
    </div>
</div>