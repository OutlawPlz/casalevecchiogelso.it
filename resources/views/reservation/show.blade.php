<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reservation') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <form action="{{ route('checkout') }}" method="POST">
                @csrf
                <x-primary-button name="reservation" value="{{ $reservation->ulid }}">{{ __('Checkout') }}</x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>
