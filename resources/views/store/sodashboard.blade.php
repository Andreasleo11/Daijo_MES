<x-app-layout>
    <div style="font-family:'IBM Plex Sans',sans-serif; background:#F5F3EF; min-height:100vh; padding:24px;">
        
        {{-- Header Area with Date Picker --}}
        <div style="margin-bottom:32px; display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:20px;">
            <div>
                <h1 style="font-size:24px; font-weight:800; color:#1A1816; margin:0 0 4px 0; font-family:'IBM Plex Mono',monospace; letter-spacing:-0.5px;">
                    SCANNING DASHBOARD <span style="font-weight:400; color:#A8A29E;">/ STORE</span>
                </h1>
                <p style="font-size:13px; color:#7A756E; margin:0;">
                    Monitoring progress SO scanning tanggal <strong>{{ Carbon\Carbon::parse($selectedDate)->format('d M Y') }}</strong>
                </p>
            </div>
            
            <form action="{{ route('so.dashboard') }}" method="GET" style="display:flex; align-items:center; gap:8px; background:#fff; padding:6px 12px; border:1px solid #D8D4CC; border-radius:6px; box-shadow:0 2px 4px rgba(0,0,0,0.02);">
                <label for="date" style="font-size:11px; font-weight:700; color:#7A756E; text-transform:uppercase;">Select Date:</label>
                <input type="date" id="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()" 
                    style="border:none; font-size:13px; font-weight:600; color:#1A1816; outline:none; cursor:pointer;">
            </form>
        </div>

        {{-- Weekly Highlights Section --}}
        <h2 style="font-size:11px; font-weight:700; color:#A8A29E; margin:0 0 16px 0; text-transform:uppercase; letter-spacing:0.1em;">Weekly Highlights (Monday - Sunday)</h2>
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:20px; margin-bottom:40px;">
            
            <div style="background: linear-gradient(135deg, #1A1816 0%, #333 100%); padding:20px; border-radius:8px; color:#fff; position:relative; overflow:hidden;">
                <span style="font-size:10px; font-weight:600; opacity:0.6; text-transform:uppercase; letter-spacing:0.05em;">Total Scans This Week</span>
                <div style="font-size:32px; font-weight:800; margin-top:8px; font-family:'IBM Plex Mono',monospace;">{{ number_format($weeklyTotalScans) }}</div>
                <div style="position:absolute; right:-10px; bottom:-10px; opacity:0.1;">
                    <svg width="80" height="80" fill="currentColor" viewBox="0 0 24 24"><path d="M3 13.125V21h18v-7.875L12 18.75 3 13.125ZM12 3 3 8.625l9 5.625 9-5.625L12 3Z"/></svg>
                </div>
            </div>

            <div style="background:#fff; padding:20px; border:1px solid #D8D4CC; border-radius:8px; position:relative;">
                <span style="font-size:10px; font-weight:700; color:#A8A29E; text-transform:uppercase; letter-spacing:0.05em;">Completed SO This Week</span>
                <div style="font-size:32px; font-weight:800; color:#059669; margin-top:8px; font-family:'IBM Plex Mono',monospace;">{{ number_format($weeklyFinishedItems) }}</div>
                <div style="font-size:11px; color:#7A756E; margin-top:4px;">Items marked as finish</div>
            </div>

            <div style="background:#fff; padding:20px; border:1px solid #D8D4CC; border-radius:8px;">
                <span style="font-size:10px; font-weight:700; color:#A8A29E; text-transform:uppercase; letter-spacing:0.05em;">Active Documents (Week)</span>
                <div style="font-size:32px; font-weight:800; color:#1A1816; margin-top:8px; font-family:'IBM Plex Mono',monospace;">{{ number_format($weeklyActiveDocsCount) }}</div>
                <div style="font-size:11px; color:#7A756E; margin-top:4px;">Unique SO tracked</div>
            </div>

        </div>

        {{-- Main Content --}}
        <div style="display:grid; grid-template-columns: 2fr 1fr; gap:24px;">
            
            {{-- Left Column: active SO Progress --}}
            <div>
                <div style="background:#fff; border:1px solid #D8D4CC; border-radius:8px; overflow:hidden; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05);">
                    <div style="background:#1A1816; color:#fff; padding:12px 16px; display:flex; justify-content:space-between; align-items:center;">
                        <h2 style="font-size:12px; font-weight:700; margin:0; letter-spacing:0.05em;">SO PROGRESS (SELECTED DATE)</h2>
                        <span style="font-size:10px; opacity:0.7;">TOTAL: {{ count($soProgress) }} SO</span>
                    </div>
                    
                    <div style="padding:20px;">
                        @forelse($soProgress as $docNum => $data)
                        <div style="margin-bottom:24px; border-bottom:1px solid #F3F1ED; padding-bottom:20px;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
                                <div>
                                    <h3 style="font-size:15px; font-weight:800; color:#1A1816; margin:0;">{{ $docNum }}</h3>
                                    <span style="font-size:12px; color:#7A756E;">{{ $data['customer'] }}</span>
                                </div>
                                <div style="text-align:right;">
                                    <span style="font-size:14px; font-weight:800; color:#1A1816; font-family:'IBM Plex Mono',monospace;">{{ $data['progress'] }}%</span>
                                    <div style="font-size:10px; color:#A8A29E; margin-top:2px;">{{ $data['finished_items'] }}/{{ $data['total_items'] }} Items Finished</div>
                                </div>
                            </div>

                            {{-- Progress Bar --}}
                            <div style="width:100%; height:8px; background:#F3F1ED; border-radius:4px; overflow:hidden; margin-bottom:10px;">
                                <div style="height:100%; width:{{ $data['progress'] }}%; background:{{ $data['progress'] == 100 ? '#059669' : '#2563EB' }}; transition: width 0.5s ease;"></div>
                            </div>

                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <a href="{{ route('so.process', $docNum) }}" style="font-size:11px; font-weight:700; color:#2563EB; text-decoration:none; text-transform:uppercase; letter-spacing:0.02em;">View Details →</a>
                                <span style="font-size:10px; color:#A8A29E;">Last activity: {{ $data['last_scan'] ? $data['last_scan']->format('H:i') : '—' }}</span>
                            </div>
                        </div>
                        @empty
                        <div style="padding:60px 40px; text-align:center; color:#A8A29E;">
                            <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-bottom:12px; opacity:0.3;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <p style="margin:0; font-size:14px; font-weight:500;">Tidak ada aktivitas scanning pada tanggal {{ Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Right Column: Activity Timeline --}}
            <div>
                <div style="background:#fff; border:1px solid #D8D4CC; border-radius:8px; overflow:hidden; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05);">
                    <div style="background:#F9F8F6; border-bottom:1px solid #D8D4CC; padding:12px 16px;">
                        <h2 style="font-size:12px; font-weight:700; color:#1A1816; margin:0; letter-spacing:0.05em;">ACTIVITY TIMELINE</h2>
                    </div>

                    <div style="padding:20px; max-height:800px; overflow-y:auto;">
                        <h3 style="font-size:10px; font-weight:700; color:#A8A29E; margin:0 0 20px 0; text-transform:uppercase; letter-spacing:0.1em;">
                            Log Scan: {{ Carbon\Carbon::parse($selectedDate)->format('d M') }}
                        </h3>
                        
                        @forelse($selectedDateScans as $scan)
                        <div style="display:flex; gap:16px; margin-bottom:20px; position:relative;">
                            <div style="flex:0 0 50px; font-size:10px; font-weight:700; color:#7A756E; font-family:'IBM Plex Mono',monospace; padding-top:2px;">
                                {{ $scan->created_at->format('H:i') }}
                            </div>
                            <div style="flex:1; border-left:2px solid #F3F1ED; padding-left:16px; padding-bottom:4px;">
                                <div style="font-size:13px; font-weight:700; color:#1A1816; margin-bottom:2px;">{{ $scan->item_code }}</div>
                                <div style="font-size:11px; color:#7A756E;">Box #{{ $scan->label }} — SO: {{ $scan->doc_num }}</div>
                            </div>
                        </div>
                        @empty
                        <p style="font-size:12px; color:#A8A29E; text-align:center; padding:20px 0;">Belum ada riwayat scan pada tanggal ini.</p>
                        @endforelse
                    </div>
                </div>
                
                {{-- Legend or Tip --}}
                <div style="margin-top:20px; background:#FEFCE8; border:1px solid #FEF08A; padding:16px; border-radius:8px;">
                    <h4 style="font-size:11px; font-weight:700; color:#854D0E; margin:0 0 4px 0;">TIP</h4>
                    <p style="font-size:11px; color:#A16207; margin:0; line-height:1.5;">Gunakan filter tanggal di kanan atas untuk melihat histori scanning pada hari-hari sebelumnya.</p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
