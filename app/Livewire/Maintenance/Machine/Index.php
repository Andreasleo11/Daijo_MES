<?php

namespace App\Livewire\Maintenance\Machine;

use Livewire\Component;
use App\Models\MaintenanceMachine;
use App\Models\User;

class Index extends Component
{
    public $maintenanceMachines;
    public $users;

    // Form input
    public $tanggal;
    public $mesin;
    public $jenis_kerusakan;
    public $perbaikan;
    public $pic;
    public $remark;
    public $tipe;

    public $showModal = false;

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->maintenanceMachines = MaintenanceMachine::latest()->get();
        $this->users = User::where('role_id', 4)->get();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function resetForm()
    {
        $this->tanggal = null;
        $this->mesin = null;
        $this->jenis_kerusakan = null;
        $this->perbaikan = null;
        $this->pic = null;
        $this->remark = null;
        $this->tipe = null;
    }

    public function saveMaintenance()
    {
        $this->validate([
            'tanggal' => 'required|date',
            'mesin' => 'required',
            'jenis_kerusakan' => 'required|string',
            'perbaikan' => 'required|string',
            'pic' => 'required|string',
            'tipe' => 'required|in:Repair,Maintenance',
        ]);

        MaintenanceMachine::create([
            'tanggal' => $this->tanggal,
            'mesin' => $this->mesin,
            'jenis_kerusakan' => $this->jenis_kerusakan,
            'perbaikan' => $this->perbaikan,
            'pic' => $this->pic,
            'remark' => $this->remark,
            'tipe' => $this->tipe,
        ]);

        $this->closeModal();
        $this->loadData();
        session()->flash('message', 'Maintenance/Repair berhasil ditambahkan.');
    }

    // Property untuk modal detail
    public $selectedMaintenance;
    public $showDetailModal = false;

    public function openDetailModal($id)
    {
        $this->selectedMaintenance = MaintenanceMachine::find($id);
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedMaintenance = null;
    }

    public function finishMaintenance()
    {
        if ($this->selectedMaintenance) {
            $this->selectedMaintenance->finished_at = now();
            // Hitung lama pengerjaan dalam jam:menit
            $created = $this->selectedMaintenance->created_at;
            $finished = $this->selectedMaintenance->finished_at;
            $diff = $finished->diff($created);
            $this->selectedMaintenance->lama_pengerjaan = $diff->h . 'h ' . $diff->i . 'm';
            $this->selectedMaintenance->status = 1; // selesai
            $this->selectedMaintenance->save();
            $this->closeDetailModal();
            $this->loadData();
            session()->flash('message', 'Maintenance selesai dan lama pengerjaan dihitung.');
        }
    }

    public function render()
    {
        return view('livewire.maintenance.machine.index');
    }
}
