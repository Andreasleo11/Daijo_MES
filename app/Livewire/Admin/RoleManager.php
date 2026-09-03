<?php

namespace App\Livewire\Admin;

use App\Models\Role;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class RoleManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $name = '';

    public ?int $editingRoleId = null;

    public string $editingRoleName = '';

    protected $paginationTheme = 'tailwind';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount(): void
    {
        if (Gate::denies('manage-users-roles')) {
            abort(403, 'Unauthorized Action.');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function createRole(): void
    {
        if (Gate::denies('manage-users-roles')) {
            abort(403, 'Unauthorized Action.');
        }

        $this->name = strtoupper(trim($this->name));

        $this->validate([
            'name' => 'required|string|max:50|unique:roles,name',
        ]);

        Role::create([
            'name' => $this->name,
        ]);

        session()->flash('message', "Role '{$this->name}' created successfully.");
        $this->reset('name');
        $this->resetErrorBag();
        $this->dispatch('close-modal', 'create-role-modal');
    }

    public function startEditRole(int $roleId): void
    {
        if (Gate::denies('manage-users-roles')) {
            abort(403, 'Unauthorized Action.');
        }

        $role = Role::findOrFail($roleId);
        $this->editingRoleId = $role->id;
        $this->editingRoleName = $role->name;
        $this->resetErrorBag();

        $this->dispatch('open-modal', 'edit-role-modal');
    }

    public function updateRole(): void
    {
        if (Gate::denies('manage-users-roles')) {
            abort(403, 'Unauthorized Action.');
        }

        $this->editingRoleName = strtoupper(trim($this->editingRoleName));

        $this->validate([
            'editingRoleName' => 'required|string|max:50|unique:roles,name,' . $this->editingRoleId,
        ], [
            'editingRoleName.required' => 'The role name field is required.',
            'editingRoleName.unique' => 'The role name has already been taken.',
            'editingRoleName.max' => 'The role name must not be greater than 50 characters.',
        ]);

        $role = Role::findOrFail($this->editingRoleId);
        $oldName = $role->name;
        $role->update([
            'name' => $this->editingRoleName,
        ]);

        session()->flash('message', "Role '{$oldName}' updated to '{$role->name}' successfully.");
        $this->reset(['editingRoleId', 'editingRoleName']);
        $this->resetErrorBag();
        $this->dispatch('close-modal', 'edit-role-modal');
    }

    public function deleteRole(int $roleId): void
    {
        if (Gate::denies('manage-users-roles')) {
            abort(403, 'Unauthorized Action.');
        }

        $role = Role::withCount('users')->findOrFail($roleId);

        if ($role->isProtected()) {
            session()->flash('error', "Cannot delete protected system role '{$role->name}'.");

            return;
        }

        if ($role->users_count > 0) {
            session()->flash('error', "Cannot delete role '{$role->name}' because {$role->users_count} user(s) are currently assigned to it.");

            return;
        }

        $roleName = $role->name;
        $role->delete();

        session()->flash('message', "Role '{$roleName}' deleted successfully.");
    }

    public function render()
    {
        $query = Role::withCount([
            'users as active_users_count' => function ($q) {
                $q->withoutTrashed()->where('is_active', true);
            },
            'users as total_users_count' => function ($q) {
                $q->withoutTrashed();
            },
        ]);

        if (! empty(trim($this->search))) {
            $query->where('name', 'like', '%' . trim($this->search) . '%');
        }

        $roles = $query->orderBy('name')->paginate(10);

        return view('livewire.admin.role-manager', compact('roles'));
    }
}
