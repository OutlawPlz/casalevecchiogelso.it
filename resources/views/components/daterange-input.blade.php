@props([
    'disabled' => false,
    'unavailable' => [],
    'start' => '',
    'end' => '',
])

<div {!! $attributes !!} x-data="{
    dates: [],
    picker: null,
    init() {
        const calendarColumns = window.innerWidth < 1024 ? 1 : 2;

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
                startDate: '{{ $start }}',
                endDate: '{{ $end }}'
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

        this.$refs.checkIn.value = '{{ $start }}';
        this.$refs.checkOut.value = '{{ $end }}';

        this.picker.on('select', (event) => {
            this.$refs.checkIn.value = format(event.detail.start, 'yyyy-MM-dd');
            this.$refs.checkOut.value = format(event.detail.end, 'yyyy-MM-dd');
        });
    },
}">
    <div>
        <x-input-label>{{ __('Check-in') }}</x-input-label>
        <x-text-input :disabled="$disabled"
                      type="text"
                      x-ref="checkIn"
                      name="check_in"
                      x-on:click="picker.show()"/>
    </div>

    <div>
        <x-input-label>{{ __('Check-out') }}</x-input-label>
        <x-text-input readonly=""
                      :disabled="$disabled"
                      type="text"
                      x-ref="checkOut"
                      name="check_out"
                      x-on:click="picker.show()"/>
    </div>
</div>
