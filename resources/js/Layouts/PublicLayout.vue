<script setup>
import LanguageSwitcher from '../Components/LanguageSwitcher.vue';
import ThemeToggle from '../Components/ThemeToggle.vue';
</script>

<template>
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-950 text-slate-600 dark:text-slate-400 font-sans selection:bg-meetrix-orange selection:text-white overflow-x-hidden transition-colors duration-500">
        <!-- Unified Sovereign Navigation -->
        <nav class="fixed top-0 w-full z-[5000] px-6 py-6 flex justify-between items-center pointer-events-none">
            <div class="text-2xl font-black tracking-tighter text-zinc-950 dark:text-white font-outfit pointer-events-auto bg-white/10 dark:bg-black/10 backdrop-blur-sm px-4 py-2 rounded-2xl">MEETRIX<span class="text-meetrix-orange">.PRO</span></div>
            <div class="flex items-center gap-8 pointer-events-auto mt-4 sm:mt-0">
                <!-- Dynamic Login Heading: Only on /login -->
                <div v-if="$route.path === '/login'" class="hidden md:flex flex-col items-end">
                    <h2 class="text-xl font-black text-zinc-950 dark:text-white uppercase tracking-tighter leading-none">
                        {{ $t('login.title').split(' ')[0] }} <span class="text-meetrix-orange">{{ $t('login.title').split(' ').slice(1).join(' ') }}</span>
                    </h2>
                    <p class="text-[8px] font-black uppercase tracking-[0.3em] text-slate-500 mt-1">
                        {{ $t('login.or') }} <router-link to="/onboarding" class="text-zinc-400 hover:text-meetrix-orange transition-colors">{{ $t('login.trial_link') }}</router-link>
                    </p>
                </div>

                <router-link v-if="$route.path !== '/login'" to="/login" class="text-[10px] uppercase font-black tracking-widest text-zinc-500 hover:text-zinc-950 dark:text-white/50 dark:hover:text-white transition-colors bg-white/10 dark:bg-black/10 backdrop-blur-sm px-4 py-2 rounded-2xl">{{ $t('home.login') }}</router-link>
                <router-link to="/onboarding" class="px-6 py-3 bg-meetrix-orange text-zinc-950 dark:bg-white dark:text-zinc-950 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:scale-105 active:scale-95 transition-all shadow-xl shadow-meetrix-orange/20">
                    {{ $t('home.get_started') }}
                </router-link>

                <div class="flex flex-col items-center gap-1 pointer-events-auto">
                    <ThemeToggle />
                    <LanguageSwitcher />
                </div>
            </div>
        </nav>

        <main>
            <router-view></router-view>
        </main>

        <footer class="py-12 px-6 lg:px-24 flex flex-col md:flex-row justify-between items-center gap-8 text-[11px] font-medium uppercase tracking-wider text-slate-500 border-t border-white/5 bg-zinc-950">
            <div>{{ $t('home.footer_tagline') }}</div>
            <div class="flex items-center gap-8">
                <span>© {{ new Date().getFullYear() }} Meetrix <span class="mx-2 text-slate-800">|</span> <a href="https://opents.com.br" target="_blank" class="hover:text-meetrix-orange transition-colors">OTS - Open Tecnologia e Serviços Ltda.</a></span>
            </div>
        </footer>
    </div>
</template>
