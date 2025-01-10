<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print All Materials</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-white">

    <div class="container mx-auto my-8">
        <h1 class="text-3xl font-bold text-center mb-8">Materials for {{ $bomParent->name }}</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach ($childrenWithDetails as $child)
                <div class="bg-white p-4 border rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-4">{{ $child->name }}</h2>
                    <p class="text-gray-700 mb-2"><strong>Material ID:</strong> {{ $child->id }}</p>
                    <p class="text-gray-700 mb-4"><strong>Quantity:</strong> {{ $child->quantity }}</p>

                    <!-- Barcode QR Code -->
                    <div class="mb-4">
                        <h3 class="text-lg font-medium mb-2">QR Code:</h3>
                        <img src="data:image/png;base64,{{ $child->barcode }}" alt="Barcode" class="w-32 h-32 object-cover mx-auto">
                    </div>

                    <!-- Image -->
                    @if($child->image_url)
                        <div>
                            <h3 class="text-lg font-medium mb-2">Material Image:</h3>
                            <img src="{{ $child->image_url }}" alt="Material Image" class="w-32 h-32 object-cover mx-auto">
                        </div>
                    @else
                        <div>
                            <h3 class="text-lg font-medium mb-2">Material Image:</h3>
                            <p>No image available</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-6 text-center">
            <button onclick="window.print()" class="px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                Print
            </button>
        </div>
    </div>

</body>
</html>
