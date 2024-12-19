<x-app-layout>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Stock Balances for {{ ucfirst($location) }}</title>
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    </head>

    <body class="bg-gray-100">
        <div class="container mx-auto py-10 px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <h1 class="text-2xl font-semibold text-gray-800 mb-6">
                    Stock Balances for {{ ucfirst($location) }}
                </h1>

                <!-- Location Switcher Buttons -->
                <div class="flex space-x-4 mb-6">
                    <a href="{{ route('stockallbarcode', ['location' => 'Jakarta']) }}"
                        class="px-4 py-2 font-semibold text-white bg-blue-500 rounded-md hover:bg-blue-600 transition ease-in-out duration-200 @if ($location == 'Jakarta') bg-blue-700 @endif">
                        Jakarta
                    </a>
                    <a href="{{ route('stockallbarcode', ['location' => 'Karawang']) }}"
                        class="px-4 py-2 font-semibold text-white bg-blue-500 rounded-md hover:bg-blue-600 transition ease-in-out duration-200 @if ($location == 'Karawang') bg-blue-700 @endif">
                        Karawang
                    </a>
                </div>

                <!-- Stock Balance Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto bg-white rounded-lg shadow-md">
                        <thead class="bg-gray-200 text-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                    Part No
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                    Description
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                    Balance
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($balances as $balance)
                                <tr class="hover:bg-gray-100 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $balance['partNo'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $balance['description'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $balance['balance'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script src="{{ asset('js/app.js') }}"></script>
    </body>

    </html>
</x-app-layout>
