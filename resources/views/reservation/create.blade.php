<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="md:col-span-1">
                    <div class="bg-white p-6 shadow sm:rounded-lg">
                        @include('reservation.partials.reservation-form')

                        @auth
                        <div class="mt-6">
                            <x-primary-button class="w-full justify-center !text-sm">{{ __('Request to book') }}</x-primary-button>
                        </div>
                        @endauth
                    </div>
                </div>

                <div class="md:col-span-2">
                    <div class="grid grid-cols-1 gap-2 px-2">
                        <div class="space-y-4">
                            @guest
                            @include('reservation.partials.login-form')
                            @endguest

                            @auth
                            <div class="flex justify-end">
                                <div class="shadow rounded-lg bg-white prose px-5 py-3">
                                    <h3>Ciao {{ Auth::user()->name }}! 👋🏼 {{ __('I\'m Matteo') }}</h3>

                                    <p>
                                        {{ __('The selected dates appear available.') }}
                                        {{ __('If you have any questions or curiosities don\'t hesitate to ask!') }}
                                        {{ __('Send us a message by clicking the "Request to book" button.') }}
                                        {{ __('We will check the availability of the dates and confirm the booking.') }}
                                        <span class="underline">{{ __('You won\'t be charged yet.') }}</span>
                                    </p>
                                </div>
                            </div>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
