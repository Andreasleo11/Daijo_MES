<x-app-layout>
    <div class="container mx-auto pt-10 px-10">
        <!-- Breadcrumb -->
        <nav class="flex mb-4 text-gray-700 text-sm font-medium" aria-label="Breadcrumb">
            <ol class="list-reset flex">
                <li>
                    <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline">Dashboard</a>
                </li>
                <li>
                    <span class="mx-2 text-gray-500">/</span>
                </li>
                <li>
                    <a href="{{ route('waiting_purchase_orders.index') }}" class="text-blue-600 hover:underline">
                        Waiting Purchase Orders
                    </a>
                </li>
                <li>
                    <span class="mx-2 text-gray-500">/</span>
                </li>
                <li class="text-gray-800">Create New Order</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <h1 class="text-3xl font-bold mb-5">Create New Waiting Purchase Order</h1>

        <!-- Create Form -->
        <form action="{{ route('waiting_purchase_orders.store') }}" method="POST" enctype="multipart/form-data"
            class="bg-white p-6 rounded shadow-md">
            @csrf
            @include('waiting_purchase_orders.form')
            <button type="submit"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Submit</button>
        </form>
    </div>
</x-app-layout>
