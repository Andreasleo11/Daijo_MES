<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="flex justify-between items-center border-b pb-4 mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">User Role & Account Management</h2>
                    <span class="text-xs text-indigo-600 font-semibold bg-indigo-50 py-1 px-3 rounded-full border border-indigo-100">Super-Admin System Portal</span>
                </div>

                <livewire:admin.user-role-manager />
            </div>
        </div>
    </div>
</x-app-layout>
