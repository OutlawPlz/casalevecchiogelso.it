@props(['value' => null])

<label {{ $attributes->class('text-sm zinc-950 select-none block') }}>
    {{ $value ?? $slot }}
</label>
