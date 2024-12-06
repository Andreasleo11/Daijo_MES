<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <nav class="flex text-gray-500 text-sm mb-6" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-500">
                    Dashboard
                </a>
                <span class="mx-2">/</span>
                <span class="text-gray-800 font-semibold">
                    Bill of Materials
                </span>
            </nav>
            <!-- Header Section -->
            <div class="bg-white shadow-md rounded-lg p-6 flex justify-between items-center">
                <h1 class="text-3xl font-semibold text-gray-900">Bill of Materials (BOM) Index</h1>
                <a href="{{ route('production.bom.create') }}"
                    class="flex items-center bg-blue-500 text-white px-4 py-2 rounded-md shadow hover:bg-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 5a1 1 0 01.993.883L11 6v4h4a1 1 0 01.117 1.993L15 12h-4v4a1 1 0 01-1.993.117L9 16v-4H5a1 1 0 01-.117-1.993L5 10h4V6a1 1 0 01.883-.993L10 5z"
                            clip-rule="evenodd" />
                    </svg>
                    Add New BOM
                </a>
            </div>

            <!-- Table Section -->
            <div class="bg-white shadow-md rounded-lg mt-6">
                @if ($bomParents->isEmpty())
                    <!-- Empty State -->
                    <div class="p-6 text-center">
                        <p class="text-lg text-gray-500">No BOMs found. Click "Add New BOM" to create your first one!
                        </p>
                    </div>
                @else
                    <!-- BOM Table -->
                    <table class="min-w-full table-auto border-collapse border border-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th
                                    class="px-4 py-3 border border-gray-200 text-left text-sm font-medium text-gray-700">
                                    Item Code</th>
                                <th
                                    class="px-4 py-3 border border-gray-200 text-left text-sm font-medium text-gray-700">
                                    Item Description</th>
                                <th
                                    class="px-4 py-3 border border-gray-200 text-left text-sm font-medium text-gray-700">
                                    Type</th>
                                <th
                                    class="px-4 py-3 border border-gray-200 text-center text-sm font-medium text-gray-700">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach ($bomParents as $parent)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 text-sm text-gray-800">{{ $parent->code }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-800">{{ $parent->description }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-800">{{ ucfirst($parent->type) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <!-- View Details -->
                                        <a href="{{ route('production.bom.show', $parent->id) }}"
                                            class="text-blue-600 hover:text-blue-800 mx-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12m-9 0a9 9 0 0118 0 9 9 0 01-18 0zm3 3a1 1 0 102 0 1 1 0 10-2 0zm3 0v2m0 0v-2m-3 0v2" />
                                            </svg>
                                            View
                                        </a>

                                        <!-- Delete -->
                                        <form action="{{ route('production.bom.destroy', $parent->id) }}" method="POST"
                                            class="inline-block"
                                            onsubmit="return confirm('Are you sure you want to delete this BOM?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 mx-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
