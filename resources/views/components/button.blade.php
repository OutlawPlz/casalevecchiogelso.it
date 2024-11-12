@props([
    'variant' => 'default',
    'size' => 'md',
    'href' => null,
])

@php
$classes = 'items-center font-medium justify-center gap-2 whitespace-nowrap disabled:opacity-75 dark:disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none text-sm rounded-lg inline-flex';

$variantClasses = match ($variant) {
    'default' => 'bg-white hover:bg-zinc-50 dark:bg-zinc-700 dark:hover:bg-zinc-600/75 text-zinc-800 dark:text-white border border-zinc-200 hover:border-zinc-200 border-b-zinc-300/80 dark:border-zinc-600 dark:hover:border-zinc-600 shadow-sm',
    'primary' => 'bg-zinc-800 hover:bg-zinc-900 dark:bg-white dark:hover:bg-zinc-100 text-white dark:text-zinc-800  shadow-[inset_0px_1px_theme(colors.zinc.900),inset_0px_2px_theme(colors.white/.15)] dark:shadow-none',
    'filled' => 'bg-zinc-800/5 hover:bg-zinc-800/10 dark:bg-white/10 dark:hover:bg-white/20 text-zinc-800 dark:text-white',
    'danger' => 'bg-red-500 hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-500 text-white  shadow-[inset_0px_1px_theme(colors.red.500),inset_0px_2px_theme(colors.white/.15)] dark:shadow-none',
    'ghost' => 'bg-transparent hover:bg-zinc-800/5 dark:hover:bg-white/15 text-zinc-800 dark:text-white',
    'subtle' => 'bg-transparent hover:bg-zinc-800/5 dark:hover:bg-white/15 text-zinc-400 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white',
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
