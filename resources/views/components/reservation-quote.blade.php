<div>
    <form
        action="{{ route('reservation.store') }}"
        method="POST"
        x-ref="form"
        class="space-y-6"
        x-data="{
            defaultOvernightStay: {{ Js::from(array_shift($priceList)) }},
            priceList: {{ Js::from($priceList) }},
            period: $persist([new Date().toJSON().slice(0, 10), new Date().toJSON().slice(0, 10)]).using(sessionStorage),
            guestCount: $persist(1).using(sessionStorage),
            loading: false,
            errors: {{ Js::from($errors->messages()) }},

            get nights() {
                if (! this.period[1] || ! this.period[0]) return 0;

                return differenceInDays(this.period[1], this.period[0]);
            },

            get tot() {
                const tot = this.priceList.reduce((partial, line) => partial + (line.unit_amount * line.quantity), 0);

                return this.defaultOvernightStay.unit_amount * this.nights + tot;
            },
        }"
    >
        <div>
            <span class="text-3xl" x-currency="defaultOvernightStay.unit_amount"></span>
            <span> / {{ __('night') }}</span>
        </div>

        <div class="space-y-3">
            <div>
                <x-daterange-input
                    class="relative grid grid-cols-2 gap-4"
                    x-model="period"
                    x-bind:disabled="loading"
                    :unavailable="$unavailable_dates"
                />

                <div class="mt-1 5">
                    @foreach(['check_in', 'check_out', 'unavailable_dates'] as $key)
                        <x-error-messages class="" messages="errors.{{ $key }}" />
                    @endforeach
                </div>
            </div>

            <x-field
                id="guest-count"
                name="guest_count"
                :label="__('Guests')"
            >
                <x-input
                    type="number"
                    min="1"
                    max="10"
                    x-bind:disabled="loading"
                    x-model="guestCount"
                />
            </x-field>
        </div>

        <div class="space-y-2">
            <div class="flex justify-between">
                <span class="underline">
                    <span x-currency="defaultOvernightStay.unit_amount"></span> x <span x-text="nights"></span> {{ __('nights') }}
                </span>
                <span x-currency="nights * defaultOvernightStay.unit_amount"></span>
            </div>

            <template x-for="(line, index) of priceList" :key="index">
                <div class="flex justify-between">
                    <span class="underline" x-text="line.name"></span>
                    <span x-currency="line.unit_amount * line.quantity"></span>
                </div>
            </template>
        </div>

        <hr>

        <div class="flex justify-between font-bold text-lg">
            <span>Tot.</span>
            <span x-currency="tot"></span>
        </div>

        <div class="mt-4">
            @csrf

            @guest()
            <button
                x-data=""
                type="button"
                class="primary w-full"
                x-on:click.prevent="$dispatch('open-modal', 'token-login')"
            >
                {{ __('Request to book') }}
            </button>
            @endguest

            @auth()
            <button class="primary w-full">
                {{ __('Request to book') }}
            </button>
            @endauth

            <p class="text-sm mt-2 text-center">{{ __('You won\'t be charged yet') }}</p>
        </div>
    </form>

    @guest()
    <x-modal name="token-login" max-width="sm">
        <div class="p-6">
            <div class="text-center">
                <h2 class="text-3xl font-semibold">{{ __('Sign-in') }}</h2>
                <p class="text-sm mt-4">
                    {{ __('In order to manage the booking we need to know your name and email.') }}
                    {{ __('Use the form below to sign-in.') }}
                </p>
            </div>

            <x-sign-in />
        </div>

        <x-ui-close
            x-on:click="$dispatch('close')"
            class="absolute top-0 right-0 mt-2 mr-2"
        />
    </x-modal>
    @endguest
</div>
