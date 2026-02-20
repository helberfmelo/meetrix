<template>
    <div class="space-y-12 animate-in fade-in duration-1000">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-5xl font-black text-zinc-950 dark:text-white tracking-tighter uppercase font-outfit">{{ $t('common.bookings') }}<span class="text-meetrix-orange">.LOG</span></h1>
                <p class="text-slate-500 font-bold text-xs uppercase tracking-[0.4em] mt-2">{{ $t('admin.historical_data') }}</p>
            </div>
            <div class="flex gap-4">
                <button class="px-6 py-3 bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 rounded-full text-[10px] font-black text-slate-500 uppercase tracking-widest hover:text-zinc-950 dark:hover:text-white hover:border-meetrix-orange/30 transition-all shadow-sm flex items-center gap-2">
                    <i class="fas fa-file-csv"></i> {{ $t('admin.export_csv') }}
                </button>
            </div>
        </div>

        <!-- Bookings List -->
        <div class="bg-white dark:bg-zinc-950 rounded-5xl border border-black/5 dark:border-white/5 overflow-hidden shadow-premium">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-zinc-50 dark:bg-zinc-900/50 border-b border-black/5 dark:border-white/5">
                        <tr>
                            <th class="px-12 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ $t('admin.client') }}</th>
                            <th class="px-12 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ $t('admin.service') }}</th>
                            <th class="px-12 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ $t('admin.date_time') }}</th>
                            <th class="px-12 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ $t('admin.status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        <tr v-if="bookings.length === 0">
                            <td colspan="4" class="px-12 py-24 text-center">
                                <div class="flex flex-col items-center gap-6">
                                    <div class="w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-900 flex items-center justify-center text-zinc-300 dark:text-zinc-800 text-2xl">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest">{{ $t('dashboard.no_bookings') }}</p>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="booking in bookings" :key="booking.id" class="group hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors">
                            <td class="px-12 py-8">
                                <p class="text-sm font-black text-zinc-950 dark:text-white uppercase tracking-tight">{{ booking.customer_name }}</p>
                                <p class="text-[10px] text-slate-500 font-medium">{{ booking.customer_email }}</p>
                            </td>
                            <td class="px-12 py-8">
                                <span class="px-4 py-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-lg text-[9px] font-black text-slate-600 dark:text-slate-400 uppercase tracking-widest">
                                    {{ booking.service_name }}
                                </span>
                            </td>
                            <td class="px-12 py-8">
                                <p class="text-xs font-bold text-zinc-950 dark:text-white">{{ booking.start_time }}</p>
                                <p class="text-[9px] text-slate-500 font-black uppercase tracking-tighter">{{ booking.duration }} MIN</p>
                            </td>
                            <td class="px-12 py-8">
                                <span class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest"
                                    :class="booking.status === 'confirmed' ? 'text-meetrix-green' : 'text-meetrix-orange'">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="booking.status === 'confirmed' ? 'bg-meetrix-green' : 'bg-meetrix-orange'"></span>
                                    {{ booking.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '../axios';

const bookings = ref([]);

onMounted(async () => {
    try {
        const response = await axios.get('/api/bookings');
        bookings.value = response.data.data || [];
    } catch (e) {
        console.error('Failed to fetch bookings', e);
    }
});
</script>

<style scoped>
.shadow-premium {
    box-shadow: 0 40px 80px -20px rgba(0, 0, 0, 0.05);
}
.dark .shadow-premium {
    box-shadow: 0 40px 80px -20px rgba(0, 0, 0, 0.5);
}
</style>
