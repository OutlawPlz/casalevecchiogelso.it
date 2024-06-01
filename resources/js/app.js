import '../css/app.css';

import './bootstrap';
import Alpine from 'alpinejs';
import { format, addYears } from 'date-fns';

window.Alpine = Alpine;

window.format = format;
window.addYears = addYears;

Alpine.start();
