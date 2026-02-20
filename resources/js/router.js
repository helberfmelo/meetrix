import { createRouter, createWebHistory } from 'vue-router';
import PublicLayout from './Layouts/PublicLayout.vue';
import AdminLayout from './Layouts/AdminLayout.vue';
import Dashboard from './Views/Dashboard.vue';

import PagesList from './Views/PagesList.vue';
import BookingPage from './Views/BookingPage.vue';

import Login from './Views/Login.vue';

import Home from './Views/Home.vue';

import PageEditor from './Views/PageEditor.vue';
import OnboardingWizard from './Views/OnboardingWizard.vue';
import BookingsView from './Views/BookingsView.vue';
import Checkout from './Views/Checkout.vue';

const routes = [
    {
        path: '/onboarding',
        component: OnboardingWizard
    },
    {
        path: '/checkout',
        component: Checkout
    },
    {
        path: '/',
        component: PublicLayout,
        children: [
            {
                path: '',
                component: Home
            },
            {
                path: 'p/:slug',
                component: BookingPage
            },
            {
                path: 'login',
                component: Login
            }
        ]
    },
    {
        path: '/dashboard',
        component: AdminLayout,
        children: [
            { path: '', component: Dashboard },
            { path: 'pages', component: PagesList },
            { path: 'bookings', component: BookingsView },
            { path: 'editor/:slug', component: PageEditor },
            { path: 'teams', component: () => import('./Views/TeamsView.vue') },
            { path: 'integrations', component: () => import('./Views/IntegrationsView.vue') },
            { path: 'integrations/:service/callback', component: () => import('./Views/IntegrationCallbackView.vue') },
            { path: 'polls', component: () => import('./Views/PollsView.vue') },
            { path: 'polls/create', component: () => import('./Views/PollCreator.vue') },
            { path: 'coupons', component: () => import('./Views/Coupons.vue') },
            { path: 'account', component: () => import('./Views/AccountView.vue') },
            { path: 'master-admin', component: () => import('./Views/MasterAdminView.vue') }
        ]
    },
    {
        path: '/vote/:slug',
        name: 'public-poll',
        component: () => import('./Views/PublicPoll.vue')
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
