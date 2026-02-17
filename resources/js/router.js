import { createRouter, createWebHistory } from 'vue-router';
import PublicLayout from '../Layouts/PublicLayout.vue';
import AdminLayout from '../Layouts/AdminLayout.vue';
import Dashboard from '../Views/Dashboard.vue';

import PagesList from '../Views/PagesList.vue';
import BookingPage from '../Views/BookingPage.vue';

const routes = [
    {
        path: '/',
        component: PublicLayout,
        children: [
            {
                path: '',
                component: { template: '<div class="text-center py-20"><h1 class="text-4xl font-bold mb-4">Scheduling Infrastructure for Everyone</h1><p class="text-xl text-gray-600">Meetrix copies YCBM perfectly.</p></div>' }
            },
            {
                path: 'p/:slug',
                component: BookingPage
            },
            {
                path: 'login',
                component: { template: '<div class="max-w-md mx-auto mt-20 p-6 bg-white shadow rounded">Login Form Placeholder</div>' }
            }
        ]
    },
    {
        path: '/dashboard',
        component: AdminLayout,
        children: [
            {
                path: '',
                component: Dashboard
            },
            {
                path: '/pages',
                component: PagesList
            }
        ]
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
