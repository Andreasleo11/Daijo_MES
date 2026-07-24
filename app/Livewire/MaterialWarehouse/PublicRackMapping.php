<?php

namespace App\Livewire\MaterialWarehouse;

use Livewire\Attributes\Layout;

class PublicRackMapping extends RackMapping
{
    public function mount(): void
    {
        $this->isViewOnly = true;
    }

    #[Layout('layouts.dashboard')]
    public function render()
    {
        return parent::render();
    }
}
