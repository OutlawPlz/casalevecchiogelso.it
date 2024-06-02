import '../css/app.css';

import './bootstrap';
import Alpine from 'alpinejs';
import { format, addYears, differenceInDays } from 'date-fns';

window.Alpine = Alpine;

window.format = format;
window.addYears = addYears;
window.differenceInDays = differenceInDays;

Alpine.start();
