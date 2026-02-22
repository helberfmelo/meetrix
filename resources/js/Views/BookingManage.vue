<template>
    <div class="min-h-screen bg-gray-50/50 py-6 sm:py-12 px-3 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <div v-if="loading" class="flex justify-center items-center h-64">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2" :style="{ borderColor: primaryColor }"></div>
            </div>

            <div v-else class="bg-white rounded-3xl shadow-2xl border border-gray-100 p-6 sm:p-10 space-y-8">
                <header class="space-y-2">
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 uppercase tracking-tighter">
                        {{ $t('booking.manage_title') }}
                    </h1>
                    <p class="text-sm text-slate-500 font-medium">{{ $t('booking.manage_subtitle') }}</p>
                </header>

                <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 text-red-700 p-4 text-sm font-semibold">
                    {{ error }}
                </div>

                <div v-if="booking" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <section class="rounded-2xl border border-slate-100 p-5 space-y-4">
                        <h2 class="text-xs font-black uppercase tracking-widest text-slate-400">{{ $t('booking.manage_current') }}</h2>
                        <div class="space-y-2 text-sm">
                            <p class="font-bold text-slate-900">{{ booking.customer_name }}</p>
                            <p class="text-slate-500">{{ booking.customer_email }}</p>
                            <p class="text-slate-500">{{ appointmentType?.name }}</p>
                            <p class="text-slate-500">{{ formatDateTime(booking.start_at) }}</p>
                            <p class="text-slate-500 uppercase tracking-widest text-[10px] font-black">{{ booking.status }}</p>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-100 p-5 space-y-4">
                        <h2 class="text-xs font-black uppercase tracking-widest text-slate-400">{{ $t('booking.manage_new_time') }}</h2>

                        <div v-if="mode === 'view' && !confirmingCancel" class="flex flex-col gap-3">
                            <button
                                class="w-full py-3 rounded-2xl bg-zinc-950 text-white text-[10px] font-black uppercase tracking-[0.2em]"
                                @click="mode = 'reschedule'"
                            >
                                {{ $t('booking.manage_reschedule') }}
                            </button>
                            <button
                                class="w-full py-3 rounded-2xl border border-red-200 text-red-600 text-[10px] font-black uppercase tracking-[0.2em]"
                                @click="cancelBooking"
                            >
                                {{ $t('booking.manage_cancel') }}
                            </button>
                        </div>

                        <div v-else-if="mode === 'view' && confirmingCancel" class="space-y-4">
                            <p class="text-sm text-slate-500 font-semibold">
                                {{ $t('booking.manage_cancel_confirm') }}
                            </p>
                            <div class="flex gap-3">
                                <button
                                    class="flex-1 py-3 rounded-2xl border border-slate-200 text-slate-500 text-[10px] font-black uppercase tracking-[0.2em]"
                                    @click="confirmingCancel = false"
                                >
                                    {{ $t('common.back') }}
                                </button>
                                <button
                                    class="flex-1 py-3 rounded-2xl border border-red-200 text-red-600 text-[10px] font-black uppercase tracking-[0.2em] disabled:opacity-50"
                                    :disabled="submitting"
                                    @click="cancelBooking"
                                >
                                    {{ submitting ? $t('common.loading') : $t('booking.manage_cancel') }}
                                </button>
                            </div>
                        </div>

                        <div v-else class="space-y-4">
                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                                <button
                                    v-for="date in upcomingDates"
                                    :key="date"
                                    @click="selectDate(date)"
                                    class="p-3 rounded-xl border-2 flex flex-col items-center transition-all text-xs font-black uppercase tracking-wider"
                                    :style="dateStyle(date)"
                                >
                                    <span>{{ getDayName(date) }}</span>
                                    <span class="text-base">{{ getDayNum(date) }}</span>
                                </button>
                            </div>

                            <div v-if="fetchingSlots" class="py-6 text-center text-slate-400 text-xs font-black uppercase tracking-widest">
                                {{ $t('common.loading') }}
                            </div>
                            <div v-else-if="slots.length > 0" class="grid grid-cols-2 gap-2">
                                <button
                                    v-for="slot in slots"
                                    :key="slot"
                                    @click="selectedSlot = slot"
                                    class="py-3 rounded-xl border-2 text-xs font-black uppercase tracking-widest transition-all"
                                    :style="slotStyle(slot)"
                                >
                                    {{ slot }}
                                </button>
                            </div>
                            <div v-else-if="selectedDate" class="py-6 text-center text-slate-300 text-[10px] font-black uppercase tracking-widest">
                                {{ $t('booking.no_slots') }}
                            </div>

                            <div class="flex gap-3 pt-2">
                                <button
                                    class="flex-1 py-3 rounded-2xl border border-slate-200 text-slate-500 text-[10px] font-black uppercase tracking-[0.2em]"
                                    @click="mode = 'view'"
                                >
                                    {{ $t('common.back') }}
                                </button>
                                <button
                                    class="flex-1 py-3 rounded-2xl bg-zinc-950 text-white text-[10px] font-black uppercase tracking-[0.2em] disabled:opacity-50"
                                    :disabled="!selectedDate || !selectedSlot || submitting"
                                    @click="submitReschedule"
                                >
                                    {{ submitting ? $t('common.loading') : $t('booking.manage_reschedule') }}
                                </button>
                            </div>
                        </div>
                    </section>
                </div>

                <div v-if="feedback" class="rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-700 p-4 text-sm font-semibold">
                    {{ feedback }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import axios from 'axios';

const route = useRoute();
const { t } = useI18n();

const loading = ref(true);
const error = ref('');
const feedback = ref('');
const booking = ref(null);
const appointmentType = ref(null);
const page = ref(null);
const mode = ref('view');
const confirmingCancel = ref(false);

const selectedDate = ref(null);
const selectedSlot = ref(null);
const slots = ref([]);
const fetchingSlots = ref(false);
const submitting = ref(false);

const token = computed(() => route.query.token);
const timezone = computed(() => booking.value?.customer_timezone || Intl.DateTimeFormat().resolvedOptions().timeZone);
const primaryColor = computed(() => page.value?.config?.primary_color || '#4f46e5');

const upcomingDates = computed(() => {
    const dates = [];
    const today = new Date();
    for (let i = 0; i < 14; i++) {
        const d = new Date(today);
        d.setDate(today.getDate() + i);
        dates.push(toDateKey(d));
    }
    return dates;
});

const parseDateKey = (dateStr) => {
    const [year, month, day] = dateStr.split('-').map(Number);
    return new Date(year, month - 1, day, 12, 0, 0, 0);
};

const toDateKey = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const getDayName = (dateStr) => parseDateKey(dateStr).toLocaleDateString(undefined, { weekday: 'short' });
const getDayNum = (dateStr) => parseDateKey(dateStr).getDate();

const formatDateTime = (iso) => {
    if (!iso) return '-';
    const date = new Date(iso);
    return date.toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
};

const dateStyle = (date) => ({
    backgroundColor: selectedDate.value === date ? primaryColor.value : 'transparent',
    borderColor: selectedDate.value === date ? primaryColor.value : '#f3f4f6',
    color: selectedDate.value === date ? '#fff' : '#475569',
});

const slotStyle = (slot) => ({
    borderColor: selectedSlot.value === slot ? primaryColor.value : '#e2e8f0',
    color: selectedSlot.value === slot ? '#fff' : primaryColor.value,
    backgroundColor: selectedSlot.value === slot ? primaryColor.value : 'transparent',
});

const fetchBooking = async () => {
    if (!token.value) {
        error.value = t('booking.manage_invalid');
        loading.value = false;
        return;
    }

    try {
        const response = await axios.get(`/api/public/p/${route.params.slug}/booking/${token.value}`);
        booking.value = response.data.booking;
        appointmentType.value = response.data.appointment_type;
        page.value = response.data.page;
    } catch (err) {
        error.value = err.response?.data?.message || t('booking.manage_error');
    } finally {
        loading.value = false;
    }
};

const selectDate = async (date) => {
    selectedDate.value = date;
    selectedSlot.value = null;
    slots.value = [];
    fetchingSlots.value = true;
    try {
        const response = await axios.get(`/api/p/${route.params.slug}/slots`, {
            params: {
                date,
                appointment_type_id: appointmentType.value?.id,
                timezone: timezone.value,
            }
        });
        slots.value = response.data;
    } catch (err) {
        error.value = err.response?.data?.message || t('booking.manage_error');
    } finally {
        fetchingSlots.value = false;
    }
};

const cancelBooking = async () => {
    if (!confirmingCancel.value) {
        confirmingCancel.value = true;
        return;
    }

    confirmingCancel.value = false;
    submitting.value = true;
    error.value = '';
    try {
        const response = await axios.post(`/api/public/p/${route.params.slug}/booking/${token.value}/cancel`);
        booking.value.status = response.data.booking?.status || 'cancelled';
        feedback.value = t('booking.manage_cancel_success');
    } catch (err) {
        error.value = err.response?.data?.message || t('booking.manage_error');
    } finally {
        submitting.value = false;
    }
};

const submitReschedule = async () => {
    if (!selectedDate.value || !selectedSlot.value) return;
    submitting.value = true;
    error.value = '';
    try {
        const response = await axios.post(`/api/public/p/${route.params.slug}/booking/${token.value}/reschedule`, {
            start_at: `${selectedDate.value} ${selectedSlot.value}`,
            timezone: timezone.value,
        });
        booking.value.start_at = response.data.booking?.start_at;
        booking.value.end_at = response.data.booking?.end_at;
        booking.value.status = response.data.booking?.status || 'confirmed';
        feedback.value = t('booking.manage_reschedule_success');
        mode.value = 'view';
    } catch (err) {
        error.value = err.response?.data?.message || t('booking.manage_error');
    } finally {
        submitting.value = false;
    }
};

onMounted(fetchBooking);
</script>
