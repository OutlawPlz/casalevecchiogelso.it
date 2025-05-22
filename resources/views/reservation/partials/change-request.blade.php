<div>
    <span class="font-semibold">{{ __('Change Request') }}</span>
    <span class="tracking-wider text-zinc-600 uppercase pl-1 text-xs">{{ $changeRequest->status }}</span>
</div>

<div class="prose-sm mt-3 text-zinc-600">
    {{ __('The :role would like to modify the reservation.', ['role' => $changeRequest->user->role]) }}
    {{ __('Evaluate the changes and approve them, or refuse.') }}

    {{ __('You have 24 hours to confirm the change request.') }}
    {{ __('If you don\'t respond, the reservation will remain unchanged.') }}
</div>

<div class="border rounded-lg p-4 mt-4">
    <div class="flex gap-3">
        <div class="w-12 font-semibold">{{ __('From') }}:</div>
        <div class="text-zinc-600">
            {{ $changeRequest->fromReservation->check_in->format('d M') }} - {{ $changeRequest->fromReservation->check_out->format('d M') }} ({{ $changeRequest->fromReservation->nights }} {{ __('nights') }}) <br>
            {{ $changeRequest->fromReservation->guest_count }} {{ __('guests') }} • Tot. <span x-money="{{ $changeRequest->fromReservation->tot }}"></span>
        </div>
    </div>

    <hr class="my-4 -mx-4">

    <div class="flex gap-3">
        <div class="w-12 font-semibold">{{ __('To') }}:</div>
        <div class="text-zinc-600">
            {{ $changeRequest->toReservation->check_in->format('d M') }} - {{ $changeRequest->toReservation->check_out->format('d M') }} ({{ $changeRequest->toReservation->nights }} {{ __('nights') }}) <br>
            {{ $changeRequest->toReservation->guest_count }} {{ __('guests') }} • Tot. <span x-money="{{ $changeRequest->toReservation->tot }}"></span>
        </div>
    </div>
</div>

<div class="flex flex-col gap-4 mt-6">
    <a
        href="{{ route('change_request.show', [$reservation, $changeRequest]) }}"
        class="button primary w-full justify-center"
    >
        {{ __('View the change') }}
    </a>
</div>
