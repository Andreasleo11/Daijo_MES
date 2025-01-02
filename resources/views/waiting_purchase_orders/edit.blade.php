<x-app-layout>
    <div class="container mx-auto mt-5 px-10">
        <h1 class="text-2xl font-bold mb-5">Edit Waiting Purchase Order</h1>
        <form action="{{ route('waiting_purchase_orders.update', $waitingPurchaseOrder->id) }}" method="POST"
            class="bg-white p-6 rounded shadow-md">
            @csrf
            @method('PUT')
            @include('waiting_purchase_orders.form', ['waitingPurchaseOrder' => $waitingPurchaseOrder])
            <button type="submit"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Submit</button>
        </form>
    </div>
</x-app-layout>
