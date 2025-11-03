<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ID Card Operator</title>
    <style>
        @media print {
            .page {
                page-break-after: always;
            }
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .page {
            display: flex;
            flex-wrap: wrap;
            width: 21cm;
            height: 29.7cm;
            padding: 1cm;
            box-sizing: border-box;
        }

        .card {
            width: 6cm;
            height: 9cm;
            border: 1px solid #000;
            margin: 0.5cm;
            padding: 0.5cm;
            box-sizing: border-box;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .logo-img {
            width: 1cm;
            height: auto;
            margin: 0 auto 0.2cm;
        }

        .logo {
            font-weight: bold;
            font-size: 0.9rem;
        }

        .photo {
            width: 3cm;
            height: 3cm;
            object-fit: cover;
            border-radius: 4px;
            margin: 0 auto;
        }

        .name {
            font-size: 0.65rem;
            font-weight: bold;
        }

        .role {
            font-size: 0.75rem;
            color: #444;
        }

        .department {
            font-size: 0.7rem;
            color: #222;
        }

        .qr {
            width: 2.5cm;
            height: 2.5cm;
            margin: 0 auto;
        }

        .nik {
            font-size: 0.65rem;
            color: #333;
            margin-top: 0.2rem;
        }
    </style>
</head>
<body>

@php $counter = 0; @endphp
<div class="page">
    @foreach ($qrCodes as $user)
        <div class="card">
            {{-- Logo Gambar --}}
            <img src="{{ asset('storage/picture/logo-dj.png') }}" alt="Logo" class="logo-img">
            <div class="logo">PT Daijo Industrial</div>

            {{-- Foto Operator --}}
            <img src="{{ $user['photo'] ? asset('storage/' . ltrim($user['photo'], '/')) : asset('images/default.png') }}" class="photo" alt="Foto {{ $user['name'] }}">
            
            <div class="name">{{ $user['name'] }}</div>
            <div class="role">{{ $user['role'] ?? 'N/A' }}</div>
            <div class="nik">NIK: {{$user['department']}}-{{ $user['nik'] }}</div>

            {{-- QR Code --}}
            <img src="{{ $user['qrCode'] }}" class="qr" alt="QR Code {{ $user['name'] }}">

            {{-- Department --}}
            <div class="department">
                @switch($user['department'])
                    @case(390)
                        Plastic Injection
                        @break
                    @case(351)
                        Maintenance Tool & Machine
                        @break
                    @default
                        Department N/A
                @endswitch
            </div>
        </div>

        @php $counter++; @endphp

        @if ($counter % 6 === 0 && !$loop->last)
            </div><div class="page">
        @endif
    @endforeach
</div>

</body>
</html>
