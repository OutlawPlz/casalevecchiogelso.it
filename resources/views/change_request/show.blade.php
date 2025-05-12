@php
    /**
     * @var float $refundAmount
     * @var \App\Models\ChangeRequest $changeRequest
     * @var \App\Models\Reservation $reservation
     */
    use function App\Helpers\money_formatter;
@endphp

<x-app-layout>
    <div class="my-6 px-4 md:px-6 max-w-xl mx-auto">
        <div class="prose">
            <h1>{{ __('Change request') }}</h1>
            <p class="text-zinc-600">
                {{ __('The :role would like to modify the reservation.', ['role' => $changeRequest->user->role]) }}
                {{ __('Evaluate the changes and approve them, or refuse.') }}

                {{ __('You have 24 hours to confirm the change request.') }}
                {{ __('If you don\'t respond, the reservation will remain unchanged.') }}
            </p>

            <p class="text-zinc-600">
                @if($amountDue)
                    {{ $changeRequest->user->isHost()
                        ? __('By confirming the request, the guest will be immediately charged an amount of :amount.', ['amount' => money_formatter($amountDue)])
                        : __('By confirming the request, you\'ll be immediately charged an amount of :amount.', ['amount' => money_formatter($amountDue)]) }}
                @endif

                @if($refundAmount)
                    {{ $changeRequest->user->isHost()
                        ? __('According to cancellation policy, the guest will receive a refund amount of :amount.', ['amount' => money_formatter($refundAmount)])
                        : __('According to cancellation policy, you\'ll receive a refund amount of :amount.', ['amount' => money_formatter($refundAmount)]) }}
                @endif
            </p>
        </div>

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

        <div class="flex justify-between font-semibold text-lg">
            <span>{{ __('Price difference') }}</span>
            <span>{{ money_formatter($changeRequest->priceDifference()) }}</span>
        </div>

        @if($refundAmount)
            <div class="mt-2 flex justify-between">
                <span class="underline">{{ __('Refund') }}</span>
                <span>{{ money_formatter($refundAmount) }}</span>
            </div>
        @endif

        @if($amountDue)
            <div class="mt-2 flex justify-between">
                <span class="underline">{{ __('Amount due') }}</span>
                <span>{{ money_formatter($amountDue) }}</span>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('change_request.approve', [$reservation, $changeRequest]) }}"
            class="mt-6 flex gap-3"
        >

            @if($refundAmount)
                <button class="primary">{{ __('Confirm and refund') }}</button>
            @elseif($amountDue)
                <button class="primary">
                    {{ $changeRequest->user->isHost()
                        ? __('Confirm and pay')
                        : __('Confirm and charge') }}
                </button>
            @else
                <button class="primary">{{ __('Confirm') }}</button>
            @endif

            <a href="{{ route('reservation.show', [$reservation]) }}" class="button ghost">{{ __('Back') }}</a>
        </form>
    </div>
</x-app-layout>
