<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Custom Barcode Labels</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f3f4f6;
            color: #000;
            padding: 10px 0;
        }

        /* Floating action buttons */
        .no-print-bar {
            background-color: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            width: 100%;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
            border-radius: 8px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease-in-out;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-primary {
            background-color: #4f46e5;
            color: #ffffff;
            border: 1px solid #4338ca;
        }

        .btn-primary:hover {
            background-color: #4338ca;
        }

        .btn-secondary {
            background-color: #ffffff;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background-color: #f9fafb;
        }

        .a4-sheet-container {
            width: 200mm;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .a4-sheet {
            width: 200mm;
            height: 277mm;
            display: grid;
            grid-template-columns: 97mm 97mm;
            grid-template-rows: repeat(6, 43mm);
            gap: 2mm 4mm;
            padding: 1mm 1mm;
            page-break-after: always;
            box-sizing: border-box;
            overflow: hidden;
            background-color: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
            border: 1px solid #e5e7eb;
        }

        .a4-sheet:last-child {
            page-break-after: auto;
            margin-bottom: 0;
        }

        /* Single Sticker Label styling */
        .sticker-label {
            width: 97mm;
            height: 43mm;
            border: 1.5px solid #000000;
            border-radius: 2px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-sizing: border-box;
        }

        /* Row 1: Header */
        .label-header {
            height: 7.5mm;
            display: flex;
            border-bottom: 1.5px solid #000000;
            box-sizing: border-box;
            align-items: center;
        }

        .header-left {
            flex: 1;
            display: flex;
            align-items: center;
            padding-left: 2mm;
        }

        .header-right {
            width: 16mm;
            height: 100%;
            border-left: 1.5px solid #000000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11pt;
            font-weight: 900;
            background-color: #fafafa;
        }

        /* Row 2: Item Code & Name */
        .label-item-info {
            height: 9.5mm;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border-bottom: 1.5px solid #000000;
            box-sizing: border-box;
            line-height: 1.15;
            text-align: center;
        }

        .item-code {
            font-size: 12pt;
            font-weight: 900;
            letter-spacing: 0.5px;
        }

        .item-name {
            font-size: 7pt;
            font-weight: 900;
            text-transform: uppercase;
        }

        /* Row 3: Bottom grid section */
        .label-body {
            flex: 1;
            display: flex;
            height: calc(43mm - 17mm);
            box-sizing: border-box;
        }

        .body-details {
            flex: 1;
            border-right: 1.5px solid #000000;
            padding: 1.2mm 2.2mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            font-size: 6.8pt;
            font-weight: 900;
            line-height: 1.2;
        }

        .detail-row {
            display: flex;
            white-space: nowrap;
        }

        .detail-label {
            width: 19mm;
            flex-shrink: 0;
        }

        .detail-divider {
            width: 2.5mm;
            flex-shrink: 0;
        }

        .detail-value {
            flex-grow: 1;
            font-weight: 900;
        }

        .shift-active {
            border: 1px solid #000000;
            border-radius: 50%;
            padding: 0px 3px;
            font-weight: 900;
            display: inline-block;
            line-height: 1;
        }

        .body-qr-container {
            width: 25mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1mm 0;
            flex-shrink: 0;
        }

        .qr-image {
            width: 15mm;
            height: 15mm;
            display: block;
        }

        .rohs-box {
            width: 21mm;
            border: 1.2px solid #000000;
            text-align: center;
            font-size: 5pt;
            font-weight: 900;
            padding: 1px 0;
            margin-top: 1mm;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        /* Print media styles */
        @media print {
            body {
                background-color: #ffffff;
                padding: 0;
                margin: 0;
            }

            .no-print-bar {
                display: none;
            }

            .a4-sheet-container {
                width: 210mm;
                margin: 0;
                padding: 0;
            }

            .a4-sheet {
                box-shadow: none;
                margin-bottom: 0;
                border: none;
                padding: 5mm 6mm 24mm 6mm;
                width: 210mm;
                height: 297mm;
                box-sizing: border-box;
                overflow: hidden;
            }
        }
    </style>
</head>
<body>

    <!-- Print Navigation Bar -->
    <div class="no-print-bar">
        <a href="{{ route('barcode.custom.form') }}" class="btn btn-secondary">
            ⬅️ Back to Form
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            🖨️ Print Now
        </button>
    </div>

    <!-- Sticker Labels Grid container -->
    <div class="a4-sheet-container">
        @php
            $chunks = array_chunk($labels, 12);
        @endphp

        @foreach($chunks as $chunk)
            <div class="a4-sheet">
                @foreach($chunk as $label)
                    <div class="sticker-label">
                        
                        <!-- Row 1: Header -->
                        <div class="label-header">
                            <div class="header-left">
                                <!-- circular dj logo badge -->
                                <svg viewBox="0 0 100 100" style="width: 5mm; height: 5mm; fill: none; stroke: #000000; stroke-width: 8; margin-right: 1.5mm; display: inline-block; vertical-align: middle;">
                                    <circle cx="50" cy="50" r="42"/>
                                    <text x="50" y="65" font-family="'Arial Black', sans-serif" font-weight="900" font-size="45" text-anchor="middle" fill="#000000" stroke="none">dj</text>
                                </svg>
                                <span style="font-size: 8.5pt; font-weight: 900; letter-spacing: 0.2px; text-transform: uppercase;">PT DAIJO INDUSTRIAL</span>
                            </div>
                            <div class="header-right">
                                {{ $label['label_no'] }}
                            </div>
                        </div>

                        <!-- Row 2: Item Info -->
                        <div class="label-item-info">
                            <div class="item-code">{{ $label['item_code'] }}</div>
                            <div class="item-name">{{ $label['item_name'] }}</div>
                        </div>

                        <!-- Row 3: Logistics details & QR -->
                        <div class="label-body">
                            
                            <!-- Left: Form details -->
                            <div class="body-details">
                                <div class="detail-row">
                                    <div class="detail-label">SPK NO</div>
                                    <div class="detail-divider">:</div>
                                    <div class="detail-value" style="display: flex; justify-content: space-between; width: 100%;">
                                        <span style="display: inline-flex; align-items: center; gap: 4px; vertical-align: middle;">
                                            <span>{{ $label['spk_number'] }}</span>
                                            @if(!empty($label['is_trial']))
                                                <span style="color: #dc2626; border: 1px solid #dc2626; font-size: 5.5pt; font-weight: 900; padding: 0 3px; border-radius: 2px; line-height: 1.2;">TRIAL</span>
                                            @endif
                                        </span>
                                        <span style="font-weight: 900; margin-right: 1mm;">FROM : {{ $label['warehouse'] }}</span>
                                    </div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">PROD DATE</div>
                                    <div class="detail-divider">:</div>
                                    <div class="detail-value">{{ $label['prod_date'] }}</div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">OPERATOR</div>
                                    <div class="detail-divider">:</div>
                                    <div class="detail-value">{{ $label['operator'] }}</div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">QTY</div>
                                    <div class="detail-divider">:</div>
                                    <div class="detail-value" style="display: flex; justify-content: space-between; width: 100%;">
                                        <span style="font-size: 8pt; font-weight: 900;">{{ $label['quantity'] }}</span>
                                        <span style="font-weight: 900; margin-right: 1mm;">
                                            SHIFT : 
                                            @if ($label['shift'] === 'I')
                                                <span class="shift-active">I</span> / II / III
                                            @elseif ($label['shift'] === 'II')
                                                I / <span class="shift-active">II</span> / III
                                            @else
                                                I / II / <span class="shift-active">III</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">CUSTOMER</div>
                                    <div class="detail-divider">:</div>
                                    <div class="detail-value">{{ $label['customer'] }}</div>
                                </div>
                            </div>

                            <!-- Right: QR Code & RoHS FREE -->
                            <div class="body-qr-container">
                                <img src="data:image/png;base64,{{ $label['qr_code_base64'] }}" alt="QR" class="qr-image">
                                <div class="rohs-box">RoHS FREE</div>
                            </div>
                        </div>

                    </div>
                @endforeach

                <!-- If current page chunk has less than 12 items, fill with invisible/empty slots to preserve grid placement -->
                @if(count($chunk) < 12)
                    @for($i = 0; $i < (12 - count($chunk)); $i++)
                        <div class="sticker-label" style="visibility: hidden;"></div>
                    @endfor
                @endif
            </div>
        @endforeach
    </div>

</body>
</html>
