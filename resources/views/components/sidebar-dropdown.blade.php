@props(['active' => false])

<div x-data="{ open: @json($active) }">
    <button @click="open = !open" class="w-full flex items-center justify-between p-2 text-gray-300 hover:bg-gray-700 rounded-md">
        <div class="flex items-center">
            {{ $trigger }}
        </div>
        <x-heroicon-o-chevron-down class="w-4 h-4 transition-transform duration-200" ::class="{'rotate-180': open}" />
    </button>
    <div x-show="open" x-transition class="mt-2 ml-4 space-y-2">
        {{ $content }}
    </div>
</div>
