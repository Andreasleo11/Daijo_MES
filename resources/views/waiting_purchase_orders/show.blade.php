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
                <li class="text-gray-800">Details</li>
            </ol>
        </nav>
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
                        <p class="text-lg font-medium text-gray-800">{{ $waitingPurchaseOrder->remark ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Files Section -->
            <div class="flex-shrink-0 md:pl-8">
                <p class="text-sm font-semibold text-gray-500 uppercase mb-4">Attached Files</p>
                <div class="grid gap-4">
                    @if ($waitingPurchaseOrder->files && $waitingPurchaseOrder->files->count() > 0)
                        @foreach ($waitingPurchaseOrder->files as $file)
                            <div class="flex items-center gap-4 bg-gray-50 p-4 rounded shadow">
                                <span>📄</span>
                                <a href="{{ asset('storage/files/' . $file->name) }}" target="_blank"
                                    class="text-blue-500 underline">{{ $file->name }}</a>
                                <span class="text-gray-500 text-sm">
                                    ({{ number_format($file->size / 1024 / 1024, 2) }} MB)
                                </span>
                            </div>
                        @endforeach
                    @else
                        <p class="text-gray-500">No files attached.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex justify-start mt-6">
            <a href="{{ route('waiting_purchase_orders.index') }}"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow">
                Back to List
            </a>
        </div>
    </div>
</x-app-layout>
