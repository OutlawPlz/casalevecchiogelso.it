<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')"/>

    <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-2">
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
        >
            <x-input
                type="password"
                required
                autocomplete="current-password"
            />
        </x-field>

        <x-field
            :label="__('Remember me')"
            id="remember"
            name="remember"
            class="inline gap-y-0.5"
            help="Questo è un messaggio di testo"
        >
            <x-input type="checkbox" />
        </x-field>

        <x-label class="flex items-center gap-2 text-zinc-600">
            <x-input type="checkbox" name="remember"/>
            {{ __('Remember me') }}
        </x-label>

        <div class="flex items-center justify-end">
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
