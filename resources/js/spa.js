import './bootstrap';
import '../css/app.css';
import axios from 'axios';
import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';

import App from './spa/App.vue';
import routes from './spa/routes';

// CSRF token
const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
}
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';

const router = createRouter({
    history: createWebHistory('/app'),
    routes,
});

createApp(App)
    .use(router)
    .mount('#app');

