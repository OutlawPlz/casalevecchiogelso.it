@php
    /**
     * @var \App\Models\User $authUser
     * @var \Illuminate\Pagination\Paginator $reservations
     * @var \App\Models\Reservation $reservation
     */
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Cancel reservation') }}
        </h2>
    </x-slot>

    <section class="max-w-3xl mx-auto p-4 md:p-6 space-y-8 md:space-y-12">
        <a href="{{ route('reservation.show', [$reservation]) }}" class="flex items-center space-x-1 text-gray-600">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>

            <span>{{ __('Back') }}</span>
        </a>

        <div>
            <h2 class="mt text-2xl">Why do you need to cancel?</h2>

            <p class="mt-2 text-gray-600">
                {{ __('We are sorry to see you cancel your reservation.') }}
                {{ __('If you like, leave us a message.') }}
            </p>

            <form
                id="cancellation-form"
                action="{{ route('reservation.destroy', [$reservation]) }}"
                method="POST"
                class="mt-6"
            >
                @csrf
                @method('DELETE')

                <x-textarea-input
                    class="max-w-lg"
                    placeholder="{{ __('Your message') }}..."
                    name="message"
                ></x-textarea-input>
            </form>

            <div class="mt-6">
            </div>
        </div>

        <div>
            <h2 class="mt text-2xl">{{ __('Confirm cancellation') }}</h2>

            <p class="mt-2 text-gray-600">
                {{ __('Your reservation will be cancelled immediately and you\'ll be refunded within few business days.') }}
            </p>

            <div class="mt-6 grid grid-cols-2 items-center max-w-sm" x-data>
                <div>
                    <span class="text-gray-600">You paid</span> <br>
                    <span class="text-2xl" x-currency="{{ $reservation->tot }}"></span>
                </div>

                <div>
                    <span class="text-gray-600">{{ __('Your refund') }}</span> <br>
                    <span class="text-2xl" x-currency="{{ $reservation->tot * $reservation->refundFactor() }}"></span>
                </div>
            </div>

            <h2 class="mt-6 text-lg">{{ __('Cancellation policy') }}</h2>

            <p class="mt-2 text-gray-600">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut at blandit sem. Nullam lobortis enim sit amet sem hendrerit, ut elementum lectus bibendum. Mauris quis lorem laoreet, porttitor arcu eu, pulvinar augue.</p>

            <x-primary-button class="mt-6" form="cancellation-form">
                {{ __('Confirm cancellation') }}
            </x-primary-button>
        </div>
    </section>
</x-app-layout>
