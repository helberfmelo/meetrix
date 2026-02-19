<template>
    <button @click="toggleTheme" 
        class="relative flex items-center justify-center p-2 text-zinc-500 hover:text-meetrix-orange dark:text-slate-400 dark:hover:text-white transition-all active:scale-90"
        aria-label="Toggle Theme">
        
        <!-- Icon: Show Moon in Light Mode (to switch to Dark), Sun in Dark Mode (to switch to Light) -->
        <i v-if="isDark" class="fas fa-sun text-lg"></i>
        <i v-else class="fas fa-moon text-lg"></i>
    </button>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const isDark = ref(false);

const toggleTheme = () => {
    isDark.value = !isDark.value;
    updateTheme();
};

const updateTheme = () => {
    if (isDark.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

onMounted(() => {
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    if (savedTheme) {
        isDark.value = savedTheme === 'dark';
    } else {
        isDark.value = prefersDark;
    }
    
    updateTheme();

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (!localStorage.getItem('theme')) {
            isDark.value = e.matches;
            updateTheme();
        }
    });
});
</script>
