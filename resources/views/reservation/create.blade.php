<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <form action="{{ route('reservation.store') }}"
                  method="POST"
                  class="grid grid-cols-1 md:grid-cols-5 gap-8">
                @csrf

                <div class="md:col-span-2">
                    <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                        @include('reservation.partials.create-sidebar-left')
                    </div>
                </div>

                <div class="md:col-span-3">
                    <div class="grid grid-cols-1 gap-2 px-2">
                        @if($errors)
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif

                        <div class="prose">
                            <h1>{{ __('You\'re one step away from booking!') }} 🥳</h1>
                            <p>{{ __('We need your name and email to proceed with the booking.') }} {{ __('If you have any questions or curiosities, this is the right time to ask!') }}
                                <span
                                    class="underline">{{ __('In this step you will not be charged anything.') }}</span>
                            </p>

                            <div class="space-y-2 mt-4">
                                @include('reservation.partials.guest-form')
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
