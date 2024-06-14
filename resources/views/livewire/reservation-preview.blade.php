<?php

use App\Models\Reservation;
use App\Services\Calendar;
use App\Services\Price;
use Illuminate\Http\Request;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;

new class extends Component {
    public string $check_in = '';

    public string $check_out = '';

    public int $guest_count = 1;

    public array $unavailable_dates = [];

    public ?int $overnight_stay;

    public ?int $cleaning_fee;

    public function mount(Request $request, Calendar $calendar, Price $price): void
    {
//        foreach (['check_in', 'check_out', 'guest_count'] as $key) {
//            $this->$key = $request->session()->get($key);
//        }

        $this->unavailable_dates = $calendar->unavailableDates();

        foreach (['overnight_stay', 'cleaning_fee'] as $key) {
            $this->$key = $price->get(config("reservation.$key"))['unit_amount'];
        }
    }

    public function validateDates(): void
    {
        //
    }

    #[Computed]
    public function nights(): int
    {
        //
    }

    #[Computed]
    public function tot(): int
    {
        return 57000;
    }

    #[Computed]
    public function reservation(): Reservation
    {
        return Reservation::fromSession();
    }
}; ?>

<div>
    <form class="space-y-6">
        <div>
            <span class="text-3xl">{{ Price::format($overnight_stay) }}</span> / night
        </div>

        <div class="space-y-2">
            <div>
                <x-daterange-input class="relative grid grid-cols-2 gap-4"
                                   :unavailable="$unavailable_dates"/>
            </div>

            <div>
                <x-input-label>{{ __('Guests') }}</x-input-label>
                <x-text-input type="number"
                              wire:model.live="guest_count"
                              min="1"/>
            </div>
        </div>

        <div class="space-y-2">
            <div class="flex justify-between">
                <span class="underline">{{ Price::format($overnight_stay) }} x 2 {{ __('nights') }}</span>
                <span>{{ Price::format($overnight_stay * 2) }}</span>
            </div>

            <div class="flex justify-between">
                <span class="underline">{{ __('Cleaning fee') }}</span>
                <span>{{ Price::format($cleaning_fee) }}</span>
            </div>
        </div>

        <hr>

        <div class="flex justify-between font-bold">
            <span>Tot.</span>
            <span>{{ Price::format($this->tot()) }}</span>
        </div>
    </form>
</div>
