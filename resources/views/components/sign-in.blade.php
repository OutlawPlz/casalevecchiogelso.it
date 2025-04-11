<form
    class="mt-8"
    x-on:submit.prevent="submit"
    x-data="{
        loading: false,
        errors: {},
        emailSent: false,

        async submit() {
            this.loading = true;

            const formData = new FormData(this.$root);

            await axios.post('/auth/token', formData)
                .then((response) => {
                    this.emailSent = true;
                    this.errors = {};
                })
                .catch((error) => {
                    if (error.response.status === 422) {
                        return this.errors = error.response.data.errors;
                    }
                });

            this.loading = false;
        },
    }"
>
    @csrf

    <div x-show="emailSent" class="p-4 border rounded-lg border-green-300 bg-green-50 flex gap-2 mb-4">
        <svg class="shrink-0 size-5 inline-block text-green-400 mt-0.5" data-flux-icon="" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"></path>
        </svg>

        <div>
            <p class="font-medium text-green-600">{{ __('Email inviata!') }}</p>

            <p class="text-green-600 mt-1">
                {{ __('A confirmation link has been sent to your email address.') }}
                {{ __('Click the link to complete the login.') }}
            </p>
        </div>
    </div>

    <div class="flex flex-col gap-2">
        <x-field
            :label="__('Name')"
            id="sign-in_name"
            jserror="errors.name"
        >
            <x-input
                name="name"
                required
                placeholder="{{ __('Mario Rossi') }}"
            />
        </x-field>

        <x-field
            :label="__('Email')"
            id="sign-in_email"
            jserror="errors.email"
        >
            <x-input
                name="email"
                required
                placeholder="{{ __('mario.rossi@example.com') }}"
            />
        </x-field>

        <button class="primary w-full">
            {{ __('Sign-in') }}
        </button>
    </div>

    <div class="relative mt-4">
        <div class="absolute inset-0 flex items-center" aria-hidden="true">
            <div class="w-full border-t border-zinc-200"></div>
        </div>
        <div class="relative flex justify-center text-sm font-medium leading-6">
            <span class="bg-white px-4 text-zinc-0">{{ __('Or continue with') }}</span>
        </div>
    </div>

    <a
        class="button w-full mt-4"
        href="{{ route('social.redirect', ['google']) }}"
    >
        <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24"><path d="M12.0003 4.75C13.7703 4.75 15.3553 5.36002 16.6053 6.54998L20.0303 3.125C17.9502 1.19 15.2353 0 12.0003 0C7.31028 0 3.25527 2.69 1.28027 6.60998L5.27028 9.70498C6.21525 6.86002 8.87028 4.75 12.0003 4.75Z" fill="#EA4335"/><path d="M23.49 12.275C23.49 11.49 23.415 10.73 23.3 10H12V14.51H18.47C18.18 15.99 17.34 17.25 16.08 18.1L19.945 21.1C22.2 19.01 23.49 15.92 23.49 12.275Z" fill="#4285F4"/><path d="M5.26498 14.2949C5.02498 13.5699 4.88501 12.7999 4.88501 11.9999C4.88501 11.1999 5.01998 10.4299 5.26498 9.7049L1.275 6.60986C0.46 8.22986 0 10.0599 0 11.9999C0 13.9399 0.46 15.7699 1.28 17.3899L5.26498 14.2949Z" fill="#FBBC05"/><path d="M12.0004 24.0001C15.2404 24.0001 17.9654 22.935 19.9454 21.095L16.0804 18.095C15.0054 18.82 13.6204 19.245 12.0004 19.245C8.8704 19.245 6.21537 17.135 5.2654 14.29L1.27539 17.385C3.25539 21.31 7.3104 24.0001 12.0004 24.0001Z" fill="#34A853"/></svg>
        <span class="text-sm font-semibold leading-6">Google</span>
    </a>
</form>
