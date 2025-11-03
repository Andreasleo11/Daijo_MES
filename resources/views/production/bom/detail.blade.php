<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Breadcrumb -->
            <nav class="flex text-gray-500 text-sm mb-6" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-500 transition">
                    Dashboard
                </a>
                <span class="mx-2">/</span>
                <a href="{{ route('production.bom.index') }}" class="hover:text-blue-500 transition">
                    Bill of Materials
                </a>
                <span class="mx-2">/</span>
                <span class="text-gray-800 font-semibold">{{ $bomParent->code }}</span>
            </nav>
            <!-- Report Header -->
            <div class="bg-gradient-to-l from-gray-100 to-gray-50 shadow-md rounded-lg p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-semibold text-gray-700">{{ $bomParent->code }}</h1>
                        <h3 class="text-lg text-gray-600">{{ $bomParent->description }}</h3>
                        <h3 class="text-lg text-gray-600">{{ $bomParent->customer }}</h3>
                        <p class="text-sm text-gray-500 mt-1">Generated on: {{ now()->format('Y-m-d H:i') }}</p>
                        <p class="text-sm text-gray-500">Type: {{ ucfirst($bomParent->type) }}</p>
                    </div>
                    <div>
                        @if ($user->role->name === 'ADMIN')
                            <button onclick="toggleModal('editParentModal', true)"
                                class="inline-flex items-center px-4 py-2 bg-green-500 text-white rounded-md shadow hover:bg-green-600 transition">
                                Edit BOM Parent
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Cards Section -->
            @if ($user->role->name === 'WAREHOUSE' )
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-4">
                    <div
                        class="bg-gradient-to-r from-blue-500 to-blue-400 flex-1 p-6 border border-blue-300 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <p class="text-lg font-semibold text-white">Buy Finish</p>
                        <p class="text-2xl font-bold text-white mt-2">{{ $actionTypeCounts->get('buyfinish', 0) }}</p>
                    </div>
                    <div
                        class="bg-gradient-to-r from-green-500 to-green-400 flex-1 p-6 border border-green-300 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <p class="text-lg font-semibold text-white">Buy Process</p>
                        <p class="text-2xl font-bold text-white mt-2">{{ $actionTypeCounts->get('buyprocess', 0) }}</p>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div
                        class="bg-gradient-to-r from-blue-500 to-blue-400 flex-1 p-6 border border-blue-300 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <p class="text-lg font-semibold text-white">Buy Finish</p>
                        <p class="text-2xl font-bold text-white mt-2">{{ $actionTypeCounts->get('buyfinish', 0) }}</p>
                    </div>
                    <div
                        class="bg-gradient-to-r from-green-500 to-green-400 flex-1 p-6 border border-green-300 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <p class="text-lg font-semibold text-white">Buy Process</p>
                        <p class="text-2xl font-bold text-white mt-2">{{ $actionTypeCounts->get('buyprocess', 0) }}</p>
                    </div>
                    <div
                        class="bg-gradient-to-r from-purple-500 to-purple-400 flex-1 p-6 border border-purple-300 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <p class="text-lg font-semibold text-white">Stock Finish</p>
                        <p class="text-2xl font-bold text-white mt-2">{{ $actionTypeCounts->get('stockfinish', 0) }}
                        </p>
                    </div>
                    <div
                        class="bg-gradient-to-r from-red-500 to-red-400 flex-1 p-6 border border-red-300 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <p class="text-lg font-semibold text-white">Stock Process</p>
                        <p class="text-2xl font-bold text-white mt-2">{{ $actionTypeCounts->get('stockprocess', 0) }}
                        </p>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-md sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items center">
                        <h2 class="text-2xl font-semibold mb-4">{{ __('BOM Parent Details') }}</h2>
                        @if ($user->role->name === 'ADMIN')
                            <button onclick="toggleModal('addChildModal', true)"
                                class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-75">
                                Add Child
                            </button>

                            <button onclick="window.location.href='{{ route('printAllMaterial', ['id' => $bomParent->id]) }}'"
                                class="inline-flex items-center px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-opacity-75">
                                Print All Material
                            </button>

                            <form action="{{ route('excel.bom.upload') }}" method="POST" enctype="multipart/form-data" class="mt-2">
                                @csrf
                                <input type="hidden" name="bom_parent_id" value="{{ $bomParent->id }}">
                                
                                <input type="file" name="excel_file" accept=".xls,.xlsx" required
                                    class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                                
                                <button type="submit"
                                    class="mt-2 inline-flex items-center px-4 py-2 bg-purple-500 text-white rounded-md hover:bg-purple-600 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:ring-opacity-75">
                                    Upload Excel
                                </button>
                            </form>
                        @endif
                    </div>
                    <!-- Child Items Table -->
                    <h4 class="text-lg font-semibold mb-4">Child Items</h4>
                    <div class="mt-4 overflow-x-auto h-dvh">
                        <table class="min-w-full table-auto border-black border-2 bg-white shadow-md">
                            <thead class="bg-gray-200 text-gray-600 text-sm uppercase font-semibold">
                                <tr>
                                    <th class="py-3 px-4 text-left">Item Code</th>
                                    <th class="px-4 py-2 text-left">Item Description</th>
                                    <th class="px-4 py-2 text-left">Quantity</th>
                                    @if ($user->role->name === 'ADMIN')
                                        <th class="px-4 py-2 text-left">Broken Quantity</th>
                                    @endif
                                    <th class="px-4 py-2 text-left">Measure</th>
                                    <th class="px-4 py-2 text-left">Created At</th>
                                    <th class="px-4 py-2 text-left">Action Type</th>
                                    <th class="px-4 py-2 text-left">Action</th>
                                    <th class="px-4 py-2 text-left">Advance Actions</th>
                                    <th class="px-4 py-2 text-left">Status</th>
                                    @if ($user->role->name === 'ADMIN')
                                        <th class="px-4 py-2 text-left">Process Count</th>
                                    @endif
                                </tr>
                            </thead>
                            @if ($user->role->name === 'WAREHOUSE')
                                <tbody>
                                    <div class="max-height: 400px; overflow-y: auto;">
                                        @foreach ($bomParent->children as $child)
                                            @if ($child->action_type === 'buyfinish' || $child->action_type === 'buyprocess')
                                                <tr>
                                                    <td class="px-4 py-2 border-black border-2">{{ $child->item_code }}</td>
                                                    <td class="px-4 py-2 border-black border-2">{{ $child->item_description }}</td>
                                                    <td class="px-4 py-2 border-black border-2">{{ $child->quantity }}</td>
                                                    <td class="px-4 py-2 border-black border-2">{{ $child->measure }}</td>
                                                    <td class="px-4 py-2 border-black border-2">
                                                        {{ $child->created_at->format('Y-m-d H:i') }}</td>
                                                    <td class="px-4 py-2 border-black border-2">{{ $child->action_type ?? 'Unknown' }}
                                                    </td>
                                                    <td class="px-4 py-2 border-black border-2"></td>
                                                    <td class="px-4 py-2 border-black border-2">
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
                                                    <td class="px-4 py-2 border-black border-2">{{ $child->status }}</td>
                                                </tr>

                                                <!-- Edit Modal for Each Child -->
                                                @include('includes.edit-child-modal', ['child' => $child])
                                            @endif
                                        @endforeach
                                    </div>
                                </tbody>
                            @endif
                            @if($user->role->name === 'ADMIN')
                            <tbody class="text-gray-700">
                                <div class="max-height: 400px; overflow-y: auto;">
                                    @foreach ($children as $child)
                                        <tr>
                                            <td class="px-4 py-2 border-black border-2">
                                                {{ $child->item_code }}
                                            </td>
                                            <td class="px-4 py-2 border-black border-2">
                                                {{ $child->item_description }}</td>
                                            <td class="px-4 py-2 border-black border-2">
                                                {{ $child->quantity }}
                                            </td>
                                            @if ($user->role->name === 'ADMIN')
                                                <td class="px-4 py-2 border-black border-2">
                                                    {{ collect($child->brokenChild)->sum('broken_quantity') ?? 0 }}
                                                </td>
                                            @endif
                                            <td class="px-4 py-2 border-black border-2">
                                                {{ $child->measure }}
                                            </td>
                                            <td class="px-4 py-2 border-black border-2">
                                                {{ $child->created_at->format('Y-m-d H:i') }}</td>
                                            <td class="px-4 py-2 border-black border-2">
                                                {{ $child->action_type ?? 'Unknown' }}
                                            </td>
                                            <td class="px-4 py-2 border-black border-2">
                                                @if ($user->role->name === 'ADMIN')
                                                    <div class="space-2 space-x-2 flex flex-auto items-center">
                                                        @if ($child->status === 'Canceled')
                                                            <span
                                                                class="px-2 py-1 rounded bg-yellow-200 text-yellow-800">
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
                                                                <!-- <form
                                                                    action="{{ route('production.bom.child.destroy', $child->id) }}"
                                                                    method="POST">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit"
                                                                        onclick="return confirm('Are you sure you want to delete this item?')"
                                                                        class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                                                        Delete
                                                                    </button>
                                                                </form> -->
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
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 border-black border-2">
                                                <div class="space-2 flex flex-auto items-center">
                                                    @if ($child->status === 'Canceled')
                                                        <span class="px-2 py-1 rounded bg-yellow-200 text-yellow-800">
                                                            {{ $child->status }}
                                                        </span>
                                                    @elseif ($child->status === 'Finished' || $child->status === 'Finished - Modified')
                                                        @if ($child->materialProcess->isNotEmpty())
                                                            <a href="{{ route('production.child.detail.material', $child->id) }}"
                                                                class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 ml-2">
                                                                Show Detail
                                                            </a>
                                                        @endif
                                                        <button
                                                            onclick="toggleModal('add-broken-child-modal-{{ $child->id }}', true)"
                                                            class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 ml-2">
                                                            Add Broken Qty
                                                        </button>

                                                        <button
                                                            onclick="toggleModal('add-child-process-modal-{{ $child->id }}', true)"
                                                            class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 ml-2">
                                                            Add Process
                                                        </button>
                                                    @else
                                                        @if (is_null($child->action_type))
                                                            <!-- Show "Assign Type" button if action_type is null -->
                                                            <button
                                                                onclick="toggleModal('assign-item-type-{{ $child->id }}', true)"
                                                                class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">
                                                                Assign Type
                                                            </button>
                                                        @elseif ($child->action_type == 'buyfinish' || $child->action_type == 'stockfinish')
                                                            <!-- Add any specific handling for 'buy' if needed -->
                                                        @elseif($child->action_type == 'buyprocess')
                                                            @if ($child->status == 'Available' || $child->status == 'Started')
                                                                <button
                                                                    onclick="toggleModal('add-child-process-modal-{{ $child->id }}', true)"
                                                                    class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                                                                    Add Process
                                                                </button>
                                                            @else
                                                                Barang belom Sampai
                                                            @endif
                                                        @else
                                                            <!-- Show "Assign Process" button if action_type is not null -->
                                                            <button
                                                                onclick="toggleModal('add-child-process-modal-{{ $child->id }}', true)"
                                                                class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                                                                Add Process
                                                            </button>
                                                        @endif

                                                        @if ($child->materialProcess->isNotEmpty())
                                                            <form
                                                                action="{{ route('production.child.detail.material', $child->id) }}"
                                                                method="get">
                                                                <button type="submit"
                                                                    class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 ml-2">
                                                                    Detail
                                                                </button>
                                                            </form>
                                                        @endif

                                                        <button
                                                            onclick="toggleModal('add-broken-child-modal-{{ $child->id }}', true)"
                                                            class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 ml-2">
                                                            Add Broken Qty
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-4 py-2 border-black border-2">{{ $child->status }}
                                            </td>
                                            @if ($user->role->name === 'ADMIN')
                                                <td class="px-4 py-2 border-black border-2">
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
                                            @endif
                                        </tr>

                                        @include('includes.edit-child-modal', ['child' => $child])
                                        @include('includes.add-broken-child-item-modal', [
                                            'child' => $child,
                                        ])
                                        @include('includes.add-child-process-modal', ['child' => $child])
                                        @include('includes.assign-item-type-modal', ['child' => $child])
                                    @endforeach
                                </div>
                            </tbody> 
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('includes.edit-parent-modal', ['bomParent' => $bomParent])

    @include('includes.add-child-modal', ['bomParent' => $bomParent])

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
