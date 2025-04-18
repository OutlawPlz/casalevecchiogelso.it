@php
/**
 * @var \App\Models\Reservation $reservation
 * @var \App\Models\User $authUser
 */
@endphp

<x-app-layout>
    <div
        class="absolute inset-0 flex"
        x-data="{
            isDetailsVisible: false,
            isFeedVisible: false,

            init() {
                this.isDetailsVisible = window.innerWidth > 768;
            }
        }"
    >
        <aside
            x-show="isDetailsVisible"
            :class="{ 'block': isDetailsVisible, 'hidden': ! isDetailsVisible }"
            class="absolute z-10 inset-y-0 md:static overflow-y-scroll hidden w-11/12 md:w-1/4 shrink-0 bg-white shadow-lg"
        >
            <div class="sticky top-0 bg-white flex items-center justify-between p-4 border-b">
                <h3 class="text-xl font-bold">{{ __('Details') }}</h3>

                <x-ui-close x-on:click="isDetailsVisible = false" />
            </div>

            <div class="px-4">
                <div class="py-6 border-b">
                    <div class="font-bold">{{ __('Price') }}</div>

                    <div class="mt-2 space-y-6 p-6 bg-zinc-50 rounded-lg">
                        <x-reservation-price :$reservation />
                    </div>

                    <x-reservation-status class="mt-4" :$reservation :$authUser />
                </div>

                @host
                <div class="py-6 border-b">
                    <x-reservation-actions class="mt-4" :$reservation :$authUser />
                </div>
                @endhost

                <div class="grid grid-cols-2 divide-x py-6 border-b">
                    <div>
                        <div class="font-bold">{{ __('Check-in') }}</div>
                        <span class="text-zinc-600" x-date="'{{ $reservation->check_in }}'"></span>
                    </div>

                    <div class="text-right">
                        <div class="font-bold">{{ __('Check-out') }}</div>
                        <span class="text-zinc-600" x-date="'{{ $reservation->check_out }}'"></span>
                    </div>
                </div>

                <div class="py-6 border-b">
                    <div class="font-bold">{{ __('Who\'s coming') }}</div>
                    <span class="text-zinc-600">{{ $reservation->guest_count }} {{ __('guests') }}</span>
                </div>

                <div class="py-6 border-b">
                    <div class="font-bold">{{ __('Reservation code') }}</div>
                    <span class="text-zinc-600 font-mono">{{ $reservation->ulid }}</span>
                </div>

                <div class="py-6 border-b">
                    <div class="font-bold">{{ __('Cancellation policy') }}</div>
                    <p class="text-zinc-600">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut at blandit sem. Nullam lobortis enim sit amet sem hendrerit, ut elementum lectus bibendum. Mauris quis lorem laoreet, porttitor arcu eu, pulvinar augue.</p>
                </div>
            </div>
        </aside>

        <x-chat :channel="$reservation->ulid" />

        @host
        <aside
            x-cloak
            :class="{ 'block': isFeedVisible, 'hidden': ! isFeedVisible }"
            class="absolute right-0 z-10 inset-y-0 md:static hidden overflow-y-scroll w-11/12 md:w-1/4 shrink-0 bg-white shadow-lg"
        >
            <x-reservation-feed :$reservation />
        </aside>
        @endhost
    </div>

    <x-modal name="chat-language" class="max-w-2xl p-6">
        <h3 class="text-2xl font-bold">{{ __('Choose a language') }}</h3>

        <div
            class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3"
            x-on:change="$dispatch('translate-chat', $event.target.value)"
        >
            @foreach(App\Services\GoogleTranslate::languages() as $language)
                <label class="has-checked:ring-zinc-700 ring-1 ring-transparent flex cursor-pointer items-center space-x-1 p-3 rounded-md hover:bg-zinc-100">
                    <input class="hidden" type="radio" name="language" value="{{ $language['code'] }}">
                    <span>{{ $language['name'] }}</span>
                </label>
            @endforeach
        </div>
    </x-modal>
</x-app-layout>
