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
            :disabled="$disabled"
        />
    @endif

    {{ $slot }}

    @if($help)
        <div class="help-message">{{ $help }}</div>
    @endif

    @foreach($errors->get($error) as $message)
        <div class="error-message">{{ $message }}</div>
    @endforeach

    @if($jserror)
        <template x-for="message in {{ $jserror }}">
            <div x-text="message" class="error-message"></div>
        </template>
    @endif
</div>
