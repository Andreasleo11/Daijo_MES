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
        html, body {
            margin: 0;
            padding: 0;
            width: 40mm;
            height: 15mm;
            overflow: hidden;
            box-sizing: border-box;
            background-color: #fff;
        }
        body {
            font-family: 'Arial', sans-serif;
            padding: 0.5mm 1mm;
            display: flex;
            align-items: center;
            justify-content: space-between;
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
        .qty-row {
            font-size: 5.5pt;
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
    <div class="info">
        <div class="info-row">
            <span class="info-label">ITEM:</span>
            <span class="info-val">{{ $log->dailyItemCode->item_code }}</span>
        </div>
        <div class="info-row" style="font-size: 5pt; font-weight: normal; color: #666;">
            {{ $log->dailyItemCode->masterItem->item_name ?? '-' }}
        </div>
        <div class="info-row">
            <span class="info-label">OPR:</span>
            <span class="info-val">{{ $log->operator_name }}</span>
        </div>
        <div class="info-row" style="font-size: 5pt; color: #555; font-weight: normal;">
            {{ $log->logged_at->format('d/m/y H:i:s') }}
        </div>
    </div>
    
    <div class="qr-container">
        <img src="data:image/png;base64,{{ $qrCodeBase64 }}" alt="QR Code">
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
