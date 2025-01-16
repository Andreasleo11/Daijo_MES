<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print All Materials</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @media print {

            @page {
                size: A4 portrait;
                margin: 1in;
            }

            .header,
            .print-button {
                display: none;
            }

            body {
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact;
            }

            .grid {
                grid-template-columns: repeat(2, 1fr);
                /* Fit 2 cards per row for printing */
            }

            .card {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .grid>div:nth-child(4n + 1) {
                page-break-before: always;
            }
        }
    </style>
</head>

<body class="bg-white">

    <div class="container mx-auto my-4">
        <h1 class="header text-3xl font-bold text-center mb-2">Materials for {{ $bomParent->code }}</h1>
        <h1 class="header text-xl text-gray-600 font-medium text-center mb-8">{{ $bomParent->description }}</h1>
    <div class="container mx-auto my-4">
        <h1 class="header text-3xl font-bold text-center mb-2">Materials for {{ $bomParent->code }}</h1>
        <h1 class="header text-xl text-gray-600 font-medium text-center mb-8">{{ $bomParent->description }}</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-8 gap-4 print:grid-cols-2">
            @foreach ($childrenWithDetails as $index => $child)
                <div
                    class="card flex flex-col items-center justify-center text-center bg-white p-4 border border-gray-300 rounded-lg shadow-md">
                    <h2 class="text-lg font-semibold mb-2">{{ $child->item_code }}</h2>
                    <p class="text-gray-700 mb-2">{{ $child->item_description }}</p>
                    <p class="text-gray-700 mb-4"><strong>Quantity:</strong> {{ $child->quantity }}</p>

                    <!-- Barcode QR Code -->
                    <div class="mb-4">
                        <h3 class="text-base font-medium mb-2">QR Code:</h3>
                        <img src="data:image/png;base64,{{ $child->barcode }}" alt="Barcode"
                            class="h-24 object-contain">
                    </div>

                    <!-- Image -->
                    @if ($child->image_url)
                        <div>
                            <h3 class="text-base font-medium mb-2">Material Image:</h3>
                            <img src="{{ $child->image_url }}" alt="Material Image" class="h-24 object-contain">
                        </div>
                    @else
                        <div>
                            <h3 class="text-base font-medium mb-2">Material Image:</h3>
                            <h3 class="text-base font-medium mb-2">Material Image:</h3>
                            <p>No image available</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="print-button mt-6 text-center">
        <div class="print-button mt-6 text-center">
            <button onclick="window.print()" class="px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                Print
            </button>
        </div>
    </div>

</body>

</html>
