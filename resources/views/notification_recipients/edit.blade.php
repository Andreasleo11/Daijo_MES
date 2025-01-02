<x-app-layout>
    <div class="container mx-auto mt-10 px-10">
        <h1 class="text-2xl font-bold mb-5">Edit Notification Recipient</h1>

        <form action="{{ route('notification_recipients.update', $notificationRecipient->id) }}" method="POST"
            class="bg-white p-6 rounded shadow-md">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="email" class="block font-bold mb-2">Email</label>
                <input type="email" name="email" id="email" class="w-full border border-gray-300 p-2 rounded"
                    value="{{ old('email', $notificationRecipient->email) }}" required>
                @error('email')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label for="notification_type_id" class="block font-bold mb-2">Notification Type</label>
                <select name="notification_type_id" id="notification_type_id"
                    class="w-full border border-gray-300 p-2 rounded" required>
                    <option value="">Select Notification Type</option>
                    @foreach ($notificationTypes as $type)
                        <option value="{{ $type->id }}"
                            {{ old('notification_type_id', $notificationRecipient->notification_type_id) == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
                @error('notification_type_id')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label for="active" class="block font-bold mb-2">Active</label>
                <select name="active" id="active" class="w-full border border-gray-300 p-2 rounded">
                    <option value="1" {{ old('active', $notificationRecipient->active) == '1' ? 'selected' : '' }}>
                        Yes</option>
                    <option value="0" {{ old('active', $notificationRecipient->active) == '0' ? 'selected' : '' }}>
                        No</option>
                </select>
                @error('active')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Update
            </button>
        </form>
    </div>
</x-app-layout>
