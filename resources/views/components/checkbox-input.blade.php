@props(['value', 'disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500']) !!} type="checkbox">
<span class="ms-2 text-sm text-gray-600">{{ $value }}</span>
