import './bootstrap';

import Alpine from 'alpinejs';

import { Toast } from './toast'; // Import Toast

window.Alpine = Alpine;
// window.Toast is already set in toast.js, but import ensures it runs.

Alpine.start();

