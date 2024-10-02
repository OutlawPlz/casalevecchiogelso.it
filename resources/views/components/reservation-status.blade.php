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

        <div class="prose">
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
        <x-primary-button
            x-on:click.prevent="$dispatch('open-modal', 'cancel')"
            class="justify-center w-full mt-6"
        >
            {{ __('Cancel the booking') }}
        </x-primary-button>

        <div class="rounded-md p-4 bg-green-50 flex space-x-2 mt-6">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 text-green-400">
                <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
            </svg>

            <span class="text-green-700 text-sm">{{ __('Reservation confirmed!') }}</span>
        </div>

        <x-modal name="cancel" max-width="xl">
            <form
                class="p-6"
                action="{{ route('reservation.status', [$reservation]) }}"
                method="POST"
            >
                @csrf

                <div class="prose">
                    <h2>Cancel the booking</h2>
                    <p>
                        {{ __('Are you sure you want to cancel your reservation?') }}
                        {{ __('You will receive a refund, if applicable.') }}
                        {{ __('Check the cancellation policy.') }}
                    </p>
                </div>

                <div class="mt-6 space-x-3 flex justify-end">
                    <x-secondary-button
                        x-on:click="$dispatch('close')"
                        type="button"
                    >
                        {{ __('Close') }}
                    </x-secondary-button>

                    <x-primary-button
                        value="{{ ReservationStatus::CANCELLED }}"
                        name="status"
                        class="justify-center"
                    >
                        {{ __('Cancel') }}
                    </x-primary-button>
                </div>
            </form>
        </x-modal>

        @break
@endswitch
