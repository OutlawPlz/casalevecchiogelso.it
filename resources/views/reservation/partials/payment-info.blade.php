@php
/** @var \App\Models\Reservation $reservation */
use function App\Helpers\money_formatter;
use function App\Helpers\datetime_formatter;
@endphp

<div>
    <div class="flex items-center justify-between">
        <div class="font-bold">{{ __('Payment info') }}</div>

        <form method="POST" action="{{ route('billing_portal') }}">
            @csrf

            <button class="ghost sm">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                    <path d="M2.5 3A1.5 1.5 0 0 0 1 4.5V5h14v-.5A1.5 1.5 0 0 0 13.5 3h-11Z" />
                    <path fill-rule="evenodd" d="M15 7H1v4.5A1.5 1.5 0 0 0 2.5 13h11a1.5 1.5 0 0 0 1.5-1.5V7ZM3 10.25a.75.75 0 0 1 .75-.75h.5a.75.75 0 0 1 0 1.5h-.5a.75.75 0 0 1-.75-.75Zm3.75-.75a.75.75 0 0 0 0 1.5h2.5a.75.75 0 0 0 0-1.5h-2.5Z" clip-rule="evenodd" />
                </svg>
                <span>{{ __('Billing Portal') }}</span>
            </button>
        </form>
    </div>

    <p class="text-zinc-600">
        {{ __('The total will be charged to your account on :date.', ['date' => datetime_formatter($reservation->dueDate, timeFormat: IntlDateFormatter::NONE)]) }}
        {{ __('Please ensure your payment method is valid in your Billing Portal before that date.') }}
    </p>
</div>

<div>
    <div class="flex justify-between mt-4">
        <div class="font-semibold">{{ __('Tot.') }}</div>
        <span class="text-zinc-600">{{ money_formatter($reservation->tot) }}</span>
    </div>

    <div class="flex justify-between">
        <div class="font-semibold">{{ __('Charged on') }}</div>
        <span class="text-zinc-600">{{ datetime_formatter($reservation->dueDate, timeFormat: IntlDateFormatter::NONE) }}</span>
    </div>
</div>
