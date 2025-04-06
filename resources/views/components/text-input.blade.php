@props([
    'defaultClasses' => 'w-full border rounded-lg block disabled:shadow-none appearance-none text-sm py-2 h-10 leading-[1.375rem] pl-3 pr-3 bg-white text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70 shadow-xs border-zinc-200 border-b-zinc-300/80 disabled:border-b-zinc-200',
])

<input {{ $attributes->class($defaultClasses) }}>
