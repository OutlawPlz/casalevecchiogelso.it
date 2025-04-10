@props([
    'variant' => 'default',
    'size' => 'lg',
    'href' => null,
])

@php
$classes = 'items-center font-medium justify-center gap-2 whitespace-nowrap cursor-pointer disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none text-sm rounded-lg inline-flex';

$variantClasses = match ($variant) {
    'default' => 'bg-white hover:bg-zinc-50 text-zinc-800 border border-zinc-200 hover:border-zinc-200 border-b-zinc-300/80 shadow-xs',
    'primary' => 'bg-zinc-800 hover:bg-zinc-900 text-white',
    'filled' => 'bg-zinc-800/5 hover:bg-zinc-800/10 text-zinc-800',
    'danger' => 'bg-red-500 hover:bg-red-600 text-white',
    'ghost' => 'bg-transparent hover:bg-zinc-800/5 text-zinc-800',
    'subtle' => 'bg-transparent hover:bg-zinc-800/5 text-zinc-400 hover:text-zinc-800',
};

$sizeClasses = match ($size) {
    'sm' => 'text-xs',
    'md' => 'text-sm h-10 px-4',
    'lg' => 'text-sm',
    default => $size,
}
@endphp

@if($href)
<a href="{{ $href }}" {{ $attributes->class([$classes, $variantClasses, $sizeClasses]) }}>{{ $slot }}</a>
@else
<button {{ $attributes->class([$classes, $variantClasses, $sizeClasses]) }}>{{ $slot }}</button>
@endif
