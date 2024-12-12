<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.guest')] // This sets the layout for the component
class LoginSwitcher extends Component
{
    public string $selectedLoginType = 'moulding'; // Default login type

    public function setLoginType(string $type): void
    {
        $this->selectedLoginType = $type;
    }

    public function render()
    {
        return view('livewire.login-switcher');
    }
}
