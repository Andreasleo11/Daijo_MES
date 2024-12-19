<div x-data="{ visible: @entangle('visible') }" x-init="$watch('visible', value => {
    if (value) {
        setTimeout(() => { visible = false }, 5000); // Set a 5-second timer when visible becomes true
    }
});">
    <template x-if="visible">
        <div id="successAlert"
            class="fixed top-5 right-5 w-full max-w-sm bg-green-100 shadow-lg rounded-lg overflow-hidden p-4 flex items-center space-x-3">
            <!-- Check Icon -->
            <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <!-- Message -->
            <div class="flex-grow">
                <span class="text-green-900 font-medium">{{ $message }}</span>
            </div>
            <!-- Close Button -->
            <button @click="visible = false" class="text-green-900 focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
    </template>
</div>
