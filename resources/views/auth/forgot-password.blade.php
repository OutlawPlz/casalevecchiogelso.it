<x-guest-layout>
    <div class="mb-4 text-sm text-zinc-600">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <x-field
            id="email"
            :label="__('Email')"
            error="email"
        >
            <x-input
                name="email"
                type="email"
                :value="old('email')"
                required
                autofocus
            />
        </x-field>

        <div class="flex items-center justify-end mt-4">
            <button class="primary">
                {{ __('Email Password Reset Link') }}
            </button>
        </div>
    </form>
</x-guest-layout>
