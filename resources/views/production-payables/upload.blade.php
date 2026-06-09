<x-app-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold mb-2">Production Payables Upload</h1>
            <p class="text-gray-600">Upload file Excel untuk import data hutang keyin produksi</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <h3 class="font-bold text-red-800 mb-2">⚠️ Error</h3>
                @foreach ($errors->all() as $error)
                    <p class="text-red-700 text-sm">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <h3 class="font-bold text-green-800 mb-2">✅ Success</h3>
                <p class="text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-6">
                <h2 class="text-xl font-bold">Upload File</h2>
                <p class="text-blue-100 text-sm mt-1">Supported: Excel (.xls, .xlsx) - Max 5 MB</p>
            </div>

            <form action="{{ route('production-payables.import') }}" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf

                <div class="mb-6">
                    <label for="file" class="block text-sm font-medium text-gray-700 mb-3">
                        Select File
                    </label>
                    <input
                        type="file"
                        id="file"
                        name="file"
                        accept=".xls,.xlsx,.csv,.tsv,.txt"
                        required
                        class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        onchange="updateFileName(this)"
                    >
                    <p class="text-gray-500 text-xs mt-2">Max file size: 5 MB</p>
                </div>

                <div id="fileInfo" class="hidden mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-700">
                        <span class="font-bold">Selected:</span>
                        <span id="fileName" class="ml-2"></span>
                    </p>
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                    <h3 class="font-bold text-gray-800 mb-3">📋 Required Columns</h3>
                    <p class="text-gray-700 text-sm mb-3">Your file must contain these columns (case-insensitive):</p>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-gray-700">
                            <thead>
                                <tr class="bg-gray-200">
                                    <th class="border border-gray-300 px-3 py-2 text-left">Column Name</th>
                                    <th class="border border-gray-300 px-3 py-2 text-left">Format</th>
                                    <th class="border border-gray-300 px-3 py-2 text-left">Example</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="border border-gray-300 px-3 py-2"><strong>Document Number</strong></td>
                                    <td class="border border-gray-300 px-3 py-2">Integer</td>
                                    <td class="border border-gray-300 px-3 py-2">26035734</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="border border-gray-300 px-3 py-2"><strong>Posting Date</strong></td>
                                    <td class="border border-gray-300 px-3 py-2">YYYY-MM-DD</td>
                                    <td class="border border-gray-300 px-3 py-2">2026-06-08</td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-300 px-3 py-2">Value Date</td>
                                    <td class="border border-gray-300 px-3 py-2">YYYY-MM-DD</td>
                                    <td class="border border-gray-300 px-3 py-2">2026-06-08</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="border border-gray-300 px-3 py-2"><strong>Item No.</strong></td>
                                    <td class="border border-gray-300 px-3 py-2">String</td>
                                    <td class="border border-gray-300 px-3 py-2">8060AW011P.</td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-300 px-3 py-2"><strong>Item/Service Description</strong></td>
                                    <td class="border border-gray-300 px-3 py-2">Text</td>
                                    <td class="border border-gray-300 px-3 py-2">BEZEL METER LHD 4L45W</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="border border-gray-300 px-3 py-2"><strong>Quantity</strong></td>
                                    <td class="border border-gray-300 px-3 py-2">Integer</td>
                                    <td class="border border-gray-300 px-3 py-2">137</td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-300 px-3 py-2">Remarks</td>
                                    <td class="border border-gray-300 px-3 py-2">Text</td>
                                    <td class="border border-gray-300 px-3 py-2">utang, UTANGAN</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-gray-600 text-xs mt-3">
                        <strong>Note:</strong> Bold columns are required. Column names are case-insensitive.
                    </p>
                </div>

                <div class="flex gap-3">
                    <button
                        type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200"
                    >
                        📤 Upload & Import
                    </button>
                    <a
                        href="{{ route('production-payables.index') }}"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-6 rounded-lg transition duration-200"
                    >
                        ← Back to List
                    </a>
                </div>
            </form>
        </div>

        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-bold text-gray-800 mb-2">🔄 Auto-Update</h3>
                <p class="text-gray-600 text-sm">Existing records updated by document number, new ones created</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-bold text-gray-800 mb-2">📊 Auto ID</h3>
                <p class="text-gray-600 text-sm">System auto-generates IDs for each imported record</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-bold text-gray-800 mb-2">✔️ Validation</h3>
                <p class="text-gray-600 text-sm">Automatic date and quantity validation on import</p>
            </div>
        </div>
    </div>

    <script>
    function updateFileName(input) {
        if (input.files && input.files[0]) {
            document.getElementById('fileName').textContent = input.files[0].name;
            document.getElementById('fileInfo').classList.remove('hidden');
        } else {
            document.getElementById('fileInfo').classList.add('hidden');
        }
    }
    </script>
</x-app-layout>