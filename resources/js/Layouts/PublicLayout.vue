<script setup>
import { computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRoute } from 'vue-router';
import LanguageSwitcher from '../Components/LanguageSwitcher.vue';
import ThemeToggle from '../Components/ThemeToggle.vue';
import { DEFAULT_I18N_LOCALE, stripLocalePrefix, urlSegmentToLocale, withLocalePrefix } from '../utils/localeRoute';

const authStore = useAuthStore();
const route = useRoute();
const activeLocale = computed(() => urlSegmentToLocale(route.params.locale) || DEFAULT_I18N_LOCALE);
const toPath = (path) => withLocalePrefix(path, activeLocale.value);
const isLoginRoute = computed(() => stripLocalePrefix(route.path) === '/login');
const logoTarget = computed(() => toPath(authStore.isAuthenticated ? '/dashboard' : '/'));
</script>

<template>
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-950 text-slate-600 dark:text-slate-400 font-sans selection:bg-meetrix-orange selection:text-white overflow-x-hidden transition-colors duration-500">
        <!-- Unified Sovereign Navigation -->
        <nav class="fixed top-0 inset-x-0 z-[5000] px-3 sm:px-6 py-3 sm:py-6 flex justify-between items-center pointer-events-none">
            <router-link :to="logoTarget" class="text-base sm:text-2xl font-black tracking-tighter text-zinc-950 dark:text-white font-outfit pointer-events-auto bg-white/10 dark:bg-black/10 backdrop-blur-sm px-3 sm:px-4 py-2 rounded-2xl">
                MEETRIX<span class="text-meetrix-orange">.PRO</span>
            </router-link>
            <div class="flex items-center gap-1.5 sm:gap-6 pointer-events-auto">
                <!-- Dynamic Login Heading: Only on /login -->
                <div v-if="isLoginRoute" class="hidden xl:flex flex-col items-end">
                    <h2 class="text-xl font-black text-zinc-950 dark:text-white uppercase tracking-tighter leading-none">
                        {{ $t('login.title').split(' ')[0] }} <span class="text-meetrix-orange">{{ $t('login.title').split(' ').slice(1).join(' ') }}</span>
                    </h2>
                    <p class="text-[8px] font-black uppercase tracking-[0.3em] text-slate-500 mt-1">
                        {{ $t('login.or') }} <router-link :to="toPath('/onboarding')" class="text-zinc-400 hover:text-meetrix-orange transition-colors">{{ $t('login.trial_link') }}</router-link>
                    </p>
                </div>

                <router-link v-if="!isLoginRoute" :to="toPath('/login')" class="inline-flex text-[9px] sm:text-[10px] uppercase font-black tracking-wider sm:tracking-widest text-zinc-600 hover:text-zinc-950 dark:text-white/70 dark:hover:text-white transition-colors bg-white/20 dark:bg-black/20 backdrop-blur-sm px-2.5 sm:px-4 py-2 rounded-2xl border border-black/5 dark:border-white/10">
                    {{ $t('home.login') }}
                </router-link>
                <router-link :to="toPath('/onboarding')" class="px-3 sm:px-6 py-2.5 sm:py-3 bg-meetrix-orange text-zinc-950 dark:bg-white dark:text-zinc-950 rounded-2xl font-black text-[9px] sm:text-[10px] uppercase tracking-widest hover:scale-105 active:scale-95 transition-all shadow-xl shadow-meetrix-orange/20">
                    {{ $t('home.get_started') }}
                </router-link>

                <div class="flex items-center gap-2 sm:gap-4 pointer-events-auto">
                    <ThemeToggle />
                    <LanguageSwitcher />
                </div>
            </div>
        </nav>

        <main>
            <router-view></router-view>
        </main>

        <footer class="py-10 sm:py-12 px-4 sm:px-6 lg:px-24 flex flex-col md:flex-row justify-between items-center gap-6 sm:gap-8 text-[10px] sm:text-[11px] font-medium uppercase tracking-wider text-slate-500 border-t border-white/5 bg-zinc-950 text-center md:text-left">
            <div>{{ $t('home.footer_tagline') }}</div>
            <div class="flex items-center gap-8 max-w-full">
                <span class="break-words">© {{ new Date().getFullYear() }} Meetrix <span class="mx-2 text-slate-800">|</span> <a href="https://opents.com.br" target="_blank" class="hover:text-meetrix-orange transition-colors">OTS - Open Tecnologia e Serviços Ltda.</a></span>
            </div>
        </footer>
    </div>
</template>
