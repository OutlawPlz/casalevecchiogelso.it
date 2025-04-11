<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-zinc-900">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-zinc-600">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <button
        class="danger"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >
        {{ __('Delete Account') }}
    </button>

    <x-modal name="confirm-user-deletion" class="max-w-xl" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-zinc-900">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-1 text-sm text-zinc-600">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <x-field
                :label="__('Password')"
                id="password"
                error="password:userDeletion"
                class="mt-6"
            >
                <x-input
                    name="password"
                    type="password"
                    placeholder="{{ __('Password') }}"
                />
            </x-field>

            <div class="mt-6 flex justify-end">
                <button
                    class="ghost"
                    x-on:click="$root.close()"
                    type="button"
                >
                    {{ __('Cancel') }}
                </button>

                <button class="danger ms-3">
                    {{ __('Delete Account') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>
