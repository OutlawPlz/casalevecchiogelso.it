<div class="space-y-6" x-data="{
        price: {{ $overnightStay['unit_amount'] / 100 }},
        cleaningFee: {{ $cleaningFee['unit_amount'] / 100 }},
        period: ['{{ $reservation->check_in }}', '{{ $reservation->check_out }}'],

        get nights() {
            if (! this.period[1] || ! this.period[0]) return 0;

            return differenceInDays(this.period[1], this.period[0]);
        }
    }">
    <div>
        <span class="text-2xl font-bold" x-text="`${price} €`"></span>
        <span class="ms-2">{{ __('night') }}</span>
    </div>

    <div class="space-y-2">
        <x-daterange-input class="relative grid grid-cols-2 gap-4"
                           x-model="period"
                           :unavailable="$unavailableDates"/>

        <div>
            <x-input-label>{{ __('Guests') }}</x-input-label>
            <x-text-input type="number" name="guest_count" min="1" max="10" value="{{ $reservation->guest_count }}"/>
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
</div>
