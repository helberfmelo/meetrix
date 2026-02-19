<template>
    <button @click="toggleTheme" 
        class="relative inline-flex items-center justify-center h-10 w-10 rounded-full bg-zinc-900 border border-white/10 transition-all duration-500 hover:border-meetrix-orange active:scale-95 shadow-premium group overflow-hidden"
        aria-label="Toggle Theme">
        
        <!-- Background Animation -->
        <div class="absolute inset-0 bg-gradient-to-tr from-meetrix-orange/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

        <!-- Icon -->
        <div class="relative z-10 flex items-center justify-center transition-all duration-500 ease-out">
            <span v-if="isDark" class="text-xs">🌙</span>
            <span v-else class="text-xs">☀️</span>
        </div>
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
        // Obey user's browser theme if no preference saved
        isDark.value = prefersDark;
    }
    
    updateTheme();

    // Listen for system changes if no preference saved
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (!localStorage.getItem('theme')) {
            isDark.value = e.matches;
            updateTheme();
        }
    });
});
</script>

<style scoped>
.shadow-premium {
    box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.3);
}
</style>
