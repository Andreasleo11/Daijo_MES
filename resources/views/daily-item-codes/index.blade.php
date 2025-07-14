<x-app-layout>
    <section>
        <div class="flex flex-col min-h-screen bg-gray-100 md:flex-row">
            <!-- Main Calendar View -->
            <div class="flex-1 p-6">
                <!-- Top Navigation -->
                <div class="flex flex-wrap items-center justify-between mb-6">
                    <div class="flex items-center space-x-4 mb-4 md:mb-0">
                        <button id="new-event-btn" class="px-4 py-2 text-blue-600 bg-blue-100 rounded-md">New
                            Event</button>
                        {{-- <button id="day-view-btn" class="px-4 py-2 text-gray-600 bg-white border rounded-md">Day</button>
                        <button id="week-view-btn"
                            class="px-4 py-2 text-gray-600 bg-white border rounded-md">Week</button>
                        <button id="month-view-btn" class="px-4 py-2 bg-blue-500 text-white rounded-md">Month</button> --}}
                    </div>

                    <!-- Month and Year Picker Button -->
                    <div class="flex items-center space-x-4">
                        <button id="today-btn"
                            class="px-4 py-2 bg-gray-200 rounded-md transition ease-in-out delay-100 hover:bg-blue-500 duration-300 hover:text-white">Today</button>
                        <div class="flex items-center">
                            <button id="prev-month-btn" class="text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <button id="month-year-picker-btn"
                                class="px-4 py-2 text-lg font-semibold bg-transparent hover:bg-gray-200 rounded-md transition duration-150">
                                <span id="current-month">October 2024</span>
                            </button>
                            <button id="next-month-btn" class="text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>


                    <!-- Month and Year Picker Modal -->
                    <div id="month-year-modal"
                        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
                        <div class="bg-white p-6 rounded-lg w-full max-w-sm">
                            <h2 class="text-lg font-semibold mb-4">Select Month & Year</h2>
                            <div class="grid grid-cols-3 gap-4 mb-4">
                                <button class="month-btn px-2 py-1 rounded-md hover:bg-blue-500 hover:text-white"
                                    data-month="0">Jan</button>
                                <button class="month-btn px-2 py-1 rounded-md hover:bg-blue-500 hover:text-white"
                                    data-month="1">Feb</button>
                                <button class="month-btn px-2 py-1 rounded-md hover:bg-blue-500 hover:text-white"
                                    data-month="2">Mar</button>
                                <button class="month-btn px-2 py-1 rounded-md hover:bg-blue-500 hover:text-white"
                                    data-month="3">Apr</button>
                                <button class="month-btn px-2 py-1 rounded-md hover:bg-blue-500 hover:text-white"
                                    data-month="4">Mei</button>
                                <button class="month-btn px-2 py-1 rounded-md hover:bg-blue-500 hover:text-white"
                                    data-month="5">Jun</button>
                                <button class="month-btn px-2 py-1 rounded-md hover:bg-blue-500 hover:text-white"
                                    data-month="6">Jul</button>
                                <button class="month-btn px-2 py-1 rounded-md hover:bg-blue-500 hover:text-white"
                                    data-month="7">Aug</button>
                                <button class="month-btn px-2 py-1 rounded-md hover:bg-blue-500 hover:text-white"
                                    data-month="8">Sep</button>
                                <button class="month-btn px-2 py-1 rounded-md hover:bg-blue-500 hover:text-white"
                                    data-month="9">Oct</button>
                                <button class="month-btn px-2 py-1 rounded-md hover:bg-blue-500 hover:text-white"
                                    data-month="10">Nov</button>
                                <button class="month-btn px-2 py-1 rounded-md hover:bg-blue-500 hover:text-white"
                                    data-month="11">Dec</button>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Day Headers -->
                <div class="grid grid-cols-7 gap-1 text-center text-gray-600 font-medium mb-2">
                    <div>Sunday</div>
                    <div>Monday</div>
                    <div>Tuesday</div>
                    <div>Wednesday</div>
                    <div>Thursday</div>
                    <div>Friday</div>
                    <div>Saturday</div>
                </div>

                <!-- Main Calendar Grid -->
                <div class="grid grid-cols-7 gap-1 text-center text-gray-800" id="calendar-grid">
                    <!-- The day headers and calendar days will be populated dynamically -->
                </div>
            </div>

            <!-- Sidebar for Event Details -->
            <div id="sidebar"
                class="md:w-1/3 bg-white border-l p-4 flex-col shadow-lg absolute inset-0 md:relative hidden">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-semibold" id="selected-date">Selected Date</h2>
                    <button id="close-sidebar" class="text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Search Field for User Filtering -->
                <div class="relative mt-4">
                    <input type="text" id="user-search" class="w-full pl-10 pr-4 py-2 border rounded-md"
                        placeholder="Search by machine or event...">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M9 3a7 7 0 105.663 11.751l4.686 4.686a1 1 0 01-1.414 1.414l-4.686-4.686A7 7 0 009 3zm0 2a5 5 0 100 10A5 5 0 009 5z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>


                <div id="event-list" class="mt-4 space-y-4">
                    <!-- Event details will be dynamically populated here -->
                </div>
            </div>

            <!-- Modal for Adding New Events -->
            <div id="event-modal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
                <div class="bg-white p-6 rounded-lg w-full max-w-md">
                    <h2 class="text-lg font-semibold mb-4">Add New Event</h2>
                    <form id="event-form" class="space-y-4" method="GET">
                        <div>
                            <label for="event-date" class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" id="event-date"
                                class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md" name="date"
                                required>
                        </div>
                        <div class="flex justify-end space-x-4">
                            <button type="button" id="close-modal"
                                class="px-4 py-2 bg-gray-300 rounded-md">Cancel</button>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">Add
                                Event</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal for Editing DailyItemCode -->
            <div id="edit-event-modal"
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
                <div class="bg-white p-6 rounded-lg w-full max-w-md">
                    <h2 class="text-lg font-semibold mb-4">Edit Daily Item Code</h2>
                    <form id="edit-event-form" class="space-y-4" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit-daily-item-id" name="id">

                        <div>
                            <label for="edit-item-code" class="block text-sm font-medium text-gray-700">Item
                                Code</label>
                            <select name="item_code" id="edit-item-code" class="w-full mt-1" required>
                                @foreach ($itemCodes as $itemCode)
                                    <option value="{{ $itemCode }}">{{ $itemCode }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="edit-quantity"
                                class="block text-sm font-medium text-gray-700">Quantity</label>
                            <input type="number" id="edit-quantity" name="quantity"
                                class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md" required>
                        </div>

                        <div>
                            <label for="edit-shift" class="block text-sm font-medium text-gray-700">Shift</label>
                            <input type="number" id="edit-shift" name="shift"
                                class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md" required>
                        </div>

                        <!-- Start Date and Start Time (2:1 grid) -->
                        <div class="grid grid-cols-3 gap-4">
                            <div class="col-span-2">
                                <label for="edit-start-date" class="block text-sm font-medium text-gray-700">Start
                                    Date</label>
                                <input type="date" id="edit-start-date" name="start_date"
                                    class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md" required>
                            </div>
                            <div>
                                <label for="edit-start-time" class="block text-sm font-medium text-gray-700">Start
                                    Time</label>
                                <input type="time" id="edit-start-time" name="start_time"
                                    class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md" required>
                            </div>
                        </div>

                        <!-- End Date and End Time (2:1 grid) -->
                        <div class="grid grid-cols-3 gap-4">
                            <div class="col-span-2">
                                <label for="edit-end-date" class="block text-sm font-medium text-gray-700">End
                                    Date</label>
                                <input type="date" id="edit-end-date" name="end_date"
                                    class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md" required>
                            </div>
                            <div>
                                <label for="edit-end-time" class="block text-sm font-medium text-gray-700">End
                                    Time</label>
                                <input type="time" id="edit-end-time" name="end_time"
                                    class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md" required>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-4">
                            <button type="button" id="close-edit-modal"
                                class="px-4 py-2 bg-gray-300 rounded-md">Cancel</button>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">Save</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        <script>
            // Initialize Tom Select on the select element
            document.addEventListener('DOMContentLoaded', function() {
                const editItemCodeTomSelect = new TomSelect("#edit-item-code", {
                    plugins: ['dropdown_input'],
                    create: false, // Prevent adding new options
                    sortField: {
                        field: "text",
                        direction: "asc"
                    },
                    maxOptions: 1000, // Customize how many options to show
                    placeholder: "Select Item Code"
                });
            });

            const dailyItemCodes = @json($dailyItemCodes);
            const userList =
                @json($users); // Assuming you have a list of users passed in from your Laravel controller

            document.addEventListener("DOMContentLoaded", function() {
                const calendarGrid = document.getElementById("calendar-grid");
                const currentMonthLabel = document.getElementById("current-month");
                const sidebar = document.getElementById("sidebar");
                const closeSidebarButton = document.getElementById("close-sidebar");
                const selectedDateDisplay = document.getElementById("selected-date");
                const eventList = document.getElementById("event-list");
                const userSearchInput = document.getElementById("user-search");

                let currentDate = new Date();
                let selectedDate = null;
                let events = {};
                let filteredEvents = {};

                // Process events to group by user
                dailyItemCodes.forEach(item => {
                    const eventDate = item.schedule_date;
                    if (!events[eventDate]) {
                        events[eventDate] = {};
                    }
                    const userName = userList.find(user => user.id === item.user_id).name;
                    if (!events[eventDate][userName]) {
                        events[eventDate][userName] = [];
                    }
                    events[eventDate][userName].push({
                        time: item.start_time + ' - ' + item.end_time,
                        title: item.item_code + ' (Shift ' + item.shift + ')',
                        id: item.id
                    });
                });

                // Populate the calendar and handle user interaction
                function populateCalendar(date) {
                    calendarGrid.innerHTML = "";
                    const daysInMonth = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
                    const firstDayOfMonth = new Date(date.getFullYear(), date.getMonth(), 1).getDay();
                    const today = new Date();

                    for (let i = 0; i < firstDayOfMonth; i++) {
                        const emptyCell = document.createElement("div");
                        emptyCell.className = "py-12 bg-gray-50";
                        calendarGrid.appendChild(emptyCell);
                    }

                    for (let i = 1; i <= daysInMonth; i++) {
                        const dayCell = document.createElement("div");
                        const cellDate = new Date(date.getFullYear(), date.getMonth(), i);
                        const dateString = new Date(cellDate.getTime() - (cellDate.getTimezoneOffset() * 60000))
                            .toISOString().split('T')[0];

                        dayCell.className =
                            "relative py-12 border cursor-pointer text-left pl-2 bg-white hover:bg-blue-100";
                        if (today.getFullYear() === cellDate.getFullYear() && today.getMonth() === cellDate
                            .getMonth() && today.getDate() === i) {
                            const todayCircle = document.createElement("span");
                            todayCircle.className =
                                "w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-lg";
                            todayCircle.style.position = "absolute";
                            todayCircle.style.top = "0.25rem";
                            todayCircle.style.left = "0.25rem";
                            todayCircle.innerText = i;
                            dayCell.appendChild(todayCircle);
                        } else {
                            const dayText = document.createElement("span");
                            dayText.className = "text-gray-800 absolute left-2 top-1 text-lg";
                            dayText.innerText = i;
                            dayCell.appendChild(dayText);
                        }

                        if (selectedDate && selectedDate.getFullYear() === cellDate.getFullYear() && selectedDate
                            .getMonth() === cellDate.getMonth() && selectedDate.getDate() === i) {
                            dayCell.classList.add("border-blue-500", "border-2", "bg-blue-100");
                        }

                        if (events[dateString]) {
                            const eventBadge = document.createElement("div");
                            let userAssignedCount = Object.keys(events[dateString]).length;
                            let color = 'bg-black text-white';
                            let text =
                                `${userAssignedCount} /${userList.length} ${userAssignedCount > 1 ? 'Machines' : 'Machine'}`;

                            if (userAssignedCount == userList.length) {
                                color = 'bg-green-500 text-white';
                                text = 'All Assigned';
                            } else if (userAssignedCount < userList.length) {
                                color = 'bg-yellow-500 text-white';
                            } else {
                                color = 'bg-red-500 text-white';
                                text = 'Not assigned'
                            }

                            eventBadge.className =
                                `absolute bottom-1 right-1 ${color} text-xs px-2 py-1 rounded-full`;
                            eventBadge.innerText = text;
                            dayCell.appendChild(eventBadge);
                        }

                        dayCell.addEventListener("click", function() {
                            selectedDate = cellDate;
                            const day = String(cellDate.getDate()).padStart(2, '0');
                            const month = String(cellDate.getMonth() + 1).padStart(2,
                                '0'); // getMonth() is zero-indexed
                            const year = cellDate.getFullYear();
                            const formattedDate = `${day}/${month}/${year}`;

                            selectedDateDisplay.innerText = formattedDate;
                            showEventsForDate(dateString);
                            sidebar.classList.remove("hidden");
                            populateCalendar(currentDate);
                        });

                        calendarGrid.appendChild(dayCell);
                    }

                    const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August",
                        "September", "October", "November", "December"
                    ];
                    currentMonthLabel.innerText = `${monthNames[date.getMonth()]} ${date.getFullYear()}`;

                    const currentMonthText = currentMonthLabel.innerText.split(' ');
                    const currentMonth = currentMonthText[0];
                    const currentYear = parseInt(currentMonthText[1]);
                    const displayedMonthIndex = monthNames.indexOf(currentMonth);

                    // Check if the displayed month is the current month
                    if (today.getFullYear() === currentYear && today.getMonth() === displayedMonthIndex) {
                        document.getElementById("today-btn").classList.add('bg-blue-500', 'text-white');
                        document.getElementById("today-btn").classList.remove('bg-gray-200', 'text-gray-800');
                    } else {
                        document.getElementById("today-btn").classList.add('bg-gray-200', 'text-gray-800');
                        document.getElementById("today-btn").classList.remove('bg-blue-500', 'text-white');
                    }
                }

                // Re-populate the calendar and update button state when Today is clicked
                document.getElementById("today-btn").addEventListener("click", function() {
                    currentDate = new Date();
                    populateCalendar(currentDate);
                });

                // Show events for the selected date and group by user
                function showEventsForDate(date) {
                    eventList.innerHTML = "";
                    const searchTerm = userSearchInput.value.toLowerCase();

                    if (events[date]) {
                        Object.keys(events[date]).forEach(userName => {
                            // Check if user name matches the search term
                            if (userName.toLowerCase().includes(searchTerm) || searchTerm === '') {
                                const userDiv = document.createElement("div");
                                userDiv.className = "mb-4";

                                const userHeader = document.createElement("h3");
                                userHeader.className =
                                    "font-medium text-lg cursor-pointer text-blue-600 border rounded-md py-2 px-4 border-blue-600 hover:bg-blue-600 hover:border-transparent hover:text-white";
                                userHeader.innerText = userName;

                                const userEventsDiv = document.createElement("div");
                                userEventsDiv.className = "hidden mt-2 px-4 py-2 border rounded-md";

                                userHeader.addEventListener("click", function() {
                                    userEventsDiv.classList.toggle("hidden");
                                });

                                // Filter events by title based on the search term
                                events[date][userName].forEach(event => {
                                    if (event.title.toLowerCase().includes(searchTerm) || searchTerm ===
                                        '') {
                                        const eventDiv = document.createElement("div");
                                        eventDiv.className = "mb-4 flex justify-between items-center";

                                        const eventInfo = document.createElement("div");
                                        eventInfo.innerHTML =
                                            `<h3 class="font-medium">${event.time}</h3><p class="text-gray-500">${event.title}</p>`;
                                        eventDiv.appendChild(eventInfo);

                                        const editButton = document.createElement("button");
                                        editButton.className =
                                            "ml-2 text-blue-600 bg-blue-100 px-2 py-1 rounded-md text-sm";
                                        editButton.innerText = "Edit";
                                        editButton.onclick = function() {
                                            editEvent(event.id);
                                        };

                                        const deleteButton = document.createElement("button");
                                        deleteButton.className =
                                            "ml-2 text-red-600 bg-red-100 px-2 py-1 rounded-md text-sm";
                                        deleteButton.innerText = "Delete";
                                        deleteButton.onclick = function() {
                                            deleteEvent(event.id);
                                        };

                                        eventDiv.appendChild(editButton);
                                        eventDiv.appendChild(deleteButton);
                                        userEventsDiv.appendChild(eventDiv);
                                    }
                                });

                                userDiv.appendChild(userHeader);
                                userDiv.appendChild(userEventsDiv);
                                eventList.appendChild(userDiv);
                            }
                        });
                    } else {
                        eventList.innerHTML = "<p>No events for this date</p>"
                    }
                    const addEventButton = document.createElement("button");
                    addEventButton.className = "mt-4 text-white bg-blue-600 px-4 py-2 rounded-md";
                    addEventButton.innerText = "Add Event";
                    addEventButton.onclick = function() {
                        const localDateString = selectedDate.toLocaleDateString('en-CA');
                        window.location.href = `/daily-item-codes/daily?date=${localDateString}`;
                    };
                    eventList.appendChild(addEventButton);
                }

                // Filter events when typing in the search box
                userSearchInput.addEventListener("input", function() {
                    if (selectedDate) {
                        showEventsForDate(selectedDate.toISOString().split('T')[0]);
                    }
                });


                function editEvent(eventId) {
                    // Find the event data using the event ID from dailyItemCodes
                    const eventData = dailyItemCodes.find(item => item.id === eventId);

                    // Get the existing TomSelect instance
                    const editItemCodeTomSelect = document.getElementById("edit-item-code").tomselect;

                    // Populate the form with the event data
                    document.getElementById("edit-daily-item-id").value = eventData.id;
                    editItemCodeTomSelect.setValue(eventData.item_code); // Set TomSelect value
                    document.getElementById("edit-quantity").value = eventData.quantity;
                    document.getElementById("edit-shift").value = eventData.shift;
                    document.getElementById("edit-start-date").value = eventData.start_date;
                    document.getElementById("edit-start-time").value = eventData.start_time;
                    document.getElementById("edit-end-date").value = eventData.end_date;
                    document.getElementById("edit-end-time").value = eventData.end_time;

                    // Set the form action to the correct update URL (assuming a RESTful route exists)
                    const editEventForm = document.getElementById("edit-event-form");
                    editEventForm.action = `/daily-item-codes/${eventData.id}`;

                    // Show the modal
                    document.getElementById("edit-event-modal").classList.remove("hidden");
                }

                function deleteEvent(id) {
                    if (!confirm("Are you sure you want to delete this item?")) {
                        return;
                    }

                    fetch(`/daily-item-codes/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                    })
                    .then(response => {
                        if (response.ok) {
                            alert("Item deleted successfully.");
                            // Optionally remove the item from the DOM
                            document.getElementById(`item-${id}`)?.remove();
                            window.location.reload();
                        } else {
                            return response.json().then(error => {
                                throw new Error(error.message || 'Failed to delete item.');
                            });
                        }
                    })
                    .catch(error => {
                        console.error("Delete error:", error);
                        alert("Error deleting item: " + error.message);
                    });
                }


                closeSidebarButton.addEventListener("click", function() {
                    sidebar.classList.add("hidden");
                });

                document.getElementById("prev-month-btn").addEventListener("click", function() {
                    currentDate.setMonth(currentDate.getMonth() - 1);
                    populateCalendar(currentDate);
                });

                document.getElementById("next-month-btn").addEventListener("click", function() {
                    currentDate.setMonth(currentDate.getMonth() + 1);
                    populateCalendar(currentDate);
                });

                document.getElementById("today-btn").addEventListener("click", function() {
                    currentDate = new Date();
                    populateCalendar(currentDate);
                });

                document.getElementById("close-edit-modal").addEventListener("click", function() {
                    document.getElementById("edit-event-modal").classList.add("hidden");
                });

                const eventForm = document.getElementById("event-form");
                const eventModal = document.getElementById("event-modal");
                const closeModalButton = document.getElementById("close-modal");
                const eventDateInput = document.getElementById("event-date");

                // Function to open the modal and pre-populate it with the selected date
                function openModalWithDate(date) {
                    eventDateInput.value = date; // Set the selected date in the input field
                    eventModal.classList.remove("hidden");
                }

                // Example function to handle opening the modal with a selected date from the calendar
                document.getElementById("new-event-btn").addEventListener("click", function() {
                    const selectedDate = new Date().toISOString().split('T')[
                        0]; // Use today's date for demo purposes
                    openModalWithDate(selectedDate); // Open modal with selected date
                });

                // Set the form action dynamically
                eventForm.addEventListener("submit", function(e) {
                    e.preventDefault(); // Prevent default submission behavior
                    const selectedDate = eventDateInput.value;

                    // Set the action URL to include the selected date as a query parameter
                    eventForm.action = `{{ route('daily-item-code.daily', '') }}?date=${selectedDate}`;
                    eventForm.submit(); // Submit the form after setting the action dynamically
                });

                // Close the modal when "Cancel" is clicked
                closeModalButton.addEventListener("click", function() {
                    eventModal.classList.add("hidden");
                });

                // Initial population of calendar
                populateCalendar(currentDate);

                // Initialize Flatpickr on button click
                document.getElementById("month-year-picker-btn").addEventListener("click", function() {
                    flatpickr("#month-year-picker-btn", {
                        dateFormat: "Y-m", // Only show month and year
                        defaultDate: currentDate, // Set the default date to the current date
                        enableTime: false,
                        plugins: [monthSelectPlugin({ // month selection plugin
                            shorthand: true, // Show abbreviated month names
                            dateFormat: "Y-m", // Format for output value
                        })],
                        onChange: function(selectedDates, dateStr) {
                            // Update the calendar based on the selected month and year
                            const [year, month] = dateStr.split("-");
                            currentDate = new Date(year, month - 1, 1);
                            populateCalendar(currentDate);
                            updateSelectedMonthYear(currentDate);
                        }
                    }).open(); // Automatically open the picker when button is clicked
                });

                function updateSelectedMonthYear(date) {
                    const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August",
                        "September", "October", "November", "December"
                    ];
                    document.getElementById("current-month").textContent =
                        `${monthNames[currentDate.getMonth()]} ${currentDate.getFullYear()}`;
                }

            });
        </script>
    </section>
</x-app-layout>
