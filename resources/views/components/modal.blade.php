@props([
    'name',
    'maxWidth' => '2xl',
    'defaultClasses' => 'shadow-lg rounded-xl mt-12 bg-white dark:bg-zinc-800 border border-transparent dark:border-zinc-700 w-full',
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
    x-data=""
    data-modal="{{ $name }}"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') $el.showModal();"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}') $el.close();"
    x-on:close.stop="$el.close();"
    {{ $attributes->class([$defaultClasses, $maxWidth]) }}
>
    {{ $slot }}
</dialog>
