<x-app-layout>
    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                alert('{{ session('error') }}');
            });
        </script>
    @endif

    @if ($user->username === null)
        <form action="{{ route('update.username') }}" method="POST" class="mt-6">
            @csrf
            <label for="username" class="block text-gray-700 font-bold mb-2">Enter Your Name:</label>
            <input type="text" name="username" id="username"
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
                placeholder="Enter Your Name" required />
            <button type="submit" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg">Save Name</button>
        </form>
    @else
        <div class="mt-4 text-red-600 font-bold">
            You are Workshop
        </div>

        <form id="barcodeForm" action="{{ route('workshop.scan') }}" method="POST" class="mt-6">
            @csrf
            <label for="barcode" class="block text-gray-700 font-bold mb-2">Scan Barcode:</label>
            <input type="text" name="barcode" id="barcode"
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
                placeholder="Scan Item Code - Item ID" autofocus required />
        </form>

        <div class="mb-6">
            <a href="{{ route('workshop.main.menu') }}"
                class="inline-block bg-green-500 text-white px-4 py-2 text-sm font-medium rounded hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-400">
                Back to Main Menu
            </a>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const barcodeInput = document.getElementById('barcode');
            const barcodeForm = document.getElementById('barcodeForm');

            barcodeInput.addEventListener('input', function() {
                if (barcodeInput.value.includes('-')) {
                    barcodeForm.submit();
                }
            });
        });
    </script>
</x-app-layout>
