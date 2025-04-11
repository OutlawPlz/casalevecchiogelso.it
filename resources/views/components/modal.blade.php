@props([
    'name',
    'maxWidth' => '2xl',
    'defaultClasses' => 'relative shadow-lg rounded-xl mt-12 mx-auto bg-white border border-transparent w-full',
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

    <x-ui-close
        x-on:click="$dispatch('close')"
        class="absolute top-0 right-0 m-2"
    />
</dialog>
