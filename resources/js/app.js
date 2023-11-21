import './bootstrap';

import Alpine from 'alpinejs';
import {easepick} from "@easepick/core";
import {LockPlugin} from "@easepick/lock-plugin";
import {RangePlugin} from "@easepick/range-plugin";

window.Alpine = Alpine;

Alpine.start();

const picker = new easepick.create({
    element: "#datepicker",
    css: [
        "https://cdn.jsdelivr.net/npm/@easepick/bundle@1.2.1/dist/index.css"
    ],
    zIndex: 10,
    grid: 2,
    calendars: 2,
    autoApply: false,
    LockPlugin: {
        minDate: Date.now(),
        minDays: 3,
        maxDays: 21
    },
    plugins: [
        RangePlugin,
        LockPlugin
    ]
})
