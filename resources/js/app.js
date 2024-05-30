// import './bootstrap';

import '#/vanillajs-datepicker/dist/css/datepicker.min.css';
import '../css/app.css';

// import Alpine from 'alpinejs';
import { DateRangePicker } from 'vanillajs-datepicker';

// window.Alpine = Alpine;

// Alpine.start();

const elem = document.getElementById('daterange');

if (elem) {
    const datepicker = new DateRangePicker(elem, {
        minDate: Date.now(),
    });
}
