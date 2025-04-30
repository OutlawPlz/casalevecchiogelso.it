@aware([
    'id',
    'error' => '',
    'jserror' => '',
    'disabled' => false,
])

@php
/** @var \Illuminate\Support\ViewErrorBag $errors */

$classObject = [];

if ($error) {
    [$key, $bag] = explode(':', "$error:default");

    $classObject['invalid'] = $errors->getBag($bag)->has($key);
}
@endphp

<textarea
    {{ $attributes->class($classObject) }}
    @if($jserror) :class="{ invalid: !!{{ $jserror }} }" @endif
    id="{{ $id }}"
    @disabled($disabled)
></textarea>
