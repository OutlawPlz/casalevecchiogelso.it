<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('reservations.create') }}" class="flex space-x-4">
                        <x-daterange-input class="flex space-x-4" :unavailable="$unavailableDates" />

                        <div>
                            <x-input-label>{{ __('Guests') }}</x-input-label>
                            <x-text-input type="number" name="guest_count" min="1" max="10" value="1" />
                        </div>

                        <div>
                            <x-primary-button>{{ __('Reserve') }}</x-primary-button>
                        </div>
                    </form>

                    @if($errors->any())
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
