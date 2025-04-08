@props([
    'message' => '',
])

@if($message)
    <div {{ $attributes->class('text-zinc-500 ') }}>{{ $message }}</div>
@endif
