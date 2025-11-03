<x-app-layout>
    <div class="max-w-lg mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-xl font-bold mb-4">Tambah Workshop Manual</h2>

    <form action="{{ route('workshop.scan.manual') }}" method="POST">
        @csrf

        <!-- Parent -->
        <div class="mb-4">
            <label for="parent" class="block text-sm font-medium text-gray-700">Pilih Parent</label>
            <select id="parent" name="parent" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                <option value="">-- Pilih Parent --</option>
                @foreach($parents as $parent)
                    <option value="{{ $parent->id }}">{{ $parent->code }} - {{ $parent->description }}</option>
                @endforeach
            </select>
        </div>

        <!-- Child -->
        <div class="mb-4">
            <label for="child" class="block text-sm font-medium text-gray-700">Pilih Child</label>
            <select id="child" name="child" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" disabled>
                <option value="">-- Pilih Parent dulu --</option>
            </select>
        </div>

        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
            Simpan
        </button>
    </form>
</div>

<script>
document.getElementById('parent').addEventListener('change', function() {
    let parentId = this.value;
    let childSelect = document.getElementById('child');
    childSelect.innerHTML = '<option>Loading...</option>';

    if (parentId) {
        fetch(`/workshop/children/${parentId}`)
            .then(response => response.json())
            .then(data => {
                childSelect.innerHTML = '<option value="">-- Pilih Child --</option>';
                data.forEach(child => {
                    childSelect.innerHTML += `<option value="${child.item_code}~${child.id}">${child.item_code} | ${child.item_description}</option>`;
                });
                childSelect.disabled = false;
            });
    } else {
        childSelect.innerHTML = '<option value="">-- Pilih Parent dulu --</option>';
        childSelect.disabled = true;
    }
});
</script>

</x-app-layout>