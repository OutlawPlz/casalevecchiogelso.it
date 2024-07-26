<div
    class="h-full flex flex-col max-w-3xl mx-auto px-4 sm:px-6"
    x-data="{
        chat: {},
        loading: false,
        authUserId: {{ Auth::id() }},
        errors: {{ json_encode($errors->messages()) }},
        message: '',
        locale: navigator.language,

        async index() {
            this.loading = true;

            await axios
                .get('{{ route('message.index', ['reservation' => $channel]) }}' + `?locale=${this.locale}`)
                .then((response) => this.chat = response.data);

            this.loading = false;
        },

        isOwner(userId) {
            return this.authUserId === userId;
        },

        async submit() {
            this.loading = true;

            const formData = new FormData(this.$refs.form);

            await axios
                .post('{{ route('message.store', ['reservation' => $channel]) }}', formData)
                .then((response) => {
                    this.errors = {};

                    this.$refs.form.reset();
                })
                .catch((error) => {
                    if (error.response.status === 422) {
                        return this.errors = error.response.data.errors;
                    }
                });

            this.loading = false;
        },

        init() {
            this.index();

            $watch('locale', (value, oldValue) => {
                if (value === oldValue) return;

                this.index();
            });

            Echo
                .private('Reservations.{{ $channel }}')
                .listen('ChatReply', (event) => {
                    event.date in this.chat
                        ? this.chat[event.date].push(event.message)
                        : this.chat[event.date] = event.message;

                    $nextTick(() => location.href = `#message-${event.message.id}`);
                });
        },
    }"
    x-on:translate-chat.window="locale = $event.detail"
>
    <div class="grow">
        <template x-for="(messages, date) in chat" :key="date">
            <div>
                <div class="text-center text-sm py-4" x-text="format(date, 'd MMM')"></div>

                <template x-for="message of messages" :key="message.id">
                    <div
                        :id="`message-${message.id}`"
                        class="flex items-start gap-2.5 mt-2"
                        :class="isOwner(message.user_id) ? 'flex-row-reverse' : 'justify-start'"
                    >
                        <div class="hidden bg-gray-200 w-7 h-7 shrink-0 rounded-full shadow-inner"></div>
                        <div
                            class="shadow flex flex-col max-w-[95%] leading-1.5 p-4 border-gray-200 rounded-lg"
                            :class="isOwner(message.user_id) ? 'bg-gray-200' : 'bg-white'"
                        >
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-semibold text-gray-900" x-text="message.author.name"></span>
                                <span
                                    :title="format(message.created_at, 'd MMM y, H:m')"
                                    class="text-sm font-normal text-gray-500"
                                    x-text="format(message.created_at, 'H:m')"
                                ></span>
                            </div>
                            <div class="prose" x-html="message.rendered_content"></div>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </div>

    <form
        x-ref="form"
        x-on:submit.prevent="submit"
        class="flex space-x-2 mt-6"
    >
        <div class="flex w-full items-center px-3 py-2 rounded-lg bg-white shadow">
            @host
            <x-dropdown align="top" class="mb-4 w-48">
                <x-slot name="trigger">
                    <button
                        type="button"
                        class="inline-flex justify-center p-2 text-gray-500 rounded-lg cursor-pointer hover:text-gray-900 hover:bg-gray-100"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                            <path fill-rule="evenodd" d="M10 2c-2.236 0-4.43.18-6.57.524C1.993 2.755 1 4.014 1 5.426v5.148c0 1.413.993 2.67 2.43 2.902 1.168.188 2.352.327 3.55.414.28.02.521.18.642.413l1.713 3.293a.75.75 0 0 0 1.33 0l1.713-3.293a.783.783 0 0 1 .642-.413 41.102 41.102 0 0 0 3.55-.414c1.437-.231 2.43-1.49 2.43-2.902V5.426c0-1.413-.993-2.67-2.43-2.902A41.289 41.289 0 0 0 10 2ZM6.75 6a.75.75 0 0 0 0 1.5h6.5a.75.75 0 0 0 0-1.5h-6.5Zm0 2.5a.75.75 0 0 0 0 1.5h3.5a.75.75 0 0 0 0-1.5h-3.5Z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <div x-on:click="message = `/blade:${$event.target.dataset.template}`">
                        @foreach($templates as $template)
                            <button
                                type="button"
                                data-template="{{ $template['template'] }}"
                                class="px-4 py-2 text-start text-sm leading-5 text-gray-700 hover:bg-gray-100 w-full"
                            >
                                {{ $template['label'] }}
                            </button>
                        @endforeach
                    </div>
                </x-slot>
            </x-dropdown>
            @endhost

            <!--
            <button type="button" class="p-2 text-gray-500 rounded-lg cursor-pointer hover:text-gray-900 hover:bg-gray-100">
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.408 7.5h.01m-6.876 0h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM4.6 11a5.5 5.5 0 0 0 10.81 0H4.6Z"/></svg>
            </button>
            -->

            <textarea
                name="message"
                x-ref="textarea"
                rows="1"
                x-model="message"
                class="block mx-4 p-2.5 w-full text-sm text-gray-900 bg-white rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                placeholder="{{ __('Your message') }}..."
            ></textarea>

            <button class="inline-flex justify-center p-2 text-gray-500 rounded-lg cursor-pointer hover:text-gray-900 hover:bg-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                    <path d="M3.105 2.288a.75.75 0 0 0-.826.95l1.414 4.926A1.5 1.5 0 0 0 5.135 9.25h6.115a.75.75 0 0 1 0 1.5H5.135a1.5 1.5 0 0 0-1.442 1.086l-1.414 4.926a.75.75 0 0 0 .826.95 28.897 28.897 0 0 0 15.293-7.155.75.75 0 0 0 0-1.114A28.897 28.897 0 0 0 3.105 2.288Z"/>
                </svg>
            </button>
        </div>
    </form>
</div>
