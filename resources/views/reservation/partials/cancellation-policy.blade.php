@php
    /**
     * @var \App\Models\Reservation $reservation
     */
    use function App\Helpers\date_format;
@endphp

<h3 class="font-semibold">{{ __('Cancellation policy') }}</h3>

<p class="text-zinc-600">
    {{ __('To obtain a full refund, you must cancel the reservation by :date.', [
            'date' => date_format($reservation->due_date, time: null)
    ]) }}

    {{ __('If you cancel the reservation within :days before check-in, you will be refunded :percentage% of the total.', [
            'days' => $reservation->cancellation_policy->timeWindow(),
            'percentage' => $reservation->cancellation_policy->refundFactor() * 100
    ]) }}

    {{ __('After the check-in date, you will not be entitled to a refund.') }}
</p>
