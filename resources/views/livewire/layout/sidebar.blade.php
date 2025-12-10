<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;
use App\Models\User;
use App\Models\MachineJob;

new class extends Component {
    public function logout(Logout $logout): void
    {
        if (auth()->user()->role->name === 'WORKSHOP') {
            auth()->user()->update(['username' => null]);
        } elseif (auth()->user()->role->name === 'OPERATOR') {
            MachineJob::where('user_id', auth()->user()->id)->update(['employee_name' => null]);
        }

        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<aside
    class="bg-white w-64 h-screen border-r border-gray-200
           fixed inset-y-0 left-0 flex flex-col justify-between
           transform transition-transform duration-200 ease-in-out
           z-30
           lg:translate-x-0"
    :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }"
    x-cloak
>
    {{-- TOP --}}
    <div class="px-6 py-4 flex-grow overflow-y-auto">
        <div class="flex items-center justify-between">
            <a href="{{ route('dashboard') }}" wire:navigate>
                <x-application-logo class="block fill-current text-gray-800 w-16 h-16" />
            </a>
            <span class="ms-5 font-semibold text-xl">
                Daijo MES System
            </span>

            {{-- Close button mobile --}}
            <button @click="sidebarOpen = false" class="lg:hidden text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor"
                     class="h-6 w-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="2"
                          d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Navigation Links -->
        <div class="space-y-2 mt-4">
            @if (!auth()->user()->can('view-store-links'))
                <livewire:sidebar-link
                    href="{{ route('dashboard') }}"
                    label="Dashboard"
                    :active="request()->routeIs('dashboard')"
                    wire:navigate
                />

                <livewire:sidebar-link
                    href="{{ route('maintenance.machine.index') }}"
                    label="Maintenance Machine"
                    :active="request()->routeIs('maintenance.machine.index')"
                    wire:navigate
                />

                <livewire:sidebar-link
                    href="{{ route('maintenance.mould.index') }}"
                    label="Maintenance Mould"
                    :active="request()->routeIs('maintenance.mould.index')"
                    wire:navigate
                />
            @endif


            @if (auth()->user()->can('view-warehouse-links'))
                <livewire:parent-dropdown label="Moulding" :childRoutes="[
                    ['name' => 'production.bom.index', 'label' => 'Production BOM'],
                    ['name' => 'waiting_purchase_orders.index', 'label' => 'Waiting Purchase Orders'],
                    ['name' => 'notification_recipients.index', 'label' => 'Notification Recipients'],
                    ['name' => 'workshop.summary.dashboard', 'label' => 'Dashboard Proses'],
                    ['name' => 'dashboard.moulding.tv', 'label' => 'Dashboard Project'],
                ]" />

                <livewire:sidebar-link
                    href="{{ route('production.bom.index') }}"
                    label="Production BOM"
                    :active="request()->routeIs('production.bom.index')"
                    wire:navigate
                />

                <livewire:sidebar-link
                    href="{{ route('waiting_purchase_orders.index') }}"
                    label="Waiting Purchase Orders"
                    :active="request()->routeIs('waiting_purchase_orders.index')"
                    wire:navigate
                />

                <livewire:sidebar-link
                    href="{{ route('notification_recipients.index') }}"
                    label="Notification Recipients"
                    :active="request()->routeIs('notification_recipients.index')"
                    wire:navigate
                />
            @endif

            <!-- Admin Links -->
            @if (auth()->user()->can('view-admin-links'))
                <livewire:sidebar-link
                    href="{{ route('barcode.index') }}"
                    label="Generate Master Barcode"
                    :active="request()->routeIs('barcode.index')"
                    wire:navigate
                />

                <livewire:sidebar-link
                    href="{{ route('so.index') }}"
                    label="DO Index"
                    :active="request()->routeIs('so.index')"
                    wire:navigate
                />

                <livewire:parent-dropdown label="Dashboard All" :childRoutes="[
                    ['name' => 'delschedfinal.dashboard', 'label' => 'Dashboard Delivery Schedule'],
                    ['name' => 'workshop.summary.dashboard', 'label' => 'Dashboard Proses Moulding'],
                    ['name' => 'dashboard.moulding.tv', 'label' => 'Dashboard Project Moulding'],
                ]" />

                <livewire:parent-dropdown label="Inventory" :childRoutes="[
                    ['name' => 'inventory.mtr', 'label' => 'Master MTR'],
                    ['name' => 'inventory.fg', 'label' => 'Master FG'],
                    ['name' => 'invlinelist', 'label' => 'Machine List'],
                ]" />

                <livewire:parent-dropdown label="Business" :childRoutes="[
                    ['name' => 'indexds', 'label' => 'Delivery Schedule'],
                    ['name' => 'production.forecast.index', 'label' => 'Forecast Production'],
                    ['name' => 'management.delivery.index', 'label' => 'Delivery Data Delete'],
                ]" />

                <livewire:parent-dropdown label="Production" :childRoutes="[
                    ['name' => 'capacityforecastindex', 'label' => 'Capacity By Forecast'],
                ]" />

                <livewire:parent-dropdown label="Setting" :childRoutes="[
                    ['name' => 'setting.holiday-schedule.index', 'label' => 'Holiday Schedule'],
                ]" />
            @endif

            <!-- PE Links -->
            @if (auth()->user()->can('view-pe-links'))
                <livewire:sidebar-link
                    href="{{ route('master-item.index') }}"
                    label="Master Item"
                    :active="request()->routeIs('master-item.index')"
                    wire:navigate
                />
            @endif

            <!-- PPIC Links -->
            @if (auth()->user()->can('view-ppic-links'))
                <livewire:sidebar-link
                    href="{{ route('daily-item-code.index') }}"
                    label="Daily Production Plan"
                    :active="request()->routeIs('daily-item-code.index')"
                    wire:navigate
                />
            @endif

            <!-- Store Links -->
            @if (auth()->user()->can('view-store-links'))
                <livewire:sidebar-link
                    href="{{ route('inandout.index') }}"
                    label="Scan Barcode"
                    :active="request()->routeIs('inandout.index')"
                    wire:navigate
                />

                <livewire:sidebar-link
                    href="{{ route('summaryDashboard') }}"
                    label="Summary Store Packaging Data"
                    :active="request()->routeIs('summaryDashboard')"
                    wire:navigate
                />

                <livewire:sidebar-link
                    href="{{ route('list.barcode') }}"
                    label="Report History"
                    :active="request()->routeIs('list.barcode')"
                    wire:navigate
                />

                <livewire:sidebar-link
                    href="{{ route('stockallbarcode') }}"
                    label="Stock Item"
                    :active="request()->routeIs('stockallbarcode')"
                    wire:navigate
                />

                <livewire:sidebar-link
                    href="{{ route('customer.add') }}"
                    label="Add Customer"
                    :active="request()->routeIs('customer.add')"
                    wire:navigate
                />

                <livewire:sidebar-link
                    href="{{ route('updated.barcode.item.position') }}"
                    label="List All Item Barcode"
                    :active="request()->routeIs('updated.barcode.item.position')"
                    wire:navigate
                />
            @endif

            <hr>

            <!-- Maintenance Links -->
            @if (auth()->user()->can('view-maintenance-links'))
                <livewire:sidebar-link
                    href="{{ route('maintenance.index') }}"
                    label="Maintenance Index"
                    :active="request()->routeIs('maintenance.index')"
                    wire:navigate
                />
            @endif

            @if (auth()->user()->can('view-second-process-links'))
                <livewire:sidebar-link
                    href="{{ route('second.daily.process.create') }}"
                    label="Plan Second Process"
                    :active="request()->routeIs('second.daily.process.create')"
                    wire:navigate
                />
            @endif
        </div>
    </div>

    {{-- BOTTOM USER DROPDOWN --}}
    <div class="px-6 py-4 border-t border-gray-200">
        <div x-data="{ open: false }" class="relative">
            <button
                @click="open = !open"
                class="w-full inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                <div
                    x-data="{ name: '{{ auth()->user()?->name ?? 'Guest' }}' }"
                    x-text="name"
                    x-on:profile-updated.window="name = $event.detail.name">
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                     viewBox="0 0 24 24" stroke-width="1.5"
                     stroke="currentColor" class="ms-1 size-2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </button>

            <div
                x-show="open"
                @click.away="open = false"
                x-transition
                class="absolute right-0 bottom-full mb-2 w-48 bg-white shadow-lg z-10 rounded-md ring-1 ring-black ring-opacity-5">
                <div class="py-1">
                    @if (auth()->user()->role->name === 'Admin')
                        <a href="{{ route('profile') }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                            {{ __('Profile') }}
                        </a>
                    @endif

                    <button
                        wire:click="logout"
                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                        {{ __('Log Out') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</aside>
