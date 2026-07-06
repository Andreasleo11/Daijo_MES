<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class ManualSync extends Component
{
    public bool   $isSyncing  = false;
    public ?string $lastResult = null;
    public ?string $lastRun    = null;
    public bool   $isSuccess  = false;

    public function spkSync(): void
    {
        $this->isSyncing  = true;
        $this->lastResult = null;

        try {
            Artisan::call('spk:sync');
            $output = Artisan::output();

            $this->isSuccess  = true;
            $this->lastResult = 'Sync delivery data berhasil dijalankan.' . ($output ? ' Output: ' . trim($output) : '');
            $this->lastRun    = Carbon::now('Asia/Jakarta')->format('d M Y, H:i:s') . ' WIB';
        } catch (\Throwable $e) {
            $this->isSuccess  = false;
            $this->lastResult = 'Gagal: ' . $e->getMessage();
            $this->lastRun    = Carbon::now('Asia/Jakarta')->format('d M Y, H:i:s') . ' WIB';
        } finally {
            $this->isSyncing = false;
        }
    }

    public function render()
    {
        return view('livewire.manual-sync');
    }
}