@props([
    'messages' => [],
])

@php
    $fromAlpine = is_string($messages);
    $classObject = 'text-base/6 text-red-600  sm:text-sm/6';
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
