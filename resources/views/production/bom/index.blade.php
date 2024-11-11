<x-app-layout>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("BOM index") }}
                    <div class="mt-4">
                    <a href="{{ route('production.bom.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 text-black rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-75">
                        Add BOM
                    </a>
                </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
