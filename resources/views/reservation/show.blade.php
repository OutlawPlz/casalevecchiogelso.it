<x-app-layout>
    <div
        class="absolute inset-0 flex"
        x-data="{
            isVisible: false,

            init() {
                this.isVisible = window.innerWidth > 768;
            }
        }"
    >
        <aside
            x-show="isVisible"
            :class="{ 'block': isVisible, 'hidden': ! isVisible }"
            class="absolute z-10 inset-y-0 md:static overflow-y-scroll hidden w-11/12 md:w-1/3 shrink-0 bg-white shadow-lg"
        >
            <div class="sticky top-0 bg-white flex items-center justify-between p-4 border-b">
                <h3 class="text-xl font-bold">{{ __('Details') }}</h3>

                <button x-on:click="isVisible = false" class="p-1 text-gray-500 rounded-lg cursor-pointer hover:text-gray-900 hover:bg-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                        <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                    </svg>
                </button>
            </div>

            <div class="px-4">
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
