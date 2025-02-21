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

                        <div id="max-quantity-display" class="max-quantity-display">

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

            // Function to initialize Tom Select on dynamically added select elements
            function initializeTomSelectForNewElements() {
                document.querySelectorAll('.item-code-selector').forEach((selectElement) => {
                    // Check if Tom Select is already initialized on this element
                    if (!selectElement.tomselect) {
                        try {
                            new TomSelect(selectElement, {
                                create: false,
                                sortField: {
                                    field: "text",
                                    direction: 'asc',
                                },
                                maxOptions: 1000,
                                placeholder: "Select item code",
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

            const maxQuantityDisplay = document.getElementById('max-quantity-display');

            // Clear the container and add shift inputs dynamically
            function updateShiftInputs() {
                shiftContainer.innerHTML = ''; // Clear the shift container

                // Assuming checkboxes is a NodeList or HTMLCollection, convert it to an array
                const checkboxesArray = Array.from(checkboxes);

                // Get the max-quantity-display element
                const maxQuantityDisplay = document.getElementById('max-quantity-display');

                // Check if any checkbox is checked
                const anyCheckboxChecked = checkboxesArray.some(checkbox => checkbox.checked);

                // Show or hide the max-quantity-display based on the checkbox status
                if (anyCheckboxChecked) {
                    maxQuantityDisplay.style.display = 'block'; // Show the element
                } else {
                    maxQuantityDisplay.style.display = 'none'; // Hide the element
                }


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
                                        <option value="" selected disabled>-- Select Item Code --</option>
                                        @foreach ($masterListItem as $masterItem)
                                            <option value="{{ $masterItem->item_code }}" data-tipe-mesin="{{ $masterItem->tipe_mesin }}">
                                                {{ $masterItem->item_code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Quantity</label>
                                    <input type="number" name="quantities[${shift}][]" id="quantity-input-${shift}" required
                                        class="quantity-input mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
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
                                <option value="" selected disabled>-- Select Item Code --</option>
                                @foreach ($masterListItem as $masterItem)
                                    <option value="{{ $masterItem->item_code }}" data-tipe-mesin="{{ $masterItem->tipe_mesin }}">
                                        {{ $masterItem->item_code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Quantity</label>
                            <input type="number" name="quantities[${shift}][]" required
                                class="quantity-input mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
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
                        const shift = this.name.match(/\d+/)[
                            0]; // Extract shift number from name attribute
                        const quantityInput = document.querySelector(
                            `input[name="quantities[${shift}]"]`);

                        // Clear the quantity input when item code changes
                        if (quantityInput) {
                            quantityInput.value = '';
                        }

                        // Trigger AJAX to fetch max quantity when the item code is selected
                        if (itemCode) {
                            triggerAjax(shift, itemCode);
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
                            const shift = this.name.match(/\d+/)[
                                0]; // Extract shift number from name attribute
                            const itemCode = document.querySelector(
                                `select[name="item_codes[${shift}][]"]`).value;
                            const quantity = this.value;

                            // Trigger AJAX and check max quantity for the input value
                            if (itemCode && quantity) {
                                triggerAjax(shift, itemCode, quantity);
                            }
                        }, doneTypingInterval);
                    });

                    input.addEventListener('keydown', function() {
                        clearTimeout(typingTimer);
                    });
                });
            }

            // Function to trigger AJAX call for max quantity
            function triggerAjax(shift, itemCode, quantity = 0) {
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
                                updateQuantityCheck(shift, itemCode, quantity);
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching max quantity:', error);
                        });
                } else {
                    updateQuantityCheck(shift, itemCode, quantity); // Use stored max quantity
                }
            }

            // Function to check and update quantity for the same item code across shifts
            function updateQuantityCheck(currentShift, itemCode, quantity) {
                const quantityInputs = document.querySelectorAll(`input[name^="quantities"]`);
                let totalQuantity = 0;

                // Track the input field for the current shift
                const currentQuantityInput = document.querySelector(`input[name="quantities[${currentShift}]"]`);

                // Sum the quantities for the same item code across shifts
                quantityInputs.forEach(input => {
                    const shift = input.name.match(/\d+/)[0]; // Get the shift number
                    const selectedItemCode = document.querySelector(`select[name="item_codes[${shift}][]"]`)
                        .value;

                    if (selectedItemCode === itemCode) {
                        const shiftQuantity = parseFloat(input.value) || 0;
                        totalQuantity += shiftQuantity;
                    }
                });

                // Get the max quantity for the item code
                const maxQuantity = itemCodeMaxQuantities[itemCode];

                // Compare the total quantity to the max quantity
                if (totalQuantity > maxQuantity) {
                    alert(
                        `The total quantity for item code ${itemCode} exceeds the max quantity of ${maxQuantity}.`
                    );

                    // Reset the quantity input for the current shift if exceeded
                    if (currentQuantityInput) {
                        const remainingQuantity = maxQuantity - (totalQuantity -
                            quantity); // Calculate remaining quantity before the current input
                        currentQuantityInput.value = remainingQuantity > 0 ? remainingQuantity :
                            ''; // Set to remaining quantity or empty if none
                    }

                    // After the alert is dismissed, reset the max quantity display for this item code
                    updateMaxQuantityDisplay(currentShift, itemCode, maxQuantity);
                } else {
                    // Update the display of remaining quantity if no alert is shown
                    updateMaxQuantityDisplay(currentShift, itemCode, maxQuantity - totalQuantity);
                }

                // Handle when maxQuantity is 0 gracefully, still allow displaying shift container
                if (maxQuantity === 0) {
                    alert(`The item code ${itemCode} has no available quantity.`);
                    updateMaxQuantityDisplay(currentShift, itemCode, 0);
                }
                // Update the display of remaining quantity or alert if exceeded
            }

            // Function to update the max quantity display based on the selected item code and shift
            function updateMaxQuantityDisplay(shift, itemCode, maxQuantity) {
                const displayContainer = document.getElementById('max-quantity-display');

                // Clear the previous max quantity for this shift by targeting the specific id
                let existingDisplay = document.getElementById(`item-max-quantity-display-${shift}`);
                if (existingDisplay) {
                    existingDisplay.remove(); // Remove old display for this shift
                }

                // Show the new max quantity for the selected item code
                const itemDisplay = document.createElement('div');
                itemDisplay.id = `item-max-quantity-display-${shift}`; // Assign dynamic ID
                itemDisplay.setAttribute('data-shift', shift); // Associate display with the shift
                itemDisplay.textContent = `Shift: ${shift} -- Max Quantity of ${itemCode}: ${maxQuantity}`;

                // Append the new div to the container
                displayContainer.appendChild(itemDisplay);
            }

            // Function to clear max quantity display when item code is changed or cleared
            function clearMaxQuantityDisplay(shift) {
                let existingDisplay = document.querySelector(`[data-shift="${shift}"]`);
                if (existingDisplay) {
                    existingDisplay.remove();
                }
            }

            // Update shift inputs on checkbox change
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateShiftInputs();
                });
            });
        });
    </script>

</x-app-layout>
