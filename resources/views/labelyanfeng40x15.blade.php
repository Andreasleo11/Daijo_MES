<!DOCTYPE html>
<html>
<head>
    <title>All Labels</title>
    <meta charset="UTF-8">
    <style>
          @page {
        size: 86mm 18mm;
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
        width: 86mm;
        height: 18mm;
        display: flex;
        padding: 0;
        margin: 0;
        page-break-after: always;
    }

    /* dua label = 40 + 40 + 6 gap = 86 pas 
    
    SETTING DI PRINTER 
    86 
    18

    top 2
    left 3 
    
    
    */
    .label {
        width: 40mm;
        height: 15mm;
        display: flex;
        align-items: center;
        padding: 0;          /* NO PADDING  */
        margin: 0;           /* NO MARGIN   */
    }

    .label + .label {
        margin-left: 6mm;    /* GAP TENGAH */
    }

    .barcode img {
        width: 12mm;
        height: 12mm;
    }

    .info {
        margin-left: 2mm;
    }

    .info div {
        font-size: 1.8mm;
        font-weight: bold;
        white-space: nowrap;
    }

    .seq-line {
        min-height: 2mm;
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


