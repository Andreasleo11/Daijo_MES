<x-app-layout>
    <div class="container mx-auto mt-10 px-10">
        <h1 class="text-2xl font-bold mb-5">Manage Notification Recipients</h1>
        <a href="{{ route('notification_recipients.create') }}"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4">
            Add Recipient
        </a>
        <table class="min-w-full bg-white border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-2 px-4 border-b">Email</th>
                    <th class="py-2 px-4 border-b">Active</th>
                    <th class="py-2 px-4 border-b">Type</th>
                    <th class="py-2 px-4 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($recipients as $recipient)
                    <tr>
                        <td class="py-2 px-4 border-b">{{ $recipient->email }}</td>
                        <td class="py-2 px-4 border-b">{{ $recipient->active ? 'Yes' : 'No' }}</td>
                        <td class="py-2 px-4 border-b">{{ $recipient->notificationType->name }}</td>
                        <td class="py-2 px-4 border-b">
                            <a href="{{ route('notification_recipients.edit', $recipient->id) }}"
                                class="text-green-500 hover:underline">Edit</a>
                            <form action="{{ route('notification_recipients.destroy', $recipient->id) }}" method="POST"
                                class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
