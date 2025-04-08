<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="flex flex-col gap-2">
        @csrf

        <x-field
            id="name"
            :label="__('Name')"
            name="name"
        >
            <x-input
                type="text"
                :value="old('name')"
                required
                autofocus
                autocomplete="name"
            />
        </x-field>

        <x-field
            :label="__('Email')"
            id="email"
            name="email"
        >
            <x-input
                type="email"
                :value="old('email')"
                required
                autocomplete="username"
            />
        </x-field>

        <x-field
            id="password"
            :label="__('Password')"
            name="password"
            >
            <x-input
                type="password"
                required
                autocomplete="new-password"
            />
        </x-field>

        <x-field
            :label="__('Confirm Password')"
            id="password_confirmation"
            name="password_confirmation"
        >
            <x-input
                type="password"
                required
                autocomplete="new-password"
            />
        </x-field>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-zinc-600 hover:text-zinc-900 rounded-md focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-button variant="primary" class="ms-4">
                {{ __('Register') }}
            </x-button>
        </div>
    </form>
</x-guest-layout>
