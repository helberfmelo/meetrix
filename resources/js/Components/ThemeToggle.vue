<template>
    <button @click="toggleTheme" 
        class="relative inline-flex items-center h-10 w-20 rounded-full bg-zinc-900 border border-white/10 p-1 transition-all duration-500 hover:border-meetrix-orange active:scale-95 shadow-premium group overflow-hidden"
        aria-label="Toggle Theme">
        
        <!-- Background Animation -->
        <div class="absolute inset-0 bg-gradient-to-tr from-meetrix-orange/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

        <!-- Knob -->
        <div 
            class="relative z-10 flex items-center justify-center h-8 w-8 rounded-full shadow-lg transform transition-all duration-500 ease-out"
            :class="isDark ? 'translate-x-10 bg-zinc-950 text-meetrix-orange' : 'translate-x-0 bg-white text-zinc-950'"
        >
            <span v-if="isDark" class="text-xs">🌙</span>
            <span v-else class="text-xs">☀️</span>
        </div>

        <!-- Labels -->
        <div class="absolute inset-0 flex items-center justify-between px-3 pointer-events-none">
            <span class="text-[8px] font-black uppercase tracking-tighter transition-opacity duration-300" :class="isDark ? 'opacity-0' : 'opacity-40 text-black'">Light</span>
            <span class="text-[8px] font-black uppercase tracking-tighter transition-opacity duration-300" :class="isDark ? 'opacity-40 text-white' : 'opacity-0'">Dark</span>
        </div>
    </button>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const isDark = ref(true);

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
    if (savedTheme === 'light') {
        isDark.value = false;
        document.documentElement.classList.remove('dark');
    } else {
        isDark.value = true;
        document.documentElement.classList.add('dark');
    }
});
</script>

<style scoped>
.shadow-premium {
    box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.3);
}
</style>
