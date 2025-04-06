@props([
    'messages' => [],
])

@php
    $fromAlpine = is_string($messages);
@endphp

<div {{ $attributes->class('invalid-text flex flex-col gap-1') }}>
    @if($fromAlpine)
        <template x-for="message in {{ $messages }}">
            <div x-text="message"></div>
        </template>
    @else
        @foreach($messages as $message)
            <div>{{ $message }}</div>
        @endforeach
    @endif
</div>
