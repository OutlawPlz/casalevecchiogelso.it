@props([
    'messages' => [],
])

@php
    $fromAlpine = is_string($messages);
    $classObject = 'text-red-500 text-sm';
@endphp

<div>
    @if($fromAlpine)
        <template x-for="message in {{ $messages }}">
            <div x-text="message" {{ $attributes->class($classObject) }}></div>
        </template>
    @else
        @foreach($messages as $message)
            <div {{ $attributes->class($classObject) }}>{{ $message }}</div>
        @endforeach
    @endif
</div>
