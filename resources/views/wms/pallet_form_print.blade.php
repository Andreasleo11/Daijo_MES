<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pallet Form - {{ $palletForm->pallet_id }}</title>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #000;
        }
        .container {
            width: 100%;
            border: 2px solid #000;
        }
        .header {
            text-align: center;
            font-size: 24pt;
            font-weight: bold;
            padding: 10px;
            border-bottom: 2px solid #000;
            background-color: #f0f0f0;
        }
        .section {
            display: flex;
            border-bottom: 1px solid #000;
        }
        .label {
            width: 30%;
            font-weight: bold;
            padding: 8px;
            border-right: 1px solid #000;
            background-color: #fafafa;
        }
        .value {
            width: 70%;
            padding: 8px;
        }
        .half {
            width: 50%;
            display: flex;
            border-right: 1px solid #000;
        }
        .half:last-child {
            border-right: none;
        }
        .half .label {
            width: 40%;
        }
        .half .value {
            width: 60%;
        }
        .remarks-area {
            min-height: 80px;
            padding: 8px;
            border-bottom: 2px solid #000;
        }
        .footer {
            background-color: #d0d0d0;
            height: 20px;
            border-bottom: 1px solid #000;
        }
        .generated-info {
            padding: 10px;
            font-size: 14pt;
        }
        .pallet-id-box {
            font-size: 28pt;
            font-weight: bold;
            text-align: center;
            padding: 20px;
            border: 3px dashed #000;
            margin: 20px;
        }
        .position-id-box {
            background-color: #000;
            color: #fff;
            padding: 10px;
            text-align: center;
            font-size: 20pt;
            font-weight: bold;
        }
        @media print {
            .no-print {
                display: none;
            }
            .container {
                border: 2px solid #000 !important;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="padding: 20px; background: #fffbe6; border-bottom: 1px solid #ffe58f; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background: #1890ff; color: white; border: none; border-radius: 4px;">🖨️ PRINT PALLET FORM</button>
        <button onclick="window.history.back()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background: #666; color: white; border: none; border-radius: 4px; margin-left: 10px;">Kembali</button>
    </div>

    <div class="container">
        <div class="header">PALLET FORM</div>
        
        <div class="section">
            <div class="label">PART NO:</div>
            <div class="value">{{ $palletForm->part_no }}</div>
        </div>
        
        <div class="section">
            <div class="label">MODEL NO:</div>
            <div class="value">{{ $palletForm->model_name }}</div>
        </div>
        
        <div class="section">
            <div class="half">
                <div class="label">PROD DATE:</div>
                <div class="value">{{ $palletForm->prod_date }}</div>
            </div>
            <div class="half">
                <div class="label">LOT NO. / MO:</div>
                <div class="value">{{ $palletForm->lot_no }}</div>
            </div>
        </div>
        
        <div class="section">
            <div class="half">
                <div class="label">DELV. NAME:</div>
                <div class="value">{{ $palletForm->delivery_name }}</div>
            </div>
            <div class="half">
                <div class="label">DELV. SHIFT:</div>
                <div class="value">{{ $palletForm->delivery_shift }}</div>
            </div>
        </div>
        
        <div class="section">
            <div class="half">
                <div class="label">BOX QTY:</div>
                <div class="value">{{ $palletForm->box_qty }} Boxes</div>
            </div>
            <div class="half">
                <div class="label">QTY PER PALLET:</div>
                <div class="value">{{ number_format($palletForm->total_pallet_qty, 0) }} pcs</div>
            </div>
        </div>
        
        <div class="section" style="border-bottom: none;">
            <div class="label" style="height: 40px;">RECEIVED BY:</div>
            <div class="value"></div>
        </div>
        
        <div class="label" style="width: 100%; border-right: none; border-top: 1px solid #000;">REMARKS:</div>
        <div class="remarks-area">
            {{ $palletForm->remarks }}
        </div>
        
        <div class="footer"></div>
        
        <div class="generated-info">
            <div class="pallet-id-box">
                {{ $palletForm->pallet_id }}
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                <div style="width: 60%;">
                    <div style="font-weight: bold; margin-bottom: 5px;">POSITION ID:</div>
                    <div class="position-id-box">
                        {{ $palletForm->position->position_code ?? 'UNASSIGNED' }}
                    </div>
                </div>
                <div style="text-align: right; font-size: 10pt; color: #666;">
                    Generated at: {{ $palletForm->created_at->format('Y-m-d H:i:s') }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
