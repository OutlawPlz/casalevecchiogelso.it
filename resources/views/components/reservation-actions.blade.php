@php
/**
 * @var App\Models\User $authUser
 * @var App\Models\Reservation $reservation
 */
@endphp

@use('\App\Enums\ReservationStatus')

<div {{ $attributes }}>
@switch($reservation->status)
    @case(ReservationStatus::QUOTE)
        @if($authUser->isHost())
        <div class="grid grid-cols-2 gap-4">
            <x-secondary-button
                type="button"
                x-on:click.prevent="$dispatch('open-modal', 'reject')"
                class="justify-center !text-sm"
            >
                {{ __('Reject') }}
            </x-secondary-button>

            <x-primary-button
                type="button"
                x-on:click.prevent="$dispatch('open-modal', 'pre-approve')"
                class="justify-center !text-sm"
            >
                {{ __('Pre-approve') }}
            </x-primary-button>
        </div>

        <x-modal name="pre-approve">
            <form action="" class="p-6" method="POST">
                @csrf
                <x-secondary-button type="button">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-primary-button>
                    {{ __('Pre-approve') }}
                </x-primary-button>
            </form>
        </x-modal>

        <x-modal name="reject">
            <div class="p-6"></div>
        </x-modal>
        @endif
    @break

    @case(ReservationStatus::PENDING)
        @if($authUser->isGuest())
        <x-primary-button class="justify-center !text-sm">{{ __('Confirm and Pay') }}</x-primary-button>
        @endif
    @break
@endswitch
</div>
