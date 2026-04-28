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
            box-sizing: border-box;
            overflow: hidden;
        }
        .a4-page:last-child {
            page-break-after: auto;
        }
        .form-slot {
            width: 105mm;
            height: 148.5mm;
            padding: 2mm;
            box-sizing: border-box;
        }
        .form-container {
            border: 1.5px solid #000;
            padding: 2mm;
            height: 100%;
            box-sizing: border-box;
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
            font-size: 8pt;
            font-weight: bold;
            background: #000;
            color: #fff;
            display: inline-block;
            padding: 1px 6px;
            border-radius: 2px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 0.8fr 1.2fr;
            gap: 1mm;
            border-bottom: 1px solid #000;
            padding-bottom: 1mm;
            margin-bottom: 1.5mm;
            flex-shrink: 0;
        }
        .section {
            display: flex;
            padding: 0.2mm 0;
        }
        .label {
            width: 13mm;
            font-size: 5.5pt;
            color: #666;
            text-transform: uppercase;
            font-weight: bold;
        }
        .value {
            flex-grow: 1;
            font-size: 7pt;
            font-weight: bold;
            line-height: 1.1;
            word-break: break-all;
        }
        .multi-table-container {
            display: flex;
            gap: 1.5mm;
            flex-grow: 1;
            overflow: hidden;
            margin-bottom: 1.5mm;
        }
        .table-column {
            flex: 1;
            border-right: 1px solid #eee;
        }
        .table-column:last-child {
            border-right: none;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 5.5pt;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 0.5px 1.5px;
            text-align: left;
            white-space: nowrap;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            font-size: 5pt;
        }
        .barcode-section {
            border-top: 1.5px solid #000;
            padding-top: 1.5mm;
            text-align: center;
            flex-shrink: 0;
        }
        .barcode-id {
            font-size: 8pt;
            font-weight: bold;
            margin-top: 0.5mm;
            letter-spacing: 1px;
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
        $rowsPerColumn = 30; // Increased rows because font is smaller
        $colsPerPage = 2;    // Reduced columns because width is halved
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

                        <div class="summary-grid">
                            <div class="summary-col">
                                <div class="section"><div class="label">ID</div><div class="value">{{ $palletForm->pallet_id }}</div></div>
                                <div class="section"><div class="label">Part No</div><div class="value" style="font-size: 7pt;">{{ $form->partNo === 'NO_LABEL' ? 'TANPA LABEL' : $form->partNo }} @if($form->isMixed)<span class="mixed-tag">MIXED</span>@endif</div></div>
                                <div class="section"><div class="label">Model</div><div class="value">{{ $form->headerInfo['model'] }}</div></div>
                            </div>
                            <div class="summary-col">
                                <div class="section"><div class="label">Date</div><div class="value">{{ \Carbon\Carbon::parse($palletForm->prod_date)->format('d/m/y') }}</div></div>
                                <div class="section"><div class="label">Boxes</div><div class="value">{{ $form->headerInfo['boxes'] }}</div></div>
                                <div class="section"><div class="label">Qty</div><div class="value">{{ number_format($form->headerInfo['qty'], 0) }}</div></div>
                            </div>
                            <div class="summary-col">
                                <div class="section"><div class="label">Shift</div><div class="value">{{ $palletForm->delivery_shift }}</div></div>
                                <div class="section"><div class="label">Delivery</div><div class="value">{{ $palletForm->delivery_name }}</div></div>
                                <div class="section"><div class="label">Lot/MO</div><div class="value">{{ $palletForm->lot_no ?: '-' }}</div></div>
                            </div>
                        </div>

                        <div class="multi-table-container">
                            @php
                                $tableColumns = $form->pageItems->chunk($rowsPerColumn);
                            @endphp
                            @foreach($tableColumns as $columnItems)
                                <div class="table-column">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th style="width: 15px">#</th>
                                                <th style="width: 25px">Qty</th>
                                                <th style="width: 50px">No Label</th>
                                                <th>SPK/Reference</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($columnItems as $item)
                                                <tr>
                                                    <td style="color: #999;">{{ $form->groupItems->search($item) + 1 }}</td>
                                                    <td style="text-align: right; font-weight: bold;">{{ number_format($item->qty, 0) }}</td>
                                                    <td style="font-family: monospace; font-size: 4.5pt; overflow: hidden;">
                                                        {{ $item->label ?: '-' }}
                                                    </td>
                                                    <td style="font-family: monospace; font-size: 4.5pt; overflow: hidden;">
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
                                {!! DNS1D::getBarcodeHTML($palletForm->pallet_id, 'C128', 1.4, 33) !!}
                            </div>
                            <div class="barcode-id">{{ $palletForm->pallet_id }}</div>
                            <div class="footer">
                                Part: {{ $form->groupIteration }}/{{ $groups->count() }} | 
                                Page: {{ $form->pageIndex + 1 }}/{{ $form->totalPages }} | 
                                {{ now()->format('d/m/y H:i') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach

</body>
</html>
