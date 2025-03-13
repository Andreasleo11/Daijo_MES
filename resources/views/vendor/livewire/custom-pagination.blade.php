@if ($paginator->hasPages())
    <nav class="flex items-center justify-between mt-4">
        {{-- Previous Page --}}
        {{-- @if ($paginator->onFirstPage())
            <span class="px-4 py-2 text-gray-500">Previous</span>
        @else
            <button wire:click="previousPage" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded">
                Previous
            </button>
        @endif --}}

        {{-- Page Numbers with Parent Codes --}}
        @foreach ($elements as $element)
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @php
                        $parentCode = $parentCodes[$page - 1] ?? "Page $page";
                    @endphp
                    @if ($page == $paginator->currentPage())
                        <span class="px-4 py-2 bg-blue-500 text-white rounded">{{ $parentCode }}</span>
                    @else
                        <button wire:click="gotoPage({{ $page }})"
                            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded">
                            {{ $parentCode }}
                        </button>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page --}}
        {{-- @if ($paginator->hasMorePages())
            <button wire:click="nextPage" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded">
                Next
            </button>
        @else
            <span class="px-4 py-2 text-gray-500">Next</span>
        @endif --}}
    </nav>
@endif
