<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reservation') }}
        </h2>

        <form action="{{ route('checkout') }}" method="POST">
            @csrf
            <x-primary-button name="reservation" value="{{ $reservation->ulid }}">{{ __('Checkout') }}</x-primary-button>
        </form>
    </x-slot>

    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <x-chat :channel="$reservation->ulid" />
    </div>
</x-app-layout>
