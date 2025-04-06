@props([
    'id',
    'name',
    'label' => '',
    'required' => false,
    'disabled' => false,
    'help' => '',
    'alpinejs' => '',
])

<div {{ $attributes }}>
    @if($label)
        <x-label for="{{ $id }}" class="mb-1">{{ $label }}</x-label>
    @endif

    {{ $slot }}

    @if($help)
        <x-help-message class="mt-1" :message="$help"/>
    @endif

    <x-error-messages class="mt-1" :messages="$alpinejs ?: $errors->get($name)"/>
</div>
