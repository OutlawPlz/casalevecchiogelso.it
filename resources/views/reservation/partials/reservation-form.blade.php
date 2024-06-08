<form x-on:input.debounce="submit" x-ref="form" class="space-y-6" x-data="{
        price: {{ $overnight_stay['unit_amount'] / 100 }},
        cleaningFee: {{ $cleaning_fee['unit_amount'] / 100 }},
        period: ['{{ $reservation->check_in }}', '{{ $reservation->check_out }}'],
        guestCount: {{ $reservation->guest_count}},
        loading: false,
        errors: {},

        get nights() {
            if (! this.period[1] || ! this.period[0]) return 0;

            return differenceInDays(this.period[1], this.period[0]);
        },

        async submit() {
            this.loading = true;

            const formData = new FormData(this.$refs.form);

            await axios.post('{{ route('validate.dates') }}', formData)
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
        <span class="text-2xl font-bold" x-text="`${price} €`"></span>
        <span class="ms-2">{{ __('night') }}</span>
    </div>

    <div class="space-y-2">
        <div>
            <x-daterange-input class="relative grid grid-cols-2 gap-4"
                               x-model="period"
                               x-bind:disabled="loading"
                               :unavailable="$unavailable_dates"/>

            <template x-if="errors.check_in">
                <div x-text="errors.check_in[0]" class="text-sm text-red-600 mt-1"></div>
            </template>

            <template x-if="errors.check_out">
                <div x-text="errors.check_out[0]" class="text-sm text-red-600 mt-1"></div>
            </template>

            <template x-if="errors.unavailable">
                <div x-text="errors.unavailable[0]" class="text-sm text-red-600 mt-1"></div>
            </template>
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
            <span class="underline" x-text="`${price} € x ${nights} {{ __('nights') }}`"></span>
            <span x-text="`€ ${nights * price}`"></span>
        </div>

        <div class="flex justify-between">
            <span class="underline">{{ __('Cleaning fee') }}</span>
            <span x-text="`€ ${cleaningFee}`"></span>
        </div>
    </div>

    <hr>

    <div class="flex justify-between font-bold">
        <span>Tot.</span>
        <span x-text="`€ ${nights * price + cleaningFee}`"></span>
    </div>
</form>
