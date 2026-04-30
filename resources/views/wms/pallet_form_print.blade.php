<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pallet Form - {{ $palletForm->pallet_id }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
            color: #000;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .a4-page {
            width: 210mm;
            height: 297.0mm; /* A4 height */
            display: grid;
            grid-template-columns: 105mm 105mm;
            grid-template-rows: 148.5mm 148.5mm;
            page-break-after: always;
            overflow: hidden;
        }
        .a4-page:last-child {
            page-break-after: auto;
        }
        .form-slot {
            width: 105mm;
            height: 148.5mm;
            padding: 2.5mm; /* Increased padding */
        }
        .form-container {
            border: 1.5px solid #000;
            padding: 1.5mm 2.5mm 1.5mm 1.5mm; /* More padding on right */
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .header {
            text-align: center;
            border-bottom: 1.5px solid #000;
            padding-bottom: 1mm;
            margin-bottom: 1.5mm;
            flex-shrink: 0;
        }
        .header h1 {
            margin: 0;
            font-size: 13pt;
            font-weight: 900;
        }
        .pos-code {
            font-size: 9pt;
            font-weight: bold;
            background: #000;
            color: #fff;
            display: inline-block;
            padding: 1px 6px;
            border-radius: 2px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1mm;
            flex-shrink: 0;
            border: 1.5px solid #000;
        }
        .summary-table td {
            border: 1px solid #000;
            padding: 1px 2px;
            vertical-align: middle;
        }
        .summary-label {
            font-size: 5pt;
            color: #000;
            font-weight: 900;
            text-transform: uppercase;
            width: 10mm;
            background-color: #eee;
        }
        .summary-value {
            font-size: 7.5pt;
            font-weight: 900;
            line-height: 1.0;
        }
        .multi-table-container {
            display: flex;
            gap: 2mm;
            flex-grow: 1;
            overflow: hidden;
            margin-bottom: 1mm;
        }
        .table-column {
            flex: 1;
            min-width: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.2pt;
            table-layout: fixed; /* Force fixed width */
        }
        th, td {
            border: 1px solid #000;
            padding: 1px 2px;
            text-align: left;
            font-weight: 900;
            color: #000;
            white-space: nowrap; /* Prevent wrapping */
        }
        th {
            background-color: #eee;
            font-weight: 900;
            text-transform: uppercase;
            border: 1px solid #000;
            font-size: 6pt;
        }
        .barcode-section {
            border-top: 1.5px solid #000;
            padding-top: 1.5mm;
            text-align: center;
            flex-shrink: 0;
        }
        .barcode-id {
            font-size: 10pt;
            font-weight: 900;
            margin-top: 0mm;
            letter-spacing: 1.5px;
        }
        .footer {
            font-size: 5pt;
            text-align: center;
            color: #aaa;
            margin-top: 0.5mm;
        }
        .mixed-tag {
            font-size: 5pt;
            border: 1px solid #000;
            padding: 0 1px;
            margin-left: 2px;
        }
        .copy-badge {
            background: #000;
            color: #fff;
            font-size: 10pt;
            font-weight: 900;
            padding: 2px 10px;
            margin-bottom: 2mm;
            text-align: center;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>

    @php
        // Parameters for 1/4 A4 size
        $rowsPerColumn = 15; // Adjusted to prevent cutoff
        $colsPerPage = 2;    
        $itemsPerPage = $rowsPerColumn * $colsPerPage;

        $groups = $palletForm->details->groupBy(function($item) {
            return $item->part_no ?: 'NO_LABEL';
        });

        $allForms = [];
        $groupIndex = 1;
        foreach($groups as $partNo => $groupItems) {
            $pages = $groupItems->chunk($itemsPerPage);
            $totalPages = count($pages);
            
            $headerInfo = [
                'qty' => $groupItems->sum('qty'),
                'boxes' => $groupItems->count(),
                'model' => $groupItems->first()->model_name ?: ($partNo === 'NO_LABEL' ? 'TANPA LABEL' : 'UNKNOWN')
            ];

            foreach($pages as $pageIndex => $pageItems) {
                $allForms[] = (object)[
                    'partNo' => $partNo,
                    'groupItems' => $groupItems,
                    'pageItems' => $pageItems,
                    'pageIndex' => $pageIndex,
                    'totalPages' => $totalPages,
                    'headerInfo' => $headerInfo,
                    'groupIteration' => $groupIndex,
                    'isMixed' => $groups->count() > 1
                ];
            }
            $groupIndex++;
        }

        // Duplicate each form to create a 'COPY' version next to it
        $formsWithCopies = [];
        foreach($allForms as $form) {
            $formsWithCopies[] = (object)['data' => $form, 'isCopy' => false];
            $formsWithCopies[] = (object)['data' => $form, 'isCopy' => true];
        }

        $a4Pages = array_chunk($formsWithCopies, 4);
    @endphp

    @foreach($a4Pages as $formsInPage)
        <div class="a4-page">
            @foreach($formsInPage as $item)
                @php $form = $item->data; @endphp
                <div class="form-slot">
                    <div class="form-container">
                        @if($item->isCopy)
                            <div class="copy-badge">COPY</div>
                        @endif
                        <div class="header">
                            <h1>PALLET FORM</h1>
                            <div class="pos-code">{{ $palletForm->position->position_code ?? 'UNMAPPED' }}</div>
                        </div>

                        <table class="summary-table">
                            <tr>
                                <td class="summary-label">ID</td>
                                <td class="summary-value" colspan="3">{{ $palletForm->pallet_id }}</td>
                                <td class="summary-label">DATE</td>
                                <td class="summary-value">{{ \Carbon\Carbon::parse($palletForm->prod_date)->format('d/m/y') }}</td>
                            </tr>
                            <tr>
                                <td class="summary-label">PART NO</td>
                                <td class="summary-value" colspan="3">{{ $form->partNo === 'NO_LABEL' ? 'TANPA LABEL' : $form->partNo }} @if($form->isMixed)<span class="mixed-tag">MIXED</span>@endif</td>
                                <td class="summary-label">SHIFT</td>
                                <td class="summary-value">{{ $palletForm->delivery_shift }}</td>
                            </tr>
                            <tr>
                                <td class="summary-label">MODEL</td>
                                <td class="summary-value" colspan="3">{{ $form->headerInfo['model'] }}</td>
                                <td class="summary-label">BOXES</td>
                                <td class="summary-value">{{ $form->headerInfo['boxes'] }}</td>
                            </tr>
                            <tr>
                                <td class="summary-label">LOT/MO</td>
                                <td class="summary-value">{{ $palletForm->lot_no ?: '-' }}</td>
                                <td class="summary-label">REMARK</td>
                                <td class="summary-value">{{ $palletForm->remark ?: '-' }}</td>
                                <td class="summary-label">QTY</td>
                                <td class="summary-value">{{ number_format($form->headerInfo['qty'], 0) }}</td>
                            </tr>
                            <tr>
                                <td class="summary-label">DELIVERY</td>
                                <td class="summary-value" colspan="5">{{ $palletForm->delivery_name }}</td>
                            </tr>
                        </table>

                        <div class="multi-table-container">
                            @php
                                $tableColumns = $form->pageItems->chunk($rowsPerColumn);
                            @endphp
                            @foreach($tableColumns as $columnItems)
                                <div class="table-column">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th style="width: 6mm; text-align: center;">#</th>
                                                <th style="width: 6mm; text-align: right;">Qty</th>
                                                <th style="width: 15mm">Box Label</th>
                                                <th style="width: 19mm">SPK NO</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($columnItems as $item)
                                                <tr>
                                                    <td style="text-align: center;">{{ $form->groupItems->search($item) + 1 }}</td>
                                                    <td style="text-align: right;">{{ number_format($item->qty, 0) }}</td>
                                                    <td style="font-family: monospace; font-size: 6.2pt;">
                                                        {{ $item->label ?: '-' }}
                                                    </td>
                                                    <td style="font-family: monospace; font-size: 6.2pt;">
                                                        {{ $item->spk_no ?: '-' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        </div>

                        <div class="barcode-section">
                            <div style="display: inline-block;">
                                {!! DNS1D::getBarcodeHTML($palletForm->pallet_id, 'C128', 1.0, 33) !!}
                            </div>
                            <div class="barcode-id">{{ $palletForm->pallet_id }}</div>
                            <div class="footer">
                                Part: {{ $form->groupIteration }}/{{ $groups->count() }} | 
                                Page: {{ $form->pageIndex + 1 }}/{{ $form->totalPages }} | 
                                {{ now()->timezone('Asia/Jakarta')->format('d/m/y H:i') }} WIB
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach

</body>
</html>
