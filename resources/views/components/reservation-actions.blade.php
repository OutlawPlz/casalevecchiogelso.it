@php
/**
 * @var App\Models\User $authUser
 * @var App\Models\Reservation $reservation
 */
@endphp

@use('\App\Enums\ReservationStatus')
@use('\Illuminate\Support\Str')

<x-field
    :id="Str::ulid()"
    label="{{ __('Amount') }}"
    name="amount"
    :required="false"
    help="Non preoccuparti, ti aiuto io..."
>
    <x-input type="checkbox" />
</x-field>

<div>
    <div>
        <span class="font-bold">{{ $reservation->user->name }}</span>
        <span class="tracking-wider text-gray-600 uppercase pl-1 text-xs">{{ $reservation->status }}</span>
    </div>

    <div class="text-gray-600">
        {{ $reservation->check_in->format('d M') }} - {{ $reservation->check_out->format('d M') }} ({{ $reservation->nights }} {{ __('nights') }}) <br>
        {{ $reservation->guest_count }} {{ __('guests') }} • Tot. <span x-currency="{{ $reservation->tot }}"></span>
    </div>
</div>

@switch($reservation->status)
    @case(ReservationStatus::QUOTE)
        <div class="flex flex-col gap-2 mt-4">
            <x-button
                x-on:click.prevent="$dispatch('open-modal', 'reject')"
                class="justify-center"
            >
                {{ __('Reject') }}
            </x-button>

            <x-button
                variant="primary"
                x-on:click.prevent="$dispatch('open-modal', 'pre-approve')"
                class="justify-center"
            >
                {{ __('Pre-approve') }}
            </x-button>
        </div>

        <x-modal name="pre-approve" max-width="xl">
            <form
                action="{{ route('reservation.status', [$reservation]) }}"
                class="p-6"
                method="POST"
            >
                @csrf
                <div>
                    <h2 class="text-lg font-bold text-gray-900">{{ __('Pre-approve') }}</h2>
                    <p class="mt-1 text-gray-600">
                        {{ __('Do you want to pre-approve this booking?') }} <br>
                        {{ __('The guest will have 24 hours to confirm the reservation.') }}
                    </p>
                </div>

                <div class="mt-6 space-x-3 flex justify-end">
                    <x-button
                        variant="ghost"
                        x-on:click="$dispatch('close')"
                        type="button"
                    >
                        {{ __('Close') }}
                    </x-button>

                    <x-button
                        variant="primary"
                        value="{{ ReservationStatus::PENDING }}"
                        name="status"
                    >
                        {{ __('Pre-approve') }}
                    </x-button>
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
                    <h2 class="text-lg font-bold text-gray-900">{{ __('Reject') }}</h2>
                    <p class="mt-1 text-gray-600">{{ __('Are you sure you want to decline this booking?') }}</p>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <x-button
                        variant="ghost"
                        x-on:click="$dispatch('close')"
                        type="button"
                    >
                        {{ __('Cancel') }}
                    </x-button>

                    <x-button
                        variant="primary"
                        value="{{ ReservationStatus::REJECTED }}"
                        name="status"
                    >
                        {{ __('Reject') }}
                    </x-button>
                </div>
            </form>
        </x-modal>

        @break

    @case(ReservationStatus::CONFIRMED)
        <div class="flex flex-col gap-2 mt-4">
            <x-button
                variant="primary"
                href="{{ route('reservation.delete', [$reservation]) }}"
                class="w-full"
            >
                {{ __('Cancel the booking') }}
            </x-button>

            <x-button
                variant="primary"
                x-on:click.prevent="$dispatch('open-modal', 'refund')"
                class="justify-center w-full"
            >
                {{ __('Send money') }}
            </x-button>
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
                <div>
                    <label>{{ __('Amount') }}</label>
                    <input type="number" name="amount" step=".01" min=".01">
                    <x-text-input step=".01" name="amount" />
                    <template x-if="errors.amount">
                        <div x-text="errors.amount[0]" class="text-sm text-red-600 mt-1"></div>
                    </template>
                </div>

                <div class="mt-6 space-x-3 flex justify-end">
                    <x-button
                        variant="ghost"
                        x-on:click="$dispatch('close')"
                        type="button"
                    >
                        {{ __('Close') }}
                    </x-button>

                    <x-button x-bind:disabled="loading" variant="primary">
                        <x-spinner-loader x-show="loading" />
                        {{ __('Send money') }}
                    </x-button>
                </div>
            </form>
        </x-modal>

        @break
@endswitch
