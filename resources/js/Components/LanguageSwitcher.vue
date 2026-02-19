<template>
    <div class="relative inline-block text-left font-outfit z-[9999]">
        <div>
            <button type="button" @click="isOpen = !isOpen"
                class="inline-flex justify-center items-center py-2 px-4 bg-zinc-950/50 border border-white/10 rounded-full text-[10px] font-black text-white/70 uppercase tracking-widest hover:text-white hover:border-meetrix-orange transition-all active:scale-95 shadow-premium"
                id="menu-button" aria-expanded="true" aria-haspopup="true">
                <span class="mr-2 opacity-80">{{ currentFlag }}</span>
                <span>{{ currentLocale }}</span>
                <svg class="ml-2 h-3 w-3 transition-transform duration-300" :class="{ 'rotate-180': isOpen }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
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
            <div v-if="isOpen" class="origin-top-right absolute right-0 mt-4 w-56 rounded-3xl shadow-premium bg-zinc-900 border border-white/10 overflow-hidden z-[9999]">
                <div class="py-2" role="menu">
                    <button class="w-full text-left font-black text-[9px] uppercase tracking-widest px-6 py-4 flex items-center transition-colors group" 
                       v-for="(flag, locale) in locales" :key="locale"
                       @click.prevent="changeLocale(locale)">
                        <span class="mr-4 grayscale group-hover:grayscale-0 transition-all">{{ flag }}</span>
                        <span class="text-slate-500 group-hover:text-meetrix-orange transition-colors">{{ getLabel(locale) }}</span>
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
