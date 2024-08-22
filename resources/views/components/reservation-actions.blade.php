@php
/**
 * @var App\Models\User $authUser
 * @var App\Models\Reservation $reservation
 */
@endphp

@use('\App\Enums\ReservationStatus')

@switch($reservation->status)
    @case(ReservationStatus::QUOTE)
        @if($authUser->isHost())
        <div class="grid grid-cols-2 gap-4 mt-4">
            <x-secondary-button
                x-on:click.prevent="$dispatch('open-modal', 'reject')"
                class="justify-center !text-sm"
            >
                {{ __('Reject') }}
            </x-secondary-button>

            <x-primary-button
                x-on:click.prevent="$dispatch('open-modal', 'pre-approve')"
                class="justify-center !text-sm"
            >
                {{ __('Pre-approve') }}
            </x-primary-button>
        </div>

        <x-modal name="pre-approve" max-width="xl">
            <form
                action="{{ route('reservation.status', [$reservation]) }}"
                class="p-6"
                method="POST"
            >
                @csrf
                <div class="prose">
                    <h2>{{ __('Pre-approve') }}</h2>
                    <p>
                        {{ __('Do you want to pre-approve this booking?') }} <br>
                        {{ __('The guest will have 24 hours to confirm the reservation.') }}
                    </p>
                </div>

                <div class="mt-6 space-x-3 flex justify-end">
                    <x-secondary-button type="button" class="!text-sm">
                        {{ __('Close') }}
                    </x-secondary-button>

                    <x-primary-button
                        value="{{ ReservationStatus::PENDING }}"
                        name="status"
                        class="!text-sm"
                    >
                        {{ __('Pre-approve') }}
                    </x-primary-button>
                </div>
            </form>
        </x-modal>

        <x-modal name="reject" max-width="xl">
            <form
                action="{{ route('reservation.status', [$reservation]) }}"
                class="p-6"
                method="POST"
            >
                @csrf
                <div class="prose">
                    <h2>{{ __('Reject') }}</h2>
                    <p>{{ __('Are you sure you want to decline this booking?') }}</p>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <x-secondary-button type="button" class="!text-sm">
                        {{ __('Cancel') }}
                    </x-secondary-button>

                    <x-primary-button
                        value="{{ ReservationStatus::REJECTED }}"
                        name="status"
                        class="!text-sm"
                    >
                        {{ __('Reject') }}
                    </x-primary-button>
                </div>
            </form>
        </x-modal>
        @endif

        @break

    @case(ReservationStatus::PENDING)
        @if($authUser->isGuest())
        <form action="{{ route('checkout') }}" method="POST" class="mt-4">
            @csrf
            <x-primary-button
                value="{{ $reservation->ulid }}"
                name="reservation"
                class="!text-sm w-full justify-center"
            >{{ __('Confirm and pay') }}</x-primary-button>
        </form>

        <div class="prose">
            {{ __('Your booking has been pre-approved.') }}
            {{ __('You have 24 hours to confirm and pay for your reservation.') }}
        </div>
        @endif

        @if($authUser->isHost())
        <div class="bg-blue-50 flex space-x-2 mt-6 p-4 rounded-md">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 text-blue-400">
                <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
            </svg>

            <span class="text-sm text-blue-700">{{ __('Waiting for the guest\'s confirmation.') }}</span>
        </div>
        @endif

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
            class="justify-center w-full !text-sm mt-6"
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
                    <x-secondary-button type="button" class="!text-sm">
                        {{ __('Close') }}
                    </x-secondary-button>

                    <x-primary-button
                        value="{{ ReservationStatus::CANCELLED }}"
                        name="status"
                        class="justify-center !text-sm"
                    >
                        {{ __('Cancel') }}
                    </x-primary-button>
                </div>
            </form>
        </x-modal>

        @break
@endswitch
