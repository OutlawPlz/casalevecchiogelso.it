@props([
    'id',
    'name',
    'label' => '',
    'required' => false,
    'disabled' => false,
    'help' => '',
    'error' => '',
    'jserror' => '',
])

<div {{ $attributes->class('field') }}>
    @if($label)
        <x-label
            for="{{ $id }}"
            :value="$label"
            :$disabled
        />
    @endif

    {{ $slot }}

    @if($help)
        <div class="help-message">{{ $help }}</div>
    @endif

    <x-error-messages :$error :$jserror />
</div>
