@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
@endphp

<div class="flex justify-between items-center mt-4 px-4">
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Pagination Navigation" class="w-full">
            <div class="flex flex-col sm:flex-row justify-between items-center w-full">
                <!-- Showing Results Text -->
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2 sm:mb-0">
                    Showing <span class="font-semibold">{{ $paginator->firstItem() }}</span>
                    to <span class="font-semibold">{{ $paginator->lastItem() }}</span>
                    of <span class="font-semibold">{{ $paginator->total() }}</span> results
                </p>

                <!-- Pagination Buttons -->
                <div class="flex space-x-2">
                    <!-- Previous Button -->
                    @if ($paginator->onFirstPage())
                        <span class="px-3 py-2 text-sm text-gray-400 bg-gray-200 rounded-lg cursor-not-allowed">
                            ← Previous
                        </span>
                    @else
                        <button wire:click="previousPage('{{ $paginator->getPageName() }}')"
                            x-on:click="{{ $scrollIntoViewJsSnippet }}"
                            class="px-3 py-2 text-sm bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                            ← Previous
                        </button>
                    @endif

                    <!-- Page Numbers -->
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span class="px-3 py-2 text-sm text-gray-400">{{ $element }}</span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span class="px-4 py-2 text-sm font-semibold bg-blue-500 text-white rounded-lg">
                                        {{ $page }}
                                    </span>
                                @else
                                    <button wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                        class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-lg hover:bg-blue-500 hover:text-white transition">
                                        {{ $page }}
                                    </button>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    <!-- Next Button -->
                    @if ($paginator->hasMorePages())
                        <button wire:click="nextPage('{{ $paginator->getPageName() }}')"
                            x-on:click="{{ $scrollIntoViewJsSnippet }}"
                            class="px-3 py-2 text-sm bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                            Next →
                        </button>
                    @else
                        <span class="px-3 py-2 text-sm text-gray-400 bg-gray-200 rounded-lg cursor-not-allowed">
                            Next →
                        </span>
                    @endif
                </div>
            </div>
        </nav>
    @endif
</div>
