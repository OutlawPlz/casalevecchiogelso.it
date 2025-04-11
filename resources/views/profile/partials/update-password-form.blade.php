<section>
    <header>
        <h2 class="text-lg font-medium text-zinc-900">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-zinc-600">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <x-field
            :label="__('Current Password')"
            id="update_password_current_password"
            error="updatePassword:current_password"
        >
            <x-input
                name="current_password"
                type="password"
                autocomplete="current-password"
            />
        </x-field>

        <x-field
            :label="__('New Password')"
            id="update_password_password"
            error="updatePassword:password"
        >
            <x-input
                name="password"
                type="password"
                autocomplete="new-password"
            />
        </x-field>


        <x-field
            :label="__('Confirm Password')"
            id="update_password_password_confirmation"
            error="updatePassword:password_confirmation"
        >
            <x-input
                name="password_confirmation"
                type="password"
                autocomplete="new-password"
            />
        </x-field>

        <div class="flex items-center gap-4">
            <button class="primary">{{ __('Save') }}</button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-zinc-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
