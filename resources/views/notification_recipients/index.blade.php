<x-app-layout>
    <div class="container mx-auto mt-10 px-10">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Manage Notification Recipients</h1>
            <a href="{{ route('notification_recipients.create') }}"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow">
                Add Recipient
            </a>
        </div>

        <div class="overflow-hidden shadow rounded-lg bg-white">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-gray-500 uppercase tracking-wider">
                            Email
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-gray-500 uppercase tracking-wider">
                            Active
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-gray-500 uppercase tracking-wider">
                            Type
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-sm font-semibold text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($recipients as $recipient)
                        <tr class="hover:bg-gray-100">
                            <td class="px-6 py-4 text-sm text-gray-800">{{ $recipient->email }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if ($recipient->active)
                                    <span class="inline-block px-3 py-1 rounded-full bg-green-100 text-green-800 text-xs font-medium">
                                        Yes
                                    </span>
                                @else
                                    <span class="inline-block px-3 py-1 rounded-full bg-red-100 text-red-800 text-xs font-medium">
                                        No
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-800">{{ $recipient->notificationType->name }}</td>
                            <td class="px-6 py-4 text-sm text-right">
                                <a href="{{ route('notification_recipients.edit', $recipient->id) }}"
                                    class="text-blue-600 hover:text-blue-900 font-medium">Edit</a>
                                <span class="text-gray-500 mx-2">|</span>
                                <form action="{{ route('notification_recipients.destroy', $recipient->id) }}" method="POST"
                                    class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-red-600 hover:text-red-900 font-medium">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
