<template>
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-950 flex flex-col items-center justify-center py-12 px-6 sm:px-12 font-sans selection:bg-meetrix-orange selection:text-white relative overflow-hidden transition-colors duration-500">
        <!-- Background Depth -->
        <div class="absolute top-0 right-0 w-[800px] h-[800px] bg-meetrix-orange/5 rounded-full blur-[200px] -z-10 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-zinc-900/10 dark:bg-white/5 rounded-full blur-[150px] -z-10"></div>

        <div class="max-w-3xl w-full space-y-16 animate-in fade-in duration-1000">
            <!-- Header Progress: Linear Depth -->
            <div class="px-8 flex items-center justify-between relative">
                <div class="absolute top-1/2 left-0 w-full h-px bg-zinc-950/5 dark:bg-white/5 -translate-y-1/2 -z-0"></div>
                <div v-for="n in 3" :key="n" class="flex flex-col items-center relative z-10 transition-all duration-700">
                    <div :class="['w-14 h-14 rounded-full flex items-center justify-center font-black text-xl border-2 transition-all duration-700', 
                                  step >= n ? 'bg-zinc-950 text-white dark:bg-white dark:text-zinc-950 border-zinc-950 dark:border-white shadow-premium scale-110' : 'bg-white dark:bg-zinc-900 text-slate-400 dark:text-slate-700 border-black/5 dark:border-white/5']">
                        {{ n }}
                    </div>
                    <span :class="['mt-4 text-[9px] font-black uppercase tracking-[0.3em] transition-colors duration-700', step >= n ? 'text-zinc-950 dark:text-white' : 'text-slate-400 dark:text-slate-700']">
                        {{ $t(`onboarding.step_${n}`) }}
                    </span>
                </div>
            </div>

            <!-- Card: High-Depth Fragment -->
            <div class="bg-white/80 dark:bg-zinc-900/40 backdrop-blur-3xl rounded-5xl border border-black/5 dark:border-white/5 p-16 min-h-[550px] flex flex-col justify-between shadow-premium relative group/card overflow-hidden transition-colors duration-500">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-meetrix-orange to-transparent opacity-0 group-hover/card:opacity-100 transition-opacity duration-1000"></div>
                
                <div v-if="step === 1" class="animate-in fade-in slide-in-from-bottom-8 duration-700">
                    <h2 class="text-5xl font-black text-zinc-950 dark:text-white mb-4 uppercase tracking-tighter font-outfit">{{ $t('onboarding.welcome_title') }}</h2>
                    <p class="text-slate-500 mb-12 font-bold text-xs uppercase tracking-widest">{{ $t('onboarding.welcome_subtitle') }}</p>
                    
                    <div class="space-y-8">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $t('onboarding.name_label') }}</label>
                                <div class="group/tooltip relative">
                                    <i class="fas fa-circle-info text-slate-400 cursor-help"></i>
                                    <div class="absolute bottom-full right-0 mb-3 w-48 p-3 bg-zinc-950 text-white text-[9px] font-bold rounded-xl opacity-0 group-hover/tooltip:opacity-100 transition-all pointer-events-none z-50 shadow-2xl border border-white/10 uppercase tracking-wider">
                                        {{ $t('onboarding.tooltip_name') }}
                                    </div>
                                </div>
                            </div>
                            <input 
                                v-model="form.name" 
                                type="text" 
                                class="w-full bg-zinc-100 dark:bg-zinc-950 border-2 border-black/5 dark:border-white/5 rounded-2xl px-8 py-5 text-zinc-950 dark:text-white font-bold text-lg outline-none focus:border-meetrix-orange transition-all tracking-tight"
                                :placeholder="$t('onboarding.name_placeholder')"
                            >
                        </div>
                        
                        <!-- Guest Registration Fields -->
                        <div v-if="!authStore.user" class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                            <div class="space-y-4">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $t('onboarding.email_label') }}</label>
                                <input 
                                    v-model="form.email" 
                                    type="email" 
                                    class="w-full bg-zinc-100 dark:bg-zinc-950 border-2 border-black/5 dark:border-white/5 rounded-2xl px-8 py-5 text-zinc-950 dark:text-white font-bold text-lg outline-none focus:border-meetrix-orange transition-all tracking-tight"
                                    placeholder="email@example.com"
                                >
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $t('onboarding.password_label') }}</label>
                                    <input 
                                        v-model="form.password" 
                                        type="password" 
                                        class="w-full bg-zinc-100 dark:bg-zinc-950 border-2 border-black/5 dark:border-white/5 rounded-2xl px-8 py-5 text-zinc-950 dark:text-white font-bold text-lg outline-none focus:border-meetrix-orange transition-all tracking-tight"
                                        placeholder="••••••••"
                                    >
                                </div>
                                <div class="space-y-4">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $t('onboarding.confirm_password_label') }}</label>
                                    <input 
                                        v-model="form.password_confirmation" 
                                        type="password" 
                                        class="w-full bg-zinc-100 dark:bg-zinc-950 border-2 border-black/5 dark:border-white/5 rounded-2xl px-8 py-5 text-zinc-950 dark:text-white font-bold text-lg outline-none focus:border-meetrix-orange transition-all tracking-tight"
                                        placeholder="••••••••"
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $t('onboarding.timezone_label') }}</label>
                                <div class="group/tooltip relative">
                                    <i class="fas fa-circle-info text-slate-400 cursor-help"></i>
                                    <div class="absolute bottom-full right-0 mb-3 w-48 p-3 bg-zinc-950 text-white text-[9px] font-bold rounded-xl opacity-0 group-hover/tooltip:opacity-100 transition-all pointer-events-none z-50 shadow-2xl border border-white/10 uppercase tracking-wider">
                                        {{ $t('onboarding.tooltip_timezone') }}
                                    </div>
                                </div>
                            </div>
                            <select 
                                v-model="form.timezone"
                                class="w-full bg-zinc-100 dark:bg-zinc-950 border-2 border-black/5 dark:border-white/5 rounded-2xl px-8 py-5 text-zinc-950 dark:text-white font-bold text-lg outline-none focus:border-meetrix-orange transition-all appearance-none cursor-pointer"
                            >
                                <option value="UTC">UTC (Universal Time)</option>
                                <option value="America/Sao_Paulo">America/Sao_Paulo</option>
                                <option value="Europe/London">Europe/London</option>
                                <option value="America/New_York">America/New_York</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div v-if="step === 2" class="animate-in fade-in slide-in-from-right-12 duration-700">
                    <h2 class="text-5xl font-black text-zinc-950 dark:text-white mb-4 uppercase tracking-tighter font-outfit">{{ $t('onboarding.page_title') }}</h2>
                    <p class="text-slate-500 mb-12 font-bold text-xs uppercase tracking-widest">{{ $t('onboarding.page_subtitle') }}</p>
                    
                    <div class="space-y-8">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $t('onboarding.p_title_label') }}</label>
                                <div class="group/tooltip relative">
                                    <i class="fas fa-circle-info text-slate-400 cursor-help"></i>
                                    <div class="absolute bottom-full right-0 mb-3 w-48 p-3 bg-zinc-950 text-white text-[9px] font-bold rounded-xl opacity-0 group-hover/tooltip:opacity-100 transition-all pointer-events-none z-50 shadow-2xl border border-white/10 uppercase tracking-wider">
                                        {{ $t('onboarding.tooltip_page_title') }}
                                    </div>
                                </div>
                            </div>
                            <input 
                                v-model="form.pageTitle" 
                                @input="generateSlug"
                                type="text" 
                                class="w-full bg-zinc-100 dark:bg-zinc-950 border-2 border-black/5 dark:border-white/5 rounded-2xl px-8 py-5 text-zinc-950 dark:text-white font-bold text-lg outline-none focus:border-meetrix-orange transition-all tracking-tight"
                                :placeholder="$t('onboarding.p_title_placeholder')"
                            >
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $t('onboarding.p_url_label') }}</label>
                                <div class="group/tooltip relative">
                                    <i class="fas fa-circle-info text-slate-400 cursor-help"></i>
                                    <div class="absolute bottom-full right-0 mb-3 w-48 p-3 bg-zinc-950 text-white text-[9px] font-bold rounded-xl opacity-0 group-hover/tooltip:opacity-100 transition-all pointer-events-none z-50 shadow-2xl border border-white/10 uppercase tracking-wider">
                                        {{ $t('onboarding.tooltip_page_url') }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center group/input">
                                <span class="bg-zinc-200 dark:bg-zinc-800 px-6 py-5 rounded-l-2xl border-2 border-r-0 border-black/5 dark:border-white/5 text-slate-500 font-bold text-sm">meetrix.opentshost.com/p/</span>
                                <input 
                                    v-model="form.pageSlug"
                                    type="text" 
                                    class="flex-1 bg-zinc-100 dark:bg-zinc-950 border-2 border-black/5 dark:border-white/5 rounded-r-2xl px-6 py-5 text-meetrix-orange font-black text-lg outline-none focus:border-meetrix-orange transition-all"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="step === 3" class="animate-in fade-in zoom-in-95 duration-700">
                    <h2 class="text-5xl font-black text-zinc-950 dark:text-white mb-4 uppercase tracking-tighter font-outfit">{{ $t('onboarding.avail_title') }}</h2>
                    <p class="text-slate-500 mb-12 font-bold text-xs uppercase tracking-widest">{{ $t('onboarding.avail_subtitle') }}</p>
                    
                    <div class="space-y-12">
                        <div class="grid grid-cols-7 gap-3">
                            <button 
                                v-for="day in days" :key="day.id"
                                @click="toggleDay(day.id)"
                                :class="['h-16 flex items-center justify-center rounded-2xl font-black text-sm transition-all border-2', 
                                        form.selectedDays.includes(day.id) ? 'bg-zinc-950 text-white dark:bg-white dark:text-zinc-950 border-zinc-950 dark:border-white shadow-xl' : 'bg-transparent text-slate-400 dark:text-slate-700 border-black/5 dark:border-white/5 hover:border-black/10 dark:hover:border-white/10']"
                            >
                                {{ day.label.substring(0, 1) }}
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">{{ $t('onboarding.start_time') }}</label>
                                <input type="time" v-model="form.startTime" class="w-full bg-zinc-100 dark:bg-zinc-950 border-2 border-black/5 dark:border-white/5 rounded-2xl px-6 py-5 text-zinc-950 dark:text-white font-bold text-lg outline-none focus:border-meetrix-orange transition-all">
                            </div>
                            <div class="space-y-4">
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">{{ $t('onboarding.end_time') }}</label>
                                <input type="time" v-model="form.endTime" class="w-full bg-zinc-100 dark:bg-zinc-950 border-2 border-black/5 dark:border-white/5 rounded-2xl px-6 py-5 text-zinc-950 dark:text-white font-bold text-lg outline-none focus:border-meetrix-orange transition-all">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="mt-16 flex gap-6">
                    <button 
                        v-if="step > 1"
                        @click="step--"
                        class="px-10 py-5 rounded-2xl font-black text-[10px] uppercase tracking-widest text-slate-500 bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 transition-all border border-black/5 dark:border-white/5"
                    >
                        {{ $t('common.back') }}
                    </button>
                    <button 
                        @click="nextStep"
                        :disabled="loading || (step === 1 && !canProceedStep1)"
                        class="flex-1 py-5 rounded-2xl font-black text-[10px] uppercase tracking-[0.3em] bg-zinc-950 text-white dark:bg-white dark:text-zinc-950 hover:scale-105 active:scale-95 transition-all shadow-premium flex items-center justify-center gap-4 group disabled:bg-zinc-200 dark:disabled:bg-zinc-800 disabled:text-slate-400 dark:disabled:text-slate-600"
                    >
                        <i v-if="loading" class="fas fa-circle-notch fa-spin text-lg"></i>
                        {{ step === 3 ? $t('onboarding.finish') : $t('onboarding.continue') }}
                        <i class="fas fa-arrow-right group-hover:translate-x-2 transition-transform"></i>
                    </button>
                </div>
            </div>
            
            <p class="text-[8px] font-black uppercase tracking-[0.5em] text-slate-800 text-center">
                MEETRIX_ONBOARDING // SYSTEM_INITIALIZATION_OK
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '../stores/auth';
import { usePageStore } from '../stores/page';
import axios from '../axios';

const { t } = useI18n();
const router = useRouter();
const authStore = useAuthStore();
const pageStore = usePageStore();

const step = ref(1);
const loading = ref(false);
const stepLabels = ['Profile', 'Page', 'Availability'];

const days = [
    { id: 1, label: 'Mon' }, { id: 2, label: 'Tue' }, { id: 3, label: 'Wed' }, 
    { id: 4, label: 'Thu' }, { id: 5, label: 'Fri' }, { id: 6, label: 'Sat' }, { id: 0, label: 'Sun' }
];

const form = reactive({
    name: authStore.user?.name || '',
    email: '',
    password: '',
    password_confirmation: '',
    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC',
    pageTitle: authStore.user ? `${authStore.user.name}'s Calendar` : 'My Calendar',
    pageSlug: '',
    selectedDays: [1, 2, 3, 4, 5],
    startTime: '09:00',
    endTime: '17:00'
});

const canProceedStep1 = computed(() => {
    if (!form.name?.trim()) return false;
    if (authStore.user) return true;

    return Boolean(
        form.email?.trim()
        && form.password
        && form.password_confirmation
        && form.password === form.password_confirmation
    );
});

// Sync name when user logs in during onboarding
watch(() => authStore.user, (newUser) => {
    if (newUser && !form.name) {
        form.name = newUser.name;
    }
    if (newUser && form.pageTitle === 'My Calendar') {
        form.pageTitle = `${newUser.name}'s Calendar`;
        generateSlug();
    }
}, { immediate: true });

const generateSlug = () => {
    form.pageSlug = form.pageTitle
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)/g, '');
};

generateSlug(); // Initial generation

const toggleDay = (id) => {
    if (form.selectedDays.includes(id)) {
        form.selectedDays = form.selectedDays.filter(d => d !== id);
    } else {
        form.selectedDays.push(id);
    }
};

const nextStep = async () => {
    console.log('Next step initiated. Current step:', step.value);
    
    if (step.value === 1 && !authStore.user) {
        console.log('Attempting guest registration...');
        loading.value = true;
        try {
            const success = await authStore.register({
                name: form.name,
                email: form.email,
                password: form.password,
                password_confirmation: form.password_confirmation,
                country_code: 'BR', // Defaulting for robust flow
                currency: 'BRL'      // Defaulting for robust flow
            });
            if (!success) {
                console.error('Registration failed:', authStore.error);
                alert(authStore.error);
                return;
            }
            console.log('Registration successful, moving to next step.');
        } catch (error) {
            console.error('Registration error:', error);
            alert(error.message);
            return;
        } finally {
            loading.value = false;
        }
    } else if (step.value === 1 && authStore.user) {
        console.log('User already authenticated, advancing to Step 2.');
    }

    if (step.value < 3) {
        step.value++;
        console.log('Step advanced to:', step.value);
        return;
    }

    // Final Step: Submission
    loading.value = true;
    try {
        // 1. Create the Page
        const page = await pageStore.createPage({
            title: form.pageTitle,
            slug: form.pageSlug,
            intro_text: 'Excited to meet you!',
        });

        // 2. Add Availability Rules
        await pageStore.updateAvailability(page.id, [{
            days_of_week: form.selectedDays,
            start_time: form.startTime + ':00',
            end_time: form.endTime + ':00',
        }]);

        // 3. Mark Onboarding as Complete
        console.log('Finalizing onboarding status...');
        await axios.post('/api/onboarding/complete');
        
        console.log('Step 3 Successful. Redirecting to Checkout...');
        loading.value = false;
        router.push('/checkout');
    } catch (error) {
        console.error('Onboarding finalization error:', error);
        alert(t('admin.save_failed') + ": " + (error.response?.data?.message || error.message));
    } finally {
        loading.value = false;
    }
};
</script>
