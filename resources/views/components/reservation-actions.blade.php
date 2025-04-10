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
        <span class="tracking-wider text-zinc-600 uppercase pl-1 text-xs">{{ $reservation->status }}</span>
    </div>

    <div class="text-zinc-600">
        {{ $reservation->check_in->format('d M') }} - {{ $reservation->check_out->format('d M') }} ({{ $reservation->nights }} {{ __('nights') }}) <br>
        {{ $reservation->guest_count }} {{ __('guests') }} • Tot. <span x-currency="{{ $reservation->tot }}"></span>
    </div>
</div>

@switch($reservation->status)
    @case(ReservationStatus::QUOTE)
        <div class="flex flex-col gap-2 mt-4">
            <button x-on:click.prevent="$dispatch('open-modal', 'reject')">
                {{ __('Reject') }}
            </button>

            <button
                class="primary"
                x-on:click.prevent="$dispatch('open-modal', 'pre-approve')"
            >
                {{ __('Pre-approve') }}
            </button>
        </div>

        <x-modal name="pre-approve" max-width="xl">
            <form
                action="{{ route('reservation.status', [$reservation]) }}"
                class="p-6"
                method="POST"
            >
                @csrf
                <div>
                    <h2 class="text-lg font-bold text-zinc-900">{{ __('Pre-approve') }}</h2>
                    <p class="mt-1 text-zinc-600">
                        {{ __('Do you want to pre-approve this booking?') }} <br>
                        {{ __('The guest will have 24 hours to confirm the reservation.') }}
                    </p>
                </div>

                <div class="mt-6 space-x-3 flex justify-end">
                    <button
                        class="ghost"
                        x-on:click="$dispatch('close')"
                        type="button"
                    >
                        {{ __('Close') }}
                    </button>

                    <button
                        class="primary"
                        value="{{ ReservationStatus::PENDING }}"
                        name="status"
                    >
                        {{ __('Pre-approve') }}
                    </button>
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
                <div>
                    <h2 class="text-lg font-bold text-zinc-900">{{ __('Reject') }}</h2>
                    <p class="mt-1 text-zinc-600">{{ __('Are you sure you want to decline this booking?') }}</p>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button
                        class="ghost"
                        x-on:click="$dispatch('close')"
                        type="button"
                    >
                        {{ __('Cancel') }}
                    </button>

                    <button
                        class="primary"
                        value="{{ ReservationStatus::REJECTED }}"
                        name="status"
                    >
                        {{ __('Reject') }}
                    </button>
                </div>
            </form>
        </x-modal>

        @break

    @case(ReservationStatus::CONFIRMED)
        <div class="flex flex-col gap-2 mt-4">
            <button
                href="{{ route('reservation.delete', [$reservation]) }}"
                class="primary w-full"
            >
                {{ __('Cancel the booking') }}
            </button>

            <button
                x-on:click.prevent="$dispatch('open-modal', 'refund')"
                class="primary w-full"
            >
                {{ __('Send money') }}
            </button>
        </div>

        <x-modal name="refund" max-width="md">
            <form
                class="p-6"
                x-on:submit.prevent="submit"
                x-data="{
                    loading: false,
                    errors: {},

                    async submit() {
                        this.loading = true;

                        const formData = new FormData(this.$root);

                        await axios.post('{{ route('refund.store', [$reservation]) }}', formData)
                            .then((response) => this.errors = {})
                            .catch((error) => {
                                if (error.response.status === 422) {
                                    return this.errors = error.response.data.errors;
                                }
                            });

                        this.loading = false;
                    },
                }"
            >
                <div class="mt-6 space-x-3 flex justify-end">
                    <button
                        class="ghost"
                        x-on:click="$dispatch('close')"
                        type="button"
                    >
                        {{ __('Close') }}
                    </button>

                    <button x-bind:disabled="loading" class="primary">
                        <x-spinner-loader x-show="loading" />
                        {{ __('Send money') }}
                    </button>
                </div>
            </form>
        </x-modal>

        @break
@endswitch
