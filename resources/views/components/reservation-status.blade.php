@php
/**
 * @var App\Models\User $authUser
 * @var App\Models\Reservation $reservation
 */
@endphp

@use('\App\Enums\ReservationStatus')

@switch($reservation->status)
    @case(ReservationStatus::PENDING)
        <form action="{{ route('checkout') }}" method="POST" class="mt-4">
            @csrf
            <x-primary-button
                value="{{ $reservation->ulid }}"
                name="reservation"
                class="w-full justify-center"
            >{{ __('Confirm and pay') }}</x-primary-button>
        </form>

        <div class="prose mt-4">
            {{ __('Your booking has been pre-approved.') }}
            {{ __('You have 24 hours to confirm and pay for your reservation.') }}
        </div>

        @break

    @case(ReservationStatus::REJECTED)
        <div class="rounded-md p-4 bg-red-50 flex space-x-2 mt-6">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 text-red-400">
                <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd" />
            </svg>

            <span class="text-sm text-red-700">{{ __('The booking was rejected.') }}</span>
        </div>

        @break

    @case(ReservationStatus::CANCELLED)
        <div class="rounded-md p-4 bg-red-50 flex space-x-2 mt-6">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 text-red-400">
                <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd" />
            </svg>

            <span class="text-red-700 text-sm">
                {{ __('The reservation has been cancelled.') }}
                {{ __('You will receive a refund, if applicable.') }}
            </span>
        </div>

        @break

    @case(ReservationStatus::CONFIRMED)
        <a
            href="{{ route('reservation.delete', [$reservation]) }}"
            class="justify-center w-full mt-3 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
            {{ __('Cancel the booking') }}
        </a>

        <div class="rounded-md p-4 bg-green-50 flex space-x-2 mt-6">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 text-green-400">
                <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
            </svg>

            <span class="text-green-700 text-sm">{{ __('Reservation confirmed!') }}</span>
        </div>

        @break
@endswitch
