@php
    /**
     * @var \App\Models\User $authUser
     * @var \App\Models\Reservation $reservation
     * @var int $refundAmount
     */
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-800 leading-tight">
            {{ __('Cancel reservation') }}
        </h2>
    </x-slot>

    <section class="max-w-3xl mx-auto p-4 md:p-6 space-y-8 md:space-y-12">
        <a href="{{ route('reservation.show', [$reservation]) }}" class="flex items-center space-x-1 text-zinc-600">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>

            <span>{{ __('Back') }}</span>
        </a>

        <div>
            <h2 class="mt text-2xl">Why do you need to cancel?</h2>

            <p class="mt-2 text-zinc-600">
                @host
                {{ __('Let the guest know why you need to cancel the reservation.') }}
                @else
                {{ __('We are sorry to see you cancel your reservation.') }}
                {{ __('If you like, leave us a message.') }}
                @endhost
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
                    rows="3"
                ></x-textarea-input>
            </form>

            <div class="mt-6">
            </div>
        </div>

        <div>
            <h2 class="mt text-2xl">{{ __('Confirm cancellation') }}</h2>

            <p class="mt-2 text-zinc-600">
                @host
                {{ __('The reservation will be cancelled immediately and the guest will be refunded according with cancellation policy.') }}
                @else
                {{ __('Your reservation will be cancelled immediately and you\'ll be refunded within few business days, according with cancellation policy.') }}
                @endhost
            </p>

            <div class="mt-6 grid grid-cols-2 items-center max-w-sm" x-data>
                <div>
                    <span class="text-zinc-600">Paid</span> <br>
                    <span class="text-2xl" x-currency="{{ $reservation->tot }}"></span>
                </div>

                <div>
                    <span class="text-zinc-600">{{ __('Refund') }}</span> <br>
                    <span class="text-2xl" x-currency="{{ $refundAmount }}"></span>
                </div>
            </div>

            <h2 class="mt-6 text-lg">{{ __('Cancellation policy') }}</h2>

            <p class="mt-2 text-zinc-600">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut at blandit sem. Nullam lobortis enim sit amet sem hendrerit, ut elementum lectus bibendum. Mauris quis lorem laoreet, porttitor arcu eu, pulvinar augue.</p>

            <button class="primary mt-6" form="cancellation-form">
                {{ __('Confirm cancellation') }}
            </button>
        </div>
    </section>
</x-app-layout>
