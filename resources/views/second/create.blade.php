<x-app-layout>
    <div class="max-w-3xl mx-auto py-8">
        <h1 class="text-2xl font-bold mb-6 text-center">
            Create Planning Entry
        </h1>

        <form action="{{ route('second.daily.process.store') }}" method="POST" x-data="planningForm()" class="space-y-6">
            @csrf

            <!-- Single entry fields -->
            <div class="space-y-4 bg-white p-6 shadow-md rounded-md">
                <div x-data x-init="window.flatpickr($refs.planDate, { dateFormat: 'Y-m-d' })">
                    <label for="plan_date" class="block text-sm font-medium text-gray-700">
                        Plan Date
                    </label>
                    <input type="text" name="plan_date" x-ref="planDate"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        required />
                </div>

                <div>
                    <label for="pic" class="block text-sm font-medium text-gray-700">
                        PIC
                    </label>
                    <input type="text" name="pic"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        required />
                </div>

                <div>
                    <label for="shift" class="block text-sm font-medium text-gray-700">
                        Shift
                    </label>
                    <input type="number" name="shift"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        required />
                </div>
            </div>

            <!-- Repeatable section -->
            <template x-for="(section, index) in repeatableSections" :key="index">
                <div class="space-y-4 bg-gray-50 p-6 shadow-md rounded-md">
                    <div>
                        <label :for="'line_' + index" class="block text-sm font-medium text-gray-700">
                            Line
                        </label>
                        <input type="text" :name="'line[]'"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required />
                    </div>

                    <div>
                        <label :for="'item_code_' + index" class="block text-sm font-medium text-gray-700">
                            Item Code
                        </label>
                        <select :name="'item_code[]'"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            x-data="{}" x-init="$nextTick(
                                () =>
                                new TomSelect($el, {
                                    valueField: 'item_code',
                                    labelField: 'item_code',
                                    searchField: 'item_code',
                                    load: function(query, callback) {
                                        if (!query.length) return callback()
                                        fetch(
                                                '{{ route('api.items') }}?query=' +
                                                encodeURIComponent(query),
                                            )
                                            .then((response) => response.json())
                                            .then((data) => {
                                                callback(data)
                                            })
                                            .catch(() => {
                                                callback()
                                            })
                                    },
                                }),
                            )"
                            @change="fetchItemDescription($event.target.value, index)" required>
                            <option value="" selected disabled>Search Item Code</option>
                        </select>
                    </div>

                    <div>
                        <label :for="'item_description_' + index" class="block text-sm font-medium text-gray-700">
                            Item Description
                        </label>
                        <input type="text" :name="'item_description[]'" x-model="section.itemDescription"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            readonly required />
                    </div>
                    <div>
                        <label :for="'quantity_hour_' + index" class="block text-sm font-medium text-gray-700">
                            Quantity Hour
                        </label>
                        <input type="number" :name="'quantity_hour[]'"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                    </div>

                    <div>
                        <label :for="'process_time_' + index" class="block text-sm font-medium text-gray-700">
                            Process Time
                        </label>
                        <input type="number" step="0.01" :name="'process_time[]'"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                    </div>

                    <div>
                        <label :for="'quantity_plan_' + index" class="block text-sm font-medium text-gray-700">
                            Quantity Plan
                        </label>
                        <input type="number" :name="'quantity_plan[]'"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                    </div>

                    <div>
                        <label :for="'customer_' + index" class="block text-sm font-medium text-gray-700">
                            Customer
                        </label>
                        <input type="text" :name="'customer[]'"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required />
                    </div>

                    <button type="button" class="mt-4 px-4 py-2 bg-red-500 text-white rounded-md"
                        @click="repeatableSections.splice(index, 1)">
                        Remove
                    </button>
                </div>
            </template>

            <div class="flex justify-between items-center mt-6">
                <button type="button" @click="repeatableSections.push({})"
                    class="px-4 py-2 bg-blue-500 text-white rounded-md flex items-center justify-center">
                    Add Another Item
                </button>
                <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md">
                    Submit
                </button>
            </div>
        </form>
    </div>

    <script>
        function planningForm() {
            return {
                repeatableSections: [{}],

                fetchItemDescription(itemCode, index) {
                    fetch(
                            `{{ route('api.item.description') }}?item_code=${itemCode}`,
                        )
                        .then((response) => response.json())
                        .then((data) => {
                            this.repeatableSections[index].itemDescription =
                                data.item_description;
                        });
                },
            };
        }
    </script>
</x-app-layout>
