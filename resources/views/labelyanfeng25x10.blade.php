<!DOCTYPE html>
<html>
<head>
    <title>Label Generator - Zebra</title>
    <style>
        /* 🔹 Ukuran total kertas: 53mm x 10mm */
        @page {
            size: 53mm 10mm;
            margin: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0 1mm; /* margin kiri-kanan 1mm */
            display: flex;
            flex-wrap: wrap;
            width: 53mm;
            background: white;
            justify-content: flex-start;
        }

        /* 🔹 Label: 25mm x 10mm */
        .label {
            width: 25mm;
            height: 10mm;
            box-sizing: border-box;
            border: 0.2mm solid #000;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            background: #fff;
            padding: 0.4mm;
            padding-left: 0.5mm;
            padding-top: 0.7mm; /* Dikurangi agar barcode 8.5mm pas di tengah 10mm */
        }

        /* 🔹 Label kanan (urutan genap) — geser isi lebih ke kanan */
        .label:nth-child(even) {
            padding-left: 2mm; /* tambahin padding kiri */
    
        }

        .barcode {
            flex-shrink: 0;
            margin-right: 1.5mm; /* Beri jarak sedikit lebih lebar sebagai quiet zone */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .barcode img {
            width: 11mm;  /* Data Matrix HARUS kotak sempurna (Square) */
            height: 9mm; /* Jangan buat lonjong agar scanner mudah baca */
            image-rendering: pixelated; /* Jaga agar kotak-kotaknya tetap tajam/crisp */
            image-rendering: crisp-edges;
        }

        .info {
            display: flex;
            flex-direction: column;
            justify-content: center;
            line-height: 1.2;
            /* font-weight: bold;  */
        }

        .info div {
            font-size: 1.1mm;
            margin-bottom: 0.2mm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .project-line {
            font-size: 1.1mm !important;
            /* font-weight: bold; */
            letter-spacing: 0.1mm;
        }

        /* Hindari break di tengah label */
        .label {
            page-break-inside: avoid;
        }

        /* 🔹 Gap antar label (tengah 1mm) */
        .label:nth-child(odd) {
            margin-right: 1mm;
        }

        @media print {
            body {
                background: none;
            }

            .label {
                border: none;
            }
        }
    </style>
</head>
<body>
    @foreach($labels as $label)
        <div class="label">
            <div class="barcode">
                <img src="data:image/png;base64,{{ $label['image'] }}" alt="datamatrix">
            </div>
            <div class="info">
                <div>{{ $label['supplierCode'] }}</div>
                <div>{{ $label['sequenceCode'] }}</div>
                <div>{{ $label['projectCode'] }}</div>
                <div >{{ $label['identifier'] }}</div>
                <div>{{ $label['partNumber'] }}</div>
            </div>
        </div>
    @endforeach
</body>
</html>


<!-- setting 

width 53
length 13 
top 4  
left 1 
continues label 
darkness 12 /13
speed paling kecil -->
