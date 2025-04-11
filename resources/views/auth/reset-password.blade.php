<x-guest-layout>
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-field
            :label="__('Email')"
            id="email"
            error="email"
        >
            <x-input
                name="email"
                type="email"
                :value="old('email', $request->email)"
                required
                autofocus
                autocomplete="username"
            />
        </x-field>

        <x-field
            :value="__('Password')"
            id="password"
            class="mt-4"
            error="password"
        >
            <x-input
                type="password"
                name="password"
                required
                autocomplete="new-password"
            />
        </x-field>

        <x-field
            :label="__('Confirm Password')"
            id="password_confirmation"
            error="password_confirmation"
        >
            <x-input
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
            />
        </x-field>

        <div class="flex items-center justify-end mt-4">
            <button class="primary">
                {{ __('Reset Password') }}
            </button>
        </div>
    </form>
</x-guest-layout>
