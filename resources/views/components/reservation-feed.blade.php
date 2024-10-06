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
                .then((response) => {
                    this.feed.push(...response.data.data);

                    this.nextPageUrl = response.data.next_page_url;
                });

            this.loading.false;

            console.log(this.feed);
        },

        init() {
            this.index();
        },
    }"
>
    <div class="sticky z-10 top-0 bg-white flex items-center justify-between p-4 border-b">
        <h3 class="text-xl font-bold">{{ __('Feed') }}</h3>

        <x-ui-close x-on:click="isFeedVisible = false" />
    </div>

    <div class="px-4 pb-6">
        <ul class="space-y-6">
            <template x-for="item in feed">
                <li class="group relative flex gap-x-4">
                    <div class="group-last:hidden absolute -bottom-6 left-0 top-0 flex w-6 justify-center">
                        <div class="w-px bg-gray-200"></div>
                    </div>

                    <div class="relative flex h-6 w-6 flex-none items-center justify-center bg-white">
                        <div class="h-1.5 w-1.5 rounded-full bg-gray-100 ring-1 ring-gray-300"></div>
                    </div>

                    <p
                        x-text="item.description"
                        class="flex-auto py-0.5 text-sm leading-5 text-gray-700"
                    ></p>

                    <time
                        :datetime="item.created_at"
                        :title="format(item.created_at, 'd MMM y, H:mm')"
                        class="flex-none py-0.5 text-xs leading-5 text-gray-500"
                        x-text="format(item.created_at, 'd MMM')"
                    ></time>
                </li>
            </template>
        </ul>
    </div>
</div>