<template>
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-950 flex font-sans selection:bg-meetrix-orange selection:text-white transition-colors duration-500">
        <!-- Sidebar: Raw Depth -->
        <aside class="hidden lg:flex w-72 bg-white dark:bg-zinc-900 border-r border-black/5 dark:border-white/5 flex-col transition-all duration-500">
            <div class="p-8">
                <div class="text-xl font-black tracking-tighter text-zinc-950 dark:text-white font-outfit hidden lg:block uppercase">MEETRIX<span class="text-meetrix-orange">.PRO</span></div>
                <div class="text-xl font-black text-meetrix-orange lg:hidden text-center">M</div>
            </div>
            
            <nav class="mt-12 flex-1 px-4 space-y-2">
                <router-link v-for="item in navItems" :key="item.path" :to="item.path" 
                    class="group flex items-center gap-4 px-6 py-4 rounded-3xl transition-all relative overflow-hidden"
                    active-class="bg-zinc-200 text-zinc-950 dark:bg-zinc-800 dark:text-white shadow-premium"
                    :class="[route.path === item.path ? '' : 'text-slate-500 hover:text-zinc-950 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5']"
                >
                    <div v-if="route.path === item.path" class="absolute left-0 top-2 bottom-2 w-1 bg-meetrix-orange rounded-full"></div>
                    <i :class="[item.icon, 'text-lg group-hover:scale-110 transition-all']"></i>
                    <span class="hidden lg:block text-[10px] font-black uppercase tracking-[0.2em]">{{ item.label }}</span>
                </router-link>
            </nav>

            <div class="p-8 border-t border-black/5 dark:border-white/5">
                <button @click="handleLogout" class="w-full flex items-center gap-4 px-6 py-4 text-slate-500 hover:text-red-500 transition-colors group">
                    <i class="fas fa-sign-out-alt text-lg"></i>
                    <span class="hidden lg:block text-[10px] font-black uppercase tracking-[0.2em]">{{ $t('common.logout') }}</span>
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0">
            <header class="py-4 sm:py-6 px-4 sm:px-6 md:px-12 flex flex-col sm:flex-row sm:justify-between sm:items-center items-start gap-3 sm:gap-0 relative z-[1000] bg-white/80 dark:bg-zinc-950/80 backdrop-blur-xl border-b border-black/5 dark:border-white/5">
                <div class="w-full sm:w-auto flex items-center gap-4">
                    <span class="text-[9px] sm:text-[10px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-[0.2em] sm:tracking-[0.4em] truncate max-w-[90vw] sm:max-w-none">{{ breadcrumb }}</span>
                </div>
                <div class="w-full sm:w-auto flex items-center justify-between sm:justify-end gap-3 sm:gap-6">
                    <div class="hidden sm:flex items-center gap-3 group cursor-pointer relative pr-6 border-r border-black/10 dark:border-white/10">
                        <div class="text-right hidden sm:block">
                            <p class="text-[10px] font-black text-zinc-950 dark:text-white uppercase tracking-widest leading-none">{{ user?.name }}</p>
                            <p class="text-[8px] font-black text-meetrix-orange uppercase tracking-widest mt-1">{{ $t('admin.sovereign_node') }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-2xl bg-zinc-950 dark:bg-white border border-black/10 dark:border-white/10 flex items-center justify-center text-white dark:text-zinc-950 group-hover:border-meetrix-orange group-hover:bg-meetrix-orange group-hover:text-white group-hover:scale-105 transition-all font-black shadow-lg group-active:scale-95">
                            {{ user?.name?.charAt(0) }}
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button @click="handleLogout" class="lg:hidden w-9 h-9 rounded-xl border border-black/10 dark:border-white/10 text-slate-500 hover:text-red-500 transition-colors flex items-center justify-center">
                            <i class="fas fa-sign-out-alt text-sm"></i>
                        </button>
                        <ThemeToggle />
                        <LanguageSwitcher class="hidden sm:block" />
                    </div>
                </div>
            </header>
            
            <div class="flex-1 px-4 sm:px-6 md:px-12 pb-24 lg:pb-12 overflow-y-auto">
                <router-view></router-view>
            </div>

            <footer class="hidden lg:flex py-8 px-6 md:px-12 flex-col md:flex-row justify-between items-center gap-4 text-[10px] font-medium uppercase tracking-wider text-slate-500 border-t border-black/5 dark:border-white/5 bg-zinc-50/50 dark:bg-zinc-950/50 backdrop-blur-sm">
                <div>MEETRIX <span class="mx-2 text-slate-300 dark:text-slate-700">|</span> {{ $t('admin.sovereign_node') }}</div>
                <div>
                    © {{ new Date().getFullYear() }} <span class="mx-2 text-slate-300 dark:text-slate-700">|</span> <a href="https://opents.com.br" target="_blank" class="hover:text-meetrix-orange transition-colors">OTS - Open Tecnologia e Serviços Ltda.</a>
                </div>
            </footer>
        </main>

        <nav class="lg:hidden fixed bottom-0 inset-x-0 z-[1200] bg-white/95 dark:bg-zinc-950/95 backdrop-blur-xl border-t border-black/5 dark:border-white/10">
            <div class="flex overflow-x-auto px-1">
                <router-link
                    v-for="item in mobileNavItems"
                    :key="item.path"
                    :to="item.path"
                    class="min-w-[68px] py-3 px-1 flex flex-col items-center justify-center gap-1 text-[8px] font-black uppercase tracking-wide transition-colors"
                    :class="isNavActive(item.path) ? 'text-meetrix-orange' : 'text-slate-500'"
                >
                    <i :class="[item.icon, 'text-sm']"></i>
                    <span class="truncate max-w-full">{{ item.label }}</span>
                </router-link>
            </div>
        </nav>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter, useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import LanguageSwitcher from '../Components/LanguageSwitcher.vue';
import ThemeToggle from '../Components/ThemeToggle.vue';

const authStore = useAuthStore();
const router = useRouter();
const route = useRoute();
const { t } = useI18n();

const user = computed(() => authStore.user);

const navItems = computed(() => [
    { path: '/dashboard', label: t('common.dashboard'), icon: 'fas fa-chart-pie' },
    { path: '/dashboard/pages', label: t('common.pages'), icon: 'fas fa-file-lines' },
    { path: '/dashboard/bookings', label: t('common.bookings'), icon: 'fas fa-calendar-check' },
    { path: '/dashboard/teams', label: t('common.teams'), icon: 'fas fa-users-viewfinder' },
    { path: '/dashboard/integrations', label: t('common.integrations'), icon: 'fas fa-plug-circle-bolt' },
    { path: '/dashboard/polls', label: t('common.polls'), icon: 'fas fa-square-poll-vertical' },
    { path: '/dashboard/coupons', label: t('common.coupons'), icon: 'fas fa-ticket' }
]);

const mobileNavItems = computed(() => navItems.value);

const breadcrumb = computed(() => {
    const active = navItems.value.find(i => i.path === route.path);
    return active ? `${t('admin.breadcrumb_prefix')} ${active.label}` : `${t('admin.breadcrumb_prefix')} ${t('admin.overview')}`;
});

const isNavActive = (path) => {
    if (path === '/dashboard/pages' && route.path.startsWith('/dashboard/editor/')) {
        return true;
    }
    return route.path === path || route.path.startsWith(`${path}/`);
};

const handleLogout = () => {
    authStore.logout();
    router.push('/login');
};
</script>

<style scoped>
.shadow-premium {
    box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.1);
}

.dark .shadow-premium {
    box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.5);
}
</style>
