@props([
    'message' => '',
])

@if($message)
    <div {{ $attributes }}>{{ $message }}</div>
@endif
