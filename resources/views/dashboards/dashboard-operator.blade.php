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
        $activeAdjustMachine = \App\Models\AdjustMachineLog::where('user_id', $userId)->whereNull('end_time')->exists();
    @endphp

    <div class="flex flex-wrap gap-4">
        <!-- Start Mould Change Button -->
        <button id="startMouldChange" 
            class="px-4 py-2 bg-yellow-500 text-white font-bold rounded-lg shadow-md hover:bg-yellow-600 transition duration-200"
            @if($activeMouldChange) style="display: none;" @endif>
            Change Mould
        </button>

        <!-- End Mould Change Button -->
        <button id="endMouldChange" 
            class="px-4 py-2 bg-green-500 text-white font-bold rounded-lg shadow-md hover:bg-green-600 transition duration-200 hidden">
            Complete Change Mould
        </button>

        <!-- Start Adjust Machine Button -->
        <button id="startAdjustMachine" 
            class="px-4 py-2 bg-blue-500 text-white font-bold rounded-lg shadow-md hover:bg-blue-600 transition duration-200"
            @if($activeAdjustMachine) style="display: none;" @endif>
            Adjust Machine
        </button>

        <!-- End Adjust Machine Button -->
        <button id="endAdjustMachine" 
            class="px-4 py-2 bg-indigo-500 text-white font-bold rounded-lg shadow-md hover:bg-indigo-600 transition duration-200 hidden">
            Complete Adjust Machine
        </button>
    </div>



        <div id="nikModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-1/3 relative z-50">
                <h2 class="text-lg font-bold mb-4">Enter NIK & Password</h2>
                <input type="text" id="nik" class="border p-2 w-full rounded" placeholder="Enter NIK...">
                <input type="password" id="password" class="border p-2 w-full rounded mt-2" placeholder="Enter Password...">
                <div class="flex justify-end mt-4">
                    <button id="closeNikModal" class="bg-gray-500 text-white px-4 py-2 rounded mr-2">Cancel</button>
                    <button id="verifyNik" class="bg-blue-600 text-white px-4 py-2 rounded">Verify</button>
                </div>
            </div>
        </div>

        <div id="mouldChangeInfo" class="hidden bg-gray-100 p-4 rounded-lg shadow-lg mt-4">
            <h2 class="text-lg font-bold mb-2">Mould Change in Progress</h2>
            <div class="flex items-center">
                <img id="currentUserProfile" src="" alt="Profile Picture" class="w-12 h-12 rounded-full mr-3">
                <span id="currentUserName" class="text-lg font-semibold"></span>
            </div>
        </div>

        <div id="adjustMachineInfo" class="hidden bg-gray-100 p-4 rounded-lg shadow-lg mt-4">
            <h2 class="text-lg font-bold mb-2">Adjust Machine in Progress</h2>
            <div class="flex items-center">
                <img id="adjustUserProfile" src="" alt="Profile Picture" class="w-12 h-12 rounded-full mr-3">
                <span id="adjustUserName" class="text-lg font-semibold"></span>
            </div>
        </div>
        

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
                                            <!-- <th class="py-1 px-2 text-gray-700">Actual Quantity</th> -->
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
                                                <!-- <td class="py-1 px-2">{{ $data->actual_quantity }}</td> -->
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
            <button 
                @click="resetVerification()" 
                class="px-4 py-2 bg-red-500 text-white font-bold rounded-lg shadow-md hover:bg-red-600 transition duration-200">
                Reset Verification
            </button>
            
                <div class="flex gap-6 items-start w-full">
                    <!-- Profile Section -->
                    <div id="dashboardSection" class="bg-white p-6 rounded-lg shadow-md w-[300px] flex flex-col items-center">
                        <img id="profileImage" class="w-40 h-40 rounded-full border-2 border-gray-300 object-cover" 
                            src="{{ asset('default-avatar.png') }}" alt="Profile Picture">
                        <h2 class="mt-4 text-xl font-bold text-center">Welcome, <span id="operatorName"></span></h2>
                    </div>
                  

                    <!-- SPK Table Section -->
                    <div class="bg-white overflow-hidden shadow-md rounded-lg p-4 flex-1">
                        <span class="text-xl font-bold">SPK</span>
                        <table class="w-full bg-white mt-2 rounded-md shadow-md overflow-hidden">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-2 px-4">SPK Number</th>
                                    <th class="py-2 px-4">Item Code</th>
                                    <th class="py-2 px-4">Scanned Quantity</th>
                                    <th class="py-2 px-4">Total Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                            @if ($itemCode && isset($itemCollections[$itemCode]))
                                @foreach ($itemCollections[$itemCode] as $data)
                                    <tr class="bg-white text-center">
                                        <td class="py-2 px-4">{{ $data['spk'] }}</td>
                                        <td class="py-2 px-4">{{ $data['item_code'] }}</td>
                                        <td class="py-2 px-4">{{ $data['scannedData'] }}/{{ $data['count'] }}</td>
                                        <td class="py-2 px-4">{{ $data['totalquantity'] }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="text-center text-gray-500 py-2">No data for selected item code</td>
                                </tr>
                            @endif
                            </tbody>
                        </table>

                        <form method="GET" action="{{ route('reset.jobs') }}" id="resetJobsForm">
                            <input type="hidden" id="uniqueData" name="uniqueData" value="{{ json_encode($uniquedata) }}" />
                            <input type="hidden" id="datas" name="datas" value="{{ json_encode($datas) }}" />

                            <button type="submit" id="resetJobsButton"
                                class="w-full py-3 px-4 bg-green-600 text-white font-semibold rounded-md hover:bg-green-700 transition mt-4">
                                Submit
                            </button>
                        </form>
                    </div>
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
                                <label for="quantity">Quantity</label>
                                <input type="number" id="quantity" name="quantity" required
                                    class="border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 w-full"
                                    placeholder="Quantity" x-on:input="checkAndSubmitForm()" />
                            </div>
                            <div>
                                <label for="warehouse">Warehouse</label>
                                <input type="text" id="warehouse" name="warehouse" required
                                    class="border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 w-full"
                                    placeholder="Warehouse" x-on:input="checkAndSubmitForm()" />
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

        $(document).ready(function () {
            let verifiedUser = null;

            // Check if a mould change is in progress on page load
            let savedMouldOperator = localStorage.getItem('mouldChangeOperator');
            if (savedMouldOperator) {
                savedMouldOperator = JSON.parse(savedMouldOperator);
                $('#mouldChangeInfo').removeClass('hidden');
                $('#currentUserProfile').attr('src', savedMouldOperator.profile_path);
                $('#currentUserName').text(savedMouldOperator.name);
                $('#startMouldChange').hide();
                $('#endMouldChange').show();
            }

            // Check if an adjust machine process is in progress on page load
            let savedAdjustOperator = localStorage.getItem('adjustMachineOperator');
            if (savedAdjustOperator) {
                savedAdjustOperator = JSON.parse(savedAdjustOperator);
                $('#adjustMachineInfo').removeClass('hidden');
                $('#currentAdjustUserProfile').attr('src', savedAdjustOperator.profile_path);
                $('#currentAdjustUserName').text(savedAdjustOperator.name);
                $('#startAdjustMachine').hide();
                $('#endAdjustMachine').show();
            }

            // Show NIK modal when clicking "Change Mould"
            $('#startMouldChange').click(function () {
                $('#nikModal').removeClass('hidden').attr('data-action', 'mould');
            });

            // Show NIK modal when clicking "Adjust Machine"
            $('#startAdjustMachine').click(function () {
                $('#nikModal').removeClass('hidden').attr('data-action', 'adjust');
            });

            // Close the modal
            $('#closeNikModal').click(function () {
                $('#nikModal').addClass('hidden');
            });

            // Verify NIK and password
            $('#verifyNik').click(function () {
                let nik = $('#nik').val().trim();
                let password = $('#password').val().trim();
                let actionType = $('#nikModal').attr('data-action');

                if (nik === '' || password === '') {
                    alert('Please enter both NIK and password.');
                    return;
                }

                $.ajax({
                    url: "{{ route('verify.nik') }}",
                    type: "POST",
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { nik: nik, password: password },
                    success: function (response) {
                        alert(response.message);
                        verifiedUser = response.user;
                        $('#nikModal').addClass('hidden');

                        if (actionType === 'mould') {
                            startMouldChange(verifiedUser.name);
                        } else if (actionType === 'adjust') {
                            startAdjustMachine(verifiedUser.name);
                        }
                    },
                    error: function (xhr) {
                        alert(xhr.responseJSON.error);
                    }
                });
            });

            // Start Mould Change Process
            function startMouldChange(picName) {
                $.ajax({
                    url: "{{ route('mould.change.start') }}",
                    type: "POST",
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { pic_name: picName },
                    success: function (response) {
                        alert(response.message);
                        localStorage.setItem('mouldChangeOperator', JSON.stringify(response.operator));

                        $('#mouldChangeInfo').removeClass('hidden');
                        $('#currentUserProfile').attr('src', response.operator.profile_path);
                        $('#currentUserName').text(response.operator.name);

                        $('#startMouldChange').hide();
                        $('#endMouldChange').show();
                    },
                    error: function (xhr) {
                        alert(xhr.responseJSON.error);
                    }
                });
            }

            // Complete Mould Change Process
            $('#endMouldChange').click(function () {
                $.ajax({
                    url: "{{ route('mould.change.end') }}",
                    type: "POST",
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function (response) {
                        alert(response.message);
                        localStorage.removeItem('mouldChangeOperator');

                        $('#mouldChangeInfo').addClass('hidden');
                        $('#startMouldChange').show();
                        $('#endMouldChange').hide();

                        location.reload();
                    },
                    error: function (xhr) {
                        alert(xhr.responseJSON.error);
                    }
                });
            });

            // Start Adjust Machine Process
            function startAdjustMachine(picName) {
                $.ajax({
                    url: "{{ route('adjust.machine.start') }}",
                    type: "POST",
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { pic_name: picName },
                    success: function (response) {
                        alert(response.message);
                        localStorage.setItem('adjustMachineOperator', JSON.stringify(response.operator));

                        $('#adjustMachineInfo').removeClass('hidden');
                        $('#currentAdjustUserProfile').attr('src', response.operator.profile_path);
                        $('#currentAdjustUserName').text(response.operator.name);

                        $('#startAdjustMachine').hide();
                        $('#endAdjustMachine').show();
                    },
                    error: function (xhr) {
                        alert(xhr.responseJSON.error);
                    }
                });
            }

            // Complete Adjust Machine Process
            $('#endAdjustMachine').click(function () {
                $.ajax({
                    url: "{{ route('adjust.machine.end') }}",
                    type: "POST",
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function (response) {
                        alert(response.message);
                        localStorage.removeItem('adjustMachineOperator');

                        $('#adjustMachineInfo').addClass('hidden');
                        $('#startAdjustMachine').show();
                        $('#endAdjustMachine').hide();

                        location.reload();
                    },
                    error: function (xhr) {
                        alert(xhr.responseJSON.error);
                    }
                });
            });
        });


    </script>

    <script>

    document.addEventListener("DOMContentLoaded", function () {
        const startMouldChange = document.getElementById("startMouldChange");
        const endMouldChange = document.getElementById("endMouldChange");
        const startAdjustMachine = document.getElementById("startAdjustMachine");
        const endAdjustMachine = document.getElementById("endAdjustMachine");

        // Handle Mould Change Start
        startMouldChange.addEventListener("click", function () {
            startMouldChange.style.display = "none";
            startAdjustMachine.style.display = "none"; // Hide adjust machine button
            endMouldChange.style.display = "inline-block";
        });

        // Handle Mould Change End
        endMouldChange.addEventListener("click", function () {
            startMouldChange.style.display = "inline-block";
            startAdjustMachine.style.display = "inline-block"; // Show adjust machine button again
            endMouldChange.style.display = "none";
        });

        // Handle Adjust Machine Start
        startAdjustMachine.addEventListener("click", function () {
            startMouldChange.style.display = "none"; // Hide mould change button
            startAdjustMachine.style.display = "none";
            endAdjustMachine.style.display = "inline-block";
        });

        // Handle Adjust Machine End
        endAdjustMachine.addEventListener("click", function () {
            startMouldChange.style.display = "inline-block"; // Show mould change button again
            startAdjustMachine.style.display = "inline-block";
            endAdjustMachine.style.display = "none";
        });
    });


          document.addEventListener("DOMContentLoaded", function () {
                // Check if user is already verified (persistent login)
                if (localStorage.getItem("verified")) {
                    let savedProfile = localStorage.getItem("profile_picture");
                    let savedNIK = localStorage.getItem("nik");
                    let savedName = localStorage.getItem("operator_name");

                    if (savedProfile && savedNIK && savedName) {
                        $('#profileImage').attr('src', savedProfile);
                        $('#operatorName').text(savedName);
                        $('#dashboardSection').removeClass('hidden');
                        $('#loginSection').addClass('hidden');
                    }
                }
            });

        function scanModeHandler(deactivateScanModeFlag) {
            return {
                scanMode: true,
                verified: localStorage.getItem('verified') === 'true', // Load verification state
                nikInput: '', 
                passwordInput: '',
                idleTimeout: null,

                initialize() {
                    if (deactivateScanModeFlag == true) {
                        this.scanMode = false;
                        localStorage.setItem('scanMode', false);
                    } else {
                        this.scanMode = localStorage.getItem('scanMode') === 'true';
                    }

                    // Restore verification state
                    if (this.verified) {
                        this.startIdleTimer(); // Restart the idle timer if already verified
                    }

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

                    if (this.scanMode) {
                        this.focusOnSPKCode();
                    }
                },

                verifyNIK() {
                    if (this.nikInput.trim() !== '' && this.passwordInput.trim() !== '') {
                        $.ajax({
                            url: "{{ route('verify.nik.password') }}",
                            type: "POST",
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            data: {
                                nik: this.nikInput,
                                password: this.passwordInput
                            },
                            success: (response) => {
                                if (response.success) {
                                    this.verified = true;
                                    localStorage.setItem('verified', true); // Save state
                                    localStorage.setItem('nik', this.nikInput);
                                    this.startIdleTimer(); // Start the idle timer
                                    localStorage.setItem('operator_name', response.operator_name);
                                    localStorage.setItem('profile_picture', response.profile_picture);


                                    $('#profileImage').attr('src', response.profile_picture);
                                    $('#operatorName').text(response.operator_name);
                                    $('#dashboardSection').removeClass('hidden'); // Show the dashboard
                                    $('#loginSection').addClass('hidden'); // Hide the login form
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

                startIdleTimer() {
                    if (this.idleTimeout) {
                        clearTimeout(this.idleTimeout);
                    }
                    this.idleTimeout = setTimeout(() => {
                        this.resetVerification(); // Reset verification after timeout
                    }, 180000); // 3 minutes
                },

                resetVerification() {
                    this.verified = false;
                    localStorage.removeItem('verified');

                    localStorage.removeItem('nik');
                    localStorage.removeItem('operator_name');
                    localStorage.removeItem('profile_picture');

                    // Reset UI elements
                    $('#profileImage').attr('src', "{{ asset('default-avatar.png') }}"); // Default image
                    $('#operatorName').text(""); // Clear operator name
                    $('#dashboardSection').addClass('hidden');
                    $('#loginSection').removeClass('hidden');
                    alert("Verification expired due to inactivity.");
                },

                focusOnSPKCode() {
                    setTimeout(() => {
                        document.getElementById('spk_code').focus();
                    }, 100);
                }
            };
        }

        function autoSubmitForm() {
            return {
                nikInput: localStorage.getItem('nik') || '',  // Load from localStorage

                checkAndSubmitForm() {
                    // Debugging logs
                    console.log("LocalStorage NIK:", localStorage.getItem('nik'));
                    console.log("Current NIK Input:", this.nikInput);

                    // Ensure NIK is set before submitting
                    if (!this.nikInput) {
                        this.nikInput = localStorage.getItem('nik') || '';
                        console.warn("NIK was empty, updated from localStorage:", this.nikInput);
                    }

                    // Update hidden input field
                    document.getElementById('nik').value = this.nikInput;

                    // Check if all required fields are filled
                    const inputs = document.querySelectorAll(
                        '#scanForm input[type="text"], #scanForm input[type="number"]'
                    );
                    let allFilled = Array.from(inputs).every(input => input.value.trim() !== '');

                    if (allFilled && this.nikInput) {
                        console.log("✅ Form is valid. Submitting...");
                        document.getElementById('scanForm').submit();
                    } else {
                        console.warn("❌ Form not submitted. Missing required fields or NIK.");
                    }
                }
            };
        }
    </script>

</x-app-layout>
