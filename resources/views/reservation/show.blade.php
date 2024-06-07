<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-8">
                <div class="md:col-span-2">
                    <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                        <form action="{{ route('reservation.update', [$reservation]) }}"
                              method="POST"
                              oninput="this.querySelector('#submit').classList.remove('hidden')">
                            @csrf
                            @method('PATCH')

                            <div class="mb-6">
                                <span class="text-2xl font-bold">250 €</span>
                                <span class="ms-2">{{ __('night') }}</span>
                            </div>

                            @include('reservation.partials.reservation-form')

                            <div class="flex justify-end mt-6 hidden" id="submit">
                                <x-primary-button>{{ __('Update Reservation') }}</x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="md:col-span-3">
                    <div class="grid grid-cols-1 gap-2 px-2">
                        <form action="{{ route('checkout.create') }}" method="POST">
                            @csrf
                            <x-primary-button name="id" value="{{ $reservation->ulid }}">{{ __('Confirm Reservation') }}</x-primary-button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
