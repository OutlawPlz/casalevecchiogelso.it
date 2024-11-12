@props(['defaultClasses' => 'block p-3 w-full shadow-sm disabled:shadow-none border rounded-lg bg-white dark:bg-white/10 dark:disabled:bg-white/[7%] resize-y text-sm text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70 dark:text-zinc-300 dark:disabled:text-zinc-400 dark:placeholder-zinc-400 dark:disabled:placeholder-zinc-500 border-zinc-200 border-b-zinc-300/80 dark:border-white/10'])

<textarea {{ $attributes->class($defaultClasses) }}>{{ $slot }}</textarea>
