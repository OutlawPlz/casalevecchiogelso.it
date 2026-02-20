@php
    /**
     * @var \App\Models\Reservation $reservation
     * @var \App\Models\User $authUser
     */
    use function App\Helpers\date_format;
@endphp

<x-app-layout>
    <div
            class="absolute inset-0 flex bg-zinc-50"
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
                <h3 class="text-xl font-semibold">{{ __('Details') }}</h3>

                <x-ui-close x-on:click="isDetailsVisible = false"/>
            </div>

            @if($changeRequest)
                <div class="px-4">
                    <div class="py-6 border-b">
                        @include('reservation.partials.change-request')
                    </div>
                </div>
            @endif

            <div class="px-4">
                <div class="py-6 border-b">
                    @include('reservation.partials.host-actions')

                    <hr class="my-6">

                    @include('reservation.partials.guest-actions')
                </div>
            </div>

            <div class="px-4">
                <div class="py-6 border-b">
                    @include('reservation.partials.payment-info')
                </div>

                <div class="grid grid-cols-2 divide-x py-6 border-b">
                    <div>
                        <div class="font-semibold">{{ __('Check-in') }}</div>
                        <span
                                class="text-zinc-600"
                                x-date="'{{ $reservation->check_in->setTimeFromTimeString(config('reservation.check_in_time')) }}'"
                        ></span>
                    </div>

                    <div class="text-right">
                        <div class="font-semibold">{{ __('Check-out') }}</div>
                        <span
                                class="text-zinc-600"
                                x-date="'{{ $reservation->check_out->setTimeFromTimeString(config('reservation.check_out_time')) }}'"
                        ></span>
                    </div>
                </div>

                <div class="py-6 border-b">
                    <div class="font-semibold">{{ __('Who\'s coming') }}</div>
                    <span class="text-zinc-600">{{ $reservation->guest_count }} {{ __('guests') }}</span>
                </div>

                <div class="py-6 border-b">
                    <div class="font-semibold">{{ __('Reservation code') }}</div>
                    <span class="text-zinc-600 font-mono">{{ $reservation->ulid }}</span>
                </div>

                <div class="py-6 border-b prose-sm">
                    @include('reservation.partials.cancellation-policy')
                </div>
            </div>
        </aside>

        <x-chat :channel="$reservation->ulid"/>

        @host
        <aside
                x-cloak
                :class="{ 'block': isFeedVisible, 'hidden': ! isFeedVisible }"
                class="absolute right-0 z-10 inset-y-0 md:static hidden overflow-y-scroll w-11/12 md:w-1/4 shrink-0 bg-white shadow-lg"
        >
            <x-reservation-feed :$reservation/>
        </aside>
        @endhost
    </div>

    <x-modal name="chat-language" class="max-w-2xl p-6">
        <h3 class="text-2xl font-semibold">{{ __('Choose a language') }}</h3>

        <div
                class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3"
                x-on:change="$dispatch('translate-chat', $event.target.value)"
        >
            @foreach(App\Services\DeepL::languages() as $language)
                <label
                        class="has-checked:ring-zinc-700 ring-1 ring-transparent flex cursor-pointer items-center space-x-1 p-3 rounded-md hover:bg-zinc-100"
                >
                    <input class="hidden" type="radio" name="language" value="{{ $language['code'] }}">
                    <span>{{ $language['name'] }}</span>
                </label>
            @endforeach
        </div>
    </x-modal>
</x-app-layout>
