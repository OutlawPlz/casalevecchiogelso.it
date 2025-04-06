@aware([
    'id',
    'name' => '',
    'required' => false,
    'disabled' => false,
])

@php
    /** @var \Illuminate\Support\ViewErrorBag $errors */

    $classObject = [
        'block w-full appearance-none rounded-lg px-[calc(--spacing(3.5)-1px)] py-[calc(--spacing(2.5)-1px)] sm:px-[calc(--spacing(3)-1px)] sm:py-[calc(--spacing(1.5)-1px)] text-base/6 text-zinc-950 placeholder:text-zinc-500 sm:text-sm/6 border border-zinc-950/10 data-hover:border-zinc-950/20 bg-transparent focus:outline-hidden data-invalid:border-red-500 data-invalid:data-hover:border-red-500 data-disabled:border-zinc-950/20',
        '!border-red-500' => $errors->has($name),
    ];
@endphp

<input
    {{ $attributes->class($classObject) }}
    id="{{ $id }}"
    name="{{ $name }}"
    @required($required)
    @disabled($disabled)
>
