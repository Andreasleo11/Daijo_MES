<x-dashboard-layout>
    <h2 class="text-xl font-bold mb-4">Pilih SPK</h2>
    <form action="/monitoring" method="GET">
        <div class="relative">
            <input 
                type="text" 
                id="spkSearch"
                placeholder="Ketik atau pilih SPK..." 
                class="border p-2 rounded w-full"
                autocomplete="off"
            >
            <div id="spkDropdown" class="absolute z-10 w-full bg-white border rounded mt-1 max-h-60 overflow-y-auto hidden">
                @foreach($spkList as $item)
                    <div class="spk-item p-2 hover:bg-gray-100 cursor-pointer" data-value="{{ $item->spk_code }}">
                        {{ $item->spk_code }}
                    </div>
                @endforeach
            </div>
        </div>
    </form>

    <script>
        const searchInput = document.getElementById('spkSearch');
        const dropdown = document.getElementById('spkDropdown');
        const items = document.querySelectorAll('.spk-item');

        // Show dropdown when input is focused
        searchInput.addEventListener('focus', function() {
            dropdown.classList.remove('hidden');
        });

        // Filter items based on input
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            let hasResults = false;

            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    item.style.display = 'block';
                    hasResults = true;
                } else {
                    item.style.display = 'none';
                }
            });

            dropdown.classList.toggle('hidden', !hasResults);
        });

        // Handle item selection
        items.forEach(item => {
            item.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                searchInput.value = value;
                dropdown.classList.add('hidden');
                window.location = '/monitoring-spkdetail/' + value;
            });
        });

        // Hide dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    </script>
</x-dashboard-layout>