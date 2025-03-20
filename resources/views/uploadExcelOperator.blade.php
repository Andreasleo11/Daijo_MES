<x-dashboard-layout>
    <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-bold">Upload Operator Users</h2>

        @if(session('success'))
            <div class="p-3 bg-green-200 text-green-800 rounded">{{ session('success') }}</div>
        @endif

        <form action="{{ route('operator-users.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="file" name="file" class="mt-3 p-2 border rounded">
            <button type="submit" class="mt-3 bg-blue-500 text-white px-4 py-2 rounded">Upload</button>
        </form>
    </div>
</x-dashboard-layout>
