@php
/**
 * @var \App\Models\Reservation $reservation
 */
@endphp

<div
    x-data="{
        loading: false,
        feed: [],
        nextPageUrl: '{{ route('reservation.feed', [$reservation]) }}',

        async index() {
            this.loading = true;

            await axios
                .get(this.nextPageUrl)
                .then((response) => this.feed = response.data);

            this.loading.false;
        },
    }"
    x-intersect.once="index"
>
    <div class="sticky z-10 top-0 bg-white flex items-center justify-between p-4 border-b">
        <h3 class="text-xl font-bold">{{ __('Feed') }}</h3>

        <div>
            <x-ui-reload x-on:click="index" />

            <x-ui-close x-on:click="isFeedVisible = false" />
        </div>
    </div>

    <div class="px-4 pb-6">
        <ul class="flex flex-col gap-6 mt-6">
            <template x-for="item in feed">
                <li class="group relative flex gap-x-4">
                    <div class="group-last:hidden absolute -bottom-6 left-0 top-0 flex w-6 justify-center">
                        <div class="w-px bg-zinc-200"></div>
                    </div>

                    <div class="relative flex h-6 w-6 flex-none items-center justify-center bg-white">
                        <div class="h-1.5 w-1.5 rounded-full bg-zinc-100 ring-1 ring-zinc-300"></div>
                    </div>

                    <p
                        class="flex-auto py-0.5 text-sm leading-5 text-zinc-700"
                        x-text="item.description"
                    ></p>

                    <time
                        :datetime="item.created_at"
                        :title="format(item.created_at, 'd MMM y, H:mm')"
                        class="flex-none py-0.5 text-xs leading-5 text-zinc-500"
                        x-text="format(item.created_at, 'd MMM')"
                    ></time>
                </li>
            </template>
        </ul>
    </div>
</div>
