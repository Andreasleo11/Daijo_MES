<x-app-layout>
    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                alert('{{ session('error') }}');
            });
        </script>
    @endif

    @if ($user->username === null)
        <!-- Username Form -->
        <div class="mt-10 max-w-lg mx-auto bg-white p-6 rounded-lg shadow-md">
            <h1 class="text-2xl font-semibold text-gray-800 mb-4">Welcome!</h1>
            <p class="text-gray-600 mb-6">Please provide your name to get started.</p>
            <form action="{{ route('update.username') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="username" class="block text-gray-700 font-medium mb-2">Enter Your Name:</label>
                    <input type="text" name="username" id="username"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
                        placeholder="Your Name" required />
                </div>
                <button type="submit"
                    class="w-full py-2 px-4 bg-blue-500 text-white font-semibold rounded-lg shadow-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-75">
                    Save Name
                </button>
            </form>
        </div>
    @else
        <!-- Barcode Scanner -->
        <div class="mt-10 max-w-lg mx-auto bg-white p-6 rounded-lg shadow-md">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Hello, Workshop!</h1>
                <p class="text-gray-600">Scan items below to proceed with your work.</p>
            </div>
            <form id="barcodeForm" action="{{ route('workshop.scan') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="barcode" class="block text-gray-700 font-medium mb-2">Scan Barcode:</label>
                    <input type="text" name="barcode" id="barcode"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
                        placeholder="Scan Item Code - Item ID" autofocus required />
                </div>
            </form>
            <div class="mt-4 text-center">
                <a href="{{ route('workshop.main.menu') }}"
                    class="inline-block px-6 py-2 bg-green-500 text-white font-medium rounded-lg shadow-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-400">
                    Back to Main Menu
                </a>
            </div>
        </div>
    @endif

    <!-- Barcode Input Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const barcodeInput = document.getElementById('barcode');
            const barcodeForm = document.getElementById('barcodeForm');

            barcodeInput.addEventListener('input', function() {
                if (barcodeInput.value.includes('~')) {
                    barcodeForm.submit();
                }
            });
        });
    </script>
</x-app-layout>
