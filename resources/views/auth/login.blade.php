<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')"/>

    <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4">
        @csrf

        <x-field
            id="email"
            error="email"
            :label="__('Email')"
        >
            <x-input
                name="email"
                type="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
            />
        </x-field>

        <x-field
            id="password"
            error="password"
            :label="__('Password')"
        >
            <x-input
                name="password"
                type="password"
                required
                autocomplete="current-password"
            />
        </x-field>

        <x-field
            :label="__('Remember me')"
            id="remember"
            error="remember"
            class="inline"
        >
            <x-input
                name="remember"
                type="checkbox"
            />
        </x-field>

        <div class="flex items-center justify-end gap-2">
            @if (Route::has('password.request'))
                <a
                    class="underline text-sm text-zinc-600 hover:text-zinc-900 rounded-md focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    href="{{ route('password.request') }}"
                >
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <button class="primary ms-3">
                {{ __('Log in') }}
            </button>
        </div>
    </form>
</x-guest-layout>
