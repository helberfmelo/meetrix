<template>
    <div class="relative inline-block text-left font-outfit z-[9999]">
        <div>
            <button type="button" @click="isOpen = !isOpen"
                class="inline-flex justify-center items-center py-1 px-3 bg-zinc-100 dark:bg-zinc-900/50 border border-zinc-200 dark:border-white/10 rounded-full text-[9px] font-black text-zinc-600 dark:text-white/70 hover:text-zinc-950 dark:hover:text-white hover:border-meetrix-orange transition-all active:scale-95 shadow-sm"
                id="menu-button" aria-expanded="true" aria-haspopup="true">
                <span class="mr-1 opacity-80">{{ currentFlag }}</span>
                <span>{{ currentLocale }}</span>
                <i class="fas fa-chevron-down ml-1 text-[7px] transition-transform duration-300" :class="{ 'rotate-180': isOpen }"></i>
            </button>
        </div>

        <transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="transform opacity-0 scale-95 -translate-y-2"
            enter-to-class="transform opacity-100 scale-100 translate-y-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="transform opacity-100 scale-100 translate-y-0"
            leave-to-class="transform opacity-0 scale-95 -translate-y-2"
        >
            <div v-if="isOpen" class="origin-top-right absolute right-0 mt-4 w-56 rounded-3xl shadow-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-white/10 overflow-hidden z-[9999]">
                <div class="py-2" role="menu">
                    <button class="w-full text-left font-black text-[9px] uppercase tracking-widest px-6 py-4 flex items-center transition-colors group" 
                       v-for="(flag, locale) in locales" :key="locale"
                       @click.prevent="changeLocale(locale)">
                        <span class="mr-4 grayscale group-hover:grayscale-0 transition-all">{{ flag }}</span>
                        <span class="text-zinc-500 dark:text-slate-500 group-hover:text-meetrix-orange transition-colors">{{ getLabel(locale) }}</span>
                    </button>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { locale } = useI18n();
const isOpen = ref(false);

const locales = {
    'en': '🇺🇸',
    'es': '🇪🇸',
    'fr': '🇫🇷',
    'de': '🇩🇪',
    'pt-BR': '🇧🇷',
    'pt': '🇵🇹',
    'zh-CN': '🇨🇳',
    'ja': '🇯🇵',
    'ko': '🇰🇷',
    'it': '🇮🇹',
    'ru': '🇷🇺'
};

const labels = {
    'en': 'English (US)',
    'es': 'Español',
    'fr': 'Français',
    'de': 'Deutsch',
    'pt-BR': 'Português (BR)',
    'pt': 'Português (PT)',
    'zh-CN': 'Chinese',
    'ja': 'Japanese',
    'ko': 'Korean',
    'it': 'Italiano',
    'ru': 'Russian'
};

const currentLocale = computed(() => locale.value);
const currentFlag = computed(() => locales[locale.value] || '🌐');

const getLabel = (loc) => labels[loc] || loc;

const changeLocale = (newLocale) => {
    locale.value = newLocale;
    localStorage.setItem('locale', newLocale);
    isOpen.value = false;
};
</script>
