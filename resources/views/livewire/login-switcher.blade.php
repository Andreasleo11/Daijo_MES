<div>
    <!-- Login Type Switch -->
    <div class="flex justify-center mb-6">
        <button class="px-4 py-2 mx-2 font-bold text-white rounded-lg">
            Moulding Login
        </button>
        <button class="px-4 py-2 mx-2 font-bold text-white rounded-lg">
            Production Login
        </button>
        <x-primary-button wire:click="setLoginType('moulding')" class="ms-3" @class([
            'bg-blue-500' => $selectedLoginType === 'moulding',
            'bg-gray-300' => $selectedLoginType !== 'moulding',
        ])>
            {{ __('Moulding Login') }}
        </x-primary-button>
        <x-primary-button wire:click="setLoginType('production')" class="ms-3" @class([
            'bg-blue-500' => $selectedLoginType === 'production',
            'bg-gray-300' => $selectedLoginType !== 'production',
        ])>
            {{ __('Production Login') }}
        </x-primary-button>
    </div>

    <!-- Render the selected login component -->
    @if ($selectedLoginType === 'moulding')
        <livewire:moulding-login />
    @elseif ($selectedLoginType === 'production')
        <livewire:production-login />
    @endif
</div>
