@php
/**
 * @var App\Models\User $authUser
 * @var App\Models\Reservation $reservation
 */
use function App\Helpers\datetime_formatter;
@endphp

@use('\App\Enums\ReservationStatus')

@switch($reservation->status)
    @case(ReservationStatus::PENDING)
        <form method="POST" action="{{ route('reservation.confirm', [$reservation]) }}">
            @csrf

            <button class="primary w-full">
                {{ __('Confirm the booking') }}
            </button>
        </form>

        <div class="prose-sm mt-2">
            {{ __('Your booking has been pre-approved.') }}
            {{ __('You have 24 hours to confirm your reservation.') }}
            {{ __('Approval expires at :datetime.', ['datetime' => datetime_formatter($reservation->checkout_session['expires_at'])]) }}
        </div>

        @break

    @case(ReservationStatus::REJECTED)
        <x-callout variant="danger">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 text-red-400">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd" />
                </svg>
            </x-slot:icon>
            <p>{{ __('The booking was rejected.') }}</p>
        </x-callout>

        @break

    @case(ReservationStatus::CANCELLED)
        <x-callout variant="danger">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 text-red-400">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd" />
                </svg>
            </x-slot:icon>
            <p>
                {{ __('The reservation has been cancelled.') }}
                {{ __('You will receive a refund, if applicable.') }}
            </p>
        </x-callout>

        @break

    @case(ReservationStatus::CONFIRMED)
        <div class="flex flex-col gap-3">
            <x-callout variant="success">
                <x-slot:icon>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 text-green-400">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                    </svg>
                </x-slot:icon>
                <p>{{ __('Reservation confirmed!') }}</p>
            </x-callout>

            <a
                class="button w-full"
                href="{{ route('change_request.create', [$reservation]) }}"
            >
                {{ __('Change the booking') }}
            </a>
            <a
                href="{{ route('reservation.delete', [$reservation]) }}"
                class="button primary w-full"
            >
                {{ __('Cancel the booking') }}
            </a>
        </div>

        @break
@endswitch
