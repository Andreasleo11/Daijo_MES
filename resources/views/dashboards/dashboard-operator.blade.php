<x-app-layout>
    <!-- Display Success and Error Messages -->
    <div class="p-3">
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-3 py-2 rounded mb-2" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-3 py-2 rounded mb-2" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Display Validation Errors -->
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-3 py-2 rounded" role="alert">
                <strong class="font-bold">Error!</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    @if (is_null($machinejobid->employee_name))
        <div class="flex items-center justify-center">
            <form action="{{ route('updateEmployeeName') }}" method="POST" x-data="{ focus: true }"
                x-init="$nextTick(() => $refs.employeeName.focus())">
                @csrf
                <!-- Text field for user to input their name -->
                <input x-ref="employeeName" type="text" name="employee_name" placeholder="Enter your name"
                    class="px-3 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    required>
                <!-- Submit button -->
                <button type="submit"
                    class="text-indigo-400 border-indigo-400 bg-indigo-50 hover:bg-indigo-600 hover:text-white border py-1 px-2 rounded-md ">Submit</button>
            </form>
        </div>
    @else
        <div x-data="scanModeHandler({{ session('deactivateScanMode') ? 'true' : 'false' }})" x-init="initialize()" class="py-4">
            <!-- Scan Mode Toggle Section -->
            <div class="px-6">
                <div x-show="scanMode" id="scanModeBanner"
                    class="p-3 bg-yellow-100 border border-yellow-500 text-yellow-700 rounded mb-4" x-cloak>
                    <strong>Scan Mode is Active!</strong> Only the Scan Barcode section is visible. Please scan your
                    items.
                </div>
            </div>

            <!-- Toggle Scan Mode -->
            <div class="flex justify-end px-6">
                <button x-on:click="toggleScanMode()" x-text="scanMode ? 'Deactivate Scan Mode' : 'Activate Scan Mode'"
                    :class="scanMode ? 'bg-red-600 hover:bg-red-700' : 'bg-indigo-600 hover:bg-indigo-700'"
                    class="py-2 px-4 text-white font-semibold rounded-md transition">
                </button>
            </div>

            <!-- Other Sections to be Hidden in Scan Mode -->
            <div x-show="!scanMode" class="not-scan-section mt-2" x-cloak>
                <!-- Active Job Section -->
                <div class="mx-auto sm:px-4 lg:px-6 pt-2">
                    <div class="bg-white shadow-sm sm:rounded-lg p-4">
                        <div class="text-gray-900">
                            <span class="font-bold ">Active Job:</span>
                            @if ($itemCode)
                                <span class="text-blue-500">{{ $itemCode }}</span>
                                <a href="{{ route('reset.job') }}"
                                    class="ms-2 text-red-400 border-red-400 bg-red-50 hover:bg-red-600 hover:text-white border py-2 px-2 rounded-md">
                                    Reset job</a>
                            @else
                                <span class="text-red-500">No item code scanned</span>
                                <p class="text-gray-400 text-sm">You must scan the master list barcode as assigned in
                                    the
                                    daily item codes.</p>
                                @if ($datas->isNotEmpty())
                                    <div class="mt-1">
                                        <form action="{{ route('update.machine_job') }}" method="POST">
                                            @csrf
                                            <div>
                                                <input type="text" id="item_code" name="item_code" required
                                                    class="px-3 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('item_code') border-red-500 @enderror"
                                                    placeholder="Item Code" />
                                                <button type="submit"
                                                    class="py-1 px-3 bg-indigo-600 text-white font-semibold rounded-md hover:bg-indigo-700 transition inline-flex">
                                                    Update Job
                                                </button>

                                                @error('item_code')
                                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            @endif

                        </div>
                    </div>
                </div>

                <!-- Files Section -->
                <div class="mx-auto sm:px-4 lg:px-6 pt-2">
                    <div class="bg-white shadow-sm sm:rounded-lg p-4">
                        @if ($itemCode)
                            <section>
                                @if (count($files) > 0)
                                    <div class="font-bold text-2xl">Files</div>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 mt-4">
                                        @foreach ($files as $file)
                                            <a href="{{ asset('files/' . $file->name) }}" data-fancybox="gallery"
                                                data-caption="{{ $file->name }}">
                                                <img class="w-full h-auto rounded-lg shadow-lg hover:shadow-2xl transition-transform transform hover:scale-105"
                                                    src="{{ asset('files/' . $file->name) }}"
                                                    alt="{{ $file->name }}" />
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-red-500 text-sm my-2">No files attached to this item code.</p>
                                @endif
                            </section>
                        @else
                            <h1 class="font-bold text-xl">Files</h1>
                            <p class="text-red-500 text-sm my-2">Please scan the master list first.</p>
                        @endif
                    </div>
                </div>

                <!-- Daily Production Plan Section -->
                <div class="mx-auto sm:px-4 lg:px-6 pt-6">
                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="p-4">
                            <h3 class="text-xl font-bold mb-2">Daily Production Plan <span
                                    class="text-gray-400">(Assigned
                                    Item Code)</span></h3>
                            @if ($datas->isNotEmpty())
                                <table
                                    class="min-w-full bg-white shadow-md rounded-lg overflow-hidden text-center mt-3">
                                    <thead class="bg-indigo-100">
                                        <tr>
                                            <th class="py-1 px-2 text-gray-700">Item Code</th>
                                            <th class="py-1 px-2 text-gray-700">Start Date - End Date</th>
                                            <th class="py-1 px-2 text-gray-700">Shift</th>
                                            <th class="py-1 px-2 text-gray-700">Quantity</th>
                                            <th class="py-1 px-2 text-gray-700">Loss Package Quantity</th>
                                            <th class="py-1 px-2 text-gray-700">Actual Quantity</th>
                                            @if ($itemCode)
                                                <th class="py-1 px-2 text-gray-700">Action</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($datas as $data)
                                            @php
                                                $startTime = \Carbon\Carbon::parse($data->start_time)->format('H:i');
                                                $endTime = \Carbon\Carbon::parse($data->end_time)->format('H:i');
                                                $startDate = \Carbon\Carbon::parse($data->start_date)->format('d/m/Y'); // Format start date as dd/mm/yyyy
                                                $endDate = \Carbon\Carbon::parse($data->end_date)->format('d/m/Y'); // Format end date as dd/mm/yyyy
                                            @endphp

                                            <tr class="bg-white border-b text-center">
                                                <td class="py-1 px-2">{{ $data->item_code }}</td>
                                                <td class="py-1 px-2">{{ $startDate }} - {{ $endDate }}</td>
                                                <td class="py-1 px-2">{{ $data->shift }} ({{ $startTime }} -
                                                    {{ $endTime }})</td>
                                                <td class="py-1 px-2">{{ $data->quantity }}</td>
                                                <td class="py-1 px-2">{{ $data->loss_package_quantity }}</td>
                                                <td class="py-1 px-2">{{ $data->actual_quantity }}</td>
                                                @if ($itemCode)
                                                    @php
                                                        $disabled = $data->shift !== $machineJobShift;
                                                    @endphp
                                                    <td class="py-1 px-2">
                                                        <form
                                                            action="{{ route('generate.itemcode.barcode', ['item_code' => $data->item_code, 'quantity' => $data->quantity]) }}"
                                                            method="get">
                                                            <button
                                                                class="m-1 p-2 rounded text-white focus:outline-none transition ease-in-out duration-150 {{ $disabled ? 'bg-gray-500 cursor-not-allowed' : 'bg-indigo-500 hover:bg-indigo-800' }}"
                                                                {{ $disabled ? 'disabled' : '' }}>
                                                                Generate Barcode
                                                            </button>
                                                        </form>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p class="text-red-500 text-sm">No assigned item code yet</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scan Barcode Section -->
            <div x-show="scanMode" class="mx-auto sm:px-4 lg:px-6 pt-6" x-cloak>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <span class="text-xl font-bold">SPK</span>
                    <table class="min-w-full bg-white mt-2 rounded-md shadow-md overflow-hidden">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-1 px-2">SPK Number</th>
                                <th class="py-1 px-2">Item Code</th>
                                <th class="py-1 px-2">Scanned Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($uniquedata as $data)
                                <tr class="bg-white text-center">
                                    <td class="py-2 px-3">{{ $data['spk'] }}</td>
                                    <td class="py-2 px-3">{{ $data['item_code'] }}</td>
                                    <td class="py-2 px-3">{{ $data['scannedData'] }}/{{ $data['count'] }}</td>
                                </tr>
                            @empty
                                No unique data
                            @endforelse
                        </tbody>
                    </table>

                    <form method="GET" action="{{ route('reset.jobs') }}" id="resetJobsForm">
                        <input type="hidden" id="uniqueData" name="uniqueData"
                            value="{{ json_encode($uniquedata) }}" />
                        <input type="hidden" id="datas" name="datas" value="{{ json_encode($datas) }}" />

                        <button type="submit" id="resetJobsButton"
                            class="w-full py-2 px-4 bg-green-600 text-white font-semibold rounded-md hover:bg-green-700 transition mt-4">
                            Submit
                        </button>
                    </form>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-4 mt-6">
                    <h3 class="text-xl font-bold">Scan Barcode</h3>
                    <form id="scanForm" action="{{ route('process.productionbarcode') }}" method="POST"
                        class="space-y-3" x-data="autoSubmitForm()">
                        @csrf
                        <input type="hidden" id="uniqueData" name="uniqueData"
                            value="{{ json_encode($uniquedata) }}" />
                        <input type="hidden" id="datas" name="datas" value="{{ json_encode($datas) }}" />

                        <!-- Grid Layout for 2 Columns -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="spk_code">SPK Code</label>
                                <input type="text" id="spk_code" name="spk_code" required
                                    class="border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 w-full"
                                    placeholder="SPK Code" x-on:input="checkAndSubmitForm()" />
                            </div>
                            <div>
                                <label for="item_code">Item Code</label>
                                <input type="text" id="item_code" name="item_code" required
                                    class="border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 w-full"
                                    placeholder="Item Code" x-on:input="checkAndSubmitForm()" />
                            </div>
                            <div>
                                <label for="warehouse">Warehouse</label>
                                <input type="text" id="warehouse" name="warehouse" required
                                    class="border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 w-full"
                                    placeholder="Warehouse" x-on:input="checkAndSubmitForm()" />
                            </div>
                            <div>
                                <label for="quantity">Quantity</label>
                                <input type="number" id="quantity" name="quantity" required
                                    class="border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 w-full"
                                    placeholder="Quantity" x-on:input="checkAndSubmitForm()" />
                            </div>
                            <div>
                                <label for="label">Label</label>
                                <input type="number" id="label" name="label" required
                                    class="border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 w-full"
                                    placeholder="Label" x-on:input="checkAndSubmitForm()" />
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit"
                            class="w-full py-2 px-4 bg-indigo-600 text-white font-semibold rounded-md hover:bg-indigo-700 transition mt-4">
                            Scan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif



    <script type="module">
        Fancybox.bind('[data-fancybox="gallery"]', {
            Thumbs: {
                autoStart: true,
            },
            Image: {
                zoom: true,
            },
            transitionEffect: "fade",
        });
    </script>

    <script>
        function scanModeHandler(deactivateScanModeFlag) {
            return {
                scanMode: true,
                initialize() {
                    if (deactivateScanModeFlag == true) {
                        this.scanMode = false;
                        localStorage.setItem('scanMode', false);
                    } else {
                        this.scanMode = localStorage.getItem('scanMode') === 'true';
                    }

                    // Automatically focus on spk_code input if scanMode is active
                    if (this.scanMode) {
                        this.focusOnSPKCode();
                    }
                },
                toggleScanMode() {
                    this.scanMode = !this.scanMode;
                    localStorage.setItem('scanMode', this.scanMode);

                    // Focus on spk_code input when scanMode is activated
                    if (this.scanMode) {
                        this.focusOnSPKCode();
                    }
                },
                focusOnSPKCode() {
                    // Delay to ensure the element is rendered before focusing
                    setTimeout(() => {
                        document.getElementById('spk_code').focus();
                    }, 100); // Small delay to ensure the input is available in the DOM
                }
            };
        }

        function autoSubmitForm() {
            return {
                checkAndSubmitForm() {
                    const inputs = document.querySelectorAll(
                        '#scanForm input[type="text"], #scanForm input[type="number"]');
                    let allFilled = true;

                    inputs.forEach(input => {
                        if (input.value.trim() === '') {
                            allFilled = false;
                        }
                    });

                    if (allFilled) {
                        document.getElementById('scanForm').submit();
                    }
                }
            };
        }
    </script>

</x-app-layout>
