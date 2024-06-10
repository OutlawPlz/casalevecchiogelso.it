<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-8">
                <div class="md:col-span-2">
                    <div class="bg-white p-6 shadow sm:rounded-lg">
                        @include('reservation.partials.reservation-form')

                        @auth
                        <div class="mt-6">
                            <x-primary-button class="w-full justify-center !text-sm">{{ __('Request to book') }}</x-primary-button>
                            <p class="text-center mt-2 text-sm">{{ __('You won\'t be charged yet') }}</p>
                        </div>
                        @endauth
                    </div>
                </div>

                <div class="md:col-span-3">
                    <div class="grid grid-cols-1 gap-2 px-2">
                        <div class="space-y-4">
                            @guest
                            @include('reservation.partials.login-form')
                            @endguest

                            @auth
                            <div class="flex justify-end">
                                <div class="shadow rounded-lg bg-white prose px-5 py-3 max-w-[90%]">
                                    <h3>Ciao {{ Auth::user()->name }}! 👋🏼</h3>

                                    <p>
                                        {{ __('If you have any questions or curiosities don\'t hesitate to ask!') }}
                                        {{ __('Send us a message or click the "Request to book" button.') }}
                                        {{ __('We will check the availability of the dates and confirm the booking.') }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex space-x-2 max-w-[90%]">
                                <x-textarea-input name="message"
                                                  rows="1"
                                          class="bg-white grow"
                                          spellcheck="true"
                                          placeholder="{{ __('Hello! I would like to book the selected dates.') }}"></x-textarea-input>

                                <x-primary-button class="place-self-end !px-3 !py-2.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                                        <path d="M3.105 2.288a.75.75 0 0 0-.826.95l1.414 4.926A1.5 1.5 0 0 0 5.135 9.25h6.115a.75.75 0 0 1 0 1.5H5.135a1.5 1.5 0 0 0-1.442 1.086l-1.414 4.926a.75.75 0 0 0 .826.95 28.897 28.897 0 0 0 15.293-7.155.75.75 0 0 0 0-1.114A28.897 28.897 0 0 0 3.105 2.288Z" />
                                    </svg>
                                </x-primary-button>
                            </div>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
