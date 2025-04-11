@props(['name'])

<dialog
    x-data=""
    data-modal="{{ $name }}"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') $el.showModal();"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}') $el.close();"
    {{ $attributes }}
>
    {{ $slot }}

    <x-ui-close
        x-on:click="$root.close()"
        class="absolute top-0 right-0 m-2"
    />
</dialog>
