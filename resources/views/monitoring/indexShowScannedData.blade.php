<x-dashboard-layout>

    <h2 class="text-xl font-bold mb-4">Pilih SPK</h2>

    <form action="/monitoring" method="GET">
        <select name="spk" onchange="window.location='/monitoring-spkdetail/' + this.value"
            class="border p-2 rounded">
            <option value="">-- Pilih SPK --</option>

            @foreach($spkList as $item)
                <option value="{{ $item->spk_code }}">{{ $item->spk_code }}</option>
            @endforeach
        </select>
    </form>
</x-dashboard-layout>

