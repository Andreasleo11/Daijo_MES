<x-app-layout>
    <div style="font-family:'IBM Plex Sans',sans-serif; background:#F5F3EF; min-height:100vh; padding:24px;">
        
        {{-- Header Area --}}
        <div style="margin-bottom:28px; display:flex; justify-content:space-between; align-items:flex-end;">
            <div>
                <h1 style="font-size:24px; font-weight:800; color:#1A1816; margin:0 0 4px 0; font-family:'IBM Plex Mono',monospace; letter-spacing:-0.5px;">
                    SCANNING DASHBOARD <span style="font-weight:400; color:#A8A29E;">/ STORE</span>
                </h1>
                <p style="font-size:13px; color:#7A756E; margin:0;">Monitoring progress SO scanning hari ini dan kemarin.</p>
            </div>
            <div style="display:flex; gap:12px;">
                <div style="background:#fff; padding:12px 20px; border:1px solid #D8D4CC; border-radius:4px; text-align:right;">
                    <span style="display:block; font-size:10px; font-weight:700; color:#A8A29E; text-transform:uppercase; letter-spacing:0.05em;">Total Scan Today</span>
                    <span style="font-size:18px; font-weight:800; color:#1A1816; font-family:'IBM Plex Mono',monospace;">{{ count($todayScans) }}</span>
                </div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: 2fr 1fr; gap:24px;">
            
            {{-- Left Column: active SO Progress --}}
            <div>
                <div style="background:#fff; border:1px solid #D8D4CC; border-radius:4px; overflow:hidden; margin-bottom:24px;">
                    <div style="background:#1A1816; color:#fff; padding:12px 16px; display:flex; justify-content:space-between; align-items:center;">
                        <h2 style="font-size:12px; font-weight:700; margin:0; letter-spacing:0.05em;">ACTIVE SO PROGRESS</h2>
                        <span style="font-size:10px; opacity:0.7;">TOTAL: {{ count($soProgress) }} SO</span>
                    </div>
                    
                    <div style="padding:16px;">
                        @forelse($soProgress as $docNum => $data)
                        <div style="margin-bottom:20px; border-bottom:1px solid #F3F1ED; padding-bottom:16px;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                                <div>
                                    <h3 style="font-size:14px; font-weight:800; color:#1A1816; margin:0;">{{ $docNum }}</h3>
                                    <span style="font-size:11px; color:#7A756E;">{{ $data['customer'] }}</span>
                                </div>
                                <div style="text-align:right;">
                                    <span style="font-size:13px; font-weight:800; color:#1A1816; font-family:'IBM Plex Mono',monospace;">{{ $data['progress'] }}%</span>
                                    <div style="font-size:9px; color:#A8A29E; margin-top:2px;">{{ $data['finished_items'] }}/{{ $data['total_items'] }} Items Finished</div>
                                </div>
                            </div>

                            {{-- Progress Bar --}}
                            <div style="width:100%; height:8px; background:#F3F1ED; border-radius:4px; overflow:hidden; margin-bottom:8px;">
                                <div style="height:100%; width:{{ $data['progress'] }}%; background:{{ $data['progress'] == 100 ? '#059669' : '#2563EB' }}; transition: width 0.5s ease;"></div>
                            </div>

                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <a href="{{ route('so.process', $docNum) }}" style="font-size:10px; font-weight:700; color:#2563EB; text-decoration:none; text-transform:uppercase;">View Details →</a>
                                <span style="font-size:9px; color:#A8A29E;">Last scan: {{ $data['last_scan'] ? $data['last_scan']->diffForHumans() : '—' }}</span>
                            </div>
                        </div>
                        @empty
                        <div style="padding:40px; text-align:center; color:#A8A29E;">
                            <p style="margin:0; font-size:14px;">Tidak ada aktivitas scanning terakhir.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Right Column: Recent Activity Timeline --}}
            <div>
                <div style="background:#fff; border:1px solid #D8D4CC; border-radius:4px; overflow:hidden;">
                    <div style="background:#F9F8F6; border-bottom:1px solid #D8D4CC; padding:12px 16px;">
                        <h2 style="font-size:12px; font-weight:700; color:#1A1816; margin:0; letter-spacing:0.05em;">RECENT ACTIVITY</h2>
                    </div>

                    <div style="padding:0;">
                        {{-- Today Section --}}
                        <div style="padding:16px 16px 8px 16px;">
                            <h3 style="font-size:10px; font-weight:700; color:#A8A29E; margin:0 0 12px 0; text-transform:uppercase; letter-spacing:0.05em;">TODAY</h3>
                            @forelse($todayScans->take(10) as $scan)
                            <div style="display:flex; gap:12px; margin-bottom:12px;">
                                <div style="flex:0 0 40px; font-size:9px; color:#7A756E; padding-top:2px; font-family:'IBM Plex Mono',monospace;">{{ $scan->created_at->format('H:i') }}</div>
                                <div style="flex:1;">
                                    <div style="font-size:12px; font-weight:700; color:#1A1816; margin-bottom:2px;">{{ $scan->item_code }}</div>
                                    <div style="font-size:10px; color:#7A756E;">Box #{{ $scan->label }} — SO: {{ $scan->doc_num }}</div>
                                </div>
                            </div>
                            @empty
                            <p style="font-size:11px; color:#A8A29E; margin:0 0 12px 0;">Belum ada scan hari ini.</p>
                            @endforelse
                        </div>

                        <div style="height:1px; background:#F3F1ED; margin:0 16px;"></div>

                        {{-- Yesterday Section --}}
                        <div style="padding:16px;">
                            <h3 style="font-size:10px; font-weight:700; color:#A8A29E; margin:0 0 12px 0; text-transform:uppercase; letter-spacing:0.05em;">YESTERDAY</h3>
                            @forelse($yesterdayScans->take(5) as $scan)
                            <div style="display:flex; gap:12px; margin-bottom:12px; opacity:0.8;">
                                <div style="flex:0 0 40px; font-size:9px; color:#7A756E; padding-top:2px; font-family:'IBM Plex Mono',monospace;">{{ $scan->created_at->format('H:i') }}</div>
                                <div style="flex:1;">
                                    <div style="font-size:12px; font-weight:600; color:#1A1816; margin-bottom:2px;">{{ $scan->item_code }}</div>
                                    <div style="font-size:10px; color:#7A756E;">SO: {{ $scan->doc_num }}</div>
                                </div>
                            </div>
                            @empty
                            <p style="font-size:11px; color:#A8A29E; margin:0;">Tidak ada aktivitas kemarin.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
