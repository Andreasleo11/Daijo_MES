<div style="font-family:'IBM Plex Sans',sans-serif; background:#F5F3EF; min-height:100vh; padding:24px;">

    {{-- Header --}}
    <div style="margin-bottom:24px;">
        <div style="font-size:11px; font-weight:700; letter-spacing:.15em; text-transform:uppercase;
                    color:#9A9590; margin-bottom:4px;">System</div>
        <div style="font-size:22px; font-weight:800; color:#1A1816;">Manual Sync</div>
        <div style="font-size:12px; color:#9A9590; margin-top:4px;">
            Jalankan sync data secara manual tanpa menunggu jadwal otomatis
        </div>
    </div>

    {{-- Cards --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(340px, 1fr)); gap:16px;">

        {{-- Card: Sync  SAP SPK  --}}
        <div style="background:#fff; border:1px solid #E8E4DC; border-radius:4px; overflow:hidden;">

            {{-- Card Header --}}
            <div style="background:#1A1816; padding:14px 18px;">
                <div style="font-size:10px; font-weight:700; color:#9A9590; letter-spacing:.12em;
                            text-transform:uppercase; margin-bottom:2px;">SAP SPK</div>
                <div style="font-size:15px; font-weight:800; color:#F5F1EB;">
                    Sync SAP SPK
                </div>
            </div>

            {{-- Card Body --}}
            <div style="padding:18px;">
                <div style="font-size:12px; color:#7A756E; line-height:1.6; margin-bottom:16px;">
                    Menjalankan perintah <code style="background:#F5F3EF; padding:1px 5px;
                    border-radius:2px; font-family:'IBM Plex Mono',monospace; font-size:11px;
                    color:#3D3935;">spk:sync</code> untuk mengambil data SPK 
                    terbaru dari sistem.
                </div>

                {{-- Jadwal otomatis info --}}
                <div style="background:#F5F3EF; border-radius:3px; padding:10px 12px;
                            margin-bottom:16px; display:flex; align-items:center; gap:8px;">
                    <span style="font-size:14px;">🕐</span>
                    <div>
                        <div style="font-size:10px; font-weight:700; color:#9A9590;
                                    text-transform:uppercase; letter-spacing:.08em;">Jadwal Otomatis</div>
                        <div style="font-size:12px; color:#5A554E; font-weight:600;">
                            Setiap hari pukul 08:15 WIB , 14:00 WIB dan 16:30 WIB
                        </div>
                    </div>
                </div>

                {{-- Result --}}
                @if($lastResult)
                <div style="margin-bottom:16px; padding:12px; border-radius:3px;
                            background:{{ $isSuccess ? '#DCFCE7' : '#FEE2E2' }};
                            border:1px solid {{ $isSuccess ? '#BBF7D0' : '#FECACA' }};">
                    <div style="font-size:11px; font-weight:700; margin-bottom:3px;
                                color:{{ $isSuccess ? '#15803D' : '#B91C1C' }};">
                        {{ $isSuccess ? '✓ Berhasil' : '✕ Gagal' }}
                    </div>
                    <div style="font-size:11px; color:{{ $isSuccess ? '#166534' : '#7F1D1D' }}; line-height:1.5;">
                        {{ $lastResult }}
                    </div>
                    @if($lastRun)
                    <div style="font-size:10px; color:#9A9590; margin-top:6px;">
                        Dijalankan: {{ $lastRun }}
                    </div>
                    @endif
                </div>
                @endif

                {{-- Button --}}
                <button wire:click="spkSync"
                        wire:loading.attr="disabled"
                        wire:confirm="Jalankan spk Sync sekarang?"
                        @if($isSyncing) disabled @endif
                        style="width:100%; padding:10px; border-radius:3px; font-size:12px;
                               font-weight:700; cursor:pointer; font-family:'IBM Plex Sans',sans-serif;
                               border:none; transition:opacity .2s;
                               background:{{ $isSyncing ? '#E8E4DC' : '#1A1816' }};
                               color:{{ $isSyncing ? '#9A9590' : '#F5F1EB' }};">
                    <span wire:loading.remove wire:target="spkSync">
                        ▶ Jalankan Sekarang
                    </span>
                    <span wire:loading wire:target="spkSync">
                        ⏳ Sedang berjalan...
                    </span>
                </button>
            </div>
        </div>

        {{-- Placeholder card buat sync lain nanti --}}
        <div style="background:#fff; border:1px dashed #D8D4CC; border-radius:4px;
                    padding:18px; display:flex; align-items:center; justify-content:center;
                    min-height:200px;">
            <div style="text-align:center; color:#C8C4BC;">
                <div style="font-size:24px; margin-bottom:8px;">＋</div>
                <div style="font-size:12px; font-weight:600;">Sync lainnya</div>
                <div style="font-size:11px; margin-top:4px;">akan ditambahkan di sini</div>
            </div>
        </div>

    </div>
</div>