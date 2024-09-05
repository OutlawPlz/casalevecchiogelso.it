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

                <button x-on:click="isDetailsVisible = false" class="p-1 text-gray-500 rounded-lg cursor-pointer hover:text-gray-900 hover:bg-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                        <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/>
                    </svg>
                </button>
            </div>

            <div class="px-4">
                <div class="py-6 border-b">
                    <div class="font-bold">{{ __('Price') }}</div>

                    <div class="mt-2 space-y-6 p-6 bg-gray-50 rounded-lg">
                        <div class="space-y-2">
                            @foreach($reservation->price_list as $line)
                            <div class="flex justify-between">
                                @if($loop->first)
                                <span class="underline">
                                    @money($line['unit_amount']) x {{ $line['quantity'] }} {{ __('nights') }}
                                </span>
                                <span>@money($line['quantity'] * $line['unit_amount'])</span>
                                @else
                                <span class="underline">
                                    {{ __($line['name']) }}
                                    @if($line['quantity'] > 1) x {{ $line['quantity'] }} @endif
                                </span>
                                <span>@money($line['unit_amount'] * $line['quantity'])</span>
                                @endif
                            </div>
                            @endforeach
                        </div>

                        <hr>

                        <div class="flex justify-between font-bold text-lg">
                            <span>Tot.</span>
                            <span x-text="$({{ $reservation->tot }})"></span>
                        </div>
                    </div>

                    <x-reservation-status class="mt-4" :$reservation :$authUser />
                </div>

                <div class="py-6 border-b">
                    <x-reservation-actions class="mt-4" :$reservation :$authUser />
                </div>

                <div class="grid grid-cols-2 divide-x py-6 border-b">
                    <div>
                        <div class="font-bold">{{ __('Check-in') }}</div>
                        <span class="text-gray-600">{{ $reservation->check_in->format('Y-m-d') }}</span>
                    </div>

                    <div class="text-right">
                        <div class="font-bold">{{ __('Check-out') }}</div>
                        <span class="text-gray-600">{{ $reservation->check_out->format('Y-m-d') }}</span>
                    </div>
                </div>

                <div class="py-6 border-b">
                    <div class="font-bold">{{ __('Who\'s coming') }}</div>
                    <span class="text-gray-600">{{ $reservation->guest_count }} {{ __('guests') }}</span>
                </div>

                <div class="py-6 border-b">
                    <div class="font-bold">{{ __('Reservation code') }}</div>
                    <span class="text-gray-600 font-mono">{{ $reservation->ulid }}</span>
                </div>

                <div class="py-6 border-b">
                    <div class="font-bold">{{ __('Cancellation policy') }}</div>
                    <p class="text-gray-600">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut at blandit sem. Nullam lobortis enim sit amet sem hendrerit, ut elementum lectus bibendum. Mauris quis lorem laoreet, porttitor arcu eu, pulvinar augue.</p>
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

    <x-modal name="chat-language">
        <div class="p-6">
            <h3 class="text-2xl font-bold">{{ __('Choose a language') }}</h3>

            <div
                class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3"
                x-on:change="$dispatch('translate-chat', $event.target.value)"
            >
                @foreach(App\Services\GoogleTranslate::languages() as $language)
                    <label class="has-[:checked]:ring-gray-700 ring-1 ring-transparent flex cursor-pointer items-center space-x-1 p-3 rounded-md hover:bg-gray-100">
                        <input class="hidden" type="radio" name="language" value="{{ $language['code'] }}">
                        <span>{{ $language['name'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </x-modal>
</x-app-layout>
