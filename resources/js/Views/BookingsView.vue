<template>
    <div class="space-y-8 sm:space-y-12 animate-in fade-in duration-1000">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl sm:text-5xl font-black text-zinc-950 dark:text-white tracking-tighter uppercase font-outfit">{{ $t('common.bookings') }}<span class="text-meetrix-orange">.LOG</span></h1>
                <p class="text-slate-500 font-bold text-[10px] sm:text-xs uppercase tracking-[0.2em] sm:tracking-[0.4em] mt-2">{{ $t('admin.historical_data') }}</p>
            </div>
            <div class="w-full md:w-auto flex gap-4">
                <button class="w-full md:w-auto px-6 py-3 bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 rounded-full text-[10px] font-black text-slate-500 uppercase tracking-widest hover:text-zinc-950 dark:hover:text-white hover:border-meetrix-orange/30 transition-all shadow-sm flex items-center justify-center gap-2">
                    <i class="fas fa-file-csv"></i> {{ $t('admin.export_csv') }}
                </button>
            </div>
        </div>

        <!-- Bookings List -->
        <div class="bg-white dark:bg-zinc-950 rounded-3xl sm:rounded-5xl border border-black/5 dark:border-white/5 overflow-hidden shadow-premium">
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-zinc-50 dark:bg-zinc-900/50 border-b border-black/5 dark:border-white/5">
                        <tr>
                            <th class="px-6 lg:px-12 py-5 lg:py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ $t('admin.client') }}</th>
                            <th class="px-6 lg:px-12 py-5 lg:py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ $t('admin.service') }}</th>
                            <th class="px-6 lg:px-12 py-5 lg:py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ $t('admin.date_time') }}</th>
                            <th class="px-6 lg:px-12 py-5 lg:py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ $t('admin.status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        <tr v-if="normalizedBookings.length === 0">
                            <td colspan="4" class="px-6 lg:px-12 py-20 lg:py-24 text-center">
                                <div class="flex flex-col items-center gap-6">
                                    <div class="w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-900 flex items-center justify-center text-zinc-300 dark:text-zinc-800 text-2xl">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest">{{ $t('dashboard.no_bookings') }}</p>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="booking in normalizedBookings" :key="booking.id" class="group hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors">
                            <td class="px-6 lg:px-12 py-6 lg:py-8">
                                <p class="text-sm font-black text-zinc-950 dark:text-white uppercase tracking-tight">{{ booking.customerName }}</p>
                                <p class="text-[10px] text-slate-500 dark:text-slate-300 font-medium">{{ booking.customerEmail }}</p>
                            </td>
                            <td class="px-6 lg:px-12 py-6 lg:py-8">
                                <span class="px-4 py-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-lg text-[9px] font-black text-slate-700 dark:text-slate-100 uppercase tracking-widest">
                                    {{ booking.serviceName }}
                                </span>
                            </td>
                            <td class="px-6 lg:px-12 py-6 lg:py-8">
                                <p class="text-xs font-bold text-zinc-950 dark:text-white">{{ booking.startLabel }}</p>
                                <p class="text-[9px] text-slate-500 dark:text-slate-300 font-black uppercase tracking-tighter">{{ booking.durationLabel }}</p>
                            </td>
                            <td class="px-6 lg:px-12 py-6 lg:py-8">
                                <span class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest"
                                    :class="statusTextClass(booking.status)">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="statusDotClass(booking.status)"></span>
                                    {{ booking.statusLabel }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="md:hidden p-4 space-y-3">
                <div v-if="normalizedBookings.length === 0" class="py-16 text-center rounded-2xl border border-dashed border-black/10 dark:border-white/10">
                    <div class="w-12 h-12 mx-auto rounded-full bg-zinc-100 dark:bg-zinc-900 flex items-center justify-center text-zinc-300 dark:text-zinc-700 text-xl mb-4">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $t('dashboard.no_bookings') }}</p>
                </div>

                <article v-for="booking in normalizedBookings" :key="`mobile-${booking.id}`" class="rounded-2xl border border-black/5 dark:border-white/5 bg-zinc-50/70 dark:bg-zinc-900/40 p-4 space-y-3">
                    <div>
                        <p class="text-sm font-black text-zinc-950 dark:text-white uppercase tracking-tight">{{ booking.customerName }}</p>
                        <p class="text-[10px] text-slate-500 dark:text-slate-300 font-medium break-all">{{ booking.customerEmail }}</p>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="px-3 py-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-lg text-[9px] font-black text-slate-700 dark:text-slate-100 uppercase tracking-widest">
                            {{ booking.serviceName }}
                        </span>
                        <span class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest"
                            :class="statusTextClass(booking.status)">
                            <span class="w-1.5 h-1.5 rounded-full" :class="statusDotClass(booking.status)"></span>
                            {{ booking.statusLabel }}
                        </span>
                    </div>
                    <div class="text-[10px] font-bold text-slate-500 dark:text-slate-300">
                        <p class="text-zinc-950 dark:text-white">{{ booking.startLabel }}</p>
                        <p class="uppercase">{{ booking.durationLabel }}</p>
                    </div>
                </article>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import axios from '../axios';

const { t, locale } = useI18n();
const bookings = ref([]);

const normalizedBookings = computed(() => bookings.value.map((booking) => normalizeBooking(booking)));

const statusTextClass = (status) => {
    const key = (status || '').toLowerCase();

    if (key === 'confirmed' || key === 'completed') return 'text-meetrix-green';
    if (key === 'pending') return 'text-meetrix-orange';
    if (key === 'cancelled' || key === 'canceled' || key === 'rejected' || key === 'no_show') return 'text-red-500';

    return 'text-slate-500 dark:text-slate-300';
};

const statusDotClass = (status) => {
    const key = (status || '').toLowerCase();

    if (key === 'confirmed' || key === 'completed') return 'bg-meetrix-green';
    if (key === 'pending') return 'bg-meetrix-orange';
    if (key === 'cancelled' || key === 'canceled' || key === 'rejected' || key === 'no_show') return 'bg-red-500';

    return 'bg-slate-400 dark:bg-slate-500';
};

const normalizeBooking = (booking) => {
    const appointmentType = booking.appointment_type || booking.appointmentType || null;
    const startAt = booking.start_at || booking.startAt || null;
    const endAt = booking.end_at || booking.endAt || null;

    let durationMinutes = Number(appointmentType?.duration_minutes ?? booking.duration ?? 0);
    if ((!durationMinutes || Number.isNaN(durationMinutes)) && startAt && endAt) {
        const startDate = new Date(startAt);
        const endDate = new Date(endAt);
        const deltaMs = endDate.getTime() - startDate.getTime();
        if (!Number.isNaN(deltaMs) && deltaMs > 0) {
            durationMinutes = Math.round(deltaMs / 60000);
        }
    }

    return {
        id: booking.id,
        status: booking.status || 'pending',
        statusLabel: resolveStatusLabel(booking.status),
        customerName: booking.customer_name || booking.customerName || t('booking.customer_fallback'),
        customerEmail: booking.customer_email || booking.customerEmail || t('booking.email_fallback'),
        serviceName: appointmentType?.name || booking.service_name || t('booking.service_fallback'),
        startLabel: formatDateTime(startAt),
        durationLabel: durationMinutes > 0
            ? `${durationMinutes} ${t('booking.minutes_short')}`
            : t('booking.duration_unknown'),
    };
};

const resolveStatusLabel = (status) => {
    const key = (status || '').toLowerCase();
    const map = {
        pending: 'booking.status_pending',
        confirmed: 'booking.status_confirmed',
        completed: 'booking.status_completed',
        cancelled: 'booking.status_cancelled',
        canceled: 'booking.status_cancelled',
        rejected: 'booking.status_rejected',
        no_show: 'booking.status_no_show',
    };

    return map[key] ? t(map[key]) : t('booking.status_unknown');
};

const formatDateTime = (value) => {
    if (!value) return t('booking.date_unknown');

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return t('booking.date_unknown');

    return new Intl.DateTimeFormat(locale.value || 'pt-BR', {
        dateStyle: 'short',
        timeStyle: 'short',
    }).format(date);
};

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
