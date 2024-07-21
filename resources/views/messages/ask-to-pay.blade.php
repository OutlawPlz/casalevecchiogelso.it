<form action="{{ route('checkout') }}" method="POST" class="mt-1">
    @csrf
    <x-primary-button
        name="reservation"
        value="{{ $reservation->ulid }}">
        {{ __('Confirm booking') }}
    </x-primary-button>
</form>
