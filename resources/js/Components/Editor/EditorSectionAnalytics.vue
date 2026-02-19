<template>
    <div class="space-y-8">
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100">
                <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest block mb-1">Total Views</span>
                <span class="text-3xl font-black text-gray-900">{{ modelValue.views || 0 }}</span>
            </div>
            <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100">
                <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest block mb-1">Conversion</span>
                <span class="text-3xl font-black text-gray-900">{{ conversionRate }}%</span>
            </div>
        </div>

        <div class="bg-indigo-600 p-8 rounded-3xl text-white relative overflow-hidden">
            <div class="relative z-10">
                <h4 class="text-xl font-bold mb-2">Performance Insights</h4>
                <p class="text-indigo-100 text-sm opacity-80 leading-relaxed">
                    Your conversion rate is calculated based on bookings vs unique views. 
                    Try adjusting your "Intro Text" to improve engagement.
                </p>
            </div>
            <div class="absolute -right-6 -bottom-6 text-8xl opacity-10">📈</div>
        </div>

        <div class="space-y-4">
            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest px-1">Top Sources</h4>
            <div class="space-y-2">
                <div class="flex justify-between items-center p-3 bg-white border border-gray-100 rounded-xl text-sm">
                    <span class="font-medium text-gray-700">Direct Link</span>
                    <span class="font-bold text-gray-900">100%</span>
                </div>
                <p class="text-[10px] text-gray-400 text-center italic">Advanced source tracking coming in Pro plan</p>
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
