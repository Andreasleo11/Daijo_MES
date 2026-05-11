<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-3xl mx-auto space-y-6">
        
        <!-- Header -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Pallet Outbound Scan</h1>
                <p class="text-gray-500">Scan barcode palet untuk mengosongkan rak (Picking/Delivery)</p>
            </div>
            <a href="{{ route('wms.mapping') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-sm font-bold transition-all flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                CHECK RACKS
            </a>
        </div>

        @if (session()->has('success'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-xl animate-bounce">
                <p class="text-green-700 font-bold flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    {{ session('success') }}
                </p>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-xl">
                <p class="text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        <!-- Scanning Bar -->
        <div class="bg-gray-800 p-8 rounded-3xl shadow-xl shadow-gray-200 relative overflow-hidden">
            <h2 class="text-white text-lg font-bold mb-6 flex items-center uppercase tracking-widest text-center justify-center">
                <svg class="w-6 h-6 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 00-1 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                Scan Pallet ID to OUT
            </h2>
            
            <form wire:submit.prevent="processOutbound">
                <div class="relative group">
                    <input type="text" wire:model.defer="pallet_id_input" id="pallet_search" 
                        class="w-full px-6 py-6 bg-white/10 border-2 border-white/20 rounded-2xl text-white text-3xl font-black text-center focus:bg-white focus:text-gray-900 outline-none transition-all placeholder:text-gray-500"
                        placeholder="PLT-YYYYMMDD-XXXX"
                        autofocus>
                    <div class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none">
                        <kbd class="px-2 py-1 bg-white/10 text-white/40 rounded text-xs">ENTER</kbd>
                    </div>
                </div>
                <p class="text-center text-gray-400 text-xs mt-6 italic">Arahkan scanner ke Barkode yang tertempel pada Pallet Form.</p>
            </form>

            <div wire:loading wire:target="processOutbound" class="absolute inset-0 bg-gray-900/80 backdrop-blur-sm flex items-center justify-center z-10">
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 border-4 border-red-500 border-t-transparent rounded-full animate-spin mb-2"></div>
                    <span class="text-white font-bold uppercase tracking-widest text-xs">Processing OUT...</span>
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="grid grid-cols-2 gap-4">
            <a href="{{ route('wms.pallet-form.create') }}" class="p-4 bg-white border border-gray-200 rounded-2xl text-center hover:bg-gray-50 transition-all font-bold text-gray-600">
                ADD NEW PALLET
            </a>
            <a href="{{ route('wms.pallet-form.index') }}" class="p-4 bg-white border border-gray-200 rounded-2xl text-center hover:bg-gray-50 transition-all font-bold text-gray-600">
                PALLET HISTORY
            </a>
        </div>

    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            const input = document.getElementById('pallet_search');
            if (input) input.focus();

            // Audio Feedback Logic
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            
            function playTone(frequency, duration, type = 'sine', volume = 0.1) {
                if (audioCtx.state === 'suspended') audioCtx.resume();
                const oscillator = audioCtx.createOscillator();
                const gainNode = audioCtx.createGain();

                oscillator.type = type;
                oscillator.frequency.setValueAtTime(frequency, audioCtx.currentTime);
                
                gainNode.gain.setValueAtTime(volume, audioCtx.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + duration/1000);

                oscillator.connect(gainNode);
                gainNode.connect(audioCtx.destination);

                oscillator.start();
                oscillator.stop(audioCtx.currentTime + duration/1000);
            }

            Livewire.on('scan-success', () => {
                playTone(1200, 200, 'sawtooth', 0.4);
                setTimeout(() => playTone(1500, 100, 'sawtooth', 0.3), 50);
                setTimeout(() => { if (input) input.focus(); }, 100);
            });

            Livewire.on('scan-error', () => {
                playTone(100, 400, 'square', 0.6);
                setTimeout(() => playTone(100, 400, 'square', 0.6), 500);
                setTimeout(() => { if (input) { input.focus(); input.select(); } }, 100);
            });
        });
    </script>
</div>
