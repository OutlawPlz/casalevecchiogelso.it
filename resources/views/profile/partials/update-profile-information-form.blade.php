<section>
    <header>
        <h2 class="text-lg font-medium text-zinc-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-zinc-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')


        <x-field
            id="name"
            :label="__('Name')"
            error="name"
        >
            <x-input
                name="name"
                :value="old('name', $user->name)"
                required
                autofocus
                autocomplete="name"
            />
        </x-field>

        <div>
            <x-field
                id="email"
                :label="__('Email')"
                error="email"
            >
                <x-input
                    name="email"
                    :value="old('email', $user->email)"
                    required
                    autocomplete="username"
                />
            </x-field>

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-zinc-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="primary">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <button class="primary">{{ __('Save') }}</button>

            @if (session('status') === 'profile-updated')
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
