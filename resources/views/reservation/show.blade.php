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
            class="hidden absolute w-11/12 md:static z-10 h-full overflow-y-auto md:w-1/3 shrink-0 bg-white shadow-lg"
        >
            <div class="sticky top-0 bg-white flex items-center justify-between p-4 border-b">
                <h3 class="text-xl font-bold">{{ __('Details') }}</h3>

                <button x-on:click="isVisible = false" class="px-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
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

        <div class="w-full relative">
            <div class="py-4 bg-white flex space-x-4 px-4 sm:px-6 border-l shadow-sm">
                <h3 class="text-xl font-bold">{{ __('Chat') }}</h3>
                <button
                    x-on:click="isVisible = ! isVisible"
                    type="button"
                    class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 flex items-center space-x-1"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                        <path d="M5.75 7.5a.75.75 0 1 0 0 1.5.75.75 0 0 0 0-1.5ZM7.25 8.25A.75.75 0 0 1 8 7.5h2.25a.75.75 0 0 1 0 1.5H8a.75.75 0 0 1-.75-.75ZM5.75 9.5a.75.75 0 0 0 0 1.5H8a.75.75 0 0 0 0-1.5H5.75Z" />
                        <path fill-rule="evenodd" d="M4.75 1a.75.75 0 0 0-.75.75V3a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2V1.75a.75.75 0 0 0-1.5 0V3h-5V1.75A.75.75 0 0 0 4.75 1ZM3.5 7a1 1 0 0 1 1-1h7a1 1 0 0 1 1 1v4.5a1 1 0 0 1-1 1h-7a1 1 0 0 1-1-1V7Z" clip-rule="evenodd" />
                    </svg>

                    <span>{{ __('Reservation details') }}</span>
                </button>

                <button
                    x-on:click.prevent="$dispatch('translate-chat', navigator.language)"
                    type="button"
                    class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 flex items-center space-x-1"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                        <path fill-rule="evenodd" d="M11 5a.75.75 0 0 1 .688.452l3.25 7.5a.75.75 0 1 1-1.376.596L12.89 12H9.109l-.67 1.548a.75.75 0 1 1-1.377-.596l3.25-7.5A.75.75 0 0 1 11 5Zm-1.24 5.5h2.48L11 7.636 9.76 10.5ZM5 1a.75.75 0 0 1 .75.75v1.261a25.27 25.27 0 0 1 2.598.211.75.75 0 1 1-.2 1.487c-.22-.03-.44-.056-.662-.08A12.939 12.939 0 0 1 5.92 8.058c.237.304.488.595.752.873a.75.75 0 0 1-1.086 1.035A13.075 13.075 0 0 1 5 9.307a13.068 13.068 0 0 1-2.841 2.546.75.75 0 0 1-.827-1.252A11.566 11.566 0 0 0 4.08 8.057a12.991 12.991 0 0 1-.554-.938.75.75 0 1 1 1.323-.707c.049.09.099.181.15.271.388-.68.708-1.405.952-2.164a23.941 23.941 0 0 0-4.1.19.75.75 0 0 1-.2-1.487c.853-.114 1.72-.185 2.598-.211V1.75A.75.75 0 0 1 5 1Z" clip-rule="evenodd" />
                    </svg>

                    <span>{{ __('Translate chat') }}</span>
                </button>
            </div>

            <div class="absolute inset-0 mt-16 pb-2">
                <x-chat :channel="$reservation->ulid" />
            </div>
        </div>
    </div>
</x-app-layout>
