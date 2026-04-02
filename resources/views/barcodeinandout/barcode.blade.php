<!DOCTYPE html>
<html>
<head>
    <title>Barcodes - Packaging QR</title>
    <style>
        @page {
            size: A4;
            margin: 5mm;
        }

        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .barcode-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* 3 Columns per Row */
            gap: 1.5mm; /* Gap for easy cutting */
            width: 100%;
        }

        .barcode-item {
            border: 1px solid #000;
            height: 30mm; /* Slightly reduced height to fit 9 rows with gaps */
            box-sizing: border-box;
            display: grid;
            grid-template-columns: 1.2fr 1fr 1fr; /* Adjusted column ratios */
            grid-template-rows: auto 1fr auto; 
            overflow: hidden;
            font-size: 7pt;
        }

        /* Common Box Style */
        .box {
            border: 0.5pt solid #000;
            display: flex;
            align-items: center;
            padding: 1mm;
        }

        .box-title {
            grid-column: span 1;
            font-weight: bold;
            font-size: 6.5pt;
            background-color: #f0f0f0;
            justify-content: center;
            text-align: center;
        }

        .box-no {
            grid-column: span 1;
            font-weight: bold;
            font-size: 9pt;
            justify-content: center;
        }

        .box-qr {
            grid-column: span 1;
            grid-row: span 2; /* QR takes height of title and data row */
            justify-content: center;
            padding: 0;
        }

        .box-qr img {
            width: 15mm;
            height: 15mm;
        }

        .box-data {
            grid-column: span 2; /* Spans across Title and NO columns */
            flex-direction: column;
            align-items: flex-start;
            justify-content: center;
        }

        .part-no {
            font-weight: bold;
            font-size: 8.5pt;
            margin-bottom: 0.5mm;
        }

        .part-name {
            font-size: 7pt;
            line-height: 1.1;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .box-footer {
            grid-column: span 3; /* Spans full width */
            font-size: 6pt;
            justify-content: space-between;
            background-color: #f9f9f9;
        }

        @media print {
            .barcode-container {
                page-break-inside: avoid;
            }
            .barcode-item {
                page-break-inside: avoid;
            }
            button {
                display: none;
            }
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #2563eb;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            z-index: 1000;
        }
    </style>
</head>
<body>

    <button class="print-btn" onclick="window.print()">Print A4 Labels</button>

    <div class="barcode-container">
        @foreach ($barcodes as $barcode)
            <div class="barcode-item">
                <!-- Row 1: Title and NO -->
                <div class="box box-title">QR CODE <br>PACKAGING</div>
                <div class="box box-no">NO: {{ $barcode['label'] }}</div>
                
                <!-- QR Code (Right Column, spans Row 1 & 2) -->
                <div class="box box-qr">
                    <img src="{{ $barcode['barcodeUrl'] }}" alt="QR">
                </div>

                <!-- Row 2: Part Data -->
                <div class="box box-data">
                    <div class="part-no">{{ $barcode['partno'] }}</div>
                    <div class="part-name">{{ $barcode['partname'] }}</div>
                </div>

                <!-- Row 3: Footer -->
                <div class="box box-footer">
                    <span>Generated: {{ $generated_at }}</span>
                    <span>Daijo MES</span>
                </div>
            </div>
        @endforeach
    </div>

</body>
</html>
