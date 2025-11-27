<!DOCTYPE html>
<html>
<head>

  <!-- SETTING DI PRINTER 
    106
    35

    left 3  -->
    <title>All Labels 50x35</title>
    <meta charset="UTF-8">
    <style>
        @page {
            size: 106mm 35mm; /* 50 + 50 + 6 gap = 106 */
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: white;
        }

        /* Satu baris = 2 label */
        .label-row {
            width: 106mm;  /* total 2 label + gap */
            height: 35mm;
            display: flex;
            padding: 0;
            margin: 0;
            page-break-after: always;
        }

        /* dua label = 50 + 50 + 6 gap = 106 pas */
        .label {
            width: 50mm;
            height: 35mm;
            display: flex;
            align-items: center;
            padding: 0; 
            margin: 0;
        }

        .label + .label {
            margin-left: 6mm;    /* GAP TENGAH */
        }

        .barcode img {
            width: 22mm;
            height: 22mm;
        }

        .info {
            margin-left: 3mm;
        }

        .info div {
            font-size: 2mm;
            font-weight: bold;
            white-space: nowrap;
        }

        .seq-line {
            min-height: 3mm;
        }

        @media print {
            body {
                background: white;
            }
            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    @php
        $labelPairs = array_chunk($labels, 2);
    @endphp

    @foreach($labelPairs as $pair)
        <div class="label-row">
            {{-- Label Kiri --}}
            @if(isset($pair[0]))
                <div class="label">
                    <div class="barcode">
                        <img src="data:image/png;base64,{{ $pair[0]['image'] }}" alt="datamatrix">
                    </div>
                    <div class="info">
                        <div>{{ $pair[0]['supplierCode'] }}</div>
                        <div class="seq-line">{{ $pair[0]['sequenceCode'] ?? '&nbsp;'}}</div>
                        <div class="project-line">{{ $pair[0]['projectCode'] }}{{ $pair[0]['identifier'] }}</div>
                        <div>{{ $pair[0]['partNumber'] }}</div>
                    </div>
                </div>
            @endif

            {{-- Label Kanan --}}
            @if(isset($pair[1]))
                <div class="label">
                    <div class="barcode">
                        <img src="data:image/png;base64,{{ $pair[1]['image'] }}" alt="datamatrix">
                    </div>
                    <div class="info">
                        <div>{{ $pair[1]['supplierCode'] }}</div>
                        <div class="seq-line">{{ $pair[1]['sequenceCode'] ?? '&nbsp;'}}</div>
                        <div class="project-line">{{ $pair[1]['projectCode'] }}{{ $pair[1]['identifier'] }}</div>
                        <div>{{ $pair[1]['partNumber'] }}</div>
                    </div>
                </div>
            @else
                <div class="label" style="visibility: hidden;"></div>
            @endif
        </div>
    @endforeach
</body>
</html>
