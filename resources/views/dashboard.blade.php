<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
    @if ($user->role_name === 'ADMIN' || $user->role_name === 'WAREHOUSE')
    <div class="mt-4">
        <a href="{{ route('production.bom.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 text-black rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-75">
                Add BOM
        </a>
    </div>
    @elseif ($user->role_name === 'WORKSHOP')
        <!-- Show this message if user is WORKSHOP -->
        <div class="mt-4 text-red-600 font-bold">
            You are Workshop
        </div>

          <!-- Barcode Scan Form for WORKSHOP -->
          <form id="barcodeForm" action="{{ route('workshop.scan') }}" method="POST" class="mt-6">
            @csrf
            <label for="barcode" class="block text-gray-700 font-bold mb-2">Scan Barcode:</label>
            <input
                type="text"
                name="barcode"
                id="barcode"
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
                placeholder="Scan Item Code - Item ID"
                autofocus
                required
            />
        </form>
    @else
        <!-- Optional: Message for other roles -->
        <div class="mt-4 text-gray-500">
            Role not recognized.
        </div>
    @endif


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const barcodeInput = document.getElementById('barcode');
            const barcodeForm = document.getElementById('barcodeForm');

            barcodeInput.addEventListener('input', function () {
                if (barcodeInput.value.includes('-')) {
                    barcodeForm.submit();
                }
            });
        });
    </script>
</x-app-layout>
