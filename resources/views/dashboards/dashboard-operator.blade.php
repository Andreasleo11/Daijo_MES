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

    @php
    $userId = auth()->id();
    $activeMouldChange = \App\Models\MouldChangeLog::where('user_id', $userId)->whereNull('end_time')->exists();
    @endphp

    @php
    $userId = auth()->id();
    $activeMouldChange = \App\Models\MouldChangeLog::where('user_id', $userId)->whereNull('end_time')->exists();
@endphp

        <!-- Start Mould Change Button -->
        <button id="startMouldChange" class="btn btn-warning" @if($activeMouldChange) style="display: none;" @endif>
            Change Mould
        </button>

        <!-- End Mould Change Button -->
        <button id="endMouldChange" class="btn btn-success" @if(!$activeMouldChange) style="display: none;" @endif>
            Complete Change Mould
        </button>

        <!-- PIC Input Modal -->
        <div id="picModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
            <div class="bg-white p-6 rounded-lg shadow-lg w-1/3">
                <h2 class="text-lg font-bold mb-4">Enter PIC Name</h2>
                <input type="text" id="pic_name" class="border p-2 w-full rounded" placeholder="Enter PIC name...">
                <div class="flex justify-end mt-4">
                    <button id="closeModal" class="bg-gray-500 text-white px-4 py-2 rounded mr-2">Cancel</button>
                    <button id="submitPic" class="bg-blue-600 text-white px-4 py-2 rounded">Submit</button>
                </div>
            </div>
        </div>


    <div id="picModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-1/3">
            <h2 class="text-xl font-semibold mb-4">Enter PIC Name</h2>
            <input type="text" id="pic_name" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter PIC name">
            <div class="mt-4 flex justify-end space-x-2">
                <button id="closeModal" class="px-4 py-2 bg-gray-400 text-white rounded-lg">Cancel</button>
                <button id="submitPic" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Submit</button>
            </div>
        </div>
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

            <div x-show="scanMode && !verified" class="mt-4 px-6">
                <label for="nik" class="block font-semibold">Enter Your NIK:</label>
                <input type="text" id="nik" x-model="nikInput"
                    class="border border-gray-300 rounded-md w-full p-2 mt-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Scan or enter your NIK"/>

                <label for="password" class="block font-semibold mt-4">Enter Your Password:</label>
                <input type="password" id="password" x-model="passwordInput"
                    class="border border-gray-300 rounded-md w-full p-2 mt-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Enter your password"/>

                <button x-on:click="verifyNIK()" 
                    class="mt-2 py-2 px-4 bg-green-600 text-white font-semibold rounded-md hover:bg-green-700 transition">
                    Verify NIK
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
                                            <a href="{{ asset('storage/files/' . $file->name) }}"
                                                data-fancybox="gallery" data-caption="{{ $file->name }}">
                                                <img class="w-full h-auto rounded-lg shadow-lg hover:shadow-2xl transition-transform transform hover:scale-105"
                                                    src="{{ asset('storage/files/' . $file->name) }}"
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
            <div x-show="scanMode && verified" class="mx-auto sm:px-4 lg:px-6 pt-6" x-cloak>
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
                        @if ($itemCode && isset($itemCollections[$itemCode]))
                            @foreach ($itemCollections[$itemCode] as $data)
                                <tr class="bg-white text-center">
                                    <td class="py-2 px-3">{{ $data['spk'] }}</td>
                                    <td class="py-2 px-3">{{ $data['item_code'] }}</td>
                                    <td class="py-2 px-3">{{ $data['scannedData'] }}/{{ $data['count'] }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="3" class="text-center text-gray-500 py-2">No data for selected item code</td>
                            </tr>
                        @endif
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
                            value="{{ json_encode($itemCollections) }}" />
                        <input type="hidden" id="datas" name="datas" value="{{ json_encode($datas) }}" />
                        <input type="hidden" id="nik" name="nik" x-model="nikInput" />

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

        
        let picName = '';

        $(document).ready(function() {
            // Show PIC modal when starting mould change
            $('#startMouldChange').click(function() {
                $('#picModal').removeClass('hidden'); // Show modal
            });

            // Close the modal
            $('#closeModal').click(function() {
                $('#picModal').addClass('hidden'); // Hide modal
            });

            // Handle PIC submission
            $('#submitPic').click(function() {
                let picName = $('#pic_name').val().trim();
                if (picName === '') {
                    alert('Please enter a PIC name.');
                    return;
                }

                $.ajax({
                    url: "{{ route('mould.change.start') }}",
                    type: "POST",
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { pic_name: picName },
                    success: function(response) {
                        alert(response.message);
                        $('#startMouldChange').hide();
                        $('#endMouldChange').show(); // Show Complete button
                        location.reload();
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON.error);
                    }
                });

                $('#picModal').addClass('hidden'); // Hide modal after submitting
            });

            // Handle Complete Mould Change button
            $(document).on('click', '#endMouldChange', function() {
                $.ajax({
                    url: "{{ route('mould.change.end') }}",
                    type: "POST",
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(response) {
                        alert(response.message);
                        $('#startMouldChange').show(); // Show Start button again
                        $('#endMouldChange').hide();  // Hide Complete button
                        location.reload();
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON.error);
                    }
                });
            });
        });

    </script>

    <script>
        function scanModeHandler(deactivateScanModeFlag) {
            return {
                scanMode: true,
                verified: false, // Verification flag
                nikInput: '', 
                passwordInput: '',
                initialize() {
                    if (deactivateScanModeFlag == true) {
                        this.scanMode = false;
                        localStorage.setItem('scanMode', false);
                    } else {
                        this.scanMode = localStorage.getItem('scanMode') === 'true';
                    }

                    // Automatically focus on spk_code input if scanMode is active
                    if (this.scanMode) {
                        if (!this.verified) {
                            alert("Please verify your NIK before activating Scan Mode.");
                            return;
                        }
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

                verifyNIK() {
                    if (this.nikInput.trim() !== '' && this.passwordInput.trim() !== '') {
                        // Call backend to verify NIK and password
                        $.ajax({
                            url: "{{ route('verify.nik.password') }}", // Update with your route
                            type: "POST",
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            data: {
                                nik: this.nikInput,
                                password: this.passwordInput
                            },
                            success: (response) => {
                                if (response.success) {
                                    this.verified = true;
                                    alert("NIK Verified Successfully!");
                                } else {
                                    alert("Invalid NIK or Password.");
                                }
                            },
                            error: function(xhr) {
                                alert("An error occurred while verifying your NIK.");
                            }
                        });
                    } else {
                        alert("Please enter both NIK and password.");
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
