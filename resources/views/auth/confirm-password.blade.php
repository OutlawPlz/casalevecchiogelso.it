<x-guest-layout>
    <div class="mb-4 text-sm text-zinc-600">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div>
            <x-field
                id="password"
                :label="__('Password')"
                error="password"
            >
                <x-input
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                />
            </x-field>
        </div>

        <div class="flex justify-end mt-4">
            <button class="primary">
                {{ __('Confirm') }}
            </button>
        </div>
    </form>
</x-guest-layout>
