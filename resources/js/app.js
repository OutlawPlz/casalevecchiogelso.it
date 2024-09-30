import './bootstrap';

import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';
import persist from '@alpinejs/persist';
import { format, addDays, addYears, differenceInDays } from 'date-fns';

window.Alpine = Alpine;

window.format = format;
window.addDays = addDays;
window.addYears = addYears;
window.differenceInDays = differenceInDays;

Alpine.plugin([intersect, persist]);

Alpine.directive(
    'currency',
    (el, { expression }, { evaluateLater, effect }) => {
        const getAmount = evaluateLater(expression);

        effect(() => {
            getAmount((cents) => {
                const formatter = new Intl.NumberFormat(
                    document.documentElement.lang,
                    { style: 'currency', currency: 'EUR' }
                );

                el.textContent = formatter.format(cents / 100);
            })
        });
    }
);

Alpine.start();
