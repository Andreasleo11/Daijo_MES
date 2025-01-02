<x-app-layout>
    <div class="container mx-auto mt-10 px-10">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Purchase Order Details</h1>
        <div class="bg-white p-8 rounded-lg shadow-lg flex flex-col md:flex-row items-center md:items-start">
            <!-- Details Section -->
            <div class="flex-1 md:pr-8">
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <p class="text-sm font-semibold text-gray-500 uppercase">Mold Name</p>
                        <p class="text-lg font-medium text-gray-800">{{ $waitingPurchaseOrder->mold_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-500 uppercase">Process</p>
                        <p class="text-lg font-medium text-gray-800">{{ $waitingPurchaseOrder->process }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-500 uppercase">Price</p>
                        <p class="text-lg font-medium text-gray-800">Rp.
                            {{ number_format($waitingPurchaseOrder->price) }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-500 uppercase">Quotation No</p>
                        <p class="text-lg font-medium text-gray-800">{{ $waitingPurchaseOrder->quotation_no }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-500 uppercase">Status</p>
                        <p class="text-lg font-medium">
                            @if ($waitingPurchaseOrder->status === 1)
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded bg-red-100 text-red-800 text-xs font-semibold">
                                    Belum PO
                                </span>
                            @elseif($waitingPurchaseOrder->status === 2)
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded bg-green-100 text-green-800 text-xs font-semibold">
                                    Sudah PO
                                </span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-500 uppercase">Remark</p>
                        <p class="text-lg font-medium text-gray-800">{{ $waitingPurchaseOrder->remark }}</p>
                    </div>
                </div>
            </div>

            <!-- Image Section -->
            @if ($waitingPurchaseOrder->capture_photo_path)
                <div class="flex-shrink-0">
                    <img src="{{ asset('storage/uploads/' . $waitingPurchaseOrder->capture_photo_path) }}"
                        alt="Capture Photo" class="rounded-lg shadow-md max-w-xs md:max-w-sm object-contain">
                </div>
            @endif
        </div>
        <div class="flex justify-start mt-6">
            <a href="{{ route('waiting_purchase_orders.index') }}"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow">
                Back to List
            </a>
        </div>
    </div>
</x-app-layout>
