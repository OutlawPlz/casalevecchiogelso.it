@props([
    'icon' => null,
    'variant' => '',
])

@php
    $styleObject = match ($variant) {
        'success' => '--callout-text:var(--color-green-600);--callout-icon:var(--color-green-400);--callout-bg:var(--color-green-50);--callout-border:var(--color-green-300)',
        'danger' => '--callout-text:var(--color-red-700);--callout-icon:var(--color-red-400);--callout-bg:var(--color-red-50);--callout-border:var(--color-red-200)',
        'warning' => '--callout-text:var(--color-yellow-700);--callout-icon:var(--color-yellow-500);--callout-bg:var(--color-yellow-50);--callout-border:var(--color-yellow-400)',
        'info' => '--callout-text:var(--color-indigo-600);--callout-icon:var(--color-indigo-500);--callout-bg:var(--color-indigo-50);--callout-border:var(--color-indigo-200)',
        default => '--callout-text:var(--color-zinc-700);--callout-icon:var(--color-zinc-400);--callout-bg:var(--color-white);--callout-border:var(--color-zinc-200)',
    };
@endphp

<div
    {{ $attributes
        ->class('p-4 border rounded-lg border-(--callout-border) bg-(--callout-bg) flex gap-3')
        ->merge(['style' => $styleObject]) }}
>
    @if($icon)
        <div class="text-(--callout-icon) shrink-0 mt-0.5">
            {{ $icon }}
        </div>
    @endif

    <div class="text-(--callout-text)">
        {{ $slot }}
    </div>
</div>
