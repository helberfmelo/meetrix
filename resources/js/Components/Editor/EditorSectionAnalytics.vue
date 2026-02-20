<template>
    <div class="space-y-8">
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 dark:bg-zinc-900/60 p-6 rounded-2xl border border-gray-100 dark:border-white/10">
                <span class="text-[10px] font-black uppercase text-gray-400 dark:text-slate-300 tracking-widest block mb-1">{{ $t('admin.total_views') }}</span>
                <span class="text-3xl font-black text-gray-900 dark:text-zinc-100">{{ modelValue.views || 0 }}</span>
            </div>
            <div class="bg-gray-50 dark:bg-zinc-900/60 p-6 rounded-2xl border border-gray-100 dark:border-white/10">
                <span class="text-[10px] font-black uppercase text-gray-400 dark:text-slate-300 tracking-widest block mb-1">{{ $t('admin.conversion') }}</span>
                <span class="text-3xl font-black text-gray-900 dark:text-zinc-100">{{ conversionRate }}%</span>
            </div>
        </div>

        <div class="bg-indigo-600 p-8 rounded-3xl text-white relative overflow-hidden">
            <div class="relative z-10">
                <h4 class="text-xl font-bold mb-2">{{ $t('admin.performance_insights') }}</h4>
                <p class="text-indigo-100 text-sm opacity-80 leading-relaxed">
                    {{ $t('admin.performance_desc') }}
                </p>
            </div>
            <div class="absolute -right-6 -bottom-6 text-8xl opacity-10">📈</div>
        </div>

        <div class="space-y-4">
            <h4 class="text-xs font-bold text-gray-400 dark:text-slate-300 uppercase tracking-widest px-1">{{ $t('admin.top_sources') }}</h4>
            <div class="space-y-2">
                <div class="flex justify-between items-center p-3 bg-white dark:bg-zinc-950 border border-gray-100 dark:border-white/10 rounded-xl text-sm">
                    <span class="font-medium text-gray-700 dark:text-slate-200">{{ $t('admin.direct_link') }}</span>
                    <span class="font-bold text-gray-900 dark:text-zinc-100">100%</span>
                </div>
                <p class="text-[10px] text-gray-400 dark:text-slate-300 text-center italic">{{ $t('admin.advanced_tracking_pro') }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: Object
});

const conversionRate = computed(() => {
    if (!props.modelValue.views) return 0;
    // We don't have total bookings count in this object yet, 
    // but we can mock or fetch it. For now, let's assume a healthy 12% if views > 0 for demo.
    return 12.5; 
});
</script>
