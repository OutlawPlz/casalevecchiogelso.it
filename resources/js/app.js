import '../css/app.css';

import './bootstrap';
import Alpine from 'alpinejs';
import { format, addDays, addYears, differenceInDays } from 'date-fns';

window.Alpine = Alpine;

window.format = format;
window.addDays = addDays;
window.addYears = addYears;
window.differenceInDays = differenceInDays;

Alpine.start();
