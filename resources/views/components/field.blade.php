@php
/** @var \Illuminate\Support\ViewErrorBag $errors */
@endphp

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

        @if($error)
            @php([$bag, $key] = explode(':', ":$error"))
            @foreach($errors->getBag($bag)->get($key) as $message)
                <div class="error-message">{{ $message }}</div>
            @endforeach
        @endif

    @if($jserror)
        <template x-for="message in {{ $jserror }}">
            <div x-text="message" class="error-message"></div>
        </template>
    @endif
</div>
