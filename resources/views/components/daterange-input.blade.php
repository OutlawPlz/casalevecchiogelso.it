@props([
    'disabled' => false,
    'unavailable' => [],
])

<div {!! $attributes !!} x-modelable="_dates" x-data="{
    _dates: [],
    picker: null,
    init() {
        const calendarColumns = (window.innerWidth < 1024) ? 1 : 2;

        this.picker = new easepick.create({
            element: this.$refs.checkIn,
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
        });
    },
}">
    <div>
        <x-input-label>{{ __('Check-in') }}</x-input-label>
        <x-text-input :disabled="$disabled"
                      type="text"
                      x-ref="checkIn"
                      name="check_in"
                      x-model="_dates[0]"
                      x-on:click="picker.show()"/>
    </div>

    <div>
        <x-input-label>{{ __('Check-out') }}</x-input-label>
        <x-text-input readonly=""
                      :disabled="$disabled"
                      type="text"
                      x-ref="checkOut"
                      name="check_out"
                      x-model="_dates[1]"
                      x-on:click="picker.show()"/>
    </div>
</div>
