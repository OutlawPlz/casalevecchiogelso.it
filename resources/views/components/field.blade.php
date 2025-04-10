@props([
    'id',
    'name',
    'label' => '',
    'required' => false,
    'disabled' => false,
    'help' => '',
    'jserror' => '',
])

<div {{ $attributes->class('field') }}>
    @if($label)
        <x-label
            for="{{ $id }}"
            :value="$label"
            :disabled="$disabled"
        />
    @endif

    {{ $slot }}

    @if($help) <x-help-message :message="$help"/> @endif

    <x-error-messages :messages="$jserror ?: $errors->get($name)"/>
</div>
