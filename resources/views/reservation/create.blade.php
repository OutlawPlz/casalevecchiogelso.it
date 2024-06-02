<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <form action="{{ route('reservations.store') }}"
                  method="POST"
                  class="grid grid-cols-1 md:grid-cols-5 gap-6">
                @csrf

                <div class="bg-white p-6 shadow-sm space-y-6 sm:rounded-lg md:col-span-2" x-data="{
                    price: {{ $pricePerNight }},
                    cleaningFee: {{ $cleaningFee }},
                    period: ['{{ $checkIn }}', '{{ $checkOut }}'],

                    get nights() {
                        return differenceInDays(this.period[1], this.period[0])
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
                            <x-text-input type="number" name="guest_count" min="1" max="10" value="{{ $guestCount }}"/>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="underline" x-text="`230 € x ${nights} {{ __('nights') }}`"></span>
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

                <div class="md:col-span-3">
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label>{{ __('First name') }}</x-input-label>
                                <x-text-input name="first_name" required/>
                            </div>

                            <div>
                                <x-input-label>{{ __('Last name') }}</x-input-label>
                                <x-text-input name="last_name" required/>
                            </div>
                        </div>

                        <div>
                            <x-input-label>{{ __('Email') }}</x-input-label>
                            <x-text-input name="email" required/>
                        </div>

                        <div>
                            <x-input-label>{{ __('Phone') }}</x-input-label>
                            <x-text-input name="phone" required/>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
