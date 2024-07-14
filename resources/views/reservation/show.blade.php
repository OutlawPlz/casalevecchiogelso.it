<x-app-layout>
    <x-slot name="sidebar">
        <x-reservation-quote :reservation="$reservation" />

        <form action="{{ route('checkout') }}" method="POST">
            @csrf
            <x-primary-button
                class="w-full justify-center !text-sm"
                name="reservation"
                value="{{ $reservation->ulid }}">
                {{ __('Ask to pay') }}
            </x-primary-button>
        </form>
    </x-slot>

    <x-chat :channel="$reservation->ulid" />
</x-app-layout>
