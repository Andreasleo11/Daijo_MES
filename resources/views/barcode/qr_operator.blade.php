<!-- resources/views/operator_user/qr_codes.blade.php -->

<x-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Operator Users QR Codes
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                <!-- Display QR Codes in 3 per row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach ($qrCodes as $qrCode)
                        <div class="text-center">
                            <h3 class="text-sm font-bold text-gray-700 mb-2">{{ $qrCode['name'] }}</h3>

                            <!-- Display the QR code as an image -->
                            <img src="{{ $qrCode['qrCode'] }}" alt="QR Code for {{ $qrCode['name'] }}" class="w-24 h-24 mx-auto" />
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>
</x-dashboard-layout>
