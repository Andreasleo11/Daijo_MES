<x-dashboard-layout>
<form action="{{ route('operator.updateProfilePicture') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <!-- User Dropdown -->
    <label class="block text-sm font-medium text-gray-700">Select User</label>
    <select name="user_id" class="border p-2 rounded w-full mb-2" required>
        <option value="" disabled selected>Select a user</option>
        @foreach ($users as $user)
            <option value="{{ $user->id }}">{{ $user->name }}</option>
        @endforeach
    </select>

    <!-- File Upload -->
    <label class="block text-sm font-medium text-gray-700">Profile Picture</label>
    <input type="file" name="profile_picture" class="border p-2 rounded w-full mb-2" required>

    <!-- Submit Button -->
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded mt-2">Upload</button>
</form>



<div class="grid grid-cols-3 gap-4 p-4">
    @foreach ($users as $user)
        <div class="bg-white p-4 rounded-lg shadow-lg flex flex-col items-center">
            <img src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : asset('default-avatar.png') }}" 
                 class="w-24 h-24 rounded-full object-cover mb-4" 
                 alt="Profile Picture">

            <h3 class="text-lg font-bold">{{ $user->name }}</h3>
            <p class="text-gray-500">{{ $user->email }}</p>
        </div>
    @endforeach
</div>


</x-dashboard-layout>