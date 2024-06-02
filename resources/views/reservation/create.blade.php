<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <form action="{{ route('reservations.store') }}"
                  method="POST"
                  class="grid grid-cols-1 md:grid-cols-5 gap-6">
                @csrf

                <div class="bg-white p-6 shadow-sm space-y-8 sm:rounded-lg md:col-span-2">
                    <div>
                        <span class="text-2xl font-bold">230 €</span>
                        <span class="ms-2">{{ __('night') }}</span>
                    </div>

                    <div class="space-y-2">
                        <x-daterange-input class="grid grid-cols-2 gap-4"
                                           start="{{ $checkIn }}"
                                           end="{{ $checkOut }}"
                                           :unavailable="$unavailableDates"/>

                        <div>
                            <x-input-label>{{ __('Guests') }}</x-input-label>
                            <x-text-input type="number" name="guest_count" min="1" max="10" value="{{ $guestCount }}"/>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="underline">230 € x 7 {{ __('nights') }}</span>
                            <span>€ 1610</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="underline">{{ __('Cleaning fee') }}</span>
                            <span>€ 50</span>
                        </div>
                    </div>

                    <hr>

                    <div class="flex justify-between font-bold">
                        <span>Tot.</span>
                        <span>€ 1660</span>
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
