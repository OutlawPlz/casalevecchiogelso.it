@aware([
    'id',
    'name' => '',
    'type' => 'text',
])

<input
    {{ $attributes }}
    id="{{ $id }}"
    name="{{ $name }}"
    type="{{ $type }}"
>
