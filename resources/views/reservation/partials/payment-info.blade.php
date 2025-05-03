@php
/** @var \App\Models\Reservation $reservation */
use function App\Helpers\is_overnight_stay;
use function App\Helpers\money_formatter;
use function App\Helpers\datetime_formatter;
@endphp

<div>
    <div class="flex items-center justify-between">
        <div class="font-semibold">{{ __('Payment info') }}</div>
    </div>

    <p class="prose-sm text-zinc-600">
        {{ __('The total will be charged to you on :date.', ['date' => datetime_formatter($reservation->dueDate, timeFormat: IntlDateFormatter::NONE)]) }}
        {{ __('Please ensure your payment method is valid in your Billing Portal before that date.') }}
    </p>
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

        <div class="flex justify-between">
            <div>{{ __('Due on:') }}</div>
            <span>{{ datetime_formatter($reservation->dueDate, timeFormat: IntlDateFormatter::NONE) }}</span>
        </div>
    </div>
</div>
