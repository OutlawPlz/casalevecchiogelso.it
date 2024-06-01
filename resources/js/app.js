import '../css/app.css';

import './bootstrap';
import Alpine from 'alpinejs';
import { format } from 'date-fns';

window.Alpine = Alpine;

window.format = format;

Alpine.start();
