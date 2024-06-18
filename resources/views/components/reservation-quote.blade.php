<form x-on:input.debounce="submit" x-ref="form" class="space-y-6" x-data="{
        overnightStay: {{ $overnight_stay }},
        cleaningFee: {{ $cleaning_fee }},
        period: ['{{ $reservation->check_in }}', '{{ $reservation->check_out }}'],
        guestCount: {{ $reservation->guest_count}},
        loading: false,
        errors: {},

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
    }">
    <div>
        <span class="text-3xl" x-text="$(overnightStay)"></span>
        <span> / {{ __('night') }}</span>
    </div>

    <div class="space-y-2">
        <div>
            <x-daterange-input class="relative grid grid-cols-2 gap-4"
                               x-model="period"
                               x-bind:disabled="loading"
                               :unavailable="$unavailable_dates"/>

            @foreach(['check_in', 'check_out', 'unavailable_dates'] as $key)
                <template x-if="errors.{{ $key }}">
                    <div x-text="errors.{{ $key }}[0]" class="text-sm text-red-600 mt-1"></div>
                </template>
            @endforeach
        </div>

        <div>
            <x-input-label>{{ __('Guests') }}</x-input-label>
            <x-text-input type="number"
                          name="guest_count"
                          min="1"
                          x-bind:disabled="loading"
                          x-model="guestCount"/>

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

    <div>
        <x-primary-button class="w-full justify-center !text-sm">{{ __('Request to book') }}</x-primary-button>
        <p class="text-sm mt-2 text-center">{{ __('You won\'t be charged yet') }}</p>
    </div>

</form>
