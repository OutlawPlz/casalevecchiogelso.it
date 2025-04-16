@props([
    'error' => '',
    'jserror' => '',
])

@if($jserror)
    <template x-for="message in {{ $jserror }}">
        <div x-text="message" {{ $attributes->class('error-message') }}></div>
    </template>
@endif

@if($error)
    @php([$key, $bag] = explode(':', "$error:default"))
    @foreach($errors->getBag($bag)->get($key) as $message)
        <div {{ $attributes->class('error-message') }}>{{ $message }}</div>
    @endforeach
@endif
