<div>
    <span class="font-semibold">{{ __('Change Request') }}</span>
    <span class="tracking-wider text-zinc-600 uppercase pl-1 text-xs">{{ $changeRequest->status }}</span>
</div>

<div class="border rounded-lg p-4 mt-4 flex gap-3">
    <div class="w-12 font-semibold">{{ __('From') }}:</div>
    <div class="text-zinc-600">
        {{ $changeRequest->fromReservation->check_in->format('d M') }} - {{ $changeRequest->fromReservation->check_out->format('d M') }} ({{ $changeRequest->fromReservation->nights }} {{ __('nights') }}) <br>
        {{ $changeRequest->fromReservation->guest_count }} {{ __('guests') }} • Tot. <span x-currency="{{ $changeRequest->fromReservation->tot }}"></span>
    </div>
</div>

<div class="flex justify-evenly my-2 text-zinc-500">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
        <path fill-rule="evenodd" d="M8 2a.75.75 0 0 1 .75.75v8.69l3.22-3.22a.75.75 0 1 1 1.06 1.06l-4.5 4.5a.75.75 0 0 1-1.06 0l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.22 3.22V2.75A.75.75 0 0 1 8 2Z" clip-rule="evenodd" />
    </svg>

    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
        <path fill-rule="evenodd" d="M8 2a.75.75 0 0 1 .75.75v8.69l3.22-3.22a.75.75 0 1 1 1.06 1.06l-4.5 4.5a.75.75 0 0 1-1.06 0l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.22 3.22V2.75A.75.75 0 0 1 8 2Z" clip-rule="evenodd" />
    </svg>

    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
        <path fill-rule="evenodd" d="M8 2a.75.75 0 0 1 .75.75v8.69l3.22-3.22a.75.75 0 1 1 1.06 1.06l-4.5 4.5a.75.75 0 0 1-1.06 0l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.22 3.22V2.75A.75.75 0 0 1 8 2Z" clip-rule="evenodd" />
    </svg>
</div>

<div class="border rounded-lg p-4 flex gap-3">
    <div class="w-12 font-semibold">{{ __('To') }}:</div>
    <div class="text-zinc-600">
        {{ $changeRequest->toReservation->check_in->format('d M') }} - {{ $changeRequest->toReservation->check_out->format('d M') }} ({{ $changeRequest->toReservation->nights }} {{ __('nights') }}) <br>
        {{ $changeRequest->toReservation->guest_count }} {{ __('guests') }} • Tot. <span x-currency="{{ $changeRequest->toReservation->tot }}"></span>
    </div>
</div>

<button class="primary w-full justify-center mt-4">{{ __('Confirm the change') }}</button>
