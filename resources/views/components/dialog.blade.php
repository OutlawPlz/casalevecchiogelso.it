@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl'
])

@php
$maxWidth = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
][$maxWidth];
@endphp

<dialog
    data-modal="{{ $name }}"
    class="relative p-6 shadow-lg rounded-xl bg-white dark:bg-zinc-800 border border-transparent dark:border-zinc-700 w-full {{ $maxWidth }}"
    x-on:open-dialog.window="if ($event.detail === '{{ $name }}') $el.showModal();"
    x-on:close-dialog.window="if ($event.detail === '{{ $name }}') $el.close();"
>
    {{ $slot }}

    <div>
        <div class="font-medium text-zinc-800 dark:text-white text-base [&:has(+[data-flux-subheading])]:mb-2 [[data-flux-subheading]+&]:mt-2">
            Update profile
        </div>
        <div class="text-sm text-zinc-500 dark:text-white/70">
            Make changes to your personal details.
        </div>
    </div>

    <button
        x-on:click.prevent="$dispatch('close-dialog', '{{ $name }}')"
        class="absolute top-4 right-4 p-1 text-gray-400 rounded-lg cursor-pointer hover:text-gray-700 hover:bg-gray-100"
    >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
            <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"></path>
        </svg>
    </button>
</dialog>
