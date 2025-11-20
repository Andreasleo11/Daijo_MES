<!DOCTYPE html>
<html>
<head>
    <title>All Labels</title>
    <meta charset="UTF-8">
    <style>
        @page {
            size: 82mm 20mm;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: white;
            padding: 0;
        }

        .label-row {
            display: flex;
            width: 82mm;
            height: 20mm;
            justify-content: flex-start;
            padding: 0 1mm;
            page-break-after: always;
            page-break-inside: avoid;
        }

        .label {
            width: 40mm;
            height: 20mm;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            background: #fff;
            padding: 1mm 0.5mm 1mm 1mm;
        }

        .label:first-child {
            margin-right: 2mm;
        }

        .label:nth-child(even) {
            padding-left: 3mm;
        }

        .barcode {
            flex-shrink: 0;
            margin-right: 2mm;
        }

        .barcode img {
            width: 13mm;
            height: 13mm;
            display: block;
        }
  
            display: flex;
            flex-direction: column;
            justify-content: center;
            line-height: 1.3;
        }

        .info div {
            font-size: 2mm;
            margin-bottom: 0.5mm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: bold;
        }

        .project-line {
            font-size: 2mm !important;
            font-weight: bold;
            letter-spacing: 0.05mm;
        }

           .label:nth-child(odd) {
            margin-right: 1mm;
        }

        .seq-line {
                min-height: 1.8mm; /* atur sesuai kebutuhan */
                line-height: 1.2;
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


