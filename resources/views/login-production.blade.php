<?php

use App\Livewire\Forms\LoginForm;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\User;

new #[Layout('layouts.guest')] class extends Component {
    public LoginForm $form;
    public $users;

    /**
     * Load the users and initialize.
     */
    public function mount()
    {
        // Load users for the select dropdown
        $this->users = User::whereHas('specification', function ($query) {
            $query->where('name', 'Operator');
        })->get();
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        // Ensure that email and password fields are set correctly
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: RouteServiceProvider::HOME, navigate: true);
    }
};
?>

<div x-data="{ isOperator: false, selectedUserEmail: '', selectedUserPassword: '' }">
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit.prevent="login">
        <!-- Operator Switch -->
        <div class="mb-4">
            <label for="isOperator" class="inline-flex items-center">
                <x-input-label for="isOperator" :value="__('Are you an Operator?')"></x-input-label>
                <input id="isOperator" type="checkbox" x-model="isOperator"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 ml-2">
                <span class="ms-2 text-sm text-gray-600">{{ __('Yes') }}</span>
            </label>
        </div>

        <hr>

        <!-- User Select (Shown when operator mode is active) -->
        <div x-show="isOperator" class="mt-4">
            <x-input-label for="user" :value="__('Select Machine')" />
            <select id="user"
                x-on:change="
                selectedUserEmail = $event.target.options[$event.target.selectedIndex].dataset.email;
                selectedUserPassword = $event.target.options[$event.target.selectedIndex].dataset.name;
                $wire.set('form.email', selectedUserEmail);
                $wire.set('form.password', selectedUserPassword);"
                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                name="user">
                <option value="">-- Select a machine --</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" data-email="{{ $user->email }}"
                        data-name="{{ $user->name }}">
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('selectedUser')" class="mt-2" />
        </div>

        <!-- Email Address (Hidden when operator mode is active) -->
        <div class="mt-4" x-show="!isOperator">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model.defer="form.email" id="email" class="block mt-1 w-full" type="email"
                name="email" x-bind:required="!isOperator" autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password (Hidden when operator mode is active) -->
        <div class="mt-4" x-show="!isOperator">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input wire:model.defer="form.password" id="password" class="block mt-1 w-full" type="password"
                name="password" x-bind:required="!isOperator" autocomplete="current-password" />
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Pre-fill email and password if operator is selected -->
        <template x-if="isOperator">
            <div>
                <!-- Hidden fields to hold selected email and password -->
                <x-text-input wire:model.defer="form.email" type="hidden" name="email" />
                <x-text-input wire:model.defer="form.password" type="hidden" name="password" />
            </div>
        </template>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</div>
