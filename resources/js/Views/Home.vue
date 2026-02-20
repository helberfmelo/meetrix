
<template>

        <!-- Hero Section: Asymmetric Tension -->
        <section class="relative pt-24 sm:pt-32 lg:pt-64 pb-20 sm:pb-32 px-4 sm:px-6 lg:px-24 min-h-[70vh] sm:min-h-[80vh] flex items-center overflow-hidden">
            <div class="max-w-screen-2xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-24 items-center w-full">
                <div class="lg:col-span-8 animate-in fade-in slide-in-from-left-8 duration-1000">
                    <h1 class="text-[clamp(2rem,8vw,5.5rem)] leading-[0.9] sm:leading-[0.85] font-black text-zinc-950 dark:text-white font-outfit tracking-tighter uppercase mb-6 sm:mb-8 max-w-4xl">
                        {{ $t('home.hero_title_1') }}<br/>
                        <span class="text-meetrix-orange">{{ $t('home.hero_title_2') }}</span>
                    </h1>
                </div>
                <div class="lg:col-span-4 flex flex-col items-start gap-8 animate-in fade-in slide-in-from-bottom-8 duration-1000 delay-300">
                    <p class="text-lg sm:text-xl md:text-2xl text-slate-500 dark:text-slate-400 max-w-md leading-tight font-medium">
                        {{ $t('home.hero_subtitle') }}
                    </p>
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 w-full sm:w-auto">
                        <router-link
                            to="/login"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 rounded-2xl border border-black/10 dark:border-white/20 bg-white/80 dark:bg-zinc-900/80 text-[10px] sm:text-xs font-black uppercase tracking-[0.2em] text-zinc-700 dark:text-slate-200 hover:border-meetrix-orange hover:text-zinc-950 dark:hover:text-white transition-all shadow-lg"
                        >
                            {{ $t('login.title') }}
                        </router-link>
                        
                        <router-link
                            to="/onboarding"
                            class="w-full sm:w-auto group inline-flex items-center justify-center gap-3 sm:gap-4 px-6 py-3 rounded-2xl bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 hover:bg-meetrix-orange hover:text-zinc-950 transition-all shadow-xl"
                        >
                            <span class="w-8 h-8 sm:w-9 sm:h-9 rounded-full border border-white/30 dark:border-zinc-950/20 flex items-center justify-center group-hover:border-zinc-950/30 group-hover:bg-zinc-950/10 transition-all duration-500">
                                <i class="fas fa-chevron-right text-white dark:text-zinc-950 group-hover:text-zinc-950 transition-colors"></i>
                            </span>
                            <span class="text-[10px] sm:text-xs font-black uppercase tracking-[0.2em]">{{ $t('home.get_started') }}</span>
                        </router-link>
                    </div>
                </div>
            </div>

            <!-- Background Aesthetic: Raw depth -->
            <div class="absolute -z-10 top-0 right-0 w-2/3 h-full opacity-20 pointer-events-none">
                <div class="absolute top-1/4 right-0 w-[800px] h-[800px] bg-meetrix-orange/20 rounded-full blur-[200px] animate-pulse"></div>
                <div class="absolute bottom-1/4 -left-1/4 w-[600px] h-[600px] bg-meetrix-green/10 rounded-full blur-[150px]"></div>
            </div>
            
            <!-- Grid noise -->
            <div class="absolute inset-0 -z-20 opacity-[0.03] pointer-events-none" 
                 style="background-image: url('https://www.transparenttextures.com/patterns/graphy-dark.png');"></div>
        </section>

        <!-- Dynamic Content: Feature Fragments -->
        <section class="py-20 sm:py-32 px-4 sm:px-6 lg:px-24 border-t border-black/5 dark:border-white/5">
            <div class="max-w-screen-2xl mx-auto">
                <!-- Full Width Title -->
                <div class="mb-14 sm:mb-24 text-center max-w-4xl mx-auto">
                    <h2 class="text-[clamp(2rem,7vw,4.5rem)] font-black text-zinc-950 dark:text-white uppercase tracking-tighter mb-6 sm:mb-8 leading-[0.95] sm:leading-[0.9]">{{ $t('home.features_title') }}</h2>
                    <p class="text-base sm:text-xl text-slate-500 font-medium leading-relaxed">{{ $t('home.features_subtitle') }}</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-12">
                    <div v-for="i in 3" :key="i" class="reveal-on-scroll group h-full">
                        <div class="h-full bg-zinc-50 dark:bg-zinc-900/40 p-6 sm:p-12 rounded-[2rem] sm:rounded-[3.5rem] border border-black/5 dark:border-white/5 hover:border-meetrix-orange/30 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-all duration-700 flex flex-col justify-between translate-y-8 opacity-0 group-[.in-view]:translate-y-0 group-[.in-view]:opacity-100"
                            :style="{ transitionDelay: `${i * 150}ms` }">
                            <div>
                                <span class="text-meetrix-orange font-black text-[9px] sm:text-[10px] uppercase tracking-[0.3em] sm:tracking-[0.4em] mb-8 sm:mb-12 block">
                                    <i class="fas fa-microchip mr-2"></i> 0{{ i }} // {{ $t('home.feature_label') }}
                                </span>
                                <h3 class="text-2xl sm:text-3xl font-black text-zinc-950 dark:text-white mb-4 sm:mb-6 uppercase font-outfit leading-none">{{ $t(`home.feature_${i}_title`) }}</h3>
                                <p class="text-base sm:text-lg text-slate-500 leading-snug font-medium mb-8 sm:mb-12">{{ $t(`home.feature_${i}_desc`) }}</p>
                            </div>
                            <!-- Small aesthetic marker instead of big mystery box -->
                            <div class="w-8 h-8 rounded-full border border-black/10 dark:border-white/20 flex items-center justify-center opacity-40 group-hover:opacity-100 group-hover:border-meetrix-orange transition-all duration-500">
                                <div class="w-2 h-2 rounded-full bg-zinc-400 dark:bg-zinc-600 group-hover:bg-meetrix-orange transition-all"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing: Extreme Contrast -->
        <section class="py-20 sm:py-32 px-4 sm:px-6 lg:px-24 bg-zinc-50 dark:bg-zinc-950 text-zinc-950 dark:text-white transition-colors duration-500">
            <div class="max-w-screen-2xl mx-auto flex flex-col lg:flex-row gap-24 items-start">
                <div class="lg:w-1/2 lg:sticky lg:top-32 pb-12">
                    <h2 class="text-[clamp(2rem,10vw,6.5rem)] leading-[0.9] sm:leading-[0.85] font-black uppercase tracking-tighter mb-8 sm:mb-12">
                        {{ $t('home.pricing_title_1') }} <span class="text-slate-400 dark:text-slate-600">{{ $t('home.pricing_title_2') }}</span><br/>
                        {{ $t('home.pricing_title_3') }} <span class="text-meetrix-orange">{{ $t('home.pricing_title_4') }}</span>
                    </h2>
                    
                    <!-- Billing Toggle -->
                    <div class="flex flex-wrap items-center gap-2 sm:gap-6 mt-8 sm:mt-12 bg-white dark:bg-zinc-900 p-2 rounded-2xl sm:rounded-full border border-black/5 dark:border-white/5 w-fit shadow-premium">
                        <button @click="billingCycle = 'monthly'" 
                            class="px-4 sm:px-8 py-2.5 sm:py-3 rounded-full text-[9px] sm:text-[10px] font-black uppercase tracking-widest transition-all"
                            :class="billingCycle === 'monthly' ? 'bg-zinc-900 text-white dark:bg-zinc-700/50 dark:text-white/90' : 'text-slate-400 hover:text-zinc-950 dark:hover:text-white'">
                            {{ $t('home.billing_monthly') }}
                        </button>
                        <button @click="billingCycle = 'annual'" 
                            class="px-4 sm:px-8 py-2.5 sm:py-3 rounded-full text-[9px] sm:text-[10px] font-black uppercase tracking-widest transition-all relative"
                            :class="billingCycle === 'annual' ? 'bg-zinc-900 text-white dark:bg-zinc-700/50 dark:text-white/90' : 'text-slate-400 hover:text-zinc-950 dark:hover:text-white'">
                            {{ $t('home.billing_annual') }}
                            <span class="absolute -top-4 -right-4 bg-meetrix-orange text-zinc-950 text-[8px] px-2 py-0.5 rounded-full animate-bounce font-black">-20%</span>
                        </button>
                    </div>
                </div>
                <div class="w-full lg:w-1/2 grid grid-cols-1 sm:grid-cols-2 gap-px bg-zinc-200 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 overflow-hidden rounded-5xl shadow-2xl">
                    <!-- Free -->
                    <div class="bg-white dark:bg-zinc-900 p-8 sm:p-12 flex flex-col h-full justify-between">
                        <div>
                            <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-8">{{ $t('home.plan_free') }}</h3>
                            <div class="text-4xl sm:text-6xl font-black mb-8 dark:text-white">{{ $t('home.price_free_value') }}</div>
                            <ul class="space-y-4 mb-20">
                                <li v-for="feat in $tm('home.free_features')" :key="feat" class="flex items-center text-sm font-bold text-slate-600 dark:text-slate-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-zinc-300 dark:bg-zinc-700 mr-3"></span> {{ $rt(feat) }}
                                </li>
                            </ul>
                        </div>
                        <router-link to="/onboarding" class="w-full py-4 text-center border-2 border-zinc-950 dark:border-white text-zinc-950 dark:text-white font-black text-xs uppercase tracking-widest hover:bg-zinc-950 hover:text-white dark:hover:bg-white dark:hover:text-zinc-950 active:scale-95 transition-all">
                            {{ $t('home.get_started') }}
                        </router-link>
                    </div>
                    <!-- Pro -->
                    <div class="bg-zinc-100 dark:bg-zinc-800 text-zinc-950 dark:text-white p-8 sm:p-12 flex flex-col h-full justify-between relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-tr from-meetrix-orange/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-1000"></div>
                        <div class="relative z-10">
                            <div class="flex justify-between items-center mb-8">
                                <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">{{ $t('home.plan_pro') }}</h3>
                                <span class="bg-meetrix-orange text-zinc-950 text-[10px] px-3 py-1 font-black rounded-full uppercase shadow-lg shadow-meetrix-orange/20">{{ $t('home.plan_pro_tag') }}</span>
                            </div>
                            <div class="text-4xl sm:text-6xl font-black mb-2 text-meetrix-orange">
                                {{ billingCycle === 'monthly' ? $t('home.price_pro_value') : $t('home.price_pro_annual_value') }}
                            </div>
                            <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-8">
                                {{ billingCycle === 'monthly' ? $t('home.per_month') : $t('home.per_year_incentive') }}
                            </div>
                            <ul class="space-y-4 mb-20">
                                <li v-for="feat in $tm('home.free_features')" :key="feat" class="flex items-center text-sm font-bold text-slate-600 dark:text-slate-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-meetrix-orange mr-3"></span> {{ $rt(feat) }}
                                </li>
                            </ul>
                        </div>
                        <router-link to="/onboarding" class="relative z-10 w-full py-4 text-center bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 font-black text-xs uppercase tracking-widest hover:scale-[1.02] active:scale-[0.98] transition-all shadow-xl shadow-black/10">
                            {{ $t('home.get_trial') }}
                        </router-link>
                    </div>
                </div>
            </div>
        </section>

</template>

<script setup>
import { ref, onMounted } from 'vue';
import LanguageSwitcher from '../Components/LanguageSwitcher.vue';

const billingCycle = ref('monthly');

onMounted(() => {
    // Basic Intersection Observer for scroll reveals
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('in-view');
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.reveal-on-scroll').forEach(el => observer.observe(el));

    // Emergency Reveal: If observer fails or user doesn't scroll immediately
    setTimeout(() => {
        document.querySelectorAll('.reveal-on-scroll').forEach(el => {
            el.classList.add('in-view');
        });
    }, 2000);
});
</script>

<style scoped>
/* Custom Animate-in utilities if not present in tailwind */
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

.fade-in { opacity: 0; animation-name: fade-in; }
.slide-in-from-left-8 { --tw-enter-translate-x: -2rem; }
.slide-in-from-bottom-8 { --tw-enter-translate-y: 2rem; }

@keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }
</style>
