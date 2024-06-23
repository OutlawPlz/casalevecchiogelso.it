<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reservation') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div
                x-data="{
                    loading: false,
                    errors: {{ json_encode($errors->messages()) }},
                    messages: {{ $reservation->getRawOriginal('messages') }},

                    async submit() {
                        this.loading = true;

                        const formData = new FormData(this.$refs.form);

                        await axios.post('{{ route('message.store', [$reservation]) }}', formData)
                            .then((response) => {
                                this.errors = {};
                                this.$refs.form.reset();
                                this.messages.push(response.data);
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
                <div class="space-y-4">
                    <template x-for="message in messages">
                        <div class="flex justify-end">
                            <div
                                class="shadow rounded-lg bg-white prose px-5 py-3 max-w-[90%]"
                                x-html="message.message"
                            ></div>
                        </div>
                    </template>
                </div>

                <form
                    x-on:submit.prevent="submit"
                    x-ref="form"
                    class="mt-6"
                >
                    <div>
                        <x-input-label>{{ __('Message') }}</x-input-label>
                        <x-textarea-input name="message" />
                        <template x-if="errors.message">
                            <div x-text="errors.message[0]" class="text-sm text-red-600 mt-1"></div>
                        </template>
                    </div>

                    <x-primary-button class="mt-3">Submit</x-primary-button>
                </form>
            </div>

            <form action="{{ route('checkout') }}" method="POST" class="mt-12">
                @csrf
                <x-primary-button name="reservation" value="{{ $reservation->ulid }}">{{ __('Checkout') }}</x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>
