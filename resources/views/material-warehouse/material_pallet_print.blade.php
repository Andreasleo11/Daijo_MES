<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Print Label QR Pallet - {{ $pallet->pallet_id }}</title>
    <style>
        @page {
            size: A6 landscape;
            margin: 4mm;
        }
        body {
            font-family: 'Courier New', Courier, monospace, sans-serif;
            margin: 0;
            padding: 8px;
            color: #000;
            background: #fff;
        }
        .label-card {
            border: 3px solid #000;
            border-radius: 8px;
            padding: 10px;
            max-width: 155mm;
            margin: 0 auto;
            box-sizing: border-box;
        }
        .header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 5px;
            margin-bottom: 8px;
        }
        .header h2 {
            margin: 0;
            font-size: 15px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .header p {
            margin: 2px 0 0 0;
            font-size: 9px;
        }
        .content-grid {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }
        .qr-box {
            text-align: center;
            min-width: 95px;
            padding: 4px;
        }
        .qr-box img {
            width: 90px;
            height: 90px;
            display: block;
            margin: 0 auto;
        }
        .qr-title {
            font-size: 8px;
            font-weight: 900;
            margin-bottom: 3px;
            text-transform: uppercase;
        }
        .qr-sub {
            font-size: 8px;
            font-weight: bold;
            margin-top: 3px;
            font-family: monospace;
        }
        .details {
            flex-grow: 1;
            padding: 0 8px;
            border-left: 1px dashed #ccc;
            border-right: 1px dashed #ccc;
        }
        .details table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        .details td {
            padding: 2px 0;
            vertical-align: top;
        }
        .details .label-title {
            font-weight: bold;
            font-size: 9px;
            color: #333;
            text-transform: uppercase;
        }
        .details .value {
            font-size: 12px;
            font-weight: 900;
        }
        .footer {
            border-top: 1px solid #000;
            margin-top: 8px;
            padding-top: 4px;
            font-size: 8px;
            display: flex;
            justify-content: space-between;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 12px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #059669; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            🖨️ Cetak Label Sekarang
        </button>
    </div>

    <div class="label-card">
        <div class="header">
            <h2>DAIJO MES — MATERIAL PALLET LABEL</h2>
            <p>PT. DAIJO INDUSTRIAL — MATERIAL WAREHOUSE</p>
        </div>

        @php
            $writer = new \Endroid\QrCode\Writer\PngWriter();

            // 1. Plain Text QR (Left Side) — For Barcode Scanner Gun / Outgoing
            $qrText = new \Endroid\QrCode\QrCode(
                data: $pallet->pallet_id,
                errorCorrectionLevel: \Endroid\QrCode\ErrorCorrectionLevel::Low,
                size: 90,
                margin: 0
            );
            $qrTextBase64 = base64_encode($writer->write($qrText)->getString());

            // 2. Full Web URL QR (Right Side) — For Native HP Camera Live Lookup
            $qrUrl = url('/material-warehouse/qr-lookup?pallet_id=' . $pallet->pallet_id);
            $qrUrlObj = new \Endroid\QrCode\QrCode(
                data: $qrUrl,
                errorCorrectionLevel: \Endroid\QrCode\ErrorCorrectionLevel::Low,
                size: 90,
                margin: 0
            );
            $qrUrlBase64 = base64_encode($writer->write($qrUrlObj)->getString());
        @endphp

        <div class="content-grid">
            <!-- Left Side: QR 1 for Barcode Scanner Gun -->
            <div class="qr-box">
                <div class="qr-title" style="color: #1e293b;">SCANNER / OUTGOING</div>
                <img src="data:image/png;base64,{{ $qrTextBase64 }}" alt="QR Outgoing">
                <div class="qr-sub">{{ $pallet->pallet_id }}</div>
            </div>

            <!-- Middle: Details Table -->
            <div class="details">
                <table>
                    <tr>
                        <td class="label-title" style="width: 38%;">PALLET ID:</td>
                        <td class="value" style="font-size: 14px; font-family: monospace; color: #065f46;">{{ $pallet->pallet_id }}</td>
                    </tr>
                    <tr>
                        <td class="label-title">PART CODE:</td>
                        <td class="value" style="font-family: monospace;">{{ $pallet->item_code }}</td>
                    </tr>
                    <tr>
                        <td class="label-title">DESKRIPSI:</td>
                        <td style="font-size: 10px;">{{ $pallet->material ? $pallet->material->item_description : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label-title">INITIAL QTY:</td>
                        <td class="value">{{ number_format($pallet->initial_qty, 2) }} KG</td>
                    </tr>
                    <tr>
                        <td class="label-title">SLOT RAK:</td>
                        <td class="value" style="font-family: monospace; text-decoration: underline;">
                            {{ $pallet->position ? $pallet->position->position_code : 'UNASSIGNED' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="label-title">LOT / BATCH:</td>
                        <td>{{ $pallet->lot_no ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label-title">SUPPLIER / PO:</td>
                        <td style="font-size: 9px;">{{ $pallet->incomingHeader ? ($pallet->incomingHeader->supplier_name ?: '-') : '-' }} / {{ $pallet->incomingHeader ? ($pallet->incomingHeader->po_number ?: '-') : '-' }}</td>
                    </tr>
                </table>
            </div>

            <!-- Right Side: QR 2 for Native HP Camera Live Lookup -->
            <div class="qr-box">
                <div class="qr-title" style="color: #059669;">LIVE CHECK (HP)</div>
                <img src="data:image/png;base64,{{ $qrUrlBase64 }}" alt="QR Live">
                <div class="qr-sub" style="color: #059669;">SCAN VIA HP</div>
            </div>
        </div>

        <div class="footer">
            <span>Tgl Masuk: {{ $pallet->created_at ? $pallet->created_at->format('Y-m-d H:i') : date('Y-m-d H:i') }}</span>
            <span>Printed by Daijo MES WMS</span>
        </div>
    </div>

</body>
</html>
