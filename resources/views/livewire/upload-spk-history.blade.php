<div class="p-6 bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800">Upload Data SPK History (SAP)</h2>
        <p class="text-gray-500 text-sm mt-1">
            Proses upload ini akan memproses file Excel di belakang layar (background) karena ukurannya yang besar. 
            Pastikan urutan kolom sesuai dengan format SAP: Kolom A (#), Kolom B (Document), Kolom C (Product No).
        </p>
    </div>

    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg text-blue-700">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-semibold text-sm">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg text-red-700">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-semibold text-sm">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <form wire:submit.prevent="uploadFile" class="space-y-4">
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Pilih File Excel (.xlsx, .csv)</label>
            <input type="file" wire:model="file" class="block w-full text-sm text-gray-500
                file:mr-4 file:py-2.5 file:px-4
                file:rounded-xl file:border-0
                file:text-sm file:font-semibold
                file:bg-blue-50 file:text-blue-700
                hover:file:bg-blue-100 transition-all cursor-pointer">
            @error('file') <span class="text-xs text-red-500 mt-1 font-bold">{{ $message }}</span> @enderror
        </div>

        <div wire:loading wire:target="file" class="text-sm font-bold text-blue-500 animate-pulse">
            Mengunggah file ke server sementara...
        </div>

        <div class="pt-4 border-t border-gray-100 flex items-center gap-3">
            <button type="submit" 
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold transition-all shadow-md flex items-center"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 cursor-not-allowed"
                    @if($isUploading) disabled @endif>
                <span wire:loading.remove wire:target="uploadFile">Mulai Proses Upload</span>
                <span wire:loading wire:target="uploadFile">Menyimpan...</span>
            </button>
            
            <p class="text-xs text-gray-400 font-medium max-w-sm">
                Sistem akan memecah data menjadi batch per 1000 baris agar tidak membebani memori.
            </p>
        </div>

        @if($isUploading)
            <div wire:poll.1s="checkProgress" class="mt-6 pt-6 border-t border-gray-100 animate-in fade-in slide-in-from-bottom-2 duration-300">
                <div class="flex justify-between items-end mb-2">
                    <div>
                        <h4 class="text-sm font-bold text-gray-800">Proses Update Database</h4>
                        <p class="text-xs text-blue-500 font-medium animate-pulse">{{ $uploadStatus }}</p>
                    </div>
                    <span class="text-2xl font-black text-blue-600 tracking-tighter">{{ $progress }}%</span>
                </div>
                
                <div class="w-full bg-gray-100 rounded-full h-3 mb-2 overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-full rounded-full transition-all duration-500 ease-out" 
                         style="width: {{ $progress }}%"></div>
                </div>
                
                <p class="text-[10px] text-gray-400 italic text-center font-medium">
                    Proses berjalan di background. Menutup halaman ini mungkin menghentikan tampilan persentase, namun proses tetap berjalan di server.
                </p>
            </div>
        @endif
    </form>
</div>
