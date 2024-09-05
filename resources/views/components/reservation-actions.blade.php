@php
/**
 * @var App\Models\User $authUser
 * @var App\Models\Reservation $reservation
 */
@endphp

@use('\App\Enums\ReservationStatus')

<div>
    <div>
        <span class="font-bold">{{ $reservation->user->name }}</span>
        <span class="tracking-wider text-gray-600 uppercase pl-1 text-xs">{{ $reservation->status }}</span>
    </div>

    <div class="text-gray-600">
        {{ $reservation->check_in->format('d M') }} - {{ $reservation->check_out->format('d M') }} ({{ $reservation->nights }} {{ __('nights') }}) <br>
        {{ $reservation->guest_count }} {{ __('guests') }} • Tot. @money($reservation->tot)
    </div>
</div>

@switch($reservation->status)
    @case(ReservationStatus::QUOTE)
        <div class="grid grid-cols-2 gap-4 mt-4">
            <x-secondary-button
                x-on:click.prevent="$dispatch('open-modal', 'reject')"
                class="justify-center"
            >
                {{ __('Reject') }}
            </x-secondary-button>

            <x-primary-button
                x-on:click.prevent="$dispatch('open-modal', 'pre-approve')"
                class="justify-center"
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
                    <x-secondary-button
                        x-on:click="$dispatch('close')"
                        type="button"
                        class="!text-sm"
                    >
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
                    <x-secondary-button
                        x-on:click="$dispatch('close')"
                        type="button"
                        class="!text-sm"
                    >
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

        @break

    @case(ReservationStatus::CONFIRMED)
        <x-primary-button
            x-on:click.prevent="$dispatch('open-modal', 'cancel')"
            class="justify-center w-full !text-sm mt-6"
        >
            {{ __('Cancel the booking') }}
        </x-primary-button>

        <x-modal name="cancel" max-width="xl">
            <form
                class="p-6"
                action="{{ route('reservation.status', [$reservation]) }}"
                method="POST"
            >
                @csrf

                <div class="prose">
                    <h2>{{ __('Cancel the booking') }}</h2>
                    <p>
                        {{ __('Are you sure you want to cancel the reservation?') }}
                    </p>
                </div>

                <div class="mt-6 space-x-3 flex justify-end">
                    <x-secondary-button
                        x-on:click="$dispatch('close')"
                        type="button"
                        class="!text-sm"
                    >
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
