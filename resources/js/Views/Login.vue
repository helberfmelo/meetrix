<template>
    <div class="min-h-screen flex flex-col items-center justify-center p-6 bg-zinc-50 dark:bg-zinc-950 relative overflow-hidden transition-colors duration-500 pt-32">
        <!-- Background Bloom -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-meetrix-orange/10 rounded-full blur-[150px] -z-10 animate-pulse"></div>

        <div class="max-w-xl w-full space-y-12 animate-in zoom-in-95 duration-1000">
            <div class="text-center">
                <h1 class="text-7xl font-black text-zinc-950 dark:text-white tracking-tighter uppercase font-outfit mb-4">
                    {{ $t('login.title').split(' ')[0] }}<br/>
                    <span class="text-meetrix-orange">{{ $t('login.title').split(' ').slice(1).join(' ') }}</span>
                </h1>
                <p class="text-[10px] font-black uppercase tracking-[0.4em] text-slate-500">
                    {{ $t('login.or') }}
                    <router-link to="/onboarding" class="text-zinc-600 dark:text-white hover:text-meetrix-orange transition-colors">{{ $t('login.trial_link') }}</router-link>
                </p>
            </div>

            <div class="bg-white/80 dark:bg-zinc-900/50 backdrop-blur-xl border border-black/5 dark:border-white/5 p-12 rounded-5xl shadow-premium relative transition-colors duration-500">
                
                <form class="space-y-8" @submit.prevent="handleLogin">
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-[9px] font-black uppercase tracking-widest text-slate-500">{{ $t('login.email_label') }}</label>
                            <input v-model="email" type="email" required 
                                class="w-full bg-zinc-100 dark:bg-zinc-950 border-2 border-black/5 dark:border-white/5 rounded-2xl px-6 py-4 text-zinc-950 dark:text-white font-bold outline-none focus:border-meetrix-orange transition-all tracking-tight"
                                :placeholder="$t('login.email_placeholder')">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black uppercase tracking-widest text-slate-500">{{ $t('login.password_label') }}</label>
                            <input v-model="password" type="password" required 
                                class="w-full bg-zinc-100 dark:bg-zinc-950 border-2 border-black/5 dark:border-white/5 rounded-2xl px-6 py-4 text-zinc-950 dark:text-white font-bold outline-none focus:border-meetrix-orange transition-all tracking-tight"
                                :placeholder="$t('login.password_placeholder')">
                        </div>
                    </div>

                    <div v-if="authStore.error" class="bg-red-500/10 border border-red-500/20 py-3 rounded-xl text-red-500 text-[10px] font-black uppercase tracking-widest text-center animate-in fade-in slide-in-from-top-2">
                        {{ authStore.error }}
                    </div>

                    <button type="submit" :disabled="authStore.loading"
                        class="w-full bg-zinc-950 text-white hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-200 py-5 rounded-2xl font-black text-xs uppercase tracking-[0.3em] hover:scale-[1.02] active:scale-[0.98] transition-all shadow-xl flex items-center justify-center gap-3 group px-8 disabled:bg-zinc-200 dark:disabled:bg-zinc-800 disabled:text-slate-400 dark:disabled:text-slate-600"
                    >
                        <span v-if="authStore.loading" class="animate-spin text-lg">🌀</span>
                        {{ authStore.loading ? $t('login.loading') : $t('login.submit') }}
                        <span class="group-hover:translate-x-2 transition-transform">→</span>
                    </button>
                </form>
            </div>
            
            <p class="text-[8px] font-black uppercase tracking-[0.5em] text-slate-700 text-center">
                MEETRIX_AUTH_PROTOCOL // SECURE_HANDSHAKE
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

const email = ref('admin@meetrix.test'); 
const password = ref('password');

const handleLogin = async () => {
    const success = await authStore.login(email.value, password.value);
    if (success) {
        router.push('/dashboard');
    }
};
</script>

<style scoped>
.shadow-premium {
    box-shadow: 0 40px 80px -20px rgba(0, 0, 0, 0.8);
}

.animate-in {
    animation: animate-in 1s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes animate-in {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
