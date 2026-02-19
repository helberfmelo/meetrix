import './bootstrap';
import '../css/app.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import i18n from './i18n';
import App from './App.vue';

const app = createApp(App);

const pinia = createPinia();
app.use(pinia);
app.use(router);
app.use(i18n);

// Initialize Auth Store
import { useAuthStore } from './stores/auth';
const authStore = useAuthStore(pinia);
authStore.init().then(() => {
    app.mount('#app');
});
