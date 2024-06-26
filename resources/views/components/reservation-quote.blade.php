<div>
    <form
        x-on:input.debounce="submit"
        x-ref="form"
        class="space-y-6"
        x-data="{
            overnightStay: {{ $overnight_stay }},
            cleaningFee: {{ $cleaning_fee }},
            period: ['{{ $reservation->check_in }}', '{{ $reservation->check_out }}'],
            guestCount: {{ $reservation->guest_count}},
            loading: false,
            errors: {{ json_encode($errors->messages()) }},

            get nights() {
                if (! this.period[1] || ! this.period[0]) return 0;

                return differenceInDays(this.period[1], this.period[0]);
            },

            get tot() {
                return this.overnightStay * this.nights + this.cleaningFee
            },

            async submit() {
                this.loading = true;

                const formData = new FormData(this.$refs.form);

                await axios.post('{{ route('reservation.quote') }}', formData)
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
            <span class="text-3xl" x-text="$(overnightStay)"></span>
            <span> / {{ __('night') }}</span>
        </div>

        <div class="space-y-2">
            <div>
                <x-daterange-input
                    class="relative grid grid-cols-2 gap-4"
                    x-model="period"
                    x-bind:disabled="loading"
                    :unavailable="$unavailable_dates"
                />

                @foreach(['check_in', 'check_out', 'unavailable_dates'] as $key)
                <template x-if="errors.{{ $key }}">
                    <div x-text="errors.{{ $key }}[0]" class="text-sm text-red-600 mt-1"></div>
                </template>
                @endforeach
            </div>

            <div>
                <x-input-label>{{ __('Guests') }}</x-input-label>
                <x-text-input
                    type="number"
                    name="guest_count"
                    min="1"
                    max="10"
                    x-bind:disabled="loading"
                    x-model="guestCount"
                />

                <template x-if="errors.guest_count">
                    <div x-text="errors.guest_count[0]" class="text-sm text-red-600 mt-1"></div>
                </template>
            </div>
        </div>

        <div class="space-y-2">
            <div class="flex justify-between">
                <span class="underline" x-text="`${$(overnightStay)} x ${nights} {{ __('nights') }}`"></span>
                <span x-text="$(nights * overnightStay)"></span>
            </div>

            <div class="flex justify-between">
                <span class="underline">{{ __('Cleaning fee') }}</span>
                <span x-text="$(cleaningFee)"></span>
            </div>
        </div>

        <hr>

        <div class="flex justify-between font-bold text-lg">
            <span>Tot.</span>
            <span x-text="$(tot)"></span>
        </div>
    </form>

    <div class="mt-4">
        @guest()
        <x-primary-button
            x-data=""
            type="button"
            class="w-full justify-center !text-sm"
            x-on:click.prevent="$dispatch('open-modal', 'token-login')"
        >
            {{ __('Request to book') }}
        </x-primary-button>
        @endguest

        @auth()
        <form action="{{ route('reservation.store') }}" method="POST">
            <x-primary-button class="w-full justify-center !text-sm">
                @csrf
                {{ __('Request to book') }}
            </x-primary-button>
        </form>
        @endauth

        <p class="text-sm mt-2 text-center">{{ __('You won\'t be charged yet') }}</p>
    </div>

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

            {{-- TODO: Handle errors and move the code to new component. --}}

            <form
                class="mt-8"
                x-on:submit.prevent="submit"
                x-data="{
                    errors: {},

                    async submit() {
                        this.loading = true;

                        const formData = new FormData(this.$root);

                        await axios.post('/auth/token', formData)
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
                @csrf

                <div>
                    <x-text-input
                        class="rounded-b-none relative focus:z-10" type="text"
                        name="name" required placeholder="{{ __('Name') }}"
                    />
                    <x-text-input
                        class="rounded-t-none relative -top-px focus:z-10"
                        type="email" name="email" required
                        placeholder="{{ __('Email address') }}"
                    />
                </div>

                <div class="mt-2">
                    <x-primary-button
                        class="w-full justify-center">{{ __('Sign-in') }}</x-primary-button>
                </div>

                <div class="relative mt-4">
                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm font-medium leading-6">
                        <span class="bg-gray-50 px-4 text-gray-0">{{ __('Or continue with') }}</span>
                    </div>
                </div>

                <a
                    href="{{ route('social.redirect', ['google']) }}"
                    class="mt-4 flex w-full items-center justify-center gap-3 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus-visible:ring-transparent"
                >
                    <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24"><path d="M12.0003 4.75C13.7703 4.75 15.3553 5.36002 16.6053 6.54998L20.0303 3.125C17.9502 1.19 15.2353 0 12.0003 0C7.31028 0 3.25527 2.69 1.28027 6.60998L5.27028 9.70498C6.21525 6.86002 8.87028 4.75 12.0003 4.75Z" fill="#EA4335"/><path d="M23.49 12.275C23.49 11.49 23.415 10.73 23.3 10H12V14.51H18.47C18.18 15.99 17.34 17.25 16.08 18.1L19.945 21.1C22.2 19.01 23.49 15.92 23.49 12.275Z" fill="#4285F4"/><path d="M5.26498 14.2949C5.02498 13.5699 4.88501 12.7999 4.88501 11.9999C4.88501 11.1999 5.01998 10.4299 5.26498 9.7049L1.275 6.60986C0.46 8.22986 0 10.0599 0 11.9999C0 13.9399 0.46 15.7699 1.28 17.3899L5.26498 14.2949Z" fill="#FBBC05"/><path d="M12.0004 24.0001C15.2404 24.0001 17.9654 22.935 19.9454 21.095L16.0804 18.095C15.0054 18.82 13.6204 19.245 12.0004 19.245C8.8704 19.245 6.21537 17.135 5.2654 14.29L1.27539 17.385C3.25539 21.31 7.3104 24.0001 12.0004 24.0001Z" fill="#34A853"/></svg>
                    <span class="text-sm font-semibold leading-6">Google</span>
                </a>
            </form>
        </div>
    </x-modal>
    @endguest
</div>
