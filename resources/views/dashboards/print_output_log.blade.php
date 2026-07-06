<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Print Label Output #{{ $log->id }}</title>

    <style>
        @page {
            size: 30mm 20mm;
            margin: 0;
        }

        html,
        body {
            width: 30mm;
            height: 20mm;
            margin: 0;
            padding: 0;
            background: #fff;
            font-family: Arial, sans-serif;
        }

        body {
            overflow: hidden;
        }

        .label {
            width: 30mm;
            height: 20mm;
            box-sizing: border-box;

            padding-top: 7mm;
            padding-right: 0.5mm;
            padding-bottom: 0.5mm;
            padding-left: 0.5mm;

            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: flex-start;

            overflow: hidden;
        }

        .label:not(:last-child) {
            page-break-after: always;
        }

        .info {
            width: 16mm;
            height: 17mm;

            display: flex;
            flex-direction: column;
            justify-content: flex-start;

            overflow: hidden;
        }

        .item-code {
            font-size: 5pt;
            font-weight: bold;
            line-height: 1.1;
            margin-bottom: 0.4mm;

            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .item-name {
            font-size: 4pt;
            line-height: 1.1;

            height: 5mm;
            margin-bottom: 0.4mm;

            overflow: hidden;
            word-break: break-word;
        }

        .operator {
            font-size: 4pt;
            line-height: 1.1;

            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .datetime {
            font-size: 3.8pt;
            line-height: 1.1;
            margin-top: 0.3mm;
        }

        .qr-container {
            width: 15mm;
            height: 15mm;

            display: flex;
            justify-content: center;
            align-items: center;

            flex-shrink: 0;
        }

        .qr-container img {
            width: 12mm;
            height: 12mm;
            display: block;
        }
    </style>
</head>

<body>

@foreach($barcodes as $barcode)

<div class="label">

    <div class="info">

        <div class="item-code">
            {{ $barcode['item_code'] }}
        </div>

        <div class="item-name">
            {{ $log->dailyItemCode->masterItem->item_name ?? '-' }}
        </div>

        <div class="operator">
            {{ $log->operator_name }}
        </div>

        <div class="datetime">
            {{ $log->logged_at->format('d/m/y H:i') }}
        </div>

    </div>

    <div class="qr-container">
        <img
            src="data:image/png;base64,{{ $barcode['qrCodeBase64'] }}"
            alt="QR Code">
    </div>

</div>

@endforeach

<script>
window.onload = function () {
    setTimeout(function () {
        window.print();
    }, 500);
};
</script>

</body>
</html>