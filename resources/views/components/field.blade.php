@props([
    'id',
    'label' => '',
    'disabled' => false,
    'help' => '',
    'error' => '',
    'jserror' => '',
])

<div {{ $attributes->class('field') }}>
    @if($label)
        <label
            for="{{ $id }}"
            @class(['disabled' => $disabled])
        >
            {{ $label }}
        </label>
    @endif

    {{ $slot }}

    @if($help)
        <div class="help-message">{{ $help }}</div>
    @endif

    <x-error-messages :$error :$jserror />
</div>
