<div>
    <form
        x-on:submit.prevent="submit"
        class="space-y-6"
        x-data="{
            overnightStay: {{ Js::from(array_shift($priceList)) }},
            priceList: {{ Js::from($priceList) }},
            period: $persist([
                new Date().toJSON().slice(0, 10),
                new Date().toJSON().slice(0, 10)
            ]).using(sessionStorage),
            guestCount: $persist(1).using(sessionStorage),
            loading: false,
            errors: {},

            get nights() {
                if (! this.period[1] || ! this.period[0]) return 0;

                return differenceInDays(this.period[1], this.period[0]);
            },

            get tot() {
                const tot = this.priceList.reduce((partial, line) => partial + (line.unit_amount * line.quantity), 0);

                return this.overnightStay.unit_amount * this.nights + tot;
            },

            async submit() {
                this.loading = true;

                const formData = new FormData($root);

                await axios
                    .post('{{ route('reservation.store') }}', formData)
                    .then((response) => window.location = response.data.redirect)
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
            <span class="text-3xl" x-money="overnightStay.unit_amount"></span>
            <span> / {{ __('night') }}</span>
        </div>

        <div class="space-y-3">
            <div>
                <x-daterange-input
                    class="relative grid grid-cols-2 gap-4"
                    x-model="period"
                    x-bind:disabled="loading"
                    :unavailable="$unavailable_dates"
                    :jserror="['errors.check_in', 'errors.check_out']"
                />

                @foreach(['check_in', 'check_out', 'unavailable_dates'] as $key)
                    <x-error-messages jserror="errors.{{ $key }}" />
                @endforeach
            </div>

            <x-field
                id="guest-count"
                jserror="errors.guest_count"
                :label="__('Guests')"
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
        </div>

        <div class="space-y-2">
            <div class="flex justify-between">
                <span class="underline">
                    <span x-money="overnightStay.unit_amount"></span> x <span x-text="nights"></span> {{ __('nights') }}
                </span>
                <span x-money="nights * overnightStay.unit_amount"></span>
            </div>

            <template x-for="(line, index) of priceList" :key="index">
                <div class="flex justify-between">
                    <span class="underline" x-text="line.name"></span>
                    <span x-money="line.unit_amount * line.quantity"></span>
                </div>
            </template>
        </div>

        <hr>

        <div class="flex justify-between font-bold text-lg">
            <span>Tot.</span>
            <span x-money="tot"></span>
        </div>

        <div class="mt-4">
            @csrf

            <button
                @guest()
                x-data=""
                type="button"
                x-on:click.prevent="$dispatch('open-modal', 'token-login')"
                @endguest
                class="primary w-full"
            >
                <x-loading x-show="loading" x-cloak />
                {{ __('Request to book') }}
            </button>

            <p class="text-sm mt-2 text-center">{{ __('You won\'t be charged yet') }}</p>
        </div>
    </form>

    @guest()
    <x-modal name="token-login" class="max-w-sm p-6">
        <div class="text-center">
            <h2 class="text-3xl font-semibold">{{ __('Sign-in') }}</h2>
            <p class="text-sm mt-4">
                {{ __('In order to manage the booking we need to know your name and email.') }}
                {{ __('Use the form below to sign-in.') }}
            </p>
        </div>

        <x-sign-in />
    </x-modal>
    @endguest
</div>
