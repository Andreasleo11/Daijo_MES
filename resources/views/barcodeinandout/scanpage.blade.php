<x-app-layout>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .barcode-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            page-break-inside: avoid;
            /* Prevent page breaks within a single barcode container */
        }

        .barcode-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 20px;
            height: 300px;
            /* Adjust this value as needed */
            box-sizing: border-box;
        }

        .barcode-item h2 {
            margin: 5px 0;
            font-size: 16px;
        }

        .barcode-item img {
            max-width: 100%;
            height: auto;
            margin-bottom: 10px;
        }

        .separator {
            width: 100%;
            border-top: 1px solid #000;
            margin: 10px 0;
        }

        .vertical-separator {
            border-left: 1px solid #000;
            height: 100%;
            margin: 0 10px;
            /* Adjust spacing */
        }

        .info {
            display: flex;
            align-items: center;
            padding-top: 10px;
            /* Adjust spacing */
        }

        .info .text {
            flex: 1;
            padding: 0 10px;
            /* Adjust spacing */
        }

        .info .text p {
            margin: 5px 0;
            font-size: 14px;
        }

        .big-number {
            font-size: 24px;
            font-weight: bold;
            margin-left: 20px;
        }

        .form-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 20px;
        }

        #barcodeForm {
            display: flex;
            flex-direction: column;
            gap: 20px;
            align-items: flex-start;
            width: 100%;
            max-width: 1200px;
        }

        .form-group-container {
            display: flex;
            justify-content: space-between;
            width: 100%;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            margin: 5px;
        }

        .form-group label {
            margin-bottom: 5px;
        }

        .form-group input {
            padding: 5px;
            font-size: 16px;
        }

        .custom-alert {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #16a34a;
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            z-index: 9999;
            max-width: 90%;
            text-align: center;
        }

        @media print {
            body {
                margin: 0;
                font-size: 10pt;
            }

            .barcode-container {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                /* 2 columns per row */
                gap: 10px;
                page-break-inside: avoid;
            }

            .barcode-item {
                border: 1px solid #000;
                padding: 5px;
                margin-bottom: 5px;
                height: 190px;
                /* Adjusted height for print */
                box-sizing: border-box;
                page-break-inside: avoid;
            }

            .barcode-item h2 {
                font-size: 12px;
                /* Adjust font size for print */
            }

            .barcode-item img {
                height: 60px;
                /* Adjust image size for print */
            }

            .info .text p {
                font-size: 10px;
                /* Adjust text size for print */
            }

            .big-number {
                font-size: 16px;
                /* Adjust font size for print */
            }

            .form-container {
                display: none;
                /* Hide the form when printing */
            }
        }

      .submit-button {
            position: fixed;
            bottom: 20px;   /* jarak dari bawah layar */
            right: 10px;    /* jarak dari kanan layar */
            padding: 12px 25px;
            font-size: 18px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            z-index: 1000;
        }

        .submit-button:hover {
            background-color: #0056b3;
        }
    </style>

  @if(session('success'))
        <div id="customAlert" class="custom-alert">
            <strong>✅ {{ session('success') }}</strong>
            <button onclick="document.getElementById('customAlert').remove()" 
                    style="background: transparent; color: white; border: 1px solid white; padding: 5px 10px; margin-left: 15px; border-radius: 3px; cursor: pointer;">
                ✕
            </button>
        </div>
        <script>
            // Auto hide after 8 seconds
            setTimeout(function() {
                var alert = document.getElementById('customAlert');
                if(alert) alert.remove();
            }, 8000);
        </script>
    @endif
        
    <h2>No Dokumen: {{ $noDokumen }}</h2>
    <p>Tanggal Scan: {{ $tanggalScanFull }}</p>
    <p> Customer   : {{ $customer }}</p>

    <h1 style="text-align: center; font-size: 2em;">{{ $HeaderScan }}</h1>


    <div class="form-container">
        <form id="barcodeForm" method="POST" action="{{ route('processbarcodeinandout') }}">
            @csrf
            <input type="hidden" name="noDokumen" value="{{ $noDokumen }}">
            <input type="hidden" name="tanggalScanFull" value="{{ $tanggalScanFull }}">
            <input type="hidden" name="position" value="{{ $position }}">


            <div class="form-group-container" id="row1">
                <div class="form-group">
                    <label for="partno1">Part No:</label>
                    <input type="text" id="partno1" name="partno1" class="barcode-input">
                </div>
                <div class="form-group">
                    <label for="label1">Label :</label>
                    <input type="text" id="label1" name="label1" class="barcode-input">
                </div>
                <div class="form-group">
                    <label for="warehouse1">Warehouse :</label>
                    <input type="text" id="warehouse1" name="warehouse1" class="barcode-input">
                </div>
                <div class="form-group">
                    <label for="scantime1">Scan Time:</label>
                    <input type="text" id="scantime1" name="scantime1" class="barcode-input" readonly>
                </div>

                <button type="button" class="delete-row-button" onclick="deleteRow(1)">X</button>
            </div>

            <button type="submit" class="submit-button">Submit</button>
        </form>
    </div>

    <script>
        let formCounter = 1;

        function updateScanTime(fieldId) {
            const scanTimeField = document.getElementById('scantime' + fieldId);
            const now = new Date();
            const formattedTime = now.toLocaleString(); // Adjust formatting as needed
            scanTimeField.value = formattedTime;
        }

        function addEventListenersToRow(fieldId) {
            document.getElementById('partno' + fieldId).addEventListener('input', () => updateScanTime(fieldId));
            document.getElementById('warehouse' + fieldId).addEventListener('input', () => updateScanTime(fieldId));
        }

        document.getElementById('warehouse1').addEventListener('focus', addNewRow);

        function addNewRow() {
            const partno = document.getElementById('partno' + formCounter).value;
            const label = document.getElementById('warehouse' + formCounter).value;

            if (partno) {
                formCounter++;

                const newRow = document.createElement('div');
                newRow.className = 'form-group-container';
                newRow.id = 'row' + formCounter;
                newRow.innerHTML = `
                <div class="form-group">
                    <label for="partno${formCounter}">Part No:</label>
                    <input type="text" id="partno${formCounter}" name="partno${formCounter}" class="barcode-input">
                </div>
                <div class="form-group">
                    <label for="label${formCounter}">Label:</label>
                    <input type="text" id="label${formCounter}" name="label${formCounter}" class="barcode-input">
                </div>
                 <div class="form-group">
                    <label for="warehouse${formCounter}">Warehouse :</label>
                    <input type="text" id="warehouse${formCounter}" name="warehouse${formCounter}" class="barcode-input">
                </div>
                <div class="form-group">
                    <label for="scantime${formCounter}">Scan Time:</label>
                    <input type="text" id="scantime${formCounter}" name="scantime${formCounter}" class="barcode-input" readonly>
                </div>
                 <button type="button" class="delete-row-button" onclick="deleteRow(${formCounter})">X</button>
            `;

                document.getElementById('barcodeForm').appendChild(newRow);

                // Add event listeners to the new input fields
                addEventListenersToRow(formCounter);

                // Add event listener to the new label input field to add new row on focus
                document.getElementById('warehouse' + formCounter).addEventListener('focus', addNewRow);

                // Set focus to the new partno input field after a short delay to ensure it is rendered
                setTimeout(() => {
                    document.getElementById('partno' + formCounter).focus();
                }, 500); // 1-second delay
            }
        }

        // Initialize event listeners for the first row
        addEventListenersToRow(formCounter);

        function deleteRow(rowId) {
            const row = document.getElementById('row' + rowId);
            if (row) {
                row.remove();
                resetRowIds();
            }
        }

        function resetRowIds() {
            const rows = document.querySelectorAll('.form-group-container');
            formCounter = 0;

            rows.forEach((row, index) => {
                formCounter++;
                row.id = 'row' + formCounter;
                row.querySelector('.delete-row-button').setAttribute('onclick', `deleteRow(${formCounter})`);
                row.querySelector('.form-group input[id^="partno"]').id = 'partno' + formCounter;
                row.querySelector('.form-group input[id^="partno"]').name = 'partno' + formCounter;
                row.querySelector('.form-group input[id^="label"]').id = 'label' + formCounter;
                row.querySelector('.form-group input[id^="label"]').name = 'label' + formCounter;
                row.querySelector('.form-group input[id^="warehouse"]').id = 'warehouse' + formCounter;
                row.querySelector('.form-group input[id^="warehouse"]').name = 'warehouse' + formCounter;
                row.querySelector('.form-group input[id^="scantime"]').id = 'scantime' + formCounter;
                row.querySelector('.form-group input[id^="scantime"]').name = 'scantime' + formCounter;

                // Reassign event listeners
                addEventListenersToRow(formCounter);
            });
        }

        document.getElementById('barcodeForm').addEventListener('submit', function(event) {
            const lastPartNoField = document.getElementById('partno' + formCounter);
            const lastLabelField = document.getElementById('warehouse' + formCounter);

            if (!lastPartNoField.value && !lastLabelField.value) {
                lastPartNoField.parentElement.parentElement.remove();
            }
        });
    </script>


</x-app-layout>
