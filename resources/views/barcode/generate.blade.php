<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcodes</title>
    <style>
        @page {
            size: A3;
            margin: 20mm;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            /* Stack barcodes vertically */
        }

        .barcode-label {
            display: flex;
            align-items: flex-start;
            /* Align items to the top */
            border: 1px solid #000;
            /* Add border */
            padding: 10px;
            margin-bottom: 20px;
            width: 100%;
            /* Full width for each barcode */
            page-break-inside: avoid;
            box-sizing: border-box;
            /* Include border in width calculation */
        }

        .barcode-label img {
            max-width: 150px;
            /* Adjusted width for better fit */
            height: auto;
            /* Maintain aspect ratio */
            margin-right: 20px;
            /* Add space between image and text */
        }

        .barcode-details {
            flex: 1;
            /* Allow this section to take the remaining space */
            font-size: 12px;
            text-align: left;
            /* Align text to the left */
        }

        .barcode-details p {
            margin: 5px 0;
            /* Add some space between text lines */
        }

        h1 {
            text-align: center;
            width: 100%;
            margin-bottom: 20px;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .barcode-label {
                width: 100%;
                /* Ensure full width for print */
                margin-right: 0;
                /* No margin needed for print */
                border-width: 0.5mm;
                /* Thinner border for print */
            }

            .barcode-label img {
                max-width: 120px;
                /* Adjusted size for print */
                margin-right: 15px;
                /* Adjusted space for print */
            }

            .barcode-details {
                font-size: 10px;
            }

            h1 {
                font-size: 16px;
            }
        }
    </style>
</head>

<body>
    <h1>Generated Barcodes</h1>

    @foreach ($items as $item)
        @for ($i = 0; $i < $labelCount; $i++)
            <div class="barcode-label">
                <img src="{{ asset('images/' . $item->item_code . '.png') }}" alt="{{ $item->item_code }}">
                <div class="barcode-details">
                    <p><strong>Item Code:</strong> {{ $item->item_code }}</p>
                    <p><strong>Item Name:</strong> {{ $item->item_name }}</p>
                    <p><strong>Barcode:</strong> {!! DNS1D::getBarcodeHTML($item->item_code, 'C128') !!}</p>
                </div>
            </div>
        @endfor
    @endforeach
</body>

</html>
