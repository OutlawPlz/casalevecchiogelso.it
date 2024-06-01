<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('reservations.store') }}" method="POST" class="flex space-x-4">
                        @csrf

                        <div class="flex space-x-4" x-data="{
                            dates: [],
                            picker: null,
                            init() {
                                this.picker = new easepick.create({
                                    element: this.$refs.checkIn,
                                    grid: 2,
                                    calendars: 2,
                                    css: [
                                        'https://cdn.jsdelivr.net/npm/@easepick/core@1.2.1/dist/index.css',
                                        'https://cdn.jsdelivr.net/npm/@easepick/range-plugin@1.2.1/dist/index.css',
                                        'https://cdn.jsdelivr.net/npm/@easepick/lock-plugin@1.2.1/dist/index.css',
                                    ],
                                    plugins: ['RangePlugin', 'LockPlugin'],
                                    RangePlugin: {
                                        tooltipNumber: (days) => days - 1,
                                        locale: { one: '{{ __('night') }}', other: '{{ __('nights') }}' },
                                    },
                                    LockPlugin: {
                                        minDate: Date.now(),
                                        maxDate: addYears(Date.now(), 1),
                                        minDays: 3,
                                        maxDays: 29,
                                        inseparable: true,
                                        selectForward: true,
                                        filter(date, picked) {
                                            const unavailable = {{ json_encode($unavailableDates, JSON_HEX_APOS) }};

                                            return unavailable.includes(format(date, 'yyyy-MM-dd'));
                                        },
                                    },
                                });

                                this.picker.on('select', (event) => {
                                    this.$refs.checkIn.value = format(event.detail.start, 'yyyy-MM-dd');
                                    this.$refs.checkOut.value = format(event.detail.end, 'yyyy-MM-dd');
                                });
                            },
                        }">
                            <div>
                                <label class="block font-medium text-sm text-gray-700 mb-1">{{ __('Check-in') }}</label>
                                <input class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full block mt-1 w-full" type="text" x-ref="checkIn">
                            </div>

                            <div>
                                <label class="block font-medium text-sm text-gray-700 mb-1">{{ __('Check-out') }}</label>
                                <input class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full block mt-1 w-full" type="text" x-ref="checkOut" x-on:click="picker.show()">
                            </div>
                        </div>

                        <div>
                            <label class="block font-medium text-sm text-gray-700 mb-1">{{ __('Guests') }}</label>
                            <input class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full block mt-1 w-full" type="number" name="guests_count" min="1" max="10" value="1">
                        </div>

                        <div class="self-end">
                            <button class="inline-flex items-center self-end px-4 py-3 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Reserve') }}</button>
                        </div>
                    </form>

                    @if($errors->any())
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
