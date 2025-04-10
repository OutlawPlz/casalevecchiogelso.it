@props([
    'id',
    'name',
    'label' => '',
    'required' => false,
    'disabled' => false,
    'help' => '',
    'jserror' => '',
])

<div {{ $attributes->class('flex flex-col gap-1.5') }}>
    @if($label)
        <x-label
            class="font-medium"
            for="{{ $id }}"
            :value="$label"
        />
    @endif

    {{ $slot }}

    @if($help)
        <x-help-message :message="$help"/>
    @endif

    <x-error-messages :messages="$jserror ?: $errors->get($name)"/>
</div>
