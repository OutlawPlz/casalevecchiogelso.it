@props([
    'value' => null,
    'disabled' => false,
])

<label {{ $attributes->class(['disabled' => $disabled]) }}>
    {{ $value ?? $slot }}
</label>
