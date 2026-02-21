import { createRouter, createWebHistory } from 'vue-router';
import PublicLayout from './Layouts/PublicLayout.vue';
import AdminLayout from './Layouts/AdminLayout.vue';
import Dashboard from './Views/Dashboard.vue';
import i18n from './i18n';
import {
    DEFAULT_I18N_LOCALE,
    LOCALE_ROUTE_PATTERN,
    localeToUrlSegment,
    normalizeI18nLocale,
    normalizeUrlLocale,
    stripLocalePrefix,
    urlSegmentToLocale,
    withLocalePrefix,
} from './utils/localeRoute';

import PagesList from './Views/PagesList.vue';
import BookingPage from './Views/BookingPage.vue';

import Login from './Views/Login.vue';

import Home from './Views/Home.vue';

import PageEditor from './Views/PageEditor.vue';
import OnboardingWizard from './Views/OnboardingWizard.vue';
import BookingsView from './Views/BookingsView.vue';
import Checkout from './Views/Checkout.vue';

const localizedRoot = `/:locale(${LOCALE_ROUTE_PATTERN})?`;

const routes = [
    {
        path: `${localizedRoot}/onboarding`,
        component: OnboardingWizard
    },
    {
        path: `${localizedRoot}/checkout`,
        component: Checkout
    },
    {
        path: localizedRoot,
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
        path: `${localizedRoot}/dashboard`,
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
        path: `${localizedRoot}/vote/:slug`,
        name: 'public-poll',
        component: () => import('./Views/PublicPoll.vue')
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to) => {
    if (to.matched.length === 0) {
        return true;
    }

    const rawLocaleParam = Array.isArray(to.params.locale) ? to.params.locale[0] : to.params.locale;
    const normalizedUrlLocale = normalizeUrlLocale(rawLocaleParam);
    const routeLocale = urlSegmentToLocale(normalizedUrlLocale);
    const storedLocale = normalizeI18nLocale(localStorage.getItem('locale'));
    const currentLocale = normalizeI18nLocale(i18n.global.locale.value);
    const activeLocale = routeLocale || storedLocale || currentLocale || DEFAULT_I18N_LOCALE;

    if (!normalizedUrlLocale) {
        return {
            path: withLocalePrefix(to.path, activeLocale),
            query: to.query,
            hash: to.hash,
            replace: true,
        };
    }

    const expectedUrlLocale = localeToUrlSegment(activeLocale);
    const shouldCanonicalizeLocale = rawLocaleParam && String(rawLocaleParam) !== normalizedUrlLocale;
    if (shouldCanonicalizeLocale || normalizedUrlLocale !== expectedUrlLocale) {
        return {
            path: withLocalePrefix(stripLocalePrefix(to.path), activeLocale),
            query: to.query,
            hash: to.hash,
            replace: true,
        };
    }

    if (currentLocale !== activeLocale) {
        i18n.global.locale.value = activeLocale;
    }

    if (storedLocale !== activeLocale) {
        localStorage.setItem('locale', activeLocale);
    }

    return true;
});

export default router;
