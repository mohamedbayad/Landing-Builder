import './bootstrap';

import Alpine from 'alpinejs';
import { initTextareaMergeTagAutocomplete } from './components/textarea-merge-tag-autocomplete';
import { initTextareaCodeEditor } from './components/textarea-code-editor';

import { Toast } from './toast'; // Import Toast

window.Alpine = Alpine;
// window.Toast is already set in toast.js, but import ensures it runs.

Alpine.start();
initTextareaMergeTagAutocomplete();
initTextareaCodeEditor();

