<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <form action="{{ route('reservation.store') }}"
                  method="POST"
                  class="grid grid-cols-1 md:grid-cols-5 gap-8">
                @csrf

                <div class="md:col-span-2">
                    <div class="bg-white p-6 shadow sm:rounded-lg">
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

                        <div class="space-y-4">
                            <div class="flex justify-end">
                                <div class="shadow rounded-lg bg-white prose px-5 py-3 max-w-[90%]">
                                    <h3>{{ __('You\'re one step away from booking!') }} 🥳</h3>
                                    <p>
                                        {{ __('In order to manage the booking I need to know your name and email.') }}
                                        {{ __('Use the form below to log-in.') }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <div class="border border-gray-300 bg-gray-50 border-dashed rounded-lg prose px-5 py-3 max-w-[90%]">
                                    <h3 class="text-center">Log-in</h3>

                                    <form action="" method="POST">
                                        @csrf

                                        <div>
                                            <x-text-input class="rounded-b-none relative focus:z-10" type="text" name="name" required placeholder="{{ __('Name') }}" />
                                            <x-text-input class="rounded-t-none relative -top-px focus:z-10" type="email" name="email" required placeholder="{{ __('Email address') }}" />
                                        </div>

                                        <div class="mt-2">
                                            <x-primary-button class="w-full justify-center">{{ __('Log-in') }}</x-primary-button>
                                        </div>

                                        <div class="relative mt-4">
                                            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                                <div class="w-full border-t border-gray-200"></div>
                                            </div>
                                            <div class="relative flex justify-center text-sm font-medium leading-6">
                                                <span class="bg-gray-50 px-4 text-gray-0">{{ __('Or continue with') }}</span>
                                            </div>
                                        </div>

                                        <a href="{{ route('social.redirect', ['google']) }}" class="mt-4 flex w-full items-center justify-center gap-3 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus-visible:ring-transparent">
                                            <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24">
                                                <path d="M12.0003 4.75C13.7703 4.75 15.3553 5.36002 16.6053 6.54998L20.0303 3.125C17.9502 1.19 15.2353 0 12.0003 0C7.31028 0 3.25527 2.69 1.28027 6.60998L5.27028 9.70498C6.21525 6.86002 8.87028 4.75 12.0003 4.75Z" fill="#EA4335" />
                                                <path d="M23.49 12.275C23.49 11.49 23.415 10.73 23.3 10H12V14.51H18.47C18.18 15.99 17.34 17.25 16.08 18.1L19.945 21.1C22.2 19.01 23.49 15.92 23.49 12.275Z" fill="#4285F4" />
                                                <path d="M5.26498 14.2949C5.02498 13.5699 4.88501 12.7999 4.88501 11.9999C4.88501 11.1999 5.01998 10.4299 5.26498 9.7049L1.275 6.60986C0.46 8.22986 0 10.0599 0 11.9999C0 13.9399 0.46 15.7699 1.28 17.3899L5.26498 14.2949Z" fill="#FBBC05" />
                                                <path d="M12.0004 24.0001C15.2404 24.0001 17.9654 22.935 19.9454 21.095L16.0804 18.095C15.0054 18.82 13.6204 19.245 12.0004 19.245C8.8704 19.245 6.21537 17.135 5.2654 14.29L1.27539 17.385C3.25539 21.31 7.3104 24.0001 12.0004 24.0001Z" fill="#34A853" />
                                            </svg>
                                            <span class="text-sm font-semibold leading-6">Google</span>
                                        </a>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
