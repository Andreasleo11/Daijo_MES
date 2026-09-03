<?php

namespace Tests\Feature;

use App\Livewire\Admin\RoleManager;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class RoleManagerTest extends TestCase
{
    use RefreshDatabase;

    protected Role $superAdminRole;

    protected Role $adminRole;

    protected Role $operatorRole;

    protected User $superAdmin;

    protected User $operatorUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdminRole = Role::firstOrCreate(['name' => 'SUPER-ADMIN']);
        $this->adminRole = Role::firstOrCreate(['name' => 'ADMIN']);
        $this->operatorRole = Role::firstOrCreate(['name' => 'OPERATOR']);

        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'username' => 'superadmin',
            'password' => Hash::make('password123'),
            'role_id' => $this->superAdminRole->id,
            'is_active' => true,
        ]);

        $this->operatorUser = User::create([
            'name' => 'Operator User',
            'email' => 'operator@test.com',
            'username' => 'operator',
            'password' => Hash::make('password123'),
            'role_id' => $this->operatorRole->id,
            'is_active' => true,
        ]);
    }

    public function test_non_admin_cannot_access_role_manager(): void
    {
        $this->actingAs($this->operatorUser);

        Livewire::test(RoleManager::class)
            ->assertStatus(403);
    }

    public function test_superadmin_can_access_role_manager(): void
    {
        $this->actingAs($this->superAdmin);

        Livewire::test(RoleManager::class)
            ->assertStatus(200)
            ->assertSee('System Roles')
            ->assertSee('SUPER-ADMIN')
            ->assertSee('ADMIN')
            ->assertSee('OPERATOR');
    }

    public function test_can_create_new_role(): void
    {
        $this->actingAs($this->superAdmin);

        Livewire::test(RoleManager::class)
            ->set('name', 'AUDITOR')
            ->call('createRole')
            ->assertHasNoErrors()
            ->assertSet('name', '')
            ->assertDispatched('close-modal', 'create-role-modal');

        $this->assertDatabaseHas('roles', [
            'name' => 'AUDITOR',
        ]);
    }

    public function test_cannot_create_duplicate_role_name(): void
    {
        $this->actingAs($this->superAdmin);

        Livewire::test(RoleManager::class)
            ->set('name', 'OPERATOR')
            ->call('createRole')
            ->assertHasErrors(['name' => 'unique']);
    }

    public function test_cannot_create_role_with_empty_name(): void
    {
        $this->actingAs($this->superAdmin);

        Livewire::test(RoleManager::class)
            ->set('name', '')
            ->call('createRole')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_can_open_edit_modal_and_update_role(): void
    {
        $this->actingAs($this->superAdmin);

        $customRole = Role::create(['name' => 'TESTER']);

        Livewire::test(RoleManager::class)
            ->call('startEditRole', $customRole->id)
            ->assertSet('editingRoleId', $customRole->id)
            ->assertSet('editingRoleName', 'TESTER')
            ->assertDispatched('open-modal', 'edit-role-modal')
            ->set('editingRoleName', 'QA-TESTER')
            ->call('updateRole')
            ->assertHasNoErrors()
            ->assertSet('editingRoleId', null)
            ->assertSet('editingRoleName', '')
            ->assertDispatched('close-modal', 'edit-role-modal');

        $this->assertDatabaseHas('roles', [
            'id' => $customRole->id,
            'name' => 'QA-TESTER',
        ]);
    }

    public function test_cannot_update_role_to_existing_duplicate_name(): void
    {
        $this->actingAs($this->superAdmin);

        $customRole = Role::create(['name' => 'SECURITY']);

        Livewire::test(RoleManager::class)
            ->call('startEditRole', $customRole->id)
            ->set('editingRoleName', 'OPERATOR')
            ->call('updateRole')
            ->assertHasErrors(['editingRoleName' => 'unique']);
    }

    public function test_can_delete_unused_custom_role(): void
    {
        $this->actingAs($this->superAdmin);

        $customRole = Role::create(['name' => 'TEMPORARY']);

        Livewire::test(RoleManager::class)
            ->call('deleteRole', $customRole->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('roles', [
            'id' => $customRole->id,
        ]);
    }

    public function test_cannot_delete_protected_roles(): void
    {
        $this->actingAs($this->superAdmin);

        Livewire::test(RoleManager::class)
            ->call('deleteRole', $this->superAdminRole->id);

        $this->assertDatabaseHas('roles', [
            'id' => $this->superAdminRole->id,
        ]);

        Livewire::test(RoleManager::class)
            ->call('deleteRole', $this->adminRole->id);

        $this->assertDatabaseHas('roles', [
            'id' => $this->adminRole->id,
        ]);
    }

    public function test_cannot_delete_role_with_assigned_users(): void
    {
        $this->actingAs($this->superAdmin);

        Livewire::test(RoleManager::class)
            ->call('deleteRole', $this->operatorRole->id);

        $this->assertDatabaseHas('roles', [
            'id' => $this->operatorRole->id,
        ]);
    }

    public function test_search_filters_roles_by_name(): void
    {
        $this->actingAs($this->superAdmin);

        Role::create(['name' => 'INSPECTOR']);

        Livewire::test(RoleManager::class)
            ->set('search', 'INSPEC')
            ->assertSee('INSPECTOR')
            ->assertDontSee('OPERATOR');
    }
}
