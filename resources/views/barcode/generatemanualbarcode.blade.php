<!-- resources/views/barcode_generator.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode Generator</title>
</head>

<body>
    <h1>Generate Barcodes</h1>

    <form id="barcode-form" method="POST" action="{{ route('generate.barcode') }}">
        @csrf
        <label for="item_code">Item Code:</label>
        <input type="text" id="item_code" name="item_code" required>
        <br>
        <label for="quantity">Quantity:</label>
        <input type="number" id="quantity" name="quantity" required>
        <br>
        <label for="warehouse">Warehouse:</label>
        <input type="text" id="warehouse" name="warehouse" value="FG" required>
        <br>
        <label for="label">Number of Barcodes:</label>
        <input type="number" id="label" name="label" value="1" required>
        <br>
        <button type="submit">Generate Barcodes</button>
    </form>
</body>

</html>
