<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <nav class="flex text-gray-500 text-sm mb-6" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-500">
                    Dashboard
                </a>
                <span class="mx-2">/</span>
                <a href="{{ route('production.bom.index') }}" class="hover:text-blue-500">
                    Bill of Materials
                </a>
                <span class="mx-2">/</span>
                <span class="text-gray-800 font-semibold">{{ $bomParent->code }}</span>
            </nav>
            <!-- Report Header -->
            <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-medium">{{ $bomParent->code }}</h1>
                        <p class="text-lg">{{ $bomParent->description }}</p>
                        <h3 class="text-lg">{{ $bomParent->customer }}</h3>
                        <p class="text-sm text-gray-600">Generated on: {{ now()->format('Y-m-d H:i') }}</p>
                        <p class="text-sm text-gray-600">Type: {{ ucfirst($bomParent->type) }}</p>
                    </div>
                    <div>
                        {{-- <button onclick="window.print()"
                            class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                            Print/Export Report
                        </button> --}}
                        <button onclick="toggleModal('parentModal', true)"
                            class="inline-flex items-center px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-opacity-75">
                            Edit BOM Parent
                        </button>
                    </div>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow-md sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items center">
                        <h2 class="text-2xl font-semibold mb-4">{{ __('BOM Parent Details') }}</h2>
                        <button onclick="toggleModal('addChildModal', true)"
                            class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-75">
                            Add Child
                        </button>
                    </div>
                    <!-- Child Items Table -->
                    <h4 class="text-lg font-semibold mb-4">Child Items</h4>
                    <div class="mt-4">
                        <table class="min-w-full table-auto">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 border">Item Code</th>
                                    <th class="px-4 py-2 border">Item Description</th>
                                    <th class="px-4 py-2 border">Quantity</th>
                                    <th class="px-4 py-2 border">Measure</th>
                                    <th class="px-4 py-2 border">Created At</th>
                                    <th class="px-4 py-2 border">Action Type</th>
                                    <th class="px-4 py-2 border">Action</th>
                                    <th class="px-4 py-2 border">Advance Actions</th>
                                    <th class="px-4 py-2 border">Status</th>
                                    @if ($user->role->name === 'ADMIN')
                                        <th class="px-4 py-2 border">Process Count</th>
                                    @endif
                                </tr>
                            </thead>
                            @if ($user->role->name === 'WAREHOUSE')
                                <tbody>
                                    @foreach ($bomParent->child as $child)
                                        @if ($child->action_type === 'buyfinish' || $child->action_type === 'buyprocess')
                                            <tr>
                                                <td class="px-4 py-2 border">{{ $child->item_code }}</td>
                                                <td class="px-4 py-2 border">{{ $child->item_description }}</td>
                                                <td class="px-4 py-2 border">{{ $child->quantity }}</td>
                                                <td class="px-4 py-2 border">{{ $child->measure }}</td>
                                                <td class="px-4 py-2 border">
                                                    {{ $child->created_at->format('Y-m-d H:i') }}</td>
                                                <td class="px-4 py-2 border">{{ $child->action_type ?? 'Unknown' }}
                                                </td>
                                                <td class="px-4 py-2 border"></td>
                                                <td class="px-4 py-2 border">
                                                    @if ($child->status === 'Finished' || $child->status === 'Available')
                                                        <span class="text-green-500 font-bold">Item Arrived</span>
                                                    @else
                                                        <form
                                                            action="{{ route('production.bom.child.updateStatus', $child->id) }}"
                                                            method="POST" class="inline-block ml-2">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="submit"
                                                                onclick="return confirm('Mark item as arrived and available?')"
                                                                class="bg-purple-500 text-white px-3 py-1 rounded hover:bg-purple-600">
                                                                Mark as Arrived
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2 border">{{ $child->status }}</td>
                                            </tr>



                                            <!-- Edit Modal for Each Child -->
                                            <div id="modal-{{ $child->id }}"
                                                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
                                                <div class="bg-white rounded-lg shadow-lg p-6 max-w-lg w-full">
                                                    <h3 class="text-xl font-semibold mb-4">Edit Child Item</h3>
                                                    <form
                                                        action="{{ route('production.bom.child.update', $child->id) }}"
                                                        method="POST">
                                                        @csrf

                                                        <div class="mb-4">
                                                            <label class="block text-sm font-medium">Item Code</label>
                                                            <input type="text" name="item_code"
                                                                value="{{ $child->item_code }}"
                                                                class="w-full border rounded px-4 py-2" required>
                                                        </div>

                                                        <div class="mb-4">
                                                            <label class="block text-sm font-medium">Item
                                                                Description</label>
                                                            <input type="text" name="item_description"
                                                                value="{{ $child->item_description }}"
                                                                class="w-full border rounded px-4 py-2" required>
                                                        </div>

                                                        <div class="mb-4">
                                                            <label class="block text-sm font-medium">Quantity</label>
                                                            <input type="number" name="quantity"
                                                                value="{{ $child->quantity }}"
                                                                class="w-full border rounded px-4 py-2" required>
                                                        </div>

                                                        <div class="mb-4">
                                                            <label class="block text-sm font-medium">Measure</label>
                                                            <input type="text" name="measure"
                                                                value="{{ $child->measure }}"
                                                                class="w-full border rounded px-4 py-2" required>
                                                        </div>

                                                        <div class="flex justify-end mt-6">
                                                            <button type="button"
                                                                onclick="toggleModal('modal-{{ $child->id }}', false)"
                                                                class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-2">
                                                                Cancel
                                                            </button>
                                                            <button type="submit"
                                                                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                                                Save Changes
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>


                                            <div id="modal-assign-{{ $child->id }}"
                                                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
                                                <div class="bg-white rounded-lg shadow-lg p-6 max-w-lg w-full">
                                                    <h3 class="text-xl font-semibold mb-4">Assign Action Type</h3>
                                                    <form
                                                        action="{{ route('production.bom.child.assign_type', $child->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('PUT')

                                                        <div class="mb-4">
                                                            <label class="block text-sm font-medium">Action Type</label>
                                                            <select name="action_type"
                                                                class="w-full border rounded px-4 py-2" required>
                                                                <option value="buy"
                                                                    {{ $child->action_type == 'buy' ? 'selected' : '' }}>
                                                                    Buy</option>
                                                                <option value="make"
                                                                    {{ $child->action_type == 'make' ? 'selected' : '' }}>
                                                                    Make</option>
                                                            </select>
                                                        </div>

                                                        <div class="flex justify-end mt-6">
                                                            <button type="button"
                                                                onclick="toggleModal('modal-assign-{{ $child->id }}', false)"
                                                                class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-2">
                                                                Cancel
                                                            </button>
                                                            <button type="submit"
                                                                class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                                                Assign Type
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </tbody>
                            @else
                                <tbody>
                                    @foreach ($bomParent->child as $child)
                                        <tr>
                                            <td class="px-4 py-2 border">{{ $child->item_code }}</td>
                                            <td class="px-4 py-2 border">{{ $child->item_description }}</td>
                                            <td class="px-4 py-2 border">{{ $child->quantity }}</td>
                                            <td class="px-4 py-2 border">{{ $child->measure }}</td>
                                            <td class="px-4 py-2 border">{{ $child->created_at->format('Y-m-d H:i') }}
                                            </td>
                                            <td class="px-4 py-2 border">{{ $child->action_type ?? 'Unknown' }}</td>
                                            <td class="px-4 py-2 border">
                                                <div class="space-y-2 text-center">
                                                    @if ($child->status === 'Canceled')
                                                        <span class="px-2 py-1 rounded bg-yellow-200 text-yellow-800">
                                                            {{ $child->status }}
                                                        </span>
                                                    @elseif ($child->status === 'Finished')
                                                        <!-- Show only the Cancel button -->
                                                        <button
                                                            onclick="toggleModal('modal-{{ $child->id }}', true)"
                                                            class="bg-yellow-500 text-black px-3 py-1 rounded hover:bg-yellow-600">
                                                            Edit
                                                        </button>
                                                        <form
                                                            action="{{ route('production.bom.child.cancel', $child->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="submit"
                                                                onclick="return confirm('Are you sure you want to cancel this material?')"
                                                                class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                                                                Cancel
                                                            </button>
                                                        </form>
                                                    @else
                                                        <button
                                                            onclick="toggleModal('modal-{{ $child->id }}', true)"
                                                            class="bg-yellow-500 text-black px-3 py-1 rounded hover:bg-yellow-600">
                                                            Edit
                                                        </button>
                                                        @if (!in_array($child->status, ['Started', 'Finished']))
                                                            <form
                                                                action="{{ route('production.bom.child.destroy', $child->id) }}"
                                                                method="POST">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    onclick="return confirm('Are you sure you want to delete this item?')"
                                                                    class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                                                    Delete
                                                                </button>
                                                            </form>
                                                        @endif
                                                        <form
                                                            action="{{ route('production.bom.child.cancel', $child->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="submit"
                                                                onclick="return confirm('Are you sure you want to cancel this material?')"
                                                                class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                                                                Cancel
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-4 py-2 border">
                                                <div class="space-y-2 text-center">
                                                    @if ($child->status === 'Canceled')
                                                        <span class="px-2 py-1 rounded bg-yellow-200 text-yellow-800">
                                                            {{ $child->status }}
                                                        </span>
                                                    @elseif ($child->status === 'Finished')
                                                        @if ($child->materialProcess->isNotEmpty())
                                                            <a href="{{ route('production.child.detail.material', $child->id) }}"
                                                                class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 ml-2">
                                                                Show Detail
                                                            </a>
                                                        @endif
                                                    @else
                                                        @if (is_null($child->action_type))
                                                            <!-- Show "Assign Type" button if action_type is null -->
                                                            <button
                                                                onclick="toggleModal('modal-assign-{{ $child->id }}', true)"
                                                                class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">
                                                                Assign Type
                                                            </button>
                                                        @elseif ($child->action_type == 'buyfinish' || $child->action_type == 'stockfinish')
                                                            <!-- Add any specific handling for 'buy' if needed -->
                                                        @elseif($child->action_type == 'buyprocess')
                                                            @if ($child->status == 'Available' || $child->status == 'Started')
                                                                <button
                                                                    onclick="toggleModal('modal-process-{{ $child->id }}', true)"
                                                                    class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                                                                    Assign Process
                                                                </button>
                                                            @else
                                                                Barang belom Sampai
                                                            @endif
                                                        @else
                                                            <!-- Show "Assign Process" button if action_type is not null -->
                                                            <button
                                                                onclick="toggleModal('modal-process-{{ $child->id }}', true)"
                                                                class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                                                                Assign Process
                                                            </button>
                                                        @endif

                                                        @if ($child->materialProcess->isNotEmpty())
                                                            <form
                                                                action="{{ route('production.child.detail.material', $child->id) }}"
                                                                method="get">
                                                                <button type="submit"
                                                                    class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 ml-2">
                                                                    Show Detail
                                                                </button>
                                                            </form>
                                                        @endif

                                                        <button
                                                            onclick="toggleModal('modal-broken-{{ $child->id }}', true)"
                                                            class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 ml-2">
                                                            Add Broken Quantity
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-4 py-2 border">{{ $child->status }}</td>
                                            <td class="px-4 py-2 border">
                                                {{-- Styled Total / Finished / Not Finished --}}
                                                <span
                                                    style="color: blue;">{{ $child->materialProcess->count() }}</span>
                                                /
                                                <span
                                                    style="color: green;">{{ $child->materialProcess->filter(fn($process) => $process->scan_out !== null)->count() }}</span>
                                                /
                                                <span
                                                    style="color: red;">{{ $child->materialProcess->filter(fn($process) => $process->scan_out === null)->count() }}</span>
                                            </td>
                                        </tr>

                                        <div id="modal-process-{{ $child->id }}"
                                            class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden z-50">
                                            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
                                                <h2 class="text-xl font-bold mb-4">Assign Process for Material</h2>

                                                <!-- Repeatable Process List -->
                                                <form id="process-form-{{ $child->id }}" method="POST"
                                                    action="{{ route('production.bom.child.assign_process', $child->id) }}">
                                                    @csrf <!-- Include CSRF token for security -->
                                                    <div id="process-list-{{ $child->id }}" class="space-y-3">
                                                        <div class="flex space-x-3 process-item">
                                                            <select name="all_process[]"
                                                                class="process-select bg-gray-100 rounded p-2 w-full">
                                                                <option value="CNC">CNC</option>
                                                                <option value="EDM">EDM</option>
                                                                <option value="WIRECUT">WIRECUT</option>
                                                                <option value="GUNDRILL">GUNDRILL</option>
                                                                <option value="QC">QC</option>
                                                                <option value="POLISH">POLISH</option>
                                                                <option value="ASSEMBLY">ASSEMBLY</option>
                                                                <option value="MANUAL">MANUAL</option>
                                                            </select>
                                                            <button type="button" onclick="removeProcess(this)"
                                                                class="bg-red-500 text-white px-2 rounded">Remove</button>
                                                        </div>
                                                    </div>

                                                    <!-- Add New Process Button -->
                                                    <button type="button"
                                                        onclick="addProcess('{{ $child->id }}')"
                                                        class="bg-green-500 text-white mt-4 px-3 py-1 rounded hover:bg-green-600">
                                                        Add Another Process
                                                    </button>

                                                    <!-- Modal Actions -->
                                                    <div class="mt-6 flex justify-end space-x-3">
                                                        <button type="button"
                                                            onclick="toggleModal('modal-process-{{ $child->id }}', false)"
                                                            class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Cancel</button>
                                                        <button type="submit"
                                                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Save</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Edit Modal for Each Child -->
                                        <div id="modal-{{ $child->id }}"
                                            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
                                            <div class="bg-white rounded-lg shadow-lg p-6 max-w-lg w-full">
                                                <h3 class="text-xl font-semibold mb-4">Edit Child Item</h3>
                                                <form action="{{ route('production.bom.child.update', $child->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PUT')

                                                    <div class="mb-4">
                                                        <label class="block text-sm font-medium">Item Code</label>
                                                        <input type="text" name="item_code"
                                                            value="{{ $child->item_code }}"
                                                            class="w-full border rounded px-4 py-2" required>
                                                    </div>

                                                    <div class="mb-4">
                                                        <label class="block text-sm font-medium">Item
                                                            Description</label>
                                                        <input type="text" name="item_description"
                                                            value="{{ $child->item_description }}"
                                                            class="w-full border rounded px-4 py-2" required>
                                                    </div>

                                                    <div class="mb-4">
                                                        <label class="block text-sm font-medium">Quantity</label>
                                                        <input type="number" name="quantity"
                                                            value="{{ $child->quantity }}"
                                                            class="w-full border rounded px-4 py-2" required>
                                                    </div>

                                                    <div class="mb-4">
                                                        <label class="block text-sm font-medium">Measure</label>
                                                        <input type="text" name="measure"
                                                            value="{{ $child->measure }}"
                                                            class="w-full border rounded px-4 py-2" required>
                                                    </div>

                                                    <div class="flex justify-end mt-6">
                                                        <button type="button"
                                                            onclick="toggleModal('modal-{{ $child->id }}', false)"
                                                            class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-2">
                                                            Cancel
                                                        </button>
                                                        <button type="submit"
                                                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                                            Save Changes
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>


                                        <div id="modal-assign-{{ $child->id }}"
                                            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
                                            <div class="bg-white rounded-lg shadow-lg p-6 max-w-lg w-full">
                                                <h3 class="text-xl font-semibold mb-4">Assign Action Type</h3>
                                                <form
                                                    action="{{ route('production.bom.child.assign_type', $child->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PUT')

                                                    <div class="mb-4">
                                                        <label class="block text-sm font-medium">Action Type</label>
                                                        <select name="action_type"
                                                            class="w-full border rounded px-4 py-2" required>
                                                            <option value="buyfinish"
                                                                {{ $child->action_type == 'buyfinish' ? 'selected' : '' }}>
                                                                Buy Finish</option>
                                                            <option value="buyprocess"
                                                                {{ $child->action_type == 'buyprocess' ? 'selected' : '' }}>
                                                                Buy Process</option>
                                                            <option value="stockfinish"
                                                                {{ $child->action_type == 'stockfinish' ? 'selected' : '' }}>
                                                                Stock Finish</option>
                                                            <option value="stockprocess"
                                                                {{ $child->action_type == 'stockprocess' ? 'selected' : '' }}>
                                                                Stock Process</option>
                                                        </select>
                                                    </div>

                                                    <div class="flex justify-end mt-6">
                                                        <button type="button"
                                                            onclick="toggleModal('modal-assign-{{ $child->id }}', false)"
                                                            class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-2">
                                                            Cancel
                                                        </button>
                                                        <button type="submit"
                                                            class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                                            Assign Type
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <div id="modal-broken-{{ $child->id }}"
                                            class="fixed inset-0 flex items-center justify-center hidden bg-black bg-opacity-50 z-50">
                                            <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
                                                <h2 class="text-lg font-bold mb-4">Add Broken Quantity for
                                                    {{ $child->item_code }}</h2>
                                                <form
                                                    action="{{ route('production.bom.child.addBrokenQuantity', $child->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    <div class="mb-4">
                                                        <label for="broken_quantity_{{ $child->id }}"
                                                            class="block text-sm font-medium text-gray-700">Broken
                                                            Quantity</label>
                                                        <input type="number" name="broken_quantity"
                                                            id="broken_quantity_{{ $child->id }}"
                                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                            required>
                                                    </div>
                                                    <div class="mb-4">
                                                        <label for="remark_{{ $child->id }}"
                                                            class="block text-sm font-medium text-gray-700">Remark</label>
                                                        <textarea name="remark" id="remark_{{ $child->id }}"
                                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                            placeholder="Optional"></textarea>
                                                    </div>
                                                    <div class="flex justify-end">
                                                        <button type="button"
                                                            onclick="toggleModal('modal-broken-{{ $child->id }}', false)"
                                                            class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-2">
                                                            Cancel
                                                        </button>
                                                        <button type="submit"
                                                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                                            Submit
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            @endif
                        </table>
                    </div>

                    <div class="mt-6">
                        <a href="{{ route('production.bom.index') }}" class="text-blue-500 hover:text-blue-700">
                            Back to BOM Index
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('includes.parent-modal', ['bomParent' => $bomParent])

    @include('includes.child-modal', ['bomParent' => $bomParent])

    <script>
        function toggleModal(modalId, show) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.toggle('hidden', !show);
            }
        }

        function addProcess(childId) {
            const processList = document.getElementById(`process-form-${childId}`);
            const processItem = document.createElement('div');
            processItem.classList.add('flex', 'space-x-3', 'process-item');
            processItem.innerHTML = `
            <select name="all_process[]" class="process-select bg-gray-100 rounded p-2 w-full">
                <option value="CNC">CNC</option>
                <option value="EDM">EDM</option>
                <option value="WIRECUT">WIRECUT</option>
                <option value="GUNDRILL">GUNDRILL</option>
                <option value="QC">QC</option>
                <option value="POLISH">POLISH</option>
                <option value="ASSEMBLY">ASSEMBLY</option>
                <option value="MANUAL">MANUAL</option>
            </select>
            <button type="button" onclick="removeProcess(this)" class="bg-red-500 text-white px-2 rounded">Remove</button>
        `;
            processList.appendChild(processItem);
        }

        // Function to remove a process dropdown
        function removeProcess(button) {
            button.parentElement.remove();
        }

        // Example function to handle form submission (customize as needed)
        function submitProcesses(childId) {
            const selectedProcesses = [...document.querySelectorAll(`#process-list-${childId} .process-select`)]
                .map(select => select.value);

            console.log(`Selected processes for material ${childId}:`, selectedProcesses);

            // Add your form submission logic here (AJAX request, etc.)

            toggleModal(`modal-process-${childId}`, false); // Close the modal after saving
        }
    </script>
</x-app-layout>
