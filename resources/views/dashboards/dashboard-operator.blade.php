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
        $activeRepairMachine = \App\Models\RepairMachineLog::where('user_id', $userId)->whereNull('finish_repair')->exists();
    @endphp

    <div class="flex justify-between items-start flex-wrap gap-4 px-4">
        <!-- Button Group -->
        <div class="flex flex-wrap gap-4 flex-grow">
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

            
            <!-- Start Repair Machine Button -->
            <button id="startRepairMachine" 
                class="px-4 py-2 bg-red-500 text-white font-bold rounded-lg shadow-md hover:bg-red-600 transition duration-200"
                @if($activeRepairMachine) style="display: none;" @endif>
                Repair Machine
            </button>

            <!-- Finish Repair Machine Button -->
            <button id="endRepairMachine" 
                class="px-4 py-2 bg-red-500 text-white font-bold rounded-lg shadow-md hover:bg-red-600 transition duration-200 hidden">
                Finish Repair Machine
            </button>
        </div>
    
        <div class="w-full px-6 py-3 bg-white border border-gray-200 rounded-xl shadow-md flex items-center space-x-6">
            <div>
                <div class="text-sm text-gray-500 font-medium uppercase">Tanggal Hari Ini</div>
                <div class="text-lg font-semibold text-gray-800" id="tanggal-hari-ini"></div>
            </div>
            <div class="border-l border-gray-300 h-8"></div>
            <div>
                <div class="text-sm text-gray-500 font-medium uppercase">Waktu Sekarang (WIB)</div>
                <div class="text-xl font-bold text-indigo-600" id="jam-hari-ini"></div>
            </div>
        </div>
        <!-- Zone & Pengawas Info -->
        <div class="bg-white shadow rounded-lg p-4 flex items-center min-w-[280px]">
                @if($zone)
                    <!-- Text Section -->
                    <div class="flex-1 pr-4">
                        <p class="text-sm text-gray-500">Zone</p>
                        <h3 class="text-lg font-semibold text-gray-800">{{ $zone->zone_name }}</h3>
                        <p class="text-sm text-gray-500">Pengawas:</p>
                        <p class="text-md font-medium text-gray-700">{{ $pengawasName }}</p>
                    </div>

                    <!-- Profile Image Section -->
                    <div>
                        @if($pengawasProfile)
                            <img src="{{ asset('storage/' . $pengawasProfile) }}" alt="Pengawas Profile Picture"
                                class="w-20 h-20 rounded-full border border-gray-300 object-cover shadow">
                        @else
                            <p class="text-xs text-gray-400 italic">No picture</p>
                        @endif
                    </div>
                @endif
            </div>
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
            <div class="mb-2">
                <label for="mouldRemarks" class="block text-sm font-medium text-gray-700">Remarks</label>
                <textarea id="mouldRemarks" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-pink-500 focus:border-pink-500" placeholder="Any remarks..."></textarea>
            </div>
        </div>

        <div id="adjustMachineInfo" class="hidden bg-gray-100 p-4 rounded-lg shadow-lg mt-4">
            <h2 class="text-lg font-bold mb-2">Adjust Machine in Progress</h2>
            <div class="flex items-center">
                <img id="currentAdjustUserProfile" src="" alt="Profile Picture" class="w-12 h-12 rounded-full mr-3">
                <span id="currentAdjustUserName" class="text-lg font-semibold"></span>
            </div>

            <div class="mb-2">
                <label for="adjustRemarks" class="block text-sm font-medium text-gray-700">Remarks</label>
                <textarea id="adjustRemarks" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-pink-500 focus:border-pink-500" placeholder="Any remarks..."></textarea>
            </div>
        </div>

        <div id="repairMachineInfo" class="hidden bg-gray-100 p-4 rounded-lg shadow-lg mt-4">
            <h2 class="text-lg font-bold mb-2">Repair Machine in Progress</h2>
            <div class="flex items-center">
                <img id="currentRepairUserProfile" src="" alt="Profile Picture" class="w-12 h-12 rounded-full mr-3">
                <span id="currentRepairUserName" class="text-lg font-semibold"></span>
            </div>

            <div class="mb-2">
                <label for="repairProblem" class="block text-sm font-medium text-gray-700">Problem</label>
                <input type="text" id="repairProblem" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-pink-500 focus:border-pink-500" placeholder="Describe the problem...">
            </div>
            <div class="mb-2">
                <label for="repairRemarks" class="block text-sm font-medium text-gray-700">Remarks</label>
                <textarea id="repairRemarks" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-pink-500 focus:border-pink-500" placeholder="Any remarks..."></textarea>
            </div>
        </div>

        <div class="container mx-auto py-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Mould Change Log Card -->
                <div class="card p-4 border border-gray-300 rounded-lg shadow-lg">
                    <h2 class="text-xl font-semibold mb-4">Mould Change Log</h2>
                    @if($mouldChangeLogs->isEmpty())
                        <p class="text-gray-500">Tidak ada data</p>
                    @else
                        <ul>
                            @foreach($mouldChangeLogs as $log)
                                <li class="mb-2">
                                    <p><strong>Waktu Mulai:</strong> {{ \Carbon\Carbon::parse($log->created_at)->timezone('Asia/Jakarta')->format('d-m-Y H:i') }} WIB</p>
                                    <p><strong>Waktu Selesai:</strong> {{ \Carbon\Carbon::parse($log->end_time)->timezone('Asia/Jakarta')->format('d-m-Y H:i') }} WIB</p>
                                    <p><strong>PIC :</strong> {{ $log->pic }}</p>
                                    <p><strong>Total Time:</strong> {{ $log->total_pengerjaan }} minutes</p>
                                    <p><strong>Remark :</strong> {{ $log->remark }}</p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <!-- Adjust Machine Log Card -->
                <div class="card p-4 border border-gray-300 rounded-lg shadow-lg">
                    <h2 class="text-xl font-semibold mb-4">Adjust Machine Log</h2>
                    @if($adjustMachineLogs->isEmpty())
                        <p class="text-gray-500">Tidak ada data</p>
                    @else
                        <ul>
                            @foreach($adjustMachineLogs as $log)
                                <li class="mb-2">
                                    <p><strong>Waktu Mulai:</strong> {{ \Carbon\Carbon::parse($log->created_at)->timezone('Asia/Jakarta')->format('Y-m-d H:i') }} WIB</p>
                                    <p><strong>Waktu Selesai:</strong> {{ \Carbon\Carbon::parse($log->end_time)->timezone('Asia/Jakarta')->format('Y-m-d H:i') }} WIB</p>
                                    <p><strong>PIC :</strong> {{ $log->pic }}</p>
                                    <p><strong>Total Time:</strong> {{ $log->total_pengerjaan }} minutes</p>
                                    <p><strong>Remark :</strong> {{ $log->remark }}</p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <!-- Repair Machine Log Card -->
                <div class="card p-4 border border-gray-300 rounded-lg shadow-lg">
                    <h2 class="text-xl font-semibold mb-4">Repair Machine Log</h2>
                    @if($repairMachineLogs->isEmpty())
                        <p class="text-gray-500">Tidak ada data</p>
                    @else
                        <ul>
                            @foreach($repairMachineLogs as $log)
                                <li class="mb-2">
                                    <p><strong>Waktu Mulai:</strong> {{ \Carbon\Carbon::parse($log->created_at)->timezone('Asia/Jakarta')->format('Y-m-d H:i') }} WIB</p>
                                    <p><strong>Waktu Selesai Perbaikan:</strong> {{ \Carbon\Carbon::parse($log->finish_repair)->timezone('Asia/Jakarta')->format('Y-m-d H:i') }} WIB</p>
                                    <p><strong>PIC :</strong> {{ $log->pic }}</p>
                                    <p><strong>Total Time:</strong> {{ $log->total_pengerjaan }} minutes</p>
                                    <p><strong>Remark :</strong> {{ $log->remark }}</p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
        

        <div x-data="scanModeHandler({{ session('deactivateScanMode') ? 'true' : 'false' }})" x-init="initialize()" x-show="ready" x-cloak x-show="ready" x-cloak class="py-4">
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
                                            <!-- <div>
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
                                            </div> -->

                                            <div>
                                                <select id="item_code" name="item_code" required
                                                    class="px-3 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('item_code') border-red-500 @enderror">
                                                    <option value="">-- Pilih Item Code --</option>
                                                    @foreach($todayitems as $item)
                                                        <option value="{{ $item->item_code }}">
                                                            {{ $item->item_code }} - Shift {{ $item->shift }}
                                                        </option>
                                                    @endforeach
                                                </select>

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
                                @php
                                    $activeFiles = $files[$itemCode] ?? collect();
                                @endphp
                                @if ($activeFiles->count() > 0)
                                    <div class="font-bold text-2xl">Files</div>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 mt-4">
                                        @foreach ($activeFiles as $file)
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
                                            <th class="py-1 px-2 text-gray-700">Pair</th>
                                            <th class="py-1 px-2 text-gray-700">Start Date - End Date</th>
                                            <th class="py-1 px-2 text-gray-700">Shift</th>
                                            <th class="py-1 px-2 text-gray-700">Quantity</th>
                                            <th class="py-1 px-2 text-gray-700">Status</th>
                                            <th class="py-1 px-2 text-gray-700">Cycle Time</th>
                                            <th class="py-1 px-2 text-gray-700">Remark</th>
                                            <!-- <th class="py-1 px-2 text-gray-700">Loss Package Quantity</th> -->
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
                                                $pairCode = $data->masterItem->pair ?? null;
                                            @endphp

                                            <tr class="bg-white border-b text-center">
                                                <td class="py-1 px-2">{{ $data->item_code }}</td>
                                                <td class="py-1 px-2">{{ $pairCode ?? '-' }}</td> <!-- Kolom pair -->
                                                <td class="py-1 px-2">{{ $startDate }} - {{ $endDate }}</td>
                                                <td class="py-1 px-2">{{ $data->shift }} ({{ $startTime }} -
                                                    {{ $endTime }})</td>
                                                <td class="py-1 px-2">{{ $data->quantity }}</td>
                                                <td class="py-1 px-2">
                                                    {{ $data->is_done === 1 ? 'Selesai' : 'Belum Selesai' }}
                                                </td>
                                                <td class="py-1 px-2">
                                                    @if ($data->temporal_cycle_time)
                                                        <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-sm">
                                                            {{ $data->temporal_cycle_time }} detik
                                                        </span>
                                                    @else
                                                        <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded-full text-sm italic">
                                                            Belum di-assign
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="py-1 px-2">{{ $data->remark }}</td>
                                                <!-- <td class="py-1 px-2">{{ $data->loss_package_quantity }}</td> -->
                                                <!-- <td class="py-1 px-2">{{ $data->actual_quantity }}</td> -->
                                                <td class="py-1 px-2">
                                                    <!-- @if ($itemCode && $data->item_code === $itemCode)
                                                        <form action="{{ route('generate.itemcode.barcode', ['item_code' => $data->item_code, 'quantity' => $data->quantity]) }}"
                                                            method="get">
                                                            <button class="px-2 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded">
                                                                Generate Barcode
                                                            </button>
                                                        </form>
                                                    @else
                                                        <button class="px-2 py-1 bg-gray-400 text-white rounded cursor-not-allowed" disabled>
                                                            Generate Barcode
                                                        </button>
                                                    @endif -->

                                                    <button 
                                                            type="button" 
                                                            class="px-2 py-1 bg-green-500 hover:bg-green-600 text-white rounded mt-1"
                                                            onclick="openCycleTimeModal('{{ $data->id }}', '{{ $data->temporal_cycle_time ?? '' }}')"
                                                        >
                                                            Set Cycle Time
                                                    </button>

                                                    <button 
                                                        type="button" 
                                                        class="px-2 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded mt-1"
                                                        onclick="openRemarkModal('{{ $data->id }}', '{{ $data->remark ?? '' }}')"
                                                    >
                                                        Set Remark
                                                    </button>

                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                <div id="remarkDICModal" class="fixed inset-0 bg-black bg-opacity-30 z-50 hidden flex items-center justify-center">
                                    <div class="bg-white p-6 rounded shadow-md w-96">
                                        <h2 class="text-lg font-semibold mb-2">Set Remark</h2>
                                        <input type="hidden" id="remark_dic_id">
                                        <textarea id="remark_dic_input" class="w-full border rounded px-2 py-1" rows="4" placeholder="Tulis remark..."></textarea>
                                        <div class="mt-4 text-right">
                                            <button onclick="closeRemarkModal()" class="px-3 py-1 bg-gray-400 text-white rounded">Cancel</button>
                                            <button onclick="saveRemark()" class="px-3 py-1 bg-blue-600 text-white rounded">Save</button>
                                        </div>
                                    </div>
                                </div>

                                <div id="cycleTimeModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex justify-center items-center z-50">
                                    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md relative">
                                        <h2 class="text-lg font-semibold mb-4">Set Temporal Cycle Time</h2>
                                        <form id="cycleTimeForm" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="data_id" id="dataIdInput">
                                            <label for="cycle_time" class="block text-sm font-medium text-gray-700 mb-1">Temporal Cycle Time</label>
                                            <input type="text" id="cycleTimeInput" name="temporal_cycle_time" class="w-full border rounded p-2 mb-4" required>
                                            <div class="flex justify-end gap-2">
                                                <button type="button" onclick="closeCycleTimeModal()" class="bg-gray-400 text-white px-3 py-1 rounded">Cancel</button>
                                                <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">Submit</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>


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
                        @php
                            $pairCode = $activeDIC->masterItem->pair ?? null;
                            $hasPair = $pairCode !== null && $pairCode !== '0';
                        @endphp
                    <div class="bg-white overflow-hidden shadow-md rounded-lg p-4 flex-1">
                      <span class="text-xl font-bold">
                            Detail Pekerjaan - {{ optional($activeDIC)->item_code ?? '-' }} 
                            (Shift: {{ optional($activeDIC)->shift ?? '-' }})
                        </span>
                        <table class="w-full bg-white mt-2 rounded-md shadow-md overflow-hidden">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-2 px-4">Item Code</th>
                                    @if ($hasPair)
                                        <th class="py-2 px-4">Pair Code</th>
                                    @endif
                                    <th class="py-2 px-4">Quantity</th>
                                    <th class="py-2 px-4">Total Box Yang sudah discan</th>
                                    <th class="py-2 px-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                           @if ($activeDIC)
                                @if ($hasPair)
                                    {{-- Jika punya pair, tampilkan versi table khusus --}}
                                    <tr class="bg-white text-center">
                                        <td class="py-2 px-4">{{ $activeDIC['item_code'] }} / {{ $pairCode }}</td>
                                        <td class="py-2 px-4">{{ $totalScannedQuantity }}/{{ $activeDIC['quantity'] }}</td>
                                        <td class="py-2 px-4">{{ $scannedCount }}</td>
                                        <td class="py-2 px-4">
                                            <div class="flex flex-wrap gap-2">
                                                <button 
                                                    onclick="document.getElementById('detailModal').showModal()" 
                                                    class="bg-blue-500 text-white px-4 py-1 rounded-md text-sm shadow hover:bg-blue-600 transition duration-150">
                                                    Detail Remark
                                                </button>
                                                <button 
                                                    onclick="document.getElementById('detailDataModal').showModal()" 
                                                    class="bg-green-500 text-white px-4 py-1 rounded-md text-sm shadow hover:bg-green-600 transition duration-150">
                                                    Detail Data
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @else
                                    {{-- Versi default tanpa pair --}}
                                    <tr class="bg-white text-center">
                                        <td class="py-2 px-4">{{ $activeDIC['item_code'] }}</td>
                                        <td class="py-2 px-4">{{ $totalScannedQuantity }}/{{ $activeDIC['quantity'] }}</td>
                                        <td class="py-2 px-4">{{ $scannedCount }}</td>
                                        <td class="py-2 px-4">
                                            <div class="flex flex-wrap gap-2">
                                                <button 
                                                    onclick="document.getElementById('detailModal').showModal()" 
                                                    class="bg-blue-500 text-white px-4 py-1 rounded-md text-sm shadow hover:bg-blue-600 transition duration-150">
                                                    Detail Remark
                                                </button>
                                                <button 
                                                    onclick="document.getElementById('detailDataModal').showModal()" 
                                                    class="bg-green-500 text-white px-4 py-1 rounded-md text-sm shadow hover:bg-green-600 transition duration-150">
                                                    Detail Data
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endif

                            @else
                                <tr>
                                    <td colspan="4" class="text-center text-gray-500 py-2">No data for selected item code</td>
                                </tr>
                            @endif
                            </tbody>
                        </table>


                            <dialog id="detailDataModal" class="p-6 rounded-lg w-11/12 max-w-4xl">
                                <h3 class="text-xl font-semibold mb-4">Detail Data Scan SPK</h3>
                                <table class="w-full border-collapse border border-gray-300 text-left text-sm">
                                    <thead>
                                        <tr class="bg-gray-100">
                                            <th class="border border-gray-300 px-3 py-2">ID</th>
                                            <th class="border border-gray-300 px-3 py-2">SPK Code</th>
                                            <th class="border border-gray-300 px-3 py-2">DIC ID</th>
                                            <th class="border border-gray-300 px-3 py-2">Item Code</th>
                                            <th class="border border-gray-300 px-3 py-2">Warehouse</th>
                                            <th class="border border-gray-300 px-3 py-2">Quantity</th>
                                            <th class="border border-gray-300 px-3 py-2">Label</th>
                                            <th class="border border-gray-300 px-3 py-2">User</th>
                                            <th class="border border-gray-300 px-3 py-2">Created At</th>
                                            <th class="border border-gray-300 px-3 py-2">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($spkData as $scan)
                                            <tr>
                                                <td class="border border-gray-300 px-3 py-1">{{ $scan->id }}</td>
                                                <td class="border border-gray-300 px-3 py-1">{{ $scan->spk_code }}</td>
                                                <td class="border border-gray-300 px-3 py-1">{{ $scan->dic_id }}</td>
                                                <td class="border border-gray-300 px-3 py-1">{{ $scan->item_code }}</td>
                                                <td class="border border-gray-300 px-3 py-1">{{ $scan->warehouse }}</td>
                                                <td class="border border-gray-300 px-3 py-1">{{ $scan->quantity }}</td>
                                                <td class="border border-gray-300 px-3 py-1">{{ $scan->label }}</td>
                                                <td class="border border-gray-300 px-3 py-1">{{ $scan->user }}</td>
                                                <td class="border border-gray-300 px-3 py-1">{{ $scan->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') }}</td>
                                                <td>
                                                <form method="POST" action="{{ route('spk-scan.destroy', $scan->id) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" onclick="return confirm('Yakin ingin hapus scan ini?')" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">
                                                            Delete
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="9" class="text-center py-4">Tidak ada data scan.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>

                                <div class="mt-4 text-right">
                                    <button onclick="document.getElementById('detailDataModal').close()" class="bg-red-500 text-white px-4 py-1 rounded hover:bg-red-600">
                                        Close
                                    </button>
                                </div>
                            </dialog>

                            <dialog id="detailModal" class="rounded-md shadow-lg p-4 w-full max-w-3xl">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-bold">Detail Per Jam - {{ $activeDIC['item_code'] ?? '' }}</h3>
                                    <button onclick="document.getElementById('addHourlyRemarksModal').showModal()" 
                                            class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                                        Add Hourly Remarks
                                    </button>
                                    <button onclick="document.getElementById('detailModal').close()" class="text-red-500 hover:text-red-700">X</button>
                                </div>

                                <dialog id="addHourlyRemarksModal" class="rounded-md p-6 w-full max-w-md bg-white shadow">
                                    <form method="POST" action="{{ route('hourly-remarks.store') }}" x-data="autoSubmitForm()" >
                                        @csrf
                                        <h3 class="text-lg font-bold mb-4">Tambah Hourly Remarks</h3>

                                        <label for="start_time" class="block text-sm font-semibold mb-1">Pilih Jam Mulai</label>
                                        <select name="start_time" id="start_time" required
                                                class="w-full border border-gray-300 rounded px-3 py-2 mb-4">
                                            @php
                                                $start = \Carbon\Carbon::parse('07:30');
                                                $end = \Carbon\Carbon::parse('7:30')->addDay(); // keesokan harinya
                                            @endphp
                                            @while ($start < $end)
                                                <option value="{{ $start->format('H:i') }}">
                                                    {{ $start->format('H:i') }}
                                                </option>
                                                @php $start->addHour(); @endphp
                                            @endwhile
                                        </select>

                                        {{-- Hidden Inputs --}}
                                        <input type="hidden" name="uniqueData" value='@json($itemCollections)' />
                                        <input type="hidden" name="datas" value='@json($datas)' />
                                        <input type="hidden" name="activedic" value='@json($activeDIC)' />
                                        <input type="hidden" id="nik" name="nik" x-model="nikInput" />

                                        <div class="flex justify-end gap-2 mt-4">
                                            <button type="button" onclick="document.getElementById('addHourlyRemarksModal').close()"
                                                    class="px-3 py-1 rounded border text-gray-700 hover:bg-gray-100">Cancel</button>
                                            <button type="submit"
                                                    class="px-4 py-1 rounded bg-green-600 text-white hover:bg-green-700">Simpan</button>
                                        </div>
                                    </form>
                                </dialog>

                                

                                <table class="w-full border border-gray-200 text-sm">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="py-2 px-4 border">Jam Mulai</th>
                                            <th class="py-2 px-4 border">Jam Selesai</th>
                                            <th class="py-2 px-4 border">Target</th>
                                            <th class="py-2 px-4 border">Actual Scan</th>
                                            <th class="py-2 px-4 border">Actual Production</th>
                                            <th class="py-2 px-4 border">NG</th>
                                            <th class="py-2 px-4 border">Status</th>
                                            <th class="py-2 px-4 border">Remark</th>
                                            <th class="py-2 px-4 border">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (!empty($hourlyRemarksActiveDIC))
                                        @foreach ($hourlyRemarksActiveDIC as $slot)
                                                <tr class="text-center">
                                                    <td class="py-2 px-4 border">{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}</td>
                                                    <td class="py-2 px-4 border">{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}</td>
                                                    <td class="py-2 px-4 border">{{ $slot->target }}</td>
                                                    <td class="py-2 px-4 border">{{ $slot->actual }}</td>
                                                    <td class="py-2 px-4 border">
                                                        {{ $slot->actual_production ? $slot->actual_production : 0 }}
                                                    </td>
                                                      <td class="py-2 px-4 border">
                                                        {{ $slot->NG ? $slot->NG : 0 }}
                                                    </td>
                                                    <td class="py-2 px-4 border">
                                                        @if ($slot->is_achieve)
                                                            <span class="px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">Tercapai</span>
                                                        @else
                                                            <span class="px-2 py-1 rounded-full bg-red-100 text-red-700 text-xs font-semibold">Tidak Tercapai</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-2 px-4 border">{{ $slot->remark ?? '-' }}</td>
                                                    <td class="py-2 px-4 border">
                                                        <button 
                                                            onclick="editRemark({{ $slot->id }}, @js($slot->remark))"
                                                            class="ml-2 bg-red-500 text-white text-xs px-2 py-1 rounded hover:bg-red-600"
                                                        >
                                                            Edit Remark
                                                        </button>
                                                        <button 
                                                            class="ml-2 bg-blue-500 text-white text-xs px-2 py-1 rounded hover:bg-blue-600"
                                                            onclick="openProductionModal({{ $slot->id }}, {{ $slot->actual_production ?? 0 }})"
                                                        >
                                                            Add Actual Production
                                                        </button>

                                                        <button 
                                                            class="ml-2 bg-purple-500 text-white text-xs px-2 py-1 rounded hover:bg-purple-600"
                                                            onclick="openNgModal({{ $slot->id }}, {{ $slot->NG ?? 0 }})"
                                                        >
                                                            Add NG
                                                        </button>
                                                    </td>
                                                    </td>
                                                </tr>

                                                    <div id="productionModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex justify-center items-center z-50">
                                                        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                                                            <h2 class="text-lg font-semibold mb-4">Update Actual Production</h2>
                                                            <form id="productionForm" method="POST">
                                                                @csrf
                                                                @method('PUT')
                                                                <input type="hidden" id="productionSlotId" name="id">
                                                                <label for="actual_production" class="block text-sm font-medium text-gray-700 mb-1">Actual Production</label>
                                                                <input type="number" id="actualProductionInput" name="actual_production" class="w-full border rounded p-2 mb-4" required min="0">
                                                                <div class="flex justify-end gap-2">
                                                                    <button type="button" onclick="closeProductionModal()" class="bg-gray-400 text-white px-3 py-1 rounded">Cancel</button>
                                                                    <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">Submit</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>

                                                    <div id="ngModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden justify-center items-center">
                                                        <div class="bg-white p-6 rounded shadow-lg w-96">
                                                            <h2 class="text-lg font-semibold mb-4">Add NG</h2>
                                                            <form id="ngForm" method="POST">
                                                                @csrf
                                                                @method('PUT')
                                                                <input type="number" name="NG" id="ngValue" class="w-full border rounded p-2 mb-4" min="0" required>

                                                                <div class="flex justify-end space-x-2">
                                                                    <button type="button" onclick="closeNgModal()" class="px-4 py-2 bg-gray-400 text-white rounded">Cancel</button>
                                                                    <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">Submit</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="3" class="text-center py-2 text-gray-500">No hourly data available</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </dialog>

                            <dialog id="remarkModal" class="rounded-md shadow-lg p-4 w-full max-w-md">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-bold">Edit Remark</h3>
                                    <button onclick="document.getElementById('remarkModal').close()" class="text-red-500 hover:text-red-700">X</button>
                                </div>

                                <form id="remarkForm">
                                    @csrf
                                    <input type="hidden" name="id" id="remarkId">
                                    <textarea name="remark" id="remarkInput" rows="4" class="w-full border rounded p-2" placeholder="Tulis remark..."></textarea>
                                    <div class="flex justify-end mt-4">
                                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Simpan</button>
                                    </div>
                                </form>
                            </dialog>

                        <form id="mainSubmitForm" method="POST" action="{{ route('submit.spk') }}">
                            @csrf
                            <input type="hidden" id="uniqueData" name="uniqueData"
                            value="{{ json_encode($itemCollections) }}" />
                            <input type="hidden" id="datas" name="datas" value="{{ json_encode($datas) }}" />
                            <input type="hidden" id="activedic" name="activedic" value="{{ $activeDIC }}" />

                            <button type="button"
                                onclick="openConfirmModal()"
                                class="w-full py-3 px-4 bg-green-600 text-white font-semibold rounded-md hover:bg-green-700 transition mt-4">
                                Submit
                            </button>
                        </form>

                            <dialog id="confirmModal" class="rounded-md shadow-lg p-6 w-full max-w-md">
                                <h3 class="text-lg font-bold mb-4">Konfirmasi Submit</h3>
                                <p class="text-sm text-gray-700 mb-4">
                                    Apakah kamu yakin ingin submit data ini? Tindakan ini tidak bisa dibatalkan.
                                </p>

                                <div class="flex justify-end gap-2">
                                    <button onclick="document.getElementById('confirmModal').close()"
                                        class="px-4 py-2 text-gray-600 hover:text-gray-800">Batal</button>

                                    <button onclick="document.getElementById('mainSubmitForm').submit()"
                                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Ya, Submit</button>
                                </div>
                            </dialog>

                    </div>
                </div>


                <div class="bg-white shadow-sm sm:rounded-lg p-4 mt-6">
                    <h3 class="text-xl font-bold">Scan Barcode</h3>
                    <form id="scanForm" action="{{ route('process.productionbarcode') }}" method="POST"
                        class="space-y-3" x-data="autoSubmitForm()" >
                        @csrf
                        <input type="hidden" id="uniqueData" name="uniqueData"
                            value="{{ json_encode($itemCollections) }}" />
                        <input type="hidden" id="datas" name="datas" value="{{ json_encode($datas) }}" />
                        <input type="hidden" id="activedic" name="activedic" value="{{ $activeDIC }}" />
                        <input type="hidden" id="nik" name="nik" x-model="nikInput" />

        
                        <!-- Grid Layout for 2 Columns -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="spk_code">SPK Code</label>
                                <input type="text" id="spk_code" name="spk_code_auto" required
                                    class="border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 w-full"
                                    placeholder="SPK Code" x-on:input="checkAndSubmitForm()" />
                            </div>
                            <div>
                                <label for="quantity">Quantity</label>
                                <input type="number" id="quantity" name="quantity_auto" required
                                    class="border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 w-full"
                                    placeholder="Quantity" x-on:input="checkAndSubmitForm()" />
                            </div>
                            <div>
                                <label for="warehouse">Warehouse</label>
                                <input type="text" id="warehouse" name="warehouse_auto" required
                                    class="border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 w-full"
                                    placeholder="Warehouse" x-on:input="checkAndSubmitForm()" />
                            </div>
                            <div>
                                <label for="label">Label</label>
                                <input type="number" id="label" name="label_auto" required
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

                <div x-data="{ showLossScan: false }" class="bg-white shadow-sm sm:rounded-lg p-4 mt-6">
                    <!-- Toggle Button -->
                    <!-- <button type="button" @click="showLossScan = !showLossScan"
                        class="text-xl font-bold flex items-center justify-between w-full text-left focus:outline-none">
                        Scan Barcode (Loss Package)
                        <svg :class="{'rotate-180': showLossScan}" class="h-5 w-5 transform transition-transform duration-200 ml-2"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button> -->

                    <!-- Hidden Form -->
                    <!-- <div x-show="showLossScan" x-transition class="mt-4 space-y-3">
                        <form id="scanForm" action="{{ route('process.productionbarcodeloss') }}" method="POST" x-data="autoSubmitForm()">
                            @csrf
                            <input type="hidden" id="uniqueData" name="uniqueData" value="{{ json_encode($itemCollections) }}" />
                            <input type="hidden" id="datas" name="datas" value="{{ json_encode($datas) }}" />
                            <input type="hidden" id="nik" name="nik" x-model="nikInput" />

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
                                        placeholder="Quantity" />
                                </div>

                                <div>
                                    <label for="warehouse">Warehouse</label>
                                    <input type="text" id="warehouse" name="warehouse" required
                                        class="border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 w-full"
                                        placeholder="Warehouse" />
                                </div>

                                <div>
                                    <label for="label">Label</label>
                                    <input type="number" id="label" name="label" required
                                        class="border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 w-full"
                                        placeholder="Label" />
                                </div>
                            </div>

                            <button type="submit"
                                class="w-full py-2 px-4 bg-indigo-600 text-white font-semibold rounded-md hover:bg-indigo-700 transition mt-4">
                                Scan
                            </button>
                        </form>
                    </div> -->
                </div>

                  <table class="w-full border border-gray-200 text-sm mt-6">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-2 px-4 border">Jam Mulai</th>
                                    <th class="py-2 px-4 border">Jam Selesai</th>
                                    <th class="py-2 px-4 border">Target</th>
                                    <th class="py-2 px-4 border">Actual Scan</th>
                                    <th class="py-2 px-4 border">Actual Production</th>
                                    <th class="py-2 px-4 border">NG</th>
                                    <th class="py-2 px-4 border">Status</th>
                                    <th class="py-2 px-4 border">PIC</th>
                                    <th class="py-2 px-4 border">Remark</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($hourlyRemarks as $remark)
                                    <tr class="text-center">
                                        <td class="py-2 px-4 border">{{ \Carbon\Carbon::parse($remark->start_time)->format('H:i') }}</td>
                                        <td class="py-2 px-4 border">{{ \Carbon\Carbon::parse($remark->end_time)->format('H:i') }}</td>
                                        <td class="py-2 px-4 border">{{ $remark->target }}</td>
                                        <td class="py-2 px-4 border">{{ $remark->actual }}</td>
                                        <td class="py-2 px-4 border">{{ $remark->actual_production ? $remark->actual_production : 0 }}</td>
                                        <td class="py-2 px-4 border">{{ $remark->NG ? $remark->NG : 0 }}</td> 
                                        <td class="py-2 px-4 border">
                                            @if ($remark->is_achieve)
                                                <span class="text-green-600 font-semibold">Tercapai</span>
                                            @else
                                                <span class="text-red-600 font-semibold">Tidak Tercapai</span>
                                            @endif
                                        </td>
                                        <td class="py-2 px-4 border">{{ $remark->pic }}</td>
                                        <td class="py-2 px-4 border">{{ $remark->remark ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-3 text-gray-500">Belum ada data summary</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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

            // Check if a repair machine process is in progress on page load
            let savedRepairOperator = localStorage.getItem('repairMachineOperator');
            if (savedRepairOperator) {
                savedRepairOperator = JSON.parse(savedRepairOperator);
                $('#repairMachineInfo').removeClass('hidden');
                $('#currentRepairUserProfile').attr('src', savedRepairOperator.profile_path);
                $('#currentRepairUserName').text(savedRepairOperator.name);
                $('#startRepairMachine').hide();
                $('#endRepairMachine').show();
            }

            // Show NIK modal when clicking "Change Mould"
            $('#startMouldChange').click(function () {
                $('#nikModal').removeClass('hidden').attr('data-action', 'mould');
            });

            // Show NIK modal when clicking "Adjust Machine"
            $('#startAdjustMachine').click(function () {
                $('#nikModal').removeClass('hidden').attr('data-action', 'adjust');
            });

            // Show NIK modal when clicking "Repair Machine"
            $('#startRepairMachine').click(function () {
                $('#nikModal').removeClass('hidden').attr('data-action', 'repair');
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
                        } else if (actionType === 'repair') {
                            startRepairMachine(verifiedUser.name);
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
                        // Cek jika backend memberikan warning message
                        if (response.message === 'Belum ada item yang diassign') {
                            alert('Gagal: ' + response.message);
                            return; // STOP, jangan update UI atau simpan localStorage
                        }

                        // Lanjut hanya kalau valid
                        alert(response.message);

                        // Simpan ke localStorage
                        localStorage.setItem('mouldChangeOperator', JSON.stringify(response.operator));

                        // Tampilkan data operator & ubah UI
                        $('#mouldChangeInfo').removeClass('hidden');
                        $('#currentUserProfile').attr('src', response.operator.profile_path);
                        $('#currentUserName').text(response.operator.name);

                        $('#startMouldChange').hide();
                        $('#endMouldChange').show();
                    },
                    error: function (xhr) {
                        // Tampilkan pesan error default dari backend
                        const msg = xhr.responseJSON?.error || 'Terjadi kesalahan saat memulai mould change';
                        alert(msg);
                    }
                });
            }


            // Complete Mould Change Process
            $('#endMouldChange').click(function () {
                const remarks = $('#mouldRemarks').val(); // ambil input dari textarea

                $.ajax({
                    url: "{{ route('mould.change.end') }}",
                    type: "POST",
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { remarks: remarks }, // kirim remarks ke backend
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
                        // Cek jika backend mengirim warning
                        if (response.message === 'Belum ada item yang diassign') {
                            alert('Gagal: ' + response.message);
                            return; // STOP, jangan ubah UI atau simpan localStorage
                        }

                        alert(response.message);

                        // Simpan ke localStorage
                        localStorage.setItem('adjustMachineOperator', JSON.stringify(response.operator));

                        // Update UI
                        $('#adjustMachineInfo').removeClass('hidden');
                        $('#currentAdjustUserProfile').attr('src', response.operator.profile_path);
                        $('#currentAdjustUserName').text(response.operator.name);

                        $('#startAdjustMachine').hide();
                        $('#endAdjustMachine').show();
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.error || 'Terjadi kesalahan saat memulai adjust machine';
                        alert(msg);
                    }
                });
            }

            // Complete Adjust Machine Process
            $('#endAdjustMachine').click(function () {
                const remarks = $('#adjustRemarks').val(); // ambil input dari textarea

                $.ajax({
                    url: "{{ route('adjust.machine.end') }}",
                    type: "POST",
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { remarks: remarks }, // kirim remarks ke backend
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


            // Start Repair Machine Process
            function startRepairMachine(picName) {
                $.ajax({
                    url: "{{ route('repair.machine.start') }}",
                    type: "POST",
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { pic_name: picName },
                    success: function (response) {
                        alert(response.message);
                        localStorage.setItem('repairMachineOperator', JSON.stringify(response.operator));

                        $('#repairMachineInfo').removeClass('hidden');
                        $('#currentRepairUserProfile').attr('src', response.operator.profile_path);
                        $('#currentRepairUserName').text(response.operator.name);

                        $('#startRepairMachine').hide();
                        $('#endRepairMachine').show();
                    },
                    error: function (xhr) {
                        alert(xhr.responseJSON.error);
                    }
                });
            }

            // Complete Repair Machine Process
            $('#endRepairMachine').click(function () {
                const problem = $('#repairProblem').val();
                const remarks = $('#repairRemarks').val();

                $.ajax({
                    url: "{{ route('repair.machine.end') }}",
                    type: "POST",
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: {
                        problem: problem,
                        remarks: remarks
                    },
                    success: function (response) {
                        alert(response.message);
                        localStorage.removeItem('repairMachineOperator');

                        $('#repairMachineInfo').addClass('hidden');
                        $('#startRepairMachine').show();
                        $('#endRepairMachine').hide();

                        location.reload();
                    },
                    error: function (xhr) {
                        alert(xhr.responseJSON?.error || 'Terjadi kesalahan.');
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
                    ready: false, // NEW
                    scanMode: false,
                    verified: false,
                    ready: false, // NEW
                    scanMode: false,
                    verified: false,
                    nikInput: '', 
                    passwordInput: '',
                    idleTimeout: null,

                    initialize() {
                        this.verified = localStorage.getItem('verified') === 'true';

                        this.verified = localStorage.getItem('verified') === 'true';

                        if (deactivateScanModeFlag == true) {
                            this.scanMode = false;
                            localStorage.setItem('scanMode', false);
                        } else {
                            this.scanMode = localStorage.getItem('scanMode') === 'true';
                        }

                        if (this.verified && this.scanMode) {
                            this.startIdleTimer();
                            this.focusOnSPKCode();
                        }

                        // Delay rendering until everything is set
                        this.ready = true;
                        if (this.verified && this.scanMode) {
                            this.startIdleTimer();
                            this.focusOnSPKCode();
                        }

                        // Delay rendering until everything is set
                        this.ready = true;

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
                                        location.reload();
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
                        }, 1800000000); // 3 minutes
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
                        location.reload();
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
                nikInput: localStorage.getItem('nik') || '',

                checkAndSubmitForm() {
                    console.log("LocalStorage NIK:", localStorage.getItem('nik'));
                    console.log("Current NIK Input:", this.nikInput);

                    if (!this.nikInput) {
                        this.nikInput = localStorage.getItem('nik') || '';
                        console.warn("NIK was empty, updated from localStorage:", this.nikInput);
                    }

                    document.getElementById('nik').value = this.nikInput;

                    const requiredFieldNames = ['spk_code_auto', 'quantity_auto', 'warehouse_auto', 'label_auto'];
                    const allFilled = requiredFieldNames.every(name => {
                        const input = document.querySelector(`[name="${name}"]`);
                        return input && input.value.trim() !== '';
                    });

                    if (allFilled && this.nikInput) {
                        console.log("✅ Form is valid. Submitting...");
                        
                    } else {
                        console.warn("❌ Form not submitted. Missing required fields or NIK.");
                    }
                }
            };
        }

            function updateWaktuIndonesia() {
                    const now = new Date();
                    const optionsTanggal = {
                        timeZone: 'Asia/Jakarta',
                        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
                    };
                    const optionsJam = {
                        timeZone: 'Asia/Jakarta',
                        hour: '2-digit', minute: '2-digit', second: '2-digit'
                    };

                    const tanggal = now.toLocaleDateString('id-ID', optionsTanggal);
                    const jam = now.toLocaleTimeString('id-ID', optionsJam);

                    document.getElementById('tanggal-hari-ini').textContent = tanggal;
                    document.getElementById('jam-hari-ini').textContent = jam;
                }

                setInterval(updateWaktuIndonesia, 1000);
                updateWaktuIndonesia();


            function editRemark(id, remark) {
                document.getElementById('remarkId').value = id;
                document.getElementById('remarkInput').value = remark || '';
                document.getElementById('remarkModal').showModal();
            }

            document.getElementById('remarkForm').addEventListener('submit', function (e) {
                e.preventDefault();
                const id = document.getElementById('remarkId').value;
                const remark = document.getElementById('remarkInput').value;
                const token = document.querySelector('input[name="_token"]').value;

                fetch(`/hourly-remarks/${id}/update-remark`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({ remark })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert("Gagal menyimpan remark");
                    }
                });
            });

            function openConfirmModal() {
                document.getElementById('confirmModal').showModal();
            }

            function openCycleTimeModal(dataId, existingValue = '') {
                document.getElementById('cycleTimeModal').classList.remove('hidden');
                document.getElementById('dataIdInput').value = dataId;
                document.getElementById('cycleTimeInput').value = existingValue;

                // Set form action dynamically
                document.getElementById('cycleTimeForm').action = `/daily-item-codes/${dataId}/temporal-cycle-time`;
            }

            function closeCycleTimeModal() {
                document.getElementById('cycleTimeModal').classList.add('hidden');
            }

            function openProductionModal(slotId, currentValue = 0) {
            const modal = document.getElementById('productionModal');
            const form = document.getElementById('productionForm');
            const input = document.getElementById('actualProductionInput');
            const hiddenId = document.getElementById('productionSlotId');

            hiddenId.value = slotId;
            input.value = currentValue;
            form.action = `/hourly-remarks/${slotId}/update-actual-production`;

            modal.classList.remove('hidden');
        }

        function closeProductionModal() {
            document.getElementById('productionModal').classList.add('hidden');
        }

         function openNgModal(id, currentValue) {
        let modal = document.getElementById('ngModal');
        let form = document.getElementById('ngForm');

        // Set form action
        form.action = '/hourly-remarks/' + id + '/ng'; // pastikan route sesuai
        document.getElementById('ngValue').value = currentValue;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeNgModal() {
        let modal = document.getElementById('ngModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

        function openRemarkModal(id, existingRemark = '') {
            document.getElementById('remark_dic_id').value = id;
            document.getElementById('remark_dic_input').value = existingRemark;
            document.getElementById('remarkDICModal').classList.remove('hidden');
        }

        function closeRemarkModal() {
            document.getElementById('remarkDICModal').classList.add('hidden');
        }

        function saveRemark() {
            const id = document.getElementById('remark_dic_id').value;
            const remark = document.getElementById('remark_dic_input').value;

            fetch(`/daily-item-codes/update-remark/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ remark })
            })
            .then(res => res.json())
            .then(data => {
                alert('Remark saved!');
                closeRemarkModal();
                location.reload();
            });
        }
    </script>

</x-app-layout>
