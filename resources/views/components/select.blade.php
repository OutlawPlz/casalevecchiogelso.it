@props([
    'options' => [],
])

@aware([
    'id',
    'name' => '',
    'type' => 'text',
    'error' => '',
    'jserror' => '',
])

@php
    /** @var \Illuminate\Support\ViewErrorBag $errors */

    if (array_is_list($options)) {
        $options = array_combine($options, $options);
    }

    $classObject = [];

    if ($error) {
        [$key, $bag] = explode(':', "$error:default");

        $classObject['invalid'] = $errors->getBag($bag)->has($key);
    }
@endphp

<div class="relative">
    <select
        {{ $attributes->class($classObject) }}
        @if($jserror) :class="{ invalid: !!{{ $jserror }} }" @endif
        id="{{ $id }}"
        name="{{ $name }}"
    >
        @foreach($options as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>

    @if(! $attributes->has('multiple'))
        <svg class="absolute right-3 size-4 top-1/2 -translate-y-1/2 text-zinc-400 pointer-events-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
        </svg>
    @endif
</div>

