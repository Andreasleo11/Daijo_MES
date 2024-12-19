<?php

namespace App\Livewire;

use Livewire\Component;

class Alert extends Component
{
    public $message = '';

    public $visible = false;

    protected $listeners = ['showAlert'];

    // Handle session flash messages in the mount() method
    public function mount()
    {
        if (session()->has('success')) {
            $this->message = session('success');
            $this->visible = true;
        }
    }

    // Handle real-time events to show alerts
    public function showAlert($message)
    {
        $this->message = $message;
        $this->visible = true;
    }

    // Close the alert manually
    public function closeAlert()
    {
        $this->visible = false;
    }

    public function render()
    {
        return view('livewire.alert');
    }
}
