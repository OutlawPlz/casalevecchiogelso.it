@php
/** @var \App\Models\Reservation $reservation */
use App\Enums\ReservationStatus as Status;
use function App\Helpers\datetime_formatter;
@endphp

<div class="flex flex-col gap-3">
    @switch($reservation->status)
        @case(Status::PENDING)
            <div>
                <form method="POST" action="{{ route('reservation.confirm', [$reservation]) }}">
                    @csrf

                    <button class="primary w-full">
                        {{ __('Confirm the booking') }}
                    </button>
                </form>

                <div class="prose-sm mt-2 text-zinc-700">
                    {{ __('Your booking has been pre-approved.') }}
                    {{ __('You have 24 hours to confirm your reservation.') }}
                    {{ __('Approval expires at :datetime.', ['datetime' => datetime_formatter($reservation->checkout_session['expires_at'])]) }}
                </div>
            </div>

            @break

        @case(Status::CONFIRMED)
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
    @endswitch
</div>
