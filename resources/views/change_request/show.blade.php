@php
    /**
     * @var float $refundAmount
     * @var \App\Models\ChangeRequest $changeRequest
     * @var \App\Models\User $authUser
     * @var \App\Models\Reservation $reservation
     */
    use App\Enums\ReservationStatus as Status;
    use function App\Helpers\money_formatter;
@endphp

<x-app-layout>
    <div class="border rounded-lg p-4 mt-4">
        <div class="flex gap-3">
            <div class="w-12 font-semibold">{{ __('From') }}:</div>
            <div class="text-zinc-600">
                {{ $changeRequest->fromReservation->check_in->format('d M') }}
                - {{ $changeRequest->fromReservation->check_out->format('d M') }}
                ({{ $changeRequest->fromReservation->nights }} {{ __('nights') }}) <br>
                {{ $changeRequest->fromReservation->guest_count }} {{ __('guests') }} • Tot.
                <span>{{ money_formatter($changeRequest->fromReservation->tot) }}</span>
            </div>
        </div>

        <hr class="my-4 -mx-4">

        <div class="flex gap-3">
            <div class="w-12 font-semibold">{{ __('To') }}:</div>
            <div class="text-zinc-600">
                {{ $changeRequest->toReservation->check_in->format('d M') }}
                - {{ $changeRequest->toReservation->check_out->format('d M') }}
                ({{ $changeRequest->toReservation->nights }} {{ __('nights') }}) <br>
                {{ $changeRequest->toReservation->guest_count }} {{ __('guests') }} • Tot.
                <span>{{ money_formatter($changeRequest->toReservation->tot) }}</span>
            </div>
        </div>
    </div>

    <div class="flex justify-between mt-6">
        <span class="underline">{{ __('Original price') }}</span>
        <span>{{ money_formatter($changeRequest->fromReservation->tot) }}</span>
    </div>

    <div class="flex justify-between mt-2">
        <span class="underline">{{ __('New price') }}</span>
        <span>{{ money_formatter($changeRequest->toReservation->tot) }}</span>
    </div>

    <hr class="my-6">

    <div class="flex justify-between font-bold text-lg">
        <span>{{ __('Price difference') }}</span>
        <span>{{ money_formatter($changeRequest->priceDifference()) }}</span>
    </div>

    @if($refundAmount)
        <div class="mt-2">
            <div class="flex justify-between">
                <span>{{ __('Refund') }}</span>
                <span>{{ money_formatter($refundAmount) }}</span>
            </div>

            @host
                <p class="help-message mt-2">{{ __('According to cancellation policy, you\'ll receive the specified refund amount.') }}</p>
            @else
                <p class="help-message mt-2">{{ __('According to cancellation policy, the guest will receive the specified refund amount.') }}</p>
            @endhost
        </div>
    @endif

    @if($amountDue)
        <div class="mt-2">
            <div class="flex justify-between">
                <span>{{ __('Amount due') }}</span>
                <span>{{ money_formatter($amountDue) }}</span>
            </div>

            @host
                <p class="help-message mt-2">{{ __('By confirming the request, you will be immediately charged the specified amount.') }}</p>
            @else
                <p class="help-message mt-2">{{ __('By confirming the request, the guest will be immediately charged the specified amount.') }}</p>
            @endhost
        </div>
    @endif

    <form method="POST" action="{{ route('change_request.approve', [$reservation, $changeRequest]) }}" class="mt-6">
        @if($refundAmount)
            <button class="primary">{{ __('Confirm and refund') }}</button>
        @endif

        @if($amountDue && $changeRequest->user->isHost())
            <button class="primary">{{ __('Confirm and pay') }}</button>
        @endif

        @if($amountDue && ! $changeRequest->user->isHost())
            <button class="primary">{{ __('Confirm and charge') }}</button>
        @endif

        @if($changeRequest->reservation->inStatus(Status::PENDING, Status::QUOTE))
            <button class="primary">{{ __('Confirm') }}</button>
        @endif
    </form>
</x-app-layout>
