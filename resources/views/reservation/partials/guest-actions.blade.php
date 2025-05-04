@php
/** @var \App\Models\Reservation $reservation */
use App\Enums\ReservationStatus as Status;
use function App\Helpers\datetime_formatter
@endphp

<div class="flex flex-col gap-4">
    @switch($reservation->status)
        @case(Status::QUOTE)
            <div class="prose-sm">
                <h3 class="capitalize font-semibold">{{ __($reservation->status->value) }}</h3>
                <p class="text-zinc-600">
                    {{ __('The host has received your request and will respond as soon as possible.') }}
                    {{ __('If you have any questions or curiosities, use the chat to contact the host.') }}
                </p>
            </div>

            @break

        @case(Status::PENDING)
            <div class="prose-sm text-zinc-700">
                <h3 class="capitalize font-semibold">{{ __($reservation->status->value) }}</h3>

                <p class="text-zinc-600">
                    {{ __('Your request has been pre-approved.') }}
                    {{ __('Add a payment method to confirm it.') }}
                    {{ __('The total will be charged to you on :date.', ['date' => datetime_formatter($reservation->dueDate, timeFormat: IntlDateFormatter::NONE)]) }}
                </p class="text-zinc-600">

                <p>
                    {{ __('You have 24 hours to confirm your reservation.') }}
                    {{ __('Approval expires at :datetime.', ['datetime' => datetime_formatter($reservation->checkout_session['expires_at'])]) }}
                </p>
            </div>

            <a
                href="{{ $reservation->checkout_session['url'] }}"
                class="button primary w-full"
            >
                {{ __('Confirm the booking') }}
            </a>

            <a
                class="hover:underline flex items-center gap-2"
                href="{{ route('change_request.create', [$reservation]) }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205 3 1m1.5.5-1.5-.5M6.75 7.364V3h-3v18m3-13.636 10.5-3.819" />
                </svg>
                <span>{{ __('Change the booking') }}</span>
            </a>

            @break

        @case(Status::CONFIRMED)
            <div class="prose-sm text-zinc-700">
                <h3 class="capitalize font-semibold">{{ __($reservation->status->value) }} 🍾 🥳</h3>

                <p class="text-zinc-600">
                    {{ __('Booking confirmed, see you in :month!', ['month' => $reservation->check_in->translatedFormat('F')]) }}
                    {{ __('If you have any questions, don\'t hesitate to ask.') }}
                </p>
            </div>

            <a
                class="hover:underline flex items-center gap-2"
                href="{{ route('change_request.create', [$reservation]) }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205 3 1m1.5.5-1.5-.5M6.75 7.364V3h-3v18m3-13.636 10.5-3.819" />
                </svg>
                <span>{{ __('Change the booking') }}</span>
            </a>

            <a
                href="{{ route('reservation.delete', [$reservation]) }}"
                class="hover:underline flex items-center gap-2"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>{{ __('Cancel the booking') }}</span>
            </a>

            @break

        @case(Status::REJECTED)
            <div class="prose-sm">
                <h3 class="capitalize font-semibold">{{ __($reservation->status->value) }}</h3>
                <p class="text-zinc-600">{{ __('You rejected this request.') }}</p>
            </div>

            @break

        @case(Status::CANCELLED)
            <div class="prose-sm">
                <h3 class="capitalize font-semibold">{{ __($reservation->status->value) }}</h3>
                <p class="text-zinc-600">{{ __('This booking has been cancelled.') }}</p>
            </div>

            @break
    @endswitch

    <form method="POST" action="{{ route('billing_portal') }}">
        @csrf

        <button class="clear flex items-center gap-2 cursor-pointer hover:underline">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
            </svg>
            <span>{{ __('Billing Portal') }}</span>
        </button>
    </form>
</div>
