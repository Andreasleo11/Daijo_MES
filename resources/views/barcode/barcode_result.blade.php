<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generated Barcodes</title>
    <style>
        .container {
            width: 100%;
            margin: 0 auto;
        }

        .barcode-container {
            width: 100%;
            /* Adjusted to prevent overlap */
            float: left;
            box-sizing: border-box;
            padding: 10px;
            text-align: center;
            margin-bottom: 20px;
        }

        .barcode-wrapper {
            border: 1px solid #ccc;
            padding: 10px;
            width: 100%;
            box-sizing: border-box;
        }

        .barcode {
            margin: 10px 0;
            display: block;
            width: 100%;
            max-width: 3000px;
            /* Adjust the size to fit better */
            height: auto;
            /* Maintain the aspect ratio */
            margin: 0 auto;
        }

        .details {
            text-align: left;
            font-size: 14px;
            margin-top: 10px;
        }

        .details span {
            display: block;
        }

        .clear {
            clear: both;
        }
    </style>
</head>

<body>
    <div class="container">
        @foreach ($barcodes as $barcodeData)
            <div class="barcode-container">
                <div class="barcode-wrapper">
                    <div class="details">
                        <span><strong>Item Code:</strong> {{ $barcodeData['item_code'] }}</span>
                        <span><strong>Quantity:</strong> {{ $barcodeData['quantity'] }}</span>
                        <span><strong>Label:</strong> {{ $barcodeData['label'] }}</span>
                    </div>
                    <div class="barcode">
                        {!! $barcodeData['barcode'] !!}
                    </div>
                </div>
            </div>

            @if ($loop->iteration % 2 == 0)
                <div class="clear"></div>
            @endif
        @endforeach
    </div>
</body>

</html>
