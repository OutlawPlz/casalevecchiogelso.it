@php
    /**
     * @var ?\App\Models\User $authUser
     * @var \App\Models\Reservation $reservation
     */
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-800 leading-tight">
            {{ __('Change request') }}
        </h2>
    </x-slot>

    <section class="max-w-3xl mx-auto p-4 md:p-6 space-y-8 md:space-y-12">
        <div>
            <h2 class="mt text-2xl">{{ __('What do you want to change?') }}</h2>

            <p class="mt-2 text-zinc-600">
                @if($authUser->isHost())
                    {{ __('Let the guest know why you need to change the reservation.') }}
                @endif
            </p>

            <form
                id="cancellation-form"
                action="{{ route('change_request.store', [$reservation]) }}"
                method="POST"
                class="mt-6"
            >
                @csrf

                <p>...</p>

                <div class="flex gap-1">
                    <button class="primary">{{ __('Confirm') }}</button>
                    <a
                        class="button ghost"
                        href="{{ route('reservation.show', [$reservation]) }}">
                        {{ __('Back') }}
                    </a>
                </div>
            </form>
        </div>
    </section>
</x-app-layout>
