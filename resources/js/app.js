import './bootstrap';

import Vue from 'vue';
import DataTable from 'laravel-vue-datatable';

Vue.use(DataTable);

import { library } from '@fortawesome/fontawesome-svg-core';
import {
    faCamera,
    faCheck,
    faPencilAlt,
    faPlus,
    faPrint,
    faSearch,
    faTimes,
    faTrash
} from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

library.add(faCamera, faCheck, faPencilAlt, faPlus, faPrint, faSearch, faTimes, faTrash);

Vue.component('font-awesome-icon', FontAwesomeIcon);

import App from './components/App.vue';
import Navigation from './components/Navigation.vue';
import Author from './components/Author.vue';
import AuthorForm from './components/AuthorForm.vue';
import AuthorView from './components/AuthorView.vue';
import Book from './components/Book.vue';
import BookForm from './components/BookForm.vue';
import BookView from './components/BookView.vue';
import DatatableActionButton from './components/DatatableActionButton.vue';

Vue.component('app', App);
Vue.component('navigation', Navigation);
Vue.component('author', Author);
Vue.component('author-form', AuthorForm);
Vue.component('author-view', AuthorView);
Vue.component('book', Book);
Vue.component('book-form', BookForm);
Vue.component('book-view', BookView);
Vue.component('datatable-action-button', DatatableActionButton);

const app = new Vue({
    el: '#app',
});
