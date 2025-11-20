<!DOCTYPE html>
<html>
<head>
    <title>Label Generator - Zebra</title>
    <style>
        /* 🔹 Total ukuran kertas: 82mm x 20mm */
        @page {
            size: 82mm 20mm;
            margin: 0;
        }

        body {
            font-family: monospace;
            margin: 0;
            padding: 0 1mm; /* margin kiri-kanan 1mm */
            display: flex;
            flex-wrap: wrap;
            width: 82mm;
            background: white;
            justify-content: flex-start;
        }

        /* 🔹 Tiap label: 40mm x 20mm */
        .label {
            width: 40mm;
            height: 20mm;
            box-sizing: border-box;
            border: 0.2mm solid #000;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            background: #fff;
            padding: 1mm 0.5mm 1mm 1mm; /* atas, kanan, bawah, kiri */
        }

        /* 🔹 Label kanan (genap) — sedikit geser biar gak nempel pinggir */
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
        }

        .info {
            display: flex;
            flex-direction: column;
            justify-content: center;
            line-height: 1.3;
            font-weight: bold;
        }

        .info div {
            font-size: 2mm;
            margin-bottom: 0.5mm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .project-line {
            font-size: 2mm !important;
            font-weight: bold;
            letter-spacing: 0.1mm;
        }

        /* Hindari break di tengah label */
        .label {
            page-break-inside: avoid;
        }

        /* 🔹 Gap antar label di tengah */
        .label:nth-child(odd) {
            margin-right: 1mm;
        }

        @media print {
            body {
                background: none;
            }
            .label {
                border: none;
            }
        }
    </style>
</head>
<body>
    @foreach($labels as $label)
        <div class="label">
            <div class="barcode">
                <img src="data:image/png;base64,{{ $label['image'] }}" alt="datamatrix">
            </div>
            <div class="info">
                <div>{{ $label['supplierCode'] }}</div>
                <div>{{ $label['sequenceCode'] }}</div>
                <div class="project-line">{{ $label['projectCode'] }}{{ $label['identifier'] }}</div>
                <div>{{ $label['partNumber'] }}</div>
            </div>
        </div>
    @endforeach
</body>
</html>
