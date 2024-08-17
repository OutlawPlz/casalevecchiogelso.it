import './bootstrap';

import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';
import { format, addDays, addYears, differenceInDays } from 'date-fns';

window.Alpine = Alpine;

window.format = format;
window.addDays = addDays;
window.addYears = addYears;
window.differenceInDays = differenceInDays;

const moneyFormatter = new Intl.NumberFormat(
    document.documentElement.lang,
    { style: 'currency', currency: 'EUR' }
)

/**
 * @param {number} cents
 * @returns {string}
 */
window.$ = (cents) => moneyFormatter.format(cents / 100);

Alpine.plugin(intersect);

Alpine.start();
