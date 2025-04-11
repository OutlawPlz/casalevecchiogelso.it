@aware([
    'id',
    'name' => '',
    'type' => 'text',
    'error' => '',
    'jserror' => '',
])

@php
/** @var \Illuminate\Support\ViewErrorBag $errors */

$classObject = [];

if ($error) {
    [$key, $bag] = explode(':', "$error:default");

    $classObject['invalid'] = $errors->getBag($bag)->has($key);
}
@endphp

<input
    {{ $attributes->class($classObject) }}
    @if($jserror)::class="{ invalid: !!{{ $jserror }} }"@endif
    id="{{ $id }}"
    name="{{ $name }}"
    type="{{ $type }}"
>
