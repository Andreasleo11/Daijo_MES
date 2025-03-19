<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Barcodes</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
                background-color: #f4f4f4;
                -webkit-print-color-adjust: exact;
            }

            h1 {
                text-align: center;
                margin-bottom: 20px;
            }

            /* Create a grid for cards */
            .card-container {
                display: grid;
                grid-template-columns: repeat(2, 1fr); /* 2 cards per row */
                grid-template-rows: repeat(8, auto); /* 8 rows of cards */
                gap: 1px;
                margin: 0;
            }

            .card {
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                padding: 1px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: space-between;
                box-sizing: border-box;
                position: relative;
                font-size: 8px; /* Reduce the font size */
                height: auto; /* Allow cards to adapt to the content */
                width: 100%;
            }

            .card-header {
                font-weight: bold;
                font-size: 16px; /* Reduce header size */
                margin-bottom: 8px;
            }

            .toplabel {
                background: #e0f7fa;
                border: 1px solid #00acc1;
                border-radius: 5px;
                padding: 5px 10px;
                font-size: 14px;
                color: #00796b;
            }

            .rohs-free {
                background: #e0f7fa;
                border: 1px solid #00acc1;
                border-radius: 5px;
                padding: 5px 10px;
                font-size: 14px;
                color: #00796b;
            }

            .card p {
                margin: 5px 0;
            }

            .barcode-container {
                margin-top: 1px;
                padding: 1px;
            }

            .barcode-container img {
                display: block;
                margin: 0 auto;
                max-width: 100%; /* Scale barcode */
            }

            .special-header {
                text-align: center;
                font-size: 12px; /* Reduce text size */
                font-weight: bold;
                margin: 0.2px 0;
            }

            .label-container {
                margin: 0px 0;
                padding: 1px;
            }

            .label-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 0px;
                font-size: 12px; /* Reduce row font size */
            }

            .label-row p {
                margin: 0 5px;
                flex: 1;
            }

            .company-logo {
                height: 20px;
                margin-right: 5px;
            }

            /* Style for the Print button */
            #printButton {
                position: fixed;
                top: 20px;
                right: 20px;
                background-color: #4caf50;
                color: white;
                border: none;
                padding: 10px 20px;
                font-size: 16px;
                cursor: pointer;
                border-radius: 5px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            }

            #printButton:hover {
                background-color: #45a049;
            }

            .svg-icon {
                width: 24px;
                height: 24px;
            }

            /* Back button styles */
            #backButton {
                position: fixed;
                top: 20px;
                left: 20px;
                background-color: #007bff;
                color: white;
                border: none;
                padding: 10px 20px;
                font-size: 16px;
                cursor: pointer;
                border-radius: 5px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            }

            #backButton:hover {
                background-color: #0056b3;
            }

            @media print {
                #printButton,
                #backButton {
                    display: none;
                }

                body {
                    background-color: white;
                    margin: 0;
                    padding: 0;
                }

                .card-container {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr); /* 2 cards per row */
                    grid-template-rows: repeat(8, auto); /* 8 rows of cards */
                    gap: 10px;
                    margin: 0;
                }

                .card {
                    box-shadow: none;
                    page-break-inside: avoid;
                    width: 100%;
                    font-size: 12px; /* Adjust font size for printing */
                    padding: 5px;
                }

                .barcode-container img {
                    max-width: 100%; /* Adjust barcode size for printing */
                }

                @page {
                    size: A4 portrait;
                    margin: 0;
                }
            }
        </style>
    </head>

    <body>
        <!-- Back Button -->
        <button id="backButton" onclick="window.history.back()">Back</button>
        <!-- Print Button with SVG -->
        <button id="printButton" onclick="window.print()">
            <!-- SVG Icon -->
            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="svg-icon"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"
                />
            </svg>
            Print
        </button>

        <h1>Generated Barcodes for Item Codes</h1>
        <div class="card-container">
            @foreach ($labels as $index => $label)
                <div class="card">
                    <table border="3" cellspacing="0" cellpadding="5">
                        <tr>
                            <!-- First Row -->
                            <td>
                                <div class="card-header">
                                    <img
                                        src="{{ asset('logoDaijo.png') }}"
                                        alt="PT Daijo Industrial Logo"
                                        class="company-logo"
                                    />
                                    <span>PT Daijo Industrial</span>
                                </div>
                            </td>
                            <td>
                                <div class="toplabel">
                                    {{ $label['label'] }}
                                </div>
                            </td>
                            <td rowspan="2">
                                <div class="barcode-container">
                                    <div>
                                        <img
                                            src="data:image/png;base64, {{ $qrcodes[$index] }}"
                                            alt="QR Code"
                                        />
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <!-- Second Row -->
                            <td colspan="2">
                                <div class="special-header">
                                    <p>{{ $label['item_code'] }}</p>
                                    <p>{{ $label['item_name'] }}</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <!-- Last Row -->
                            <td colspan="3">
                                <div class="label-container">
                                    <div class="label-row">
                                        <p>
                                            <strong>SPK:</strong>
                                            {{ $label['spk'] }}
                                        </p>
                                        <p>
                                            <strong>Warehouse:</strong>
                                            {{ $label['warehouse'] }}
                                        </p>
                                    </div>
                                    <div class="label-row">
                                        <p>
                                            <strong>Quantity:</strong>
                                            {{ $label['quantity'] }}
                                        </p>
                                        <p>
                                            <strong>Shift:</strong>
                                            I / II / III
                                        </p>
                                    </div>
                                    <div class="label-row">
                                        <p><strong>Prod Date:</strong></p>
                                    </div>
                                    <div class="label-row">
                                        <p><strong>Operator:</strong></p>
                                        <div class="rohs-free">
                                            <p>ROHS FREE</p>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            @endforeach
        </div>
    </body>
</html>
