<div class="space-y-6" x-data="{
        price: 250,
        cleaningFee: 70,
        period: ['{{ $reservation->check_in }}', '{{ $reservation->check_out }}'],

        get nights() {
            return differenceInDays(this.period[1], this.period[0]);
        }
    }">
    <div class="space-y-2">
        <x-daterange-input class="relative grid grid-cols-2 gap-4"
                           x-model="period"
                           :unavailable="$unavailableDates"/>
        <x-input-error :messages="$errors->get('check_in')" class="mt-2" />
        <x-input-error :messages="$errors->get('check_out')" class="mt-2" />
        <x-input-error :messages="$errors->get('unavailableDates')" class="mt-2" />

        <div>
            <x-input-label>{{ __('Guests') }}</x-input-label>
            <x-text-input type="number" name="guest_count" min="1" max="10" value="{{ $reservation->guest_count }}"/>
            <x-input-error :messages="$errors->get('guest_count')" class="mt-2" />
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
