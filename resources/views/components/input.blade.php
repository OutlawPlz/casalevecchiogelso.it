@aware([
    'id',
    'name' => '',
    'type' => 'text',
])

@php
    /** @var \Illuminate\Support\ViewErrorBag $errors */

    $classObject = match ($type) {
        'radio' => 'block size-4 checked:bg-current rounded-full appearance-none border border-zinc-950/10 hover:border-zinc-950/20 bg-transparent',
        'checkbox' => 'block size-4 checked:bg-current rounded-sm appearance-none border border-zinc-950/10 hover:border-zinc-950/20 bg-transparent',
        default => 'block w-full appearance-none rounded-lg px-[calc(--spacing(3)-1px)] py-[calc(--spacing(1.5)-1px)] text-zinc-950 placeholder:text-zinc-500 text-base border border-zinc-950/10 hover:border-zinc-950/20 bg-transparent data-invalid:border-red-500 data-invalid:hover:border-red-500 disabled:border-zinc-950/20',
    }
@endphp

<input
    {{ $attributes->class($classObject) }}
    id="{{ $id }}"
    name="{{ $name }}"
    type="{{ $type }}"
>
