<x-app-layout>
    <div class="container mx-auto">
        <h1 class="text-3xl font-bold mb-4">Full Year Calendar</h1>
        <div class="mb-4">
            <button class="bg-blue-500 text-white py-2 px-4 rounded" onclick="openEventModal()">Add Event</button>
        </div>

        <div class="grid grid-cols-3 gap-4">
            @for ($month = 1; $month <= 12; $month++)
                @php
                    $dateObj = \Carbon\Carbon::createFromDate(null, $month, 1);
                    $daysInMonth = $dateObj->daysInMonth;
                @endphp
                <div class="border p-4">
                    <h2 class="text-xl font-bold text-center">{{ $dateObj->format('F') }}</h2>
                    <div class="grid grid-cols-7 gap-2 mt-4">
                        @for ($day = 1; $day <= $daysInMonth; $day++)
                            <button class="border py-2 text-center hover:bg-blue-100"
                                onclick="showEventDetails('{{ $month }}', '{{ $day }}')">
                                {{ $day }}
                            </button>
                        @endfor
                    </div>
                </div>
            @endfor
        </div>

        <!-- Event Details Sidebar -->
        <div id="event-details" class="fixed right-0 top-0 w-1/3 h-full bg-white shadow-lg hidden p-4">
            <h3 class="text-lg font-bold mb-4">Events on <span id="event-date"></span></h3>
            <ul id="event-list"></ul>
            <button class="mt-4 bg-red-500 text-white py-2 px-4 rounded" onclick="closeEventDetails()">Close</button>
        </div>

        <!-- Add Event Modal -->
        <div id="event-modal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h3 class="text-lg font-bold mb-4">Add Event</h3>
                <form id="event-form">
                    @csrf
                    <label for="selected-date" class="block mb-2">Select Date</label>
                    <input type="date" id="selected-date-input" name="date" class="border p-2 w-full mb-4"
                        required />
                    <label for="event" class="block mb-2">Event</label>
                    <input type="text" id="event" name="event" class="border p-2 w-full" required />
                    <div class="flex justify-end space-x-4 mt-4">
                        <button type="button" id="close-event-modal" class="px-4 py-2 bg-gray-300 rounded-md"
                            onclick="closeEventModal()">Cancel</button>
                        <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded-md">Assign
                            Event</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
        // Open the add event modal
        function openEventModal() {
            document.getElementById('event-modal').classList.remove('hidden');
        }

        function closeEventModal() {
            document.getElementById("event-modal").classList.add("hidden");
            // document.getElementById("close-event-modal").addEventListener("click", function() {
            // });
        }

        // Show event details for the selected date
        function showEventDetails(month, day) {
            const date = `${month}/${day}`;
            document.getElementById('event-date').textContent = date;

            // Fetch events for the selected date
            axios.get(`/events/${month}/${day}`)
                .then(response => {
                    const eventList = document.getElementById('event-list');
                    eventList.innerHTML = '';

                    response.data.events.forEach(event => {
                        const li = document.createElement('li');
                        li.textContent = event;
                        eventList.appendChild(li);
                    });

                    document.getElementById('event-details').classList.remove('hidden');
                })
                .catch(error => {
                    alert('Failed to load events.');
                });
        }

        // Close the event details sidebar
        function closeEventDetails() {
            document.getElementById('event-details').classList.add('hidden');
        }

        // Submit the event form
        document.getElementById('event-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const date = document.getElementById('selected-date-input').value;
            const event = document.getElementById('event').value;

            axios.post('/assign-event', {
                date: date,
                event: event
            }).then(response => {
                alert('Event assigned successfully!');
                document.getElementById('event-modal').classList.add('hidden');
            }).catch(error => {
                alert('Failed to assign event');
            });
        });
    </script>
</x-app-layout>
