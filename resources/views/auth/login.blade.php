<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')"/>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <x-field
            id="email"
            name="email"
            :label="__('Email')"
        >
            <x-input
                type="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
            />
        </x-field>

        <x-field
            id="password"
            name="password"
            :label="__('Password')"
            class="mt-4"
        >
            <x-input
                type="password"
                required
                autocomplete="current-password"
            />
        </x-field>

        <div class="block mt-4">
            <x-label class="flex items-center gap-2 text-zync-600">
                <x-input type="checkbox"/>
                {{ __('Remember me') }}
            </x-label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a
                    class="underline text-sm text-zync-600 hover:text-zync-900 rounded-md focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    href="{{ route('password.request') }}"
                >
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-button variant="primary" class="ms-3">
                {{ __('Log in') }}
            </x-button>
        </div>
    </form>
</x-guest-layout>
