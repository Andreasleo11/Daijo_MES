<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-semibold mb-4">{{ __("BOM Index") }}</h1>

                    <div class="mt-4">
                        <a href="{{ route('production.bom.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 text-black rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-75">
                            Add BOM
                        </a>
                    </div>

                    <div class="mt-6">
                        <table class="min-w-full table-auto">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 border-b">Item Code</th>
                                    <th class="px-4 py-2 border-b">Item Description</th>
                                    <th class="px-4 py-2 border-b">Type</th>
                                    <th class="px-4 py-2 border-b">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bomParents as $parent)
                                    <tr>
                                        <td class="px-4 py-2 border-b text-center">{{ $parent->item_code }}</td>
                                        <td class="px-4 py-2 border-b text-center">{{ $parent->item_description }}</td>
                                        <td class="px-4 py-2 border-b text-center">{{ $parent->type }}</td>
                                        <td class="px-4 py-2 border-b text-center">
                                            <a href="{{ route('production.bom.show', $parent->id) }}" class="text-blue-500 hover:underline">View Details</a>
                                            
                                            <!-- Delete Button -->
                                            <form action="{{ route('production.bom.destroy', $parent->id) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('Are you sure you want to delete this BOM and its children?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:underline">
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
