<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Chat') }}
        </h2>
    </x-slot>

    <div class="flex w-full" x-data="{ isVisible: true }">
        <aside x-show="isVisible" class="w-1/3 shrink-0 hidden md:block">
            <div class="bg-white shadow-lg p-6 h-full">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold">{{ __('Reservation details') }}</h3>

                    <button x-on:click="isVisible = false">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <hr class="-mx-6 my-6">

                <div class="grid grid-cols-2 divide-x">
                    <div>
                        <span class="font-bold">{{ __('Check-in') }}</span> <br>
                        <span class="text-gray-600">{{ $reservation->check_in->format('Y-m-d') }}</span>
                    </div>

                    <div class="text-right">
                        <span class="font-bold">{{ __('Check-out') }}</span> <br>
                        <span class="text-gray-600">{{ $reservation->check_out->format('Y-m-d') }}</span>
                    </div>
                </div>

                <hr class="my-6">

                <div>
                    <span class="font-bold">{{ __('Who\'s coming') }}</span> <br>
                    <span class="text-gray-600">{{ $reservation->guest_count }} {{ __('guests') }}</span>
                </div>

                <hr class="my-6">

                <div>
                    <span class="font-bold">{{ __('Reservation code') }}</span> <br>
                    <span class="text-gray-600 font-mono">{{ $reservation->ulid }}</span>
                </div>

                <hr class="my-6">

                <div>
                    <span class="font-bold">{{ __('Cancellation policy') }}</span> <br>
                    <span class="text-gray-600">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut at blandit sem. Nullam lobortis enim sit amet sem hendrerit, ut elementum lectus bibendum. Mauris quis lorem laoreet, porttitor arcu eu, pulvinar augue.</span>
                </div>
            </div>
        </aside>

        <x-chat :channel="$reservation->ulid" />
    </div>
</x-app-layout>
