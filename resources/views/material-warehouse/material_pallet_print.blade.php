<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Print Label QR Pallet - {{ $pallet->pallet_id }}</title>
    <style>
        @page {
            size: 100mm 150mm;
            margin: 0;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        html, body {
            margin: 0;
            padding: 0;
            background: #fff;
            font-family: 'Courier New', Courier, monospace, sans-serif;
            color: #000;
            width: 100mm;
            height: 150mm;
            overflow: hidden;
        }

        /* Kertas 100mm(lebar) x 150mm(panjang) portrait.
           Label 135mm lebar di-rotate 90° → muat di 150mm panjang kertas.
           Tinggi label ~80mm → muat di 100mm lebar kertas. */
        .page-wrapper {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(90deg);
            transform-origin: center center;
            width: 135mm;
        }

        .label-card {
            border: 2px solid #000;
            border-radius: 6px;
            padding: 8px 10px;
            width: 135mm;
            background: #fff;
        }
        .header {
            text-align: center;
            border-bottom: 1.5px dashed #000;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }
        .header h2 {
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .header p {
            font-size: 7px;
            font-weight: bold;
            margin-top: 1px;
        }
        .content-grid {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: flex-start;
            gap: 6px;
        }
        .qr-box {
            text-align: center;
            width: 70px;
            min-width: 70px;
            flex-shrink: 0;
        }
        .qr-box img {
            width: 62px;
            height: 62px;
            display: block;
            margin: 0 auto;
        }
        .qr-title {
            font-size: 6.5px;
            font-weight: 900;
            margin-bottom: 1px;
            text-transform: uppercase;
        }
        .qr-sub {
            font-size: 6px;
            font-weight: bold;
            margin-top: 1px;
            font-family: monospace;
            word-break: break-all;
        }
        .details {
            flex-grow: 1;
            padding: 0 6px;
            border-left: 1px dashed #000;
            border-right: 1px dashed #000;
            min-width: 0;
        }
        .details table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
        }
        .details td {
            padding: 1.5px 0;
            vertical-align: top;
            word-break: break-word;
        }
        .details .lbl {
            font-weight: bold;
            font-size: 7px;
            color: #000;
            text-transform: uppercase;
            width: 30%;
            white-space: nowrap;
        }
        .details .val {
            font-size: 10px;
            font-weight: 900;
        }
        .footer {
            border-top: 1px solid #000;
            margin-top: 4px;
            padding-top: 2px;
            font-size: 7px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
                width: 100mm !important;
                height: 150mm !important;
            }
            .page-wrapper {
                position: absolute !important;
                top: 50% !important;
                left: 50% !important;
                transform: translate(-50%, -50%) rotate(90deg) !important;
                width: 135mm !important;
            }
            .label-card {
                width: 135mm !important;
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>
<body>

    <div class="no-print" style="position: fixed; top: 12px; right: 12px; z-index: 9999;">
        <button onclick="window.print()" style="padding: 10px 18px; background: #059669; color: white; border: none; border-radius: 8px; font-weight: bold; font-size: 12px; cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            🖨️ Cetak Label (Portrait → Sideways)
        </button>
    </div>

    <div class="page-wrapper">
        <div class="label-card">
            <div class="header">
                <h2>DAIJO MES — MATERIAL PALLET LABEL</h2>
                <p>PT. DAIJO INDUSTRIAL — MATERIAL WAREHOUSE</p>
            </div>

        @php
            $writer = new \Endroid\QrCode\Writer\PngWriter();

            $qrText = new \Endroid\QrCode\QrCode(
                data: $pallet->pallet_id,
                errorCorrectionLevel: \Endroid\QrCode\ErrorCorrectionLevel::Low,
                size: 80,
                margin: 0
            );
            $qrTextBase64 = base64_encode($writer->write($qrText)->getString());

            $qrUrl = \Illuminate\Support\Facades\URL::signedRoute('mwh.public-pallet-lookup', ['palletId' => $pallet->pallet_id]);
            $qrUrlObj = new \Endroid\QrCode\QrCode(
                data: $qrUrl,
                errorCorrectionLevel: \Endroid\QrCode\ErrorCorrectionLevel::Low,
                size: 80,
                margin: 0
            );
            $qrUrlBase64 = base64_encode($writer->write($qrUrlObj)->getString());
        @endphp

        <div class="content-grid">
            <div class="qr-box">
                <div class="qr-title" style="color: #1e293b;">SCANNER / OUTGOING</div>
                <img src="data:image/png;base64,{{ $qrTextBase64 }}" alt="QR Outgoing">
                <div class="qr-sub">{{ $pallet->pallet_id }}</div>
            </div>

            <div class="details">
                <table>
                    <tr>
                        <td class="lbl">PALLET ID:</td>
                        <td class="val" style="font-family: monospace; color: #065f46;">{{ $pallet->pallet_id }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">PART CODE:</td>
                        <td class="val" style="font-family: monospace;">{{ $pallet->item_code }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">DESKRIPSI:</td>
                        <td style="font-size: 8.5px;">{{ $pallet->material ? $pallet->material->item_description : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">INITIAL QTY:</td>
                        <td class="val">{{ number_format($pallet->initial_qty, 2) }} KG</td>
                    </tr>
                    <tr>
                        <td class="lbl">SLOT RAK:</td>
                        <td class="val" style="font-family: monospace; text-decoration: underline;">
                            {{ $pallet->position ? $pallet->position->position_code : 'UNASSIGNED' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="lbl">LOT / BATCH:</td>
                        <td style="font-size: 8px;">{{ $pallet->lot_no ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">SUPPLIER/PO:</td>
                        <td style="font-size: 7.5px;">{{ $pallet->incomingHeader ? ($pallet->incomingHeader->supplier_name ?: '-') : '-' }} / {{ $pallet->incomingHeader ? ($pallet->incomingHeader->po_number ?: '-') : '-' }}</td>
                    </tr>
                </table>
            </div>

            <div class="qr-box">
                <div class="qr-title" style="color: #059669;">LIVE CHECK (HP)</div>
                <img src="data:image/png;base64,{{ $qrUrlBase64 }}" alt="QR Live">
                <div class="qr-sub" style="color: #059669;">SCAN VIA HP</div>
            </div>
        </div>

        <div class="footer">
            <span>Tgl Masuk: {{ $pallet->created_at ? $pallet->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') : now()->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</span>
            <span>Printed by Daijo MES WMS</span>
        </div>
        </div>
    </div>

</body>
</html>
