<?php

use Livewire\Volt\Component;

new class extends Component {
    public string $label;
    public array $childRoutes;
    public bool $isParentActive;

    public function mount(string $label, array $childRoutes)
    {
        $this->label = $label;
        $this->childRoutes = $childRoutes;
        $this->isParentActive = $this->isDropdownActive($childRoutes);
    }

    public function isDropdownActive(array $routes)
    {
        foreach ($routes as $route) {
            if (request()->routeIs($route)) {
                return true;
            }
        }

        return false;
    }
}; ?>

<!-- Parent Dropdown -->
<div x-data="{ dropdownOpen: {{ $isParentActive ? 'true' : 'false' }} }" {{ !$isParentActive ? 'x-cloak' : '' }} class="relative">
    <!-- Parent Route as a button to toggle the dropdown -->
    <button @click="dropdownOpen = !dropdownOpen"
        class="w-full text-left inline-flex items-center px-3 py-2 bg-white border {{ $isParentActive ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
        {{ $label }}
        <svg :class="{ 'rotate-180': dropdownOpen }" class="ml-2 h-4 w-4 transition-transform duration-200"
            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <!-- Child Links -->
    <!-- Conditionally apply x-cloak if no child route is active -->
    <div x-show="dropdownOpen" :class="{ 'x-cloak': !{{ $isParentActive ? 'true' : 'false' }} }" class="mt-2 space-y-2">
        @foreach ($childRoutes as $childRoute)
            <livewire:sidebar-link href="{{ route($childRoute['name']) }}" label="{{ $childRoute['label'] }}"
                :active="request()->routeIs($childRoute['name'])" wire:navigate class="ml-6" />
        @endforeach
    </div>
</div>
