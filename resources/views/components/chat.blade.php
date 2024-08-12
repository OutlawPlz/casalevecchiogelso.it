<div
    {{ $attributes }}
    x-data="{
        loading: false,
        authUserId: {{ Auth::id() }},
        errors: {{ json_encode($errors->messages()) }},
        chat: {},
        message: '',
        previews: [],
        locale: '',

        async index() {
            this.loading = true;

            await axios
                .get('{{ route('message.index', ['reservation' => $channel]) }}' + `?locale=${this.locale}`)
                .then((response) => this.chat = response.data);

            $nextTick(() => location.href = '#end');

            this.loading = false;
        },

        async show(messageId) {
            this.loading = true;

            await axios
                .get(`/reservations/{{ $channel }}/messages/${messageId}?locale=${this.locale}`)
                .then((response) => {
                    const date = format(response.data.created_at, 'Y-MM-dd');

                    date in this.chat
                        ? this.chat[date].push(response.data)
                        : this.chat[date] = [response.data];

                    $nextTick(() => location.href = '#end');
                })

            this.loading = false;
        },

        isAuthUser(userId) {
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

        imagePreview(fileList) {
            this.previews = Array.from(fileList)
                .map((file) => URL.createObjectURL(file));
        },

        init() {
            $watch('locale', (value, oldValue) => {
                if (value === oldValue) return;

                this.index();
            });

            this.index();

            Echo
                .private('Reservations.{{ $channel }}')
                .listen('ChatReply', (event) => this.show(event.message.id));
        },
    }"
    x-on:translate-chat.window="locale = $event.detail"
>
    <div class="sticky top-16 py-4 bg-white flex space-x-4 px-4 sm:px-6 border-l shadow-sm">
        <h3 class="text-xl font-bold">{{ __('Chat') }}</h3>

        <button
            x-on:click="isVisible = ! isVisible"
            type="button"
            class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 flex items-center space-x-1"
        >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                <path d="M5.75 7.5a.75.75 0 1 0 0 1.5.75.75 0 0 0 0-1.5ZM7.25 8.25A.75.75 0 0 1 8 7.5h2.25a.75.75 0 0 1 0 1.5H8a.75.75 0 0 1-.75-.75ZM5.75 9.5a.75.75 0 0 0 0 1.5H8a.75.75 0 0 0 0-1.5H5.75Z" />
                <path fill-rule="evenodd" d="M4.75 1a.75.75 0 0 0-.75.75V3a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2V1.75a.75.75 0 0 0-1.5 0V3h-5V1.75A.75.75 0 0 0 4.75 1ZM3.5 7a1 1 0 0 1 1-1h7a1 1 0 0 1 1 1v4.5a1 1 0 0 1-1 1h-7a1 1 0 0 1-1-1V7Z" clip-rule="evenodd" />
            </svg>

            <span class="whitespace-nowrap">{{ __('Reservation details') }}</span>
        </button>

        <button
            x-on:click.prevent="$dispatch('open-modal', 'chat-language')"
            type="button"
            class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 flex items-center space-x-1"
        >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                <path fill-rule="evenodd" d="M11 5a.75.75 0 0 1 .688.452l3.25 7.5a.75.75 0 1 1-1.376.596L12.89 12H9.109l-.67 1.548a.75.75 0 1 1-1.377-.596l3.25-7.5A.75.75 0 0 1 11 5Zm-1.24 5.5h2.48L11 7.636 9.76 10.5ZM5 1a.75.75 0 0 1 .75.75v1.261a25.27 25.27 0 0 1 2.598.211.75.75 0 1 1-.2 1.487c-.22-.03-.44-.056-.662-.08A12.939 12.939 0 0 1 5.92 8.058c.237.304.488.595.752.873a.75.75 0 0 1-1.086 1.035A13.075 13.075 0 0 1 5 9.307a13.068 13.068 0 0 1-2.841 2.546.75.75 0 0 1-.827-1.252A11.566 11.566 0 0 0 4.08 8.057a12.991 12.991 0 0 1-.554-.938.75.75 0 1 1 1.323-.707c.049.09.099.181.15.271.388-.68.708-1.405.952-2.164a23.941 23.941 0 0 0-4.1.19.75.75 0 0 1-.2-1.487c.853-.114 1.72-.185 2.598-.211V1.75A.75.75 0 0 1 5 1Z" clip-rule="evenodd" />
            </svg>

            <span class="whitespace-nowrap">{{ __('Translate chat') }}</span>
        </button>
    </div>

    <div class="grow overflow-y-auto max-w-3xl w-full mx-auto px-4 md:px-6">
        <div id="start"></div>

        <template x-for="(messages, date) in chat" :key="date">
            <div>
                <div class="text-center text-sm py-4" x-text="format(date, 'd MMM')"></div>

                <template x-for="message of messages" :key="message.id">
                    <div
                        :id="`message-${message.id}`"
                        class="flex items-start gap-2.5 mt-2"
                        :class="isAuthUser(message.user_id) ? 'flex-row-reverse' : 'justify-start'"
                    >
                        <div class="hidden bg-gray-200 w-7 h-7 shrink-0 rounded-full shadow-inner"></div>
                        <div
                            class="shadow flex flex-col max-w-[95%] leading-1.5 p-3 border-gray-200 rounded-lg"
                            :class="isAuthUser(message.user_id) ? 'bg-gray-200' : 'bg-white'"
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

                            <div class="flex flex-wrap space-x-2">
                                <template x-for="path in message.media" :key="path">
                                    <a :href="`/storage/${path}`" target="_blank">
                                        <img class="mt-1 rounded-lg w-24 h-24 object-cover" :src="`/storage/${path}`" alt=media"">
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <div id="end"></div>
    </div>

    <div class="sticky bottom-0 py-2 px-4 md:px-6 mt-2 max-w-3xl w-full mx-auto">
        <form
            enctype="multipart/form-data"
            x-ref="form"
            x-on:submit.prevent="submit"
            class="p-3 rounded-lg bg-white shadow space-y-2"
        >
            <div class="w-full text-sm text-gray-900 bg-white">
                <textarea
                    name="message"
                    rows="1"
                    x-model="message"
                    class="block w-full p-0 border-0 focus:ring-0"
                    placeholder="{{ __('Your message') }}..."
                ></textarea>

                <div class="flex space-x-2">
                    <template x-for="src in previews">
                        <div class="relative cursor-pointer mt-2 -translate-x-2">
                            <img :src="src" alt="Preview" class="rounded-lg w-24 h-24 object-cover hover:opacity-30">

                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 absolute left-1/2 top-1/2 -translate-y-1/2 -translate-x-1/2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex w-full">
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

                <div class="relative p-2 text-gray-500 rounded-lg cursor-pointer hover:text-gray-900 hover:bg-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                        <path fill-rule="evenodd" d="M15.621 4.379a3 3 0 0 0-4.242 0l-7 7a3 3 0 0 0 4.241 4.243h.001l.497-.5a.75.75 0 0 1 1.064 1.057l-.498.501-.002.002a4.5 4.5 0 0 1-6.364-6.364l7-7a4.5 4.5 0 0 1 6.368 6.36l-3.455 3.553A2.625 2.625 0 1 1 9.52 9.52l3.45-3.451a.75.75 0 1 1 1.061 1.06l-3.45 3.451a1.125 1.125 0 0 0 1.587 1.595l3.454-3.553a3 3 0 0 0 0-4.242Z" clip-rule="evenodd" />
                    </svg>

                    <input
                        type="file"
                        name="media[]"
                        multiple
                        accept="image/*"
                        class="absolute inset-0 opacity-0 cursor-pointer"
                        x-on:change="imagePreview($event.target.files)"
                    >
                </div>

                <div class="grow"></div>

                <button class="relative p-2 bg-gray-800 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                        <path d="M3.105 2.288a.75.75 0 0 0-.826.95l1.414 4.926A1.5 1.5 0 0 0 5.135 9.25h6.115a.75.75 0 0 1 0 1.5H5.135a1.5 1.5 0 0 0-1.442 1.086l-1.414 4.926a.75.75 0 0 0 .826.95 28.897 28.897 0 0 0 15.293-7.155.75.75 0 0 0 0-1.114A28.897 28.897 0 0 0 3.105 2.288Z"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>
