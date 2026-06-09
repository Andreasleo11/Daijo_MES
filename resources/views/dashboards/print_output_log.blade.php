<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Print Label Output #{{ $log->id }}</title>

    <style>
        @page {
            size: 40mm 15mm;
            margin: 0;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #fff;
        }

        body {
            font-family: Arial, sans-serif;
        }

        .label {
            width: 40mm;
            height: 15mm;
            padding: 0.5mm 1mm;
            box-sizing: border-box;

            display: flex;
            align-items: center;
            justify-content: space-between;

            overflow: hidden;
        }

        .label:not(:last-child) {
            page-break-after: always;
        }

        .info {
            display: flex;
            flex-direction: column;
            justify-content: center;
            width: 68%;
            height: 100%;
            gap: 0.2mm;
        }

        .info-row {
            font-size: 4.5pt;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: bold;
            color: #000;
        }

        .info-label {
            color: #444;
        }

        .info-val {
            color: #000;
        }

        .qr-container {
            width: 28%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .qr-container img {
            width: 10.5mm;
            height: 10.5mm;
            display: block;
        }
    </style>
</head>

<body>

@foreach($barcodes as $barcode)

<div class="label">

    <div class="info">

        <div class="info-row">
            <span class="info-label">ITEM:</span>
            <span class="info-val">{{ $barcode['item_code'] }}</span>
        </div>

        <div
            class="info-row"
            style="font-size:5pt;font-weight:normal;color:#666;"
        >
            {{ $log->dailyItemCode->masterItem->item_name ?? '-' }}
        </div>

        <div class="info-row">
            <span class="info-label">OPR:</span>
            <span class="info-val">{{ $log->operator_name }}</span>
        </div>

        <div
            class="info-row"
            style="font-size:5pt;font-weight:normal;color:#555;"
        >
            {{ $log->logged_at->format('d/m/y H:i:s') }}
        </div>

    </div>

    <div class="qr-container">
        <img src="data:image/png;base64,{{ $barcode['qrCodeBase64'] }}">
    </div>

</div>

@endforeach

<script>
window.onload = function () {
    window.print();
};
</script>

</body>
</html>