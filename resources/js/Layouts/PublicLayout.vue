<script setup>
import LanguageSwitcher from '../Components/LanguageSwitcher.vue';
import ThemeToggle from '../Components/ThemeToggle.vue';
</script>

<template>
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-950 text-slate-600 dark:text-slate-400 font-sans selection:bg-meetrix-orange selection:text-white overflow-x-hidden transition-colors duration-500">
        <!-- Unified Sovereign Navigation -->
        <nav class="fixed top-0 w-full z-[1000] px-6 py-6 flex justify-between items-center pointer-events-none">
            <div class="text-2xl font-black tracking-tighter text-zinc-950 dark:text-white font-outfit pointer-events-auto bg-white/10 dark:bg-black/10 backdrop-blur-sm px-4 py-2 rounded-2xl">MEETRIX<span class="text-meetrix-orange">.PRO</span></div>
            <div class="flex items-center gap-6 pointer-events-auto mt-4 sm:mt-0">
                <router-link to="/login" class="text-[10px] uppercase font-black tracking-widest text-zinc-500 hover:text-zinc-950 dark:text-white/50 dark:hover:text-white transition-colors bg-white/10 dark:bg-black/10 backdrop-blur-sm px-4 py-2 rounded-2xl">{{ $t('home.login') }}</router-link>
                <div class="flex items-center gap-2 bg-zinc-100/80 dark:bg-zinc-900/80 backdrop-blur-md p-1.5 rounded-2xl border border-black/5 dark:border-white/5 shadow-lg">
                    <ThemeToggle />
                    <LanguageSwitcher />
                </div>
                <router-link to="/onboarding" class="px-6 py-3 bg-zinc-950 text-white dark:bg-white dark:text-zinc-950 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:scale-105 active:scale-95 transition-all shadow-xl shadow-black/10">
                    {{ $t('home.get_started') }}
                </router-link>
            </div>
        </nav>

        <main>
            <router-view></router-view>
        </main>

        <footer class="py-24 px-6 lg:px-24 flex flex-col md:flex-row justify-between items-center gap-12 text-[10px] font-black uppercase tracking-[0.4em] text-slate-700 border-t border-white/5 bg-zinc-950">
            <div>{{ $t('home.footer_tagline') }}</div>
            <div class="flex gap-12">
                <span class="text-slate-800">© {{ new Date().getFullYear() }} // SOVEREIGN_PRO</span>
                <div class="flex gap-6">
                    <a href="#" class="hover:text-meetrix-orange transition-colors">TW</a>
                    <a href="#" class="hover:text-meetrix-orange transition-colors">LI</a>
                </div>
            </div>
        </footer>
    </div>
</template>
