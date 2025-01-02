<x-app-layout>
    <div class="container mx-auto mt-5 px-10">
        <h1 class="text-2xl font-bold mb-5">Create New Waiting Purchase Order</h1>
        <form action="{{ route('waiting_purchase_orders.store') }}" method="POST" enctype="multipart/form-data"
            class="bg-white p-6 rounded shadow-md">
            @csrf
            @include('waiting_purchase_orders.form')
            <button type="submit"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Submit</button>
        </form>
    </div>
</x-app-layout>
