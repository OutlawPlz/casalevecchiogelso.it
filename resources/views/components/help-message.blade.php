@props([
    'message' => '',
])

@if($message)
    <div {{ $attributes->class('text-zinc-500 text-sm') }}>{{ $message }}</div>
@endif
