<x-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Update Master Zone</h2>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        {{-- Form for updating master zone --}}
        <form method="POST" action="{{ route('zone.update') }}" class="bg-white shadow p-6 rounded-md space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="zone_id" class="block font-medium text-sm text-gray-700">Zone</label>
                    <select name="zone_id" id="zone_id" class="form-select w-full">
                        @foreach($zones as $zone)
                            <option value="{{ $zone->id }}">{{ $zone->zone_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="pengawas" class="block font-medium text-sm text-gray-700">Pengawas (Adjuster)</label>
                    <select name="pengawas" id="pengawas" class="form-select w-full">
                        @foreach($adjusters as $adjuster)
                            <option value="{{ $adjuster->name }}">{{ $adjuster->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="shift" class="block font-medium text-sm text-gray-700">Shift</label>
                    <select name="shift" id="shift" class="form-select w-full">
                        <option value="1">Shift 1</option>
                        <option value="2">Shift 2</option>
                        <option value="3">Shift 3</option>
                    </select>
                </div>

                <div>
                    <label for="start_date" class="block font-medium text-sm text-gray-700">Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-input w-full">
                </div>

                <div>
                    <label for="end_date" class="block font-medium text-sm text-gray-700">End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-input w-full">
                </div>
            </div>

            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 mt-4">
                Update Zone
            </button>
        </form>

        {{-- Table showing existing ZonePengawas data --}}
        <div class="bg-white shadow p-6 rounded-md">
            <h3 class="text-lg font-semibold mb-4">Existing Zone Pengawas Assignments</h3>
            <div class="overflow-x-auto">
                <table class="w-full table-auto border border-gray-200 text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-4 py-2">Zone Name</th>
                            <th class="border px-4 py-2">Pengawas</th>
                            <th class="border px-4 py-2">Shift</th>
                            <th class="border px-4 py-2">Start Date</th>
                            <th class="border px-4 py-2">End Date</th>
                            <th class="border px-4 py-2">Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($zoneData as $data)
                            <tr>
                                <td class="border px-4 py-2">
                                    {{ $zones->firstWhere('id', $data->zone_id)?->zone_name ?? 'Unknown' }}
                                </td>
                                <td class="border px-4 py-2">{{ $data->pengawas }}</td>
                                <td class="border px-4 py-2">Shift {{ $data->shift }}</td>
                                <td class="border px-4 py-2">{{ $data->start_date ?? '-' }}</td>
                                <td class="border px-4 py-2">{{ $data->end_date ?? '-' }}</td>
                                <td class="border px-4 py-2">{{ $data->updated_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

</x-dashboard-layout>
