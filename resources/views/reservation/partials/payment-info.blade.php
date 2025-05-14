@php
    /** @var \App\Models\Reservation $reservation */
    use App\Enums\ReservationStatus as Status;
    use function App\Helpers\is_overnight_stay;
    use function App\Helpers\money_formatter;
    use function App\Helpers\datetime_formatter;
@endphp

<div class="prose-sm">
    <h3 class="font-semibold">{{ __('Payment info') }}</h3>

    @if(! $reservation->inStatus(Status::QUOTE) && ! $reservation->hasBeenPaid())
        <p class="text-zinc-600">
            {{ __('The total will be charged to you on :date.', ['date' => datetime_formatter($reservation->due_date, timeFormat: IntlDateFormatter::NONE)]) }}
            {{ __('Please ensure your payment method is valid in your Billing Portal before that date.') }}
        </p>
    @else
        <p>{{ __('Your booking payment was successful!') }}</p>
    @endif
</div>

<div class="border rounded-lg p-4 mt-4">
    <div class="space-y-1">
        @foreach($reservation->price_list as $line)
            <div class="flex justify-between">
                <div class="underline">
                    @if(is_overnight_stay($line['product']))
                        <span>{{ money_formatter($line['unit_amount']) }}</span> x
                        <span>{{ $line['quantity'] }}</span> {{ __('nights') }}
                    @else
                        <span class="underline">{{ __($line['name']) }}</span>
                    @endif
                </div>

                <div>{{ money_formatter($line['quantity'] * $line['unit_amount']) }}</div>
            </div>
        @endforeach
    </div>

    <hr class="-mx-4 my-3">

    <div class="space-y-1">
        <div class="flex justify-between font-semibold">
            <div>{{ __('Tot.') }}</div>
            <span>{{ money_formatter($reservation->tot) }}</span>
        </div>

        @if(! $reservation->inStatus(Status::QUOTE))
            <div class="flex justify-between">
                <div>{{ __('Due on:') }}</div>
                <span>{{ datetime_formatter($reservation->due_date, timeFormat: IntlDateFormatter::NONE) }}</span>
            </div>
        @endif
    </div>
</div>

@foreach($reservation->payments as $payment)
    <a
        class="hover:underline flex items-center gap-2 mt-4"
        href="{{ $payment->receipt_url }}"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            class="size-6"
        >
            <path
                stroke-linecap="round" stroke-linejoin="round"
                d="m9 14.25 6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0c1.1.128 1.907 1.077 1.907 2.185ZM9.75 9h.008v.008H9.75V9Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm4.125 4.5h.008v.008h-.008V13.5Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"
            />
        </svg>
        <span>{{ __('Receipt') }}</span>
    </a>
@endforeach
