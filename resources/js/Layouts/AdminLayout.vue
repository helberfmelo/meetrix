<template>
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-950 flex font-sans selection:bg-meetrix-orange selection:text-white transition-colors duration-500">
        <!-- Sidebar: Raw Depth -->
        <aside class="w-20 lg:w-72 bg-white dark:bg-zinc-900 border-r border-black/5 dark:border-white/5 flex flex-col transition-all duration-500">
            <div class="p-8">
                <div class="text-xl font-black tracking-tighter text-zinc-950 dark:text-white font-outfit hidden lg:block uppercase">MEETRIX<span class="text-meetrix-orange">.PRO</span></div>
                <div class="text-xl font-black text-meetrix-orange lg:hidden text-center">M</div>
            </div>
            
            <nav class="mt-12 flex-1 px-4 space-y-2">
                <router-link v-for="item in navItems" :key="item.path" :to="item.path" 
                    class="group flex items-center gap-4 px-6 py-4 rounded-3xl transition-all relative overflow-hidden"
                    active-class="bg-zinc-950 text-white dark:bg-white dark:text-zinc-950 shadow-premium"
                    :class="[route.path === item.path ? '' : 'text-slate-500 hover:text-zinc-950 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5']"
                >
                    <div v-if="route.path === item.path" class="absolute left-0 top-2 bottom-2 w-1 bg-meetrix-orange rounded-full"></div>
                    <span class="text-xl grayscale group-hover:grayscale-0 transition-all">{{ item.icon }}</span>
                    <span class="hidden lg:block text-[10px] font-black uppercase tracking-[0.2em]">{{ item.label }}</span>
                </router-link>
            </nav>

            <div class="p-8 border-t border-black/5 dark:border-white/5">
                <button @click="handleLogout" class="w-full flex items-center gap-4 px-6 py-4 text-slate-500 hover:text-red-500 transition-colors group">
                    <span class="text-xl">🚪</span>
                    <span class="hidden lg:block text-[10px] font-black uppercase tracking-[0.2em]">{{ $t('common.logout') }}</span>
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0">
            <header class="py-10 px-12 flex justify-between items-center mix-blend-difference relative z-[1000]">
                <div class="flex items-center gap-4">
                    <span class="text-[10px] font-black text-slate-600 uppercase tracking-[0.4em]">{{ breadcrumb }}</span>
                </div>
                <div class="flex items-center gap-8">
                    <ThemeToggle />
                    <LanguageSwitcher />
                    <div class="flex items-center gap-3 pl-8 border-l border-black/10 dark:border-white/10 group cursor-pointer">
                        <div class="text-right hidden sm:block">
                            <p class="text-[10px] font-black text-zinc-950 dark:text-white uppercase tracking-widest leading-none">{{ user?.name }}</p>
                            <p class="text-[8px] font-black text-slate-400 dark:text-slate-600 uppercase tracking-widest mt-1">Sovereign_Node</p>
                        </div>
                        <div class="w-10 h-10 rounded-2xl bg-zinc-100 dark:bg-zinc-900 border border-black/5 dark:border-white/5 flex items-center justify-center text-zinc-400 dark:text-white/50 group-hover:border-meetrix-orange transition-all font-black">
                            {{ user?.name?.charAt(0) }}
                        </div>
                    </div>
                </div>
            </header>
            
            <div class="flex-1 px-12 pb-12 overflow-y-auto">
                <router-view></router-view>
            </div>
        </main>
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
    { path: '/dashboard', label: t('common.dashboard'), icon: '📊' },
    { path: '/dashboard/pages', label: t('common.pages'), icon: '📄' },
    { path: '/dashboard/bookings', label: t('common.bookings'), icon: '📅' },
    { path: '/dashboard/teams', label: 'Teams', icon: '👥' },
    { path: '/dashboard/integrations', label: 'Integrations', icon: '🔌' },
    { path: '/dashboard/polls', label: 'Polls', icon: '🗳️' },
    { path: '/dashboard/coupons', label: 'Coupons', icon: '🏷️' }
]);

const breadcrumb = computed(() => {
    const active = navItems.value.find(i => i.path === route.path);
    return active ? `SYSTEM // ${active.label}` : 'SYSTEM // OVERVIEW';
});

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
