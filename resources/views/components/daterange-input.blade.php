@props([
    'unavailable' => [],
    'name' => ['check_in', 'check_out'],
    'jserror' => ['', ''],
    'error' => ['', ''],
    'value' => ['', ''],
    'disabled' => [],
])

@php
$value = array_map(fn ($date) => $date instanceof DateTimeInterface ? $date->format('Y-m-d') : $date, $value);
@endphp

@pushOnce('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@easepick/bundle@1.2.1/dist/index.umd.min.js"></script>
@endpushOnce

<div
    {{ $attributes->class('relative') }}
    x-modelable="_dates"
    x-data="{
        _dates: ['{{ $value[0] }}', '{{ $value[1] }}'],
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
                    @if($disabled)
                    repick: true,
                    @endif
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
    <x-field
        id="daterange_start-date"
        :label="__('Check-in')"
        :disabled="in_array($name[0], $disabled)"
        class="w-full"
    >
        <x-input
            readonly
            x-ref="startDate"
            name="{{ $name[0] }}"
            x-model="_dates[0]"
            jserror="{{ $jserror[0] }}"
            error="{{ $error[0] }}"
            :disabled="in_array($name[0], $disabled)"
        />
    </x-field>

    <x-field
        id="daterange_end-date"
        :label="__('Check-out')"
        :disabled="in_array($name[1], $disabled)"
        class="w-full"
    >
        <x-input
            readonly
            x-ref="endDate"
            name="{{ $name[1] }}"
            x-model="_dates[1]"
            jserror="{{ $jserror[1] }}"
            error="{{ $error[1] }}"
            :disabled="in_array($name[1], $disabled)"
        />
    </x-field>
</div>
