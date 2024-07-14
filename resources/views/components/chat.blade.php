<div
    class="max-w-3xl mx-auto w-full h-full"
    x-data="{
        chat: {},
        loading: false,
        authUserId: {{ Auth::id() }},
        errors: {{ json_encode($errors->messages()) }},

        async index() {
            this.loading = true;

            await axios
                .get('{{ route('message.index', ['reservation' => $channel]) }}')
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
>
    <template x-for="(messages, date) in chat" :key="date">
        <div>
            <div class="text-center text-sm my-6 grow" x-text="format(date, 'd MMM')"></div>

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
                        <div class="prose" x-html="message.data.content"></div>
                    </div>
                </div>
            </template>
        </div>
    </template>

    <form
        x-ref="form"
        x-on:submit.prevent="submit"
        class="flex space-x-2 mt-6 sticky bottom-2"
    >
        <div class="flex w-full items-center px-3 py-2 rounded-lg bg-white shadow">
            <span
                id="input-message"
                contenteditable=""
                class="block mx-4 p-2.5 w-full text-sm text-gray-900 bg-white rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                placeholder="{{ __('Your message') }}..."></span>

            <button class="inline-flex justify-center p-2 rounded-full cursor-pointer hover:bg-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                    <path
                        d="M3.105 2.288a.75.75 0 0 0-.826.95l1.414 4.926A1.5 1.5 0 0 0 5.135 9.25h6.115a.75.75 0 0 1 0 1.5H5.135a1.5 1.5 0 0 0-1.442 1.086l-1.414 4.926a.75.75 0 0 0 .826.95 28.897 28.897 0 0 0 15.293-7.155.75.75 0 0 0 0-1.114A28.897 28.897 0 0 0 3.105 2.288Z"/>
                </svg>
            </button>
        </div>
    </form>
</div>
