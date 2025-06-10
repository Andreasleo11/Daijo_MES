<x-app-layout>
    <!-- Display Success and Error Messages -->
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Display Validation Errors -->
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="py-12">
        <div class="mx-auto sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-4">
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                            <li class="inline-flex items-center">
                                <a href="{{ route('daily-item-code.index') }}"
                                    class="inline-flex items-center text-sm font-medium text-gray-400 hover:underline">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-3 h-3 me-2.5 size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                    </svg>
                                    Daily Production Calendar
                                </a>
                            </li>
                            <li aria-current="page">
                                <div class="flex items-center">
                                    <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m1 9 4-4-4-4" />
                                    </svg>
                                    <a href="{{ route('daily-item-code.daily', ['date' => $selectedDate]) }}"
                                        class="ms-1 text-sm font-medium md:ms-2 text-gray-400 hover:underline">Daily
                                        Production Plan</a>
                                </div>
                            </li>
                            <li aria-current="page">
                                <div class="flex items-center">
                                    <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m1 9 4-4-4-4" />
                                    </svg>
                                    <span
                                        class="ms-1 text-sm font-medium text-gray-500 md:ms-2 dark:text-gray-400">Assign</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                </div>
                <h2 class="text-2xl mb-4">
                    Assign Item Codes to <span class="font-semibold">{{ $selectedMachine->name }}</span>
                </h2>
                <div class="bg-white shadow-md rounded-lg mt-8 p-6">
                    <form id="input-form" method="POST" action="{{ route('daily-item-code.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Schedule Date -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">
                                    Schedule Date
                                </label>
                                <input type="date" name="schedule_date" id="schedule_date"
                                    class="block w-full py-2 text-base border-gray-300 focus:outline-none sm:text-sm rounded-md bg-gray-100"
                                    value="{{ old('schedule_date', $selectedDate) }}" required readonly />
                                @error('schedule_date')
                                    <div class="text-red-500 text-sm mt-1">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Machine Selector -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">
                                    Machine Name
                                </label>
                                <select id="machine-selector" name="machine_id_display"
                                    class="block w-full py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md bg-gray-100"
                                    disabled>
                                    <option value="" selected disabled>
                                        -- Select Machine Name --
                                    </option>
                                    @foreach ($machines as $machine)
                                        <option value="{{ $machine->id }}"
                                            data-tipe-mesin="{{ $machine->tipe_mesin }}"
                                            {{ old('machine_id', $selectedMachine->id) == $machine->id ? 'selected' : '' }}>
                                            {{ $machine->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="machine_id"
                                    value="{{ old('machine_id', $selectedMachine->id) }}">
                                @error('machine_id')
                                    <div class="text-red-500 text-sm mt-1">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                        </div>

                        <!-- Shift Checkboxes -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">
                                Select Shifts
                            </label>
                            <div class="flex space-x-4">
                                <label class="flex items-center">
                                    <input type="checkbox" class="shift-checkbox" name="shifts[]" value="1"
                                        {{ is_array(old('shifts')) && in_array('1', old('shifts')) ? 'checked' : '' }} />
                                    <span class="ml-2">Shift 1</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="shift-checkbox" name="shifts[]" value="2"
                                        {{ is_array(old('shifts')) && in_array('2', old('shifts')) ? 'checked' : '' }} />
                                    <span class="ml-2">Shift 2</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="shift-checkbox" name="shifts[]" value="3"
                                        {{ is_array(old('shifts')) && in_array('3', old('shifts')) ? 'checked' : '' }} />
                                    <span class="ml-2">Shift 3</span>
                                </label>
                            </div>
                            @error('shifts')
                                <div class="text-red-500 text-sm mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Dynamic Shift Inputs -->
                        <div id="shift-container">

                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end mt-6">
                            <button type="submit"
                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Submit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const shiftContainer = document.getElementById('shift-container');
            const checkboxes = document.querySelectorAll('.shift-checkbox');
            const maxShifts = 3; // Limit to 3 shifts max

            // Function to initialize Tom Select with remote data loading
            function initializeTomSelectForNewElements() {
                document.querySelectorAll('.item-code-selector').forEach((selectElement) => {
                    // Check if Tom Select is already initialized on this element
                    if (!selectElement.tomselect) {
                        try {
                            new TomSelect(selectElement, {
                                create: false,
                                valueField: 'value',
                                labelField: 'text',
                                searchField: 'text',
                                placeholder: "Type to search item codes...",
                                load: function(query, callback) {
                                    if (query.length < 2) {
                                        return callback();
                                    }
                                    
                                    fetch(`{{ route('daily-item-code.get-item-codes') }}?search=${encodeURIComponent(query)}&limit=50`)
                                        .then(response => response.json())
                                        .then(data => {
                                            callback(data.items);
                                        })
                                        .catch(error => {
                                            console.error('Error loading item codes:', error);
                                            callback();
                                        });
                                }
                            });
                        } catch (error) {
                            console.error("Error initializing TomSelect: ", error);
                        }
                    }
                });
            }

            const itemCodeMaxQuantities = {}; // Store max quantities for each item code
            const shiftQuantities = {}; // Store quantities for each shift and item code

            const form = document.getElementById('input-form');

            let formChanged = false;
            let formSubmitting = false;

            form.addEventListener('input', function() {
                formChanged = true;
            });

            // Beforeunload event to show confirmation dialog
            window.addEventListener('beforeunload', function(event) {
                if (formChanged && !formSubmitting) {
                    // Standard message for modern browsers
                    event.preventDefault();
                    event.returnValue = '';

                    // Custom message won't always show in all browsers but can still be used
                    return "Changes you made may not be saved.";
                }
            });

            // Disable the alert when the form is submitted
            form.addEventListener('submit', function() {
                formSubmitting = true; // Set this to true to prevent the alert from showing
            });

            // Clear the container and add shift inputs dynamically
            function updateShiftInputs() {
                shiftContainer.innerHTML = ''; // Clear the shift container

                // Assuming checkboxes is a NodeList or HTMLCollection, convert it to an array
                const checkboxesArray = Array.from(checkboxes);

                checkboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        const shift = checkbox.value;
                        const selectedDate = new Date("{{ $selectedDate }}");

                        // Get the current date
                        const today = new Date(selectedDate);

                        // Get tomorrow's date
                        const tomorrow = new Date(today);
                        tomorrow.setDate(today.getDate() + 1);

                        // Define default values for each shift based on the current date
                        const defaultStartDates = {
                            'shift1': formatDate(today),
                            'shift2': formatDate(today),
                            'shift3': formatDate(today)
                        };

                        const defaultEndDates = {
                            'shift1': formatDate(today),
                            'shift2': formatDate(today),
                            'shift3': formatDate(tomorrow)
                        };

                        const defaultStartTimes = {
                            'shift1': '07:30',
                            'shift2': '16:30',
                            'shift3': '23:30'
                        };

                        const defaultEndTimes = {
                            'shift1': '15:30',
                            'shift2': '22:30',
                            'shift3': '07:30'
                        };

                        // Retrieve the default values for this shift from the backend (blade syntax)
                        const defaultStartDate = defaultStartDates['shift' + shift];
                        const defaultEndDate = defaultEndDates['shift' + shift];
                        const defaultStartTime = defaultStartTimes['shift' + shift];
                        const defaultEndTime = defaultEndTimes['shift' + shift];

                        const shiftHtml = `
                            <div class="shift-wrapper space-y-4 mb-6 border border-gray-400 rounded-md p-3" data-shift="${shift}">
                                <h3 class="text-md font-bold">Shift ${shift}</h3>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Item Code</label>
                                    <select name="item_codes[${shift}][]" required
                                        class="item-code-selector mt-1 block w-full bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="" selected disabled>-- Start typing to search Item Code --</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Quantity</label>
                                    <input type="number"  min="0"  name="quantities[${shift}][]" id="quantity-input-${shift}" required
                                        class="quantity-input mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Remark</label>
                                    <textarea name="remarks[${shift}][]" rows="2" placeholder="Enter any additional notes or remarks (optional)"
                                        class="remark-input mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm resize-vertical"></textarea>
                                </div>
                                <!-- Max Quantity Display Container for this shift -->
                                <div id="max-quantity-display-${shift}" class="max-quantity-display-shift" style="display: none;">
                                </div>
                                <!-- Start Date and End Date -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Start Date</label>
                                        <input type="date" name="start_dates[${shift}][]" value="${defaultStartDate}" required
                                            class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">End Date</label>
                                        <input type="date" name="end_dates[${shift}][]" value="${defaultEndDate}" required
                                            class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>
                                </div>

                                <!-- Start Time and End Time -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Start Time</label>
                                        <input type="time" name="start_times[${shift}][]" value="${defaultStartTime}" required
                                            class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">End Time</label>
                                        <input type="time" name="end_times[${shift}][]" value="${defaultEndTime}" required
                                            class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>
                                </div>
                                <button type="button" class="add-more-button-${shift} bg-indigo-500 text-white px-4 py-2 mt-4 rounded-md">Add Another Item</button>
                            </div>
                            `;
                        shiftContainer.insertAdjacentHTML('beforeend', shiftHtml);

                        // Initialize Tom Select after elements are dynamically added
                        initializeTomSelectForNewElements();

                        // Add the event listener for "Add Another Item" button
                        document.querySelectorAll(`.add-more-button-${shift}`).forEach(button => {
                            button.addEventListener('click', function() {
                                const shiftWrapper = this.closest('.shift-wrapper');
                                addAdditionalInputs(shiftWrapper, shift);
                            });
                        });
                    }
                });

                // Add event listeners for item code changes and quantity inputs
                addItemCodeAndQuantityListeners();
            }

            // Helper function to format date as YYYY-MM-DD
            function formatDate(date) {
                const year = date.getFullYear();
                const month = ('0' + (date.getMonth() + 1)).slice(-2);
                const day = ('0' + date.getDate()).slice(-2);
                return `${year}-${month}-${day}`;
            }

            // Function to add additional inputs dynamically
            function addAdditionalInputs(shiftWrapper, shift) {
                const selectedDate = new Date("{{ $selectedDate }}");

                // Get the current date
                const today = new Date(selectedDate);

                // Get tomorrow's date
                const tomorrow = new Date(today);
                tomorrow.setDate(today.getDate() + 1);

                // Define default values for each shift based on the current date
                const defaultStartDates = {
                    'shift1': formatDate(today),
                    'shift2': formatDate(today),
                    'shift3': formatDate(today)
                };

                const defaultEndDates = {
                    'shift1': formatDate(today),
                    'shift2': formatDate(today),
                    'shift3': formatDate(tomorrow)
                };

                const defaultStartTimes = {
                    'shift1': '07:30',
                    'shift2': '16:30',
                    'shift3': '23:30'
                };

                const defaultEndTimes = {
                    'shift1': '15:30',
                    'shift2': '22:30',
                    'shift3': '07:30'
                };

                const defaultStartDate = defaultStartDates['shift' + shift];
                const defaultEndDate = defaultEndDates['shift' + shift];
                const defaultStartTime = defaultStartTimes['shift' + shift];
                const defaultEndTime = defaultEndTimes['shift' + shift];

                const additionalHtml = `
                    <div class="additional-inputs space-y-4 border border-gray-300 rounded-md p-3 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Item Code</label>
                            <select name="item_codes[${shift}][]" required
                                class="item-code-selector mt-1 block w-full bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="" selected disabled>-- Start typing to search Item Code --</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Quantity</label>
                            <input type="number"  min="0"  name="quantities[${shift}][]" required
                                class="quantity-input mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Remark</label>
                            <textarea name="remarks[${shift}][]" rows="2" placeholder="Enter any additional notes or remarks (optional)"
                                class="remark-input mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm resize-vertical"></textarea>
                        </div>
                        <!-- Max Quantity Display Container for additional inputs -->
                        <div class="max-quantity-display-additional" style="display: none;">
                        </div>
                        <!-- Start Date and End Date -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Start Date</label>
                                <input type="date" name="start_dates[${shift}][]" value="${defaultStartDate}" required
                                    class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">End Date</label>
                                <input type="date" name="end_dates[${shift}][]" value="${defaultEndDate}" required
                                    class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                        </div>

                        <!-- Start Time and End Time -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Start Time</label>
                                <input type="time" name="start_times[${shift}][]" value="${defaultStartTime}" required
                                    class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">End Time</label>
                                <input type="time" name="end_times[${shift}][]" value="${defaultEndTime}" required
                                    class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                    `;

                shiftWrapper.insertAdjacentHTML('beforeend', additionalHtml);

                // Reinitialize any necessary event listeners or plugins for the new inputs
                initializeTomSelectForNewElements();
                addItemCodeAndQuantityListeners();
            }

            // Function to add event listeners to item code and quantity inputs
            function addItemCodeAndQuantityListeners() {
                // Add event listener for item code change
                document.querySelectorAll('.item-code-selector').forEach(selector => {
                    selector.addEventListener('change', function() {
                        const itemCode = this.value;
                        const shiftWrapper = this.closest('.shift-wrapper') || this.closest('.additional-inputs');
                        let shift;
                        
                        if (shiftWrapper.classList.contains('shift-wrapper')) {
                            shift = shiftWrapper.getAttribute('data-shift');
                        } else {
                            // For additional inputs, find the parent shift wrapper
                            const parentShiftWrapper = shiftWrapper.closest('.shift-wrapper');
                            shift = parentShiftWrapper.getAttribute('data-shift');
                        }
                        
                        const quantityInput = shiftWrapper.querySelector('.quantity-input');

                        // Clear the quantity input when item code changes
                        if (quantityInput) {
                            quantityInput.value = '';
                        }

                        // Trigger AJAX to fetch max quantity when the item code is selected
                        if (itemCode) {
                            triggerAjax(shift, itemCode, shiftWrapper);
                        }
                    });
                });

                // Add event listener for quantity inputs
                document.querySelectorAll('.quantity-input').forEach(input => {
                    let typingTimer; // Timer identifier
                    const doneTypingInterval = 500; // Time in ms, 0.5 seconds

                    input.addEventListener('keyup', function() {
                        clearTimeout(typingTimer);
                        typingTimer = setTimeout(() => {
                            const shiftWrapper = this.closest('.shift-wrapper') || this.closest('.additional-inputs');
                            let shift;
                            
                            if (shiftWrapper.classList.contains('shift-wrapper')) {
                                shift = shiftWrapper.getAttribute('data-shift');
                            } else {
                                // For additional inputs, find the parent shift wrapper
                                const parentShiftWrapper = shiftWrapper.closest('.shift-wrapper');
                                shift = parentShiftWrapper.getAttribute('data-shift');
                            }
                            
                            const itemCodeSelector = shiftWrapper.querySelector('.item-code-selector');
                            const itemCode = itemCodeSelector ? itemCodeSelector.value : '';
                            const quantity = this.value;

                            // Trigger AJAX and check max quantity for the input value
                            if (itemCode && quantity) {
                                triggerAjax(shift, itemCode, shiftWrapper, quantity);
                            }
                        }, doneTypingInterval);
                    });

                    input.addEventListener('keydown', function() {
                        clearTimeout(typingTimer);
                    });
                });
            }

            // Function to trigger AJAX call for max quantity
            function triggerAjax(shift, itemCode, shiftWrapper, quantity = 0) {
                // Check if we already have a max_quantity stored for this item_code
                let maxQuantity = itemCodeMaxQuantities[itemCode] || null;

                // If no max quantity is stored, fetch it from the server
                if (!maxQuantity) {
                    fetch("{{ route('calculate.item') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                item_code: itemCode
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                alert(data.error);
                            } else {
                                itemCodeMaxQuantities[itemCode] = data.max_quantity; // Store the max quantity
                                updateQuantityCheck(shift, itemCode, quantity, shiftWrapper);
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching max quantity:', error);
                        });
                } else {
                    updateQuantityCheck(shift, itemCode, quantity, shiftWrapper); // Use stored max quantity
                }
            }

            // Function to check and update quantity for the same item code across shifts
            function updateQuantityCheck(currentShift, itemCode, quantity, shiftWrapper) {
                const maxQuantity = itemCodeMaxQuantities[itemCode];

                // Store the quantity for this shift and item code
                if (!shiftQuantities[itemCode]) {
                    shiftQuantities[itemCode] = {};
                }
                shiftQuantities[itemCode][currentShift] = parseInt(quantity) || 0;

                // Calculate total quantity across all shifts for this item code
                let totalQuantity = 0;
                Object.values(shiftQuantities[itemCode]).forEach(qty => {
                    totalQuantity += qty;
                });

                // Update the max quantity display in the current shift wrapper
                updateMaxQuantityDisplayInShift(itemCode, maxQuantity, totalQuantity, shiftWrapper);

                // Check if total quantity exceeds max quantity
                const currentQuantityInput = shiftWrapper.querySelector('.quantity-input');
                if (totalQuantity > maxQuantity) {
                              if (currentQuantityInput) {
                        currentQuantityInput.style.borderColor = 'red';
                        currentQuantityInput.setCustomValidity(`Total quantity (${totalQuantity}) exceeds maximum allowed (${maxQuantity})`);
                    }
                } else {
                    if (currentQuantityInput) {
                        currentQuantityInput.style.borderColor = '';
                        currentQuantityInput.setCustomValidity('');
                    }
                }

                // Update display for all other shifts that have the same item code
                updateAllShiftsMaxQuantityDisplay(itemCode, maxQuantity, totalQuantity);
            }

            // Function to update max quantity display in a specific shift wrapper
            function updateMaxQuantityDisplayInShift(itemCode, maxQuantity, totalQuantity, shiftWrapper) {
                let displayContainer = shiftWrapper.querySelector('.max-quantity-display-shift, .max-quantity-display-additional');
                
                if (displayContainer) {
                    const exceedsMax = totalQuantity > maxQuantity;
                    const colorClass = exceedsMax ? 'text-red-600' : 'text-green-600';
                    const warningIcon = exceedsMax ? '⚠️' : '✅';
                    
                    displayContainer.innerHTML = `
                        <div class="p-3 bg-gray-50 rounded-md border ${exceedsMax ? 'border-red-300' : 'border-green-300'}">
                            <p class="${colorClass} text-sm font-medium">
                                ${warningIcon} Item Code: <span class="font-mono">${itemCode}</span>
                            </p>
                            <p class="${colorClass} text-sm">
                                Total Quantity: <span class="font-bold">${totalQuantity}</span> / Max: <span class="font-bold">${maxQuantity}</span>
                            </p>
                            ${exceedsMax ? '<p class="text-red-600 text-xs mt-1">⚠️ Warning: Total quantity exceeds maximum allowed!</p>' : ''}
                        </div>
                    `;
                    displayContainer.style.display = 'block';
                }
            }

            // Function to update max quantity display for all shifts with the same item code
            function updateAllShiftsMaxQuantityDisplay(itemCode, maxQuantity, totalQuantity) {
                // Find all item code selectors with the same value
                document.querySelectorAll('.item-code-selector').forEach(selector => {
                    if (selector.value === itemCode) {
                        const shiftWrapper = selector.closest('.shift-wrapper') || selector.closest('.additional-inputs');
                        updateMaxQuantityDisplayInShift(itemCode, maxQuantity, totalQuantity, shiftWrapper);
                    }
                });
            }

            // Function to handle remark field validation and formatting
            function addRemarkHandlers() {
                document.querySelectorAll('.remark-input').forEach(textarea => {
                    // Auto-resize textarea as user types
                    textarea.addEventListener('input', function() {
                        this.style.height = 'auto';
                        this.style.height = this.scrollHeight + 'px';
                        
                        // Optional: Add character counter
                        updateCharacterCounter(this);
                    });

                    // Add focus and blur effects
                    textarea.addEventListener('focus', function() {
                        this.classList.add('ring-2', 'ring-indigo-500');
                    });

                    textarea.addEventListener('blur', function() {
                        this.classList.remove('ring-2', 'ring-indigo-500');
                        
                        // Trim whitespace when user leaves the field
                        this.value = this.value.trim();
                    });

                    // Prevent form submission on Enter key in remark field
                    textarea.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' && !e.shiftKey) {
                            e.preventDefault();
                            // Insert line break instead
                            const start = this.selectionStart;
                            const end = this.selectionEnd;
                            this.value = this.value.substring(0, start) + '\n' + this.value.substring(end);
                            this.selectionStart = this.selectionEnd = start + 1;
                            
                            // Trigger input event to resize
                            this.dispatchEvent(new Event('input'));
                        }
                    });
                });
            }

            // Function to add character counter for remark fields
            function updateCharacterCounter(textarea) {
                const maxLength = 500; // Set maximum character limit
                const currentLength = textarea.value.length;
                
                // Find or create character counter
                let counter = textarea.parentNode.querySelector('.char-counter');
                if (!counter) {
                    counter = document.createElement('div');
                    counter.className = 'char-counter text-xs text-gray-500 mt-1 text-right';
                    textarea.parentNode.appendChild(counter);
                }
                
                counter.textContent = `${currentLength}/${maxLength}`;
                
                // Change color if approaching limit
                if (currentLength > maxLength * 0.9) {
                    counter.className = 'char-counter text-xs text-red-500 mt-1 text-right';
                } else if (currentLength > maxLength * 0.7) {
                    counter.className = 'char-counter text-xs text-yellow-500 mt-1 text-right';
                } else {
                    counter.className = 'char-counter text-xs text-gray-500 mt-1 text-right';
                }

                // Enforce character limit
                if (currentLength > maxLength) {
                    textarea.value = textarea.value.substring(0, maxLength);
                    counter.textContent = `${maxLength}/${maxLength}`;
                }
            }

           function validateForm() {
            let isValid = true;
            const errors = [];

            // Check if at least one shift is selected
            const checkedShifts = document.querySelectorAll('.shift-checkbox:checked');
            if (checkedShifts.length === 0) {
                errors.push('Please select at least one shift.');
                isValid = false;
            }

            // Validate each shift's inputs
            checkedShifts.forEach(checkbox => {
                const shift = checkbox.value;
                const shiftWrapper = document.querySelector(`[data-shift="${shift}"]`);
                
                if (shiftWrapper) {
                    // Get all item selectors in this shift (including additional inputs)
                    const itemSelectors = shiftWrapper.querySelectorAll('.item-code-selector');
                    const quantityInputs = shiftWrapper.querySelectorAll('.quantity-input');
                    
                    itemSelectors.forEach((selector, index) => {
                        // Check if Tom Select is initialized on this element
                        let selectedValue = '';
                        
                        if (selector.tomselect) {
                            // Get value from Tom Select instance
                            selectedValue = selector.tomselect.getValue();
                        } else {
                            // Fallback to regular select value
                            selectedValue = selector.value;
                        }
                        
                        // if (!selectedValue || selectedValue === '') {
                        //     errors.push(`Shift ${shift}: Please select an item code for entry ${index + 1}.`);
                        //     isValid = false;
                            
                        //     // Highlight the problematic field
                        //     if (selector.tomselect) {
                        //         selector.tomselect.control.style.borderColor = 'red';
                        //     } else {
                        //         selector.style.borderColor = 'red';
                        //     }
                        // } else {
                        //     // Reset border color if valid
                        //     if (selector.tomselect) {
                        //         selector.tomselect.control.style.borderColor = '';
                        //     } else {
                        //         selector.style.borderColor = '';
                        //     }
                        // }
                    });
                    
                    quantityInputs.forEach((input, index) => {
                        const quantity = parseFloat(input.value);
                        if (!input.value || isNaN(quantity) || quantity < 0) {
                            errors.push(`Shift ${shift}: Please enter a valid quantity for entry ${index + 1}.`);
                            isValid = false;
                            input.style.borderColor = 'red';
                        } else {
                            input.style.borderColor = '';
                        }
                    });
                }
            });

           // Check for quantity limit violations
            Object.keys(itemCodeMaxQuantities).forEach(itemCode => {
            if (shiftQuantities[itemCode]) {
                const totalQuantity = Object.values(shiftQuantities[itemCode]).reduce((sum, qty) => sum + qty, 0);
                const maxQuantity = itemCodeMaxQuantities[itemCode];
                
                if (totalQuantity > maxQuantity) {
                    errors.push(`Item ${itemCode}: Total quantity (${totalQuantity}) exceeds maximum allowed (${maxQuantity}).`);
                    isValid = false;
                }
            }
            });

            // Show errors if any
            if (!isValid) {
            // Create a more user-friendly error display
            const errorHtml = `
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                    <strong class="font-bold">Please fix the following errors:</strong>
                    <ul class="mt-2 list-disc list-inside">
                        ${errors.map(error => `<li>${error}</li>`).join('')}
                    </ul>
                </div>
            `;

            // Remove existing error messages
            const existingError = document.querySelector('.validation-errors');
            if (existingError) {
                existingError.remove();
            }

            // Insert error message at the top of the form
            const form = document.getElementById('input-form');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'validation-errors';
            errorDiv.innerHTML = errorHtml;
            form.insertBefore(errorDiv, form.firstChild);

            // Scroll to the top to show the error
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            return isValid;
        }

        // Enhanced form submission handler
        document.getElementById('input-form').addEventListener('submit', function(e) {
            // Remove previous validation errors
            const existingError = document.querySelector('.validation-errors');
            if (existingError) {
                existingError.remove();
            }
            
            if (!validateForm()) {
                e.preventDefault();
                return false;
            }
            
            formSubmitting = true;
            
            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                const originalText = submitButton.innerHTML;
                submitButton.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Submitting...
                `;
                
                // Reset button if form submission fails
                setTimeout(() => {
                    if (submitButton.disabled) {
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalText;
                    }
                }, 10000); // Reset after 10 seconds as fallback
            }
        });

        // Additional debugging function to check Tom Select values
        function debugTomSelectValues() {
            console.log('=== Tom Select Debug Info ===');
            document.querySelectorAll('.item-code-selector').forEach((selector, index) => {
                console.log(`Selector ${index + 1}:`);
                console.log('  Regular value:', selector.value);
                if (selector.tomselect) {
                    console.log('  Tom Select value:', selector.tomselect.getValue());
                    console.log('  Tom Select items:', selector.tomselect.items);
                } else {
                    console.log('  Tom Select not initialized');
                }
            });
            console.log('========================');
        }

            // Enhanced form submission handler
            form.addEventListener('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                    return false;
                }
                
                formSubmitting = true;
                
                // Optional: Show loading state
                const submitButton = this.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = `
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Submitting...
                    `;
                }
            });

            // Event listener for checkboxes to update shift inputs
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateShiftInputs);
            });

            // Initialize remark handlers
            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('remark-input')) {
                    addRemarkHandlers();
                }
            });

            // Initialize form on page load
            updateShiftInputs();
            addRemarkHandlers();
        });
    </script>

    <style>
        /* Custom styles for Tom Select */
        .ts-wrapper {
            position: relative;
        }
        
        .ts-control {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            background-color: white;
            min-height: 2.5rem;
        }
        
        .ts-control:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 1px #6366f1;
        }
        
        .ts-dropdown {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .ts-option {
            padding: 0.5rem 0.75rem;
        }
        
        .ts-option:hover,
        .ts-option.active {
            background-color: #f3f4f6;
        }
        
        /* Styles for quantity validation */
        .quantity-input.error {
            border-color: #ef4444;
            background-color: #fef2f2;
        }
        
        .quantity-input.warning {
            border-color: #f59e0b;
            background-color: #fffbeb;
        }
        
        .quantity-input.success {
            border-color: #10b981;
            background-color: #f0fdf4;
        }
        
        /* Max quantity display styles */
        .max-quantity-display-shift,
        .max-quantity-display-additional {
            transition: all 0.3s ease;
        }
        
        .max-quantity-display-shift[style*="block"],
        .max-quantity-display-additional[style*="block"] {
            animation: fadeIn 0.3s ease-in-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Shift wrapper styling */
        .shift-wrapper {
            transition: all 0.3s ease;
        }
        
        .shift-wrapper:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .additional-inputs {
            background-color: #fafafa;
            position: relative;
        }
        
        .additional-inputs::before {
            content: '';
            position: absolute;
            left: -10px;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 80%;
            background-color: #6366f1;
            border-radius: 2px;
        }
    </style>
</x-app-layout>