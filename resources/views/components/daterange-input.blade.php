@props([
    'unavailable' => [],
    'names' => ['check_in', 'check_out'],
])

<div
    {!! $attributes !!}
    class="relative"
    x-modelable="_dates"
    x-data="{
        _dates: [],
        picker: null,
        init() {
            const calendarColumns = (window.innerWidth < 1024) ? 1 : 2;

            this.picker = new easepick.create({
                element: this.$refs.startDate,
                lang: document.documentElement.lang || undefined,
                grid: calendarColumns,
                calendars: calendarColumns,
                css: [
                    'https://cdn.jsdelivr.net/npm/@easepick/core@1.2.1/dist/index.css',
                    'https://cdn.jsdelivr.net/npm/@easepick/range-plugin@1.2.1/dist/index.css',
                    'https://cdn.jsdelivr.net/npm/@easepick/lock-plugin@1.2.1/dist/index.css',
                ],
                plugins: ['RangePlugin', 'LockPlugin'],
                RangePlugin: {
                    tooltipNumber: (days) => days - 1,
                    elementEnd: this.$refs.endDate,
                    locale: { one: '{{ __('night') }}', other: '{{ __('nights') }}' },
                },
                LockPlugin: {
                    minDate: addDays(Date.now(), 2),
                    maxDate: addYears(Date.now(), 1),
                    minDays: 3,
                    maxDays: 29,
                    inseparable: true,
                    selectForward: true,
                    filter(date, picked) {
                        const unavailable = {{ json_encode($unavailable, JSON_HEX_APOS) }};

                        return unavailable.includes(format(date, 'yyyy-MM-dd'));
                    },
                },
            });

            this.$watch('_dates', () => this.picker.setDateRange(this._dates[0], this._dates[1]));

            this.picker.on('select', (event) => {
                this._dates = [
                    format(event.detail.start, 'yyyy-MM-dd'),
                    format(event.detail.end, 'yyyy-MM-dd')
                ]

                this.$el.dispatchEvent(new Event('input', { bubbles: true }));
            });
        },
    }"
>
    <div>
        <x-label for="start-date" class="mb-1">
            {{ __('Check-in') }}
        </x-label>
        <x-input
            readonly=""
            type="text"
            x-ref="startDate"
            name="{{ $names[0] }}"
            x-model="_dates[0]"
            x-on:click="picker.show()"
            id="start-date"
        />
    </div>

    <div>
        <x-label for="end-date" class="mb-1">
            {{ __('Check-out') }}
        </x-label>
        <x-input
            readonly=""
            type="text"
            x-ref="endDate"
            name="{{ $names[1] }}"
            x-model="_dates[1]"
            x-on:click="picker.show()"
            id="end-date"
        />
    </div>
</div>
