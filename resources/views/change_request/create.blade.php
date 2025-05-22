@php
    /**
     * @var ?\App\Models\User $authUser
     * @var \App\Models\Reservation $reservation
     * @var string[] $unavailable
     * @var float $refundFactor
     */
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-800 leading-tight">
            {{ __('Change request') }}
        </h2>
    </x-slot>

    <section
        x-data="{
            period: [
                '{{ $reservation->check_in->format('Y-m-d') }}',
                '{{ $reservation->check_out->format('Y-m-d') }}',
            ],
            priceList: {{ Js::encode($reservation->price_list) }},
            guestCount: {{ $reservation->guest_count }},
            errors: {},
            loading: false,

            get overnightStay() {
                return this.priceList.find((line) => this.isOvernightStay(line));
            },

            init() {
                $watch('period', (period) => {
                    const nights = differenceInDays(period[1], period[0]);

                    this.overnightStay.quantity = nights;
                });
            },

            get tot() {
                return this.priceList.reduce((partial, line) => partial + (line.unit_amount * line.quantity), 0);
            },

            isOvernightStay(line) {
                return line.product === '{{ config('reservation.overnight_stay') }}';
            },

            async submit() {
                this.loading = true;

                const formData = new FormData($refs.form);

                await axios
                    .post('{{ route('change_request.store', [$reservation]) }}', formData)
                    .then((response) => window.location = response.data.redirect)
                    .catch((error) => {
                        if (error.response.status === 422) {
                            return this.errors = error.response.data.errors;
                        }
                    });

                this.loading = false;
            }
        }"
        class="grid gap-16 mx-auto mt-6 md:grid-cols-5 md:max-w-5xl"
    >
        <div class="lg:col-span-3">
            <h2 class="mt text-2xl">{{ __('What do you want to change?') }}</h2>

            <p class="mt-2 text-zinc-600">
                @host
                    {{ __('Let the guest know why you need to change the reservation.') }}
                @else
                    {{ __('Let the host know the reason for your change.') }}
                @endhost
            </p>

            <form class="mt-6" x-ref="form" x-on:submit.prevent="submit" id="change_request_form">
                <x-field
                    id="reason"
                    jserror="errors.reason"
                    :label="__('Reason')"
                >
                    <x-textarea
                        name="reason"
                        placeholder="{{ __('Hello, I\'d like to change the reservation...') }}"
                    />
                </x-field>

                <div class="mt-6">
                    <span class="text-3xl" x-money="overnightStay.unit_amount"></span>
                    <span> / {{ __('night') }}</span>
                </div>

                <x-daterange-input
                    :value="[$reservation->check_in, $reservation->check_out]"
                    :disabled="$reservation->inProgress() ? ['check_in'] : []"
                    :$unavailable
                    x-model="period"
                    class="flex gap-3 mt-6"
                />

                <x-field
                    id="guest-count"
                    :label="__('Guests')"
                    class="mt-3"
                >
                    <x-input
                        name="guest_count"
                        type="number"
                        min="1"
                        max="10"
                        x-bind:disabled="loading"
                        x-model="guestCount"
                    />
                </x-field>

                <div class="space-y-2 mt-6">
                    <template x-for="line of priceList" :key="line.product">
                        <div>
                            <template x-if="isOvernightStay(line)">
                                <div class="flex justify-between">
                                    <div class="underline">
                                        <span x-money="line.unit_amount"></span> x <span
                                            x-text="line.quantity"
                                        ></span> {{ __('nights') }}
                                    </div>

                                    <div x-money="line.quantity * line.unit_amount"></div>
                                </div>
                            </template>

                            <template x-if="! isOvernightStay(line)">
                                <div class="flex justify-between">
                                    <span class="underline" x-text="line.name"></span>
                                    <span x-money="line.unit_amount * line.quantity"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <hr class="my-6">

                <div class="flex justify-between font-bold text-lg">
                    <span>Tot.</span>
                    <span x-money="tot"></span>
                </div>
            </form>
        </div>

        <div class="lg:col-span-2">
            <div class="border rounded-lg p-6 bg-white shadow-lg">
                <div>
                    <div>
                        <span class="font-bold">{{ $reservation->user->name }}</span>
                        <span class="tracking-wider text-zinc-600 uppercase pl-1 text-xs">{{ $reservation->status }}</span>
                    </div>

                    <div class="text-zinc-600">
                        {{ $reservation->check_in->format('d M') }} - {{ $reservation->check_out->format('d M') }} ({{ $reservation->nights }} {{ __('nights') }}) <br>
                        {{ $reservation->guest_count }} {{ __('guests') }} • Tot. <span x-money="{{ $reservation->tot }}"></span>
                    </div>
                </div>

                <div class="flex justify-between mt-6">
                    <span class="underline">{{ __('Original price') }}</span>
                    <span x-money="{{ $reservation->tot }}"></span>
                </div>

                <div class="flex justify-between mt-2">
                    <span class="underline">{{ __('New price') }}</span>
                    <span x-money="tot"></span>
                </div>

                <hr class="my-6">

                <div class="flex justify-between font-bold text-lg">
                    <span>{{ __('Price difference') }}</span>
                    <span x-money="tot - {{ $reservation->tot }}"></span>
                </div>

                @if($reservation->hasBeenPaid())
                    <template x-if="(tot - {{ $reservation->tot }}) < 0">
                        <div class="mt-2">
                            <div class="flex justify-between">
                                <span>{{ __('Refund') }}</span>
                                <span x-money="(tot - {{ $reservation->tot }}) * -{{ $refundFactor }}"></span>
                            </div>

                            <p class="help-message mt-2">{{ __('According to cancellation policy, you\'ll receive the specified refund amount.' ) }}</p>
                        </div>
                    </template>
                @endif

                <button class="primary w-full mt-6" form="change_request_form">
                    <x-loading x-show="loading" />
                    {{ __('Request to change') }}
                </button>
            </div>
        </div>
    </section>
</x-app-layout>
