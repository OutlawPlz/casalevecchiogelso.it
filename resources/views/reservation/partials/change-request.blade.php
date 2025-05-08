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
            {{ $changeRequest->fromReservation->guest_count }} {{ __('guests') }} • Tot. <span x-currency="{{ $changeRequest->fromReservation->tot }}"></span>
        </div>
    </div>

    <hr class="my-4 -mx-4">

    <div class="flex gap-3">
        <div class="w-12 font-semibold">{{ __('To') }}:</div>
        <div class="text-zinc-600">
            {{ $changeRequest->toReservation->check_in->format('d M') }} - {{ $changeRequest->toReservation->check_out->format('d M') }} ({{ $changeRequest->toReservation->nights }} {{ __('nights') }}) <br>
            {{ $changeRequest->toReservation->guest_count }} {{ __('guests') }} • Tot. <span x-currency="{{ $changeRequest->toReservation->tot }}"></span>
        </div>
    </div>
</div>

<div class="flex flex-col gap-4 mt-6">
    <button
        x-data x-on:click.prevent="$dispatch('open-modal', 'confirm-change')"
        type="button"
        class="primary w-full justify-center"
    >
        {{ __('Confirm the change') }}
    </button>

    <button
        x-data x-on:click.prevent="$dispatch('open-modal', 'reject-change')"
        type="button"
        class="clear hover:underline flex items-center gap-2 cursor-pointer">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
        </svg>
        <span>{{ __('Reject the change') }}</span>
    </button>
</div>

<x-modal name="confirm-change" class="max-w-xl">
    <div class="p-6">
        <div class="prose">
            <h3>{{ __('Confirm the modification') }}</h3>
            <p class="text-zinc-600">{{ __('You are about to confirm the modification.') }}</p>
        </div>

        <div class="flex justify-end mt-4 gap-3">
            <button class="ghost" x-on:click="$dispatch('close')">{{ __('Close') }}</button>
            <button class="primary">{{ __('Confirm') }}</button>
        </div>
    </div>
</x-modal>

<x-modal name="reject-change" class="max-w-xl">
    <div class="p-6">
        <div class="prose">
            <h3>{{ __('Reject the modification') }}</h3>
            <p class="text-zinc-600">{{ __('Are you sure you want to reject this change?') }}</p>
        </div>

        <div class="flex justify-end mt-4 gap-3">
            <button class="ghost" x-on:click="$dispatch('close')">{{ __('Close') }}</button>
            <button class="primary">{{ __('Reject') }}</button>
        </div>
    </div>
</x-modal>
