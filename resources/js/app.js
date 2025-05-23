import './bootstrap';

import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';
import persist from '@alpinejs/persist';
import {format, addDays, addYears, differenceInDays} from 'date-fns';

window.Alpine = Alpine;

window.format = format;
window.addDays = addDays;
window.addYears = addYears;
window.differenceInDays = differenceInDays;

Alpine.plugin([intersect, persist]);

window.moneyFormatter = new Intl.NumberFormat(
    document.documentElement.lang || undefined,
    {style: 'currency', currency: 'EUR'}
);

window.dateTimeFormatter = new Intl.DateTimeFormat(
    document.documentElement.lang || undefined,
    {dateStyle: 'short', timeStyle: 'short'}
);

window.$money = (cents) => moneyFormatter.format(cents / 100);
window.$date = (dateTimeString) => dateTimeFormatter.format(Date.parse(dateTimeString));

Alpine.directive(
    'money',
    (el, {expression}, {evaluateLater, effect}) => {
        const getAmount = evaluateLater(expression);

        effect(() => {
            getAmount((cents) => {
                el.textContent = moneyFormatter.format(cents / 100);
            })
        });
    }
);

Alpine.directive(
    'date',
    (el, {expression}, {evaluateLater, effect}) => {
        const getDateTime = evaluateLater(expression);

        effect(() => {
            getDateTime((dateTimeString) => {
                const dateTime = Date.parse(dateTimeString);

                el.textContent = dateTimeFormatter.format(dateTime);
            })
        });
    }
)

Alpine.start();
