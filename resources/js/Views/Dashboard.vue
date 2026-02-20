<template>
    <div class="space-y-8 sm:space-y-12 animate-in fade-in duration-1000">
        <!-- Header: Sovereign Style -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 sm:gap-6">
            <div>
                <h1 class="text-3xl sm:text-5xl font-black text-zinc-950 dark:text-white tracking-tighter uppercase font-outfit">{{ $t('dashboard.title') }}<span class="text-meetrix-orange">.INSIGHTS</span></h1>
                <p class="text-slate-500 font-bold text-[10px] sm:text-xs uppercase tracking-[0.2em] sm:tracking-[0.4em] mt-2">{{ $t('dashboard.insights_subtitle') }}</p>
            </div>
            <div class="px-4 sm:px-6 py-2.5 sm:py-3 bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 rounded-full text-[9px] sm:text-[10px] font-black text-slate-400 uppercase tracking-wide sm:tracking-widest flex items-center gap-2 sm:gap-3 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-meetrix-green animate-pulse"></span>
                {{ $t('dashboard.system_live') }}: {{ new Date().toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' }) }}
            </div>
        </div>
        
        <!-- Continuous Data Stream: Vertical Layout -->
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 sm:gap-12">
            
            <!-- Left Column: Primary Stats & Stream -->
            <div class="xl:col-span-8 space-y-6 sm:space-y-12">
                
                <!-- Stat Grid: Extreme Depth -->
                <div v-if="loadingStats" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-8 animate-pulse">
                    <div v-for="i in 3" :key="i" class="h-36 sm:h-40 bg-white dark:bg-zinc-900 rounded-3xl sm:rounded-5xl border border-black/5 dark:border-white/5"></div>
                </div>
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-8">
                    <div class="group relative bg-white dark:bg-zinc-900 p-6 sm:p-8 rounded-3xl sm:rounded-5xl border border-black/5 dark:border-white/5 hover:border-meetrix-orange/30 transition-all duration-500 overflow-hidden shadow-premium">
                        <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-meetrix-orange/5 rounded-full blur-3xl group-hover:bg-meetrix-orange/10 transition-all duration-500"></div>
                        <h3 class="text-slate-500 text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em] sm:tracking-[0.3em] mb-3 sm:mb-4">{{ $t('dashboard.total_bookings') }}</h3>
                        <p class="text-4xl sm:text-6xl font-black text-zinc-950 dark:text-white tracking-tighter">{{ stats.total_bookings }}</p>
                        <div class="mt-4 sm:mt-6 flex items-center gap-2">
                            <span class="text-meetrix-green text-xs font-black">{{ stats.confirmed_rate }}% {{ $t('dashboard.confirmed_tag') }}</span>
                            <div class="flex-1 h-px bg-black/5 dark:bg-white/5"></div>
                        </div>
                    </div>

                    <div class="group relative bg-white dark:bg-zinc-900 p-6 sm:p-8 rounded-3xl sm:rounded-5xl border border-black/5 dark:border-white/5 hover:border-meetrix-green/30 transition-all duration-500 overflow-hidden shadow-premium">
                        <h3 class="text-slate-500 text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em] sm:tracking-[0.3em] mb-3 sm:mb-4">{{ $t('dashboard.active_pages') }}</h3>
                        <p class="text-4xl sm:text-6xl font-black text-zinc-950 dark:text-white tracking-tighter">{{ stats.active_pages }}</p>
                        <div class="mt-4 sm:mt-6 flex items-center gap-2 text-[9px] sm:text-[10px] font-black text-slate-500 uppercase tracking-wide sm:tracking-widest">
                            {{ $t('dashboard.pages_online_tag') }}
                        </div>
                    </div>

                    <div class="group relative bg-zinc-50 dark:bg-zinc-950 p-6 sm:p-8 rounded-3xl sm:rounded-5xl border border-black/5 dark:border-white/5 hover:border-white/10 transition-all duration-500 overflow-hidden shadow-premium">
                        <div class="absolute inset-0 bg-gradient-to-br from-meetrix-orange/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <h3 class="text-slate-500 text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em] sm:tracking-[0.3em] mb-3 sm:mb-4">{{ $t('dashboard.revenue_engine') }}</h3>
                        <p class="text-4xl sm:text-6xl font-black text-meetrix-orange tracking-tighter">PRO</p>
                        <div class="mt-4 sm:mt-6 flex items-center gap-2">
                             <span class="text-xs font-black text-zinc-950 dark:text-white px-2 py-0.5 border border-black/10 dark:border-white/10 rounded tracking-widest">{{ $t('dashboard.active_tag') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Funnel: 3D Visualization -->
                <div class="bg-white dark:bg-zinc-900 rounded-3xl sm:rounded-5xl border border-black/5 dark:border-white/5 p-5 sm:p-8 lg:p-12 relative overflow-hidden shadow-premium">
                    <h2 class="text-xl sm:text-2xl font-black text-zinc-950 dark:text-white uppercase tracking-tighter mb-6 sm:mb-12 flex items-center gap-3 sm:gap-4">
                        <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg sm:rounded-xl bg-meetrix-orange/20 flex items-center justify-center text-meetrix-orange text-xs text-center"><i class="fas fa-filter"></i></span>
                        {{ $t('dashboard.conversion_arch') }}
                    </h2>
                    
                    <div class="space-y-6 sm:space-y-12 relative z-10">
                        <div v-for="(stage, index) in funnelData" :key="index" class="group relative flex items-end gap-3 sm:gap-8">
                            <div class="flex-1">
                                <div class="flex justify-between items-end gap-4 mb-3 sm:mb-4">
                                    <span class="text-[10px] sm:text-xs font-black uppercase tracking-[0.15em] sm:tracking-[0.2em] text-slate-500">{{ $t(stage.label) }}</span>
                                    <span class="text-xl sm:text-2xl font-black text-zinc-950 dark:text-white tracking-tighter">{{ stage.value }}</span>
                                </div>
                                <div class="h-12 sm:h-16 bg-zinc-50 dark:bg-zinc-950 rounded-xl sm:rounded-2xl p-1 overflow-hidden shadow-inner relative">
                                    <div class="h-full bg-gradient-to-r transition-all duration-1000 ease-out flex items-center px-3 sm:px-6"
                                        :class="stage.color"
                                        :style="{ width: stage.percent + '%' }">
                                        <span class="text-[8px] sm:text-[10px] font-black text-white/50 tracking-wide sm:tracking-widest uppercase" v-if="stage.percent > 10">
                                            {{ $t(stage.sublabel) }} // {{ stage.percent }}%
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <!-- 3D Depth Indicator -->
                            <div class="hidden lg:flex w-32 h-16 bg-white dark:bg-zinc-950 rounded-2xl border border-black/5 dark:border-white/5 flex-col justify-center items-center shadow-3d group-hover:shadow-3d-hover transition-all translate-y-[-4px] group-hover:translate-y-0 duration-300">
                                <span class="text-[8px] font-black text-slate-600 uppercase">{{ $t('dashboard.retention') }}</span>
                                <span class="text-lg font-black text-zinc-950 dark:text-white leading-none">{{ stage.retention }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Continuous Stream -->
            <div class="xl:col-span-4 space-y-6 sm:space-y-8">
                <div class="bg-white dark:bg-zinc-950 rounded-3xl sm:rounded-5xl border border-black/5 dark:border-white/5 p-5 sm:p-8 lg:p-10 h-full shadow-premium">
                    <h2 class="text-[10px] sm:text-xs font-black text-zinc-950 dark:text-white uppercase tracking-[0.2em] sm:tracking-[0.5em] mb-6 sm:mb-12">LIVE_STREAM_PROTOCOL</h2>
                    
                    <div v-if="pageStore.pages.length === 0" class="flex flex-col items-center justify-center py-14 sm:py-24 text-center">
                         <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-3xl sm:rounded-4xl bg-zinc-100 dark:bg-zinc-900 border border-black/5 dark:border-white/5 flex items-center justify-center mb-6 sm:mb-8">
                             <div class="w-2 h-2 rounded-full bg-meetrix-orange animate-ping"></div>
                         </div>
                         <p class="text-[10px] sm:text-xs font-black text-slate-600 uppercase tracking-wide sm:tracking-widest">{{ $t('dashboard.waiting_first_page') }}</p>
                    </div>

                    <div v-else class="space-y-3 sm:space-y-4">
                        <div v-for="page in pageStore.pages" :key="page.id" 
                            class="group p-4 sm:p-6 rounded-2xl sm:rounded-4xl border border-transparent bg-zinc-50 dark:bg-zinc-900/50 hover:bg-zinc-100 dark:hover:bg-zinc-900 hover:border-black/5 dark:hover:border-white/5 transition-all duration-500 relative overflow-hidden">
                            <div class="absolute -left-2 top-0 bottom-0 w-1 bg-meetrix-orange opacity-0 group-hover:opacity-100 transition-all rounded-full"></div>
                            <div class="flex justify-between items-start gap-3 mb-4 sm:mb-6">
                                <div class="min-w-0">
                                    <h3 class="text-base sm:text-lg font-black text-zinc-950 dark:text-white uppercase tracking-tighter truncate group-hover:text-meetrix-orange transition-colors">{{ page.title }}</h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-meetrix-green"></span>
                                        <span class="text-[8px] font-black text-slate-500 tracking-widest uppercase">{{ $t('dashboard.node_status') }}</span>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                     <a :href="`/p/${page.slug}`" target="_blank" class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded-xl border border-black/5 dark:border-white/5 text-zinc-400 hover:text-zinc-950 dark:hover:text-white transition-colors">
                                        <i class="fas fa-external-link-alt text-xs"></i>
                                    </a>
                                    <router-link :to="`/dashboard/editor/${page.slug}`" class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded-xl border border-black/5 dark:border-white/5 text-zinc-400 hover:text-zinc-950 dark:hover:text-white transition-colors">
                                        <i class="fas fa-edit text-xs"></i>
                                    </router-link>
                                </div>
                            </div>
                            <div class="flex justify-between items-center pt-4 sm:pt-6 border-t border-black/5 dark:border-white/5 text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">
                                <span>{{ $t('dashboard.views_index') }}</span>
                                <span class="text-zinc-950 dark:text-white">{{ page.views || 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Info: Raw Industrial -->
        <div class="py-8 sm:py-12 border-t border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 sm:gap-6 text-[8px] font-black text-slate-400 dark:text-slate-600 uppercase tracking-[0.2em] sm:tracking-[0.5em]">
            <div class="text-center sm:text-left">MEETRIX // CORE_ENGINE_STATUS: 200_OK</div>
            <div class="flex gap-4 sm:gap-8 justify-center">
                <span>VER: 2.1.0-PRO</span>
                <span>ENC: SHA-256</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import { usePageStore } from '../stores/page';
import { useAuthStore } from '../stores/auth';
import axios from '../axios';

const router = useRouter();
const pageStore = usePageStore();
const authStore = useAuthStore();

const loadingStats = ref(true);
const stats = ref({ total_bookings: 0, active_pages: 0, upcoming: 0, confirmed_rate: 0 });
const funnel = ref({ views: 0, clicks: 0, bookings: 0, conversion_rate: 0 });

const funnelData = computed(() => [
    { 
        label: 'dashboard.impressions', 
        value: funnel.value.views, 
        percent: 100, 
        sublabel: 'dashboard.page_reach', 
        color: 'from-zinc-800 to-zinc-900',
        retention: 100
    },
    { 
        label: 'dashboard.intent_signals', 
        value: funnel.value.clicks, 
        percent: funnel.value.views > 0 ? Math.round(funnel.value.clicks / funnel.value.views * 100) : 0, 
        sublabel: 'dashboard.slot_interaction', 
        color: 'from-meetrix-orange/80 to-meetrix-orange',
        retention: funnel.value.views > 0 ? Math.round(funnel.value.clicks / funnel.value.views * 100) : 0
    },
    { 
        label: 'dashboard.confirmed_conversions', 
        value: funnel.value.bookings, 
        percent: funnel.value.clicks > 0 ? Math.round(funnel.value.bookings / funnel.value.clicks * 100) : 0, 
        sublabel: 'dashboard.success_booking', 
        color: 'from-meetrix-green/80 to-meetrix-green',
        retention: funnel.value.clicks > 0 ? Math.round(funnel.value.bookings / funnel.value.clicks * 100) : 0
    }
]);

const checkOnboarding = () => {
    if (authStore.user?.is_super_admin) {
        return false;
    }

    if (authStore.user && !authStore.user.onboarding_completed_at) {
        router.push('/onboarding');
        return true;
    }
    return false;
};

const fetchStats = async () => {
    try {
        const response = await axios.get('/api/dashboard/stats');
        stats.value = response.data.stats;
        funnel.value = response.data.funnel;
    } catch (e) {
        console.error(e);
    } finally {
        loadingStats.value = false;
    }
};

onMounted(async () => {
    if (checkOnboarding()) return;
    await Promise.all([
        pageStore.fetchPages(),
        fetchStats()
    ]);
});

watch(() => authStore.user, () => {
    checkOnboarding();
}, { immediate: true });

</script>

<style scoped>
.shadow-premium {
    box-shadow: 0 40px 80px -20px rgba(0, 0, 0, 0.1);
}

.dark .shadow-premium {
    box-shadow: 0 40px 80px -20px rgba(0, 0, 0, 0.8);
}

.shadow-inner {
    box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.05);
}

.dark .shadow-inner {
    box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.6);
}

.shadow-3d {
    box-shadow: 0 8px 0 rgb(230,230,230), 0 15px 20px rgba(0,0,0,.05);
}

.dark .shadow-3d {
    box-shadow: 0 8px 0 rgb(0,0,0), 0 15px 20px rgba(0,0,0,.5);
}

.shadow-3d-hover {
    box-shadow: 0 2px 0 rgb(230,230,230), 0 5px 10px rgba(0,0,0,.05);
}

.dark .shadow-3d-hover {
    box-shadow: 0 2px 0 rgb(0,0,0), 0 5px 10px rgba(0,0,0,.5);
}

.animate-in {
    animation: animate-in 1s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes animate-in {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
