<template>
    <div class="space-y-12 animate-in fade-in slide-in-from-bottom-8 duration-1000">
        <!-- Header -->
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-8 py-8 border-b border-black/5 dark:border-white/5 mb-12">
            <div class="max-w-2xl">
                <h1 class="text-[clamp(2.5rem,8vw,5rem)] font-black text-zinc-950 dark:text-white uppercase tracking-tighter leading-[0.85] mb-4">
                    DISCOUNT // <span class="text-meetrix-orange">{{ $t('common.coupons') }}</span>
                </h1>
                <p class="text-slate-500 font-medium tracking-tight text-sm">{{ $t('admin.coupons_description') }}</p>
            </div>
            <button @click="showCreateModal = true" 
                class="px-8 py-4 bg-meetrix-orange text-zinc-950 font-black text-xs uppercase tracking-widest hover:scale-105 active:scale-95 transition-all shadow-xl shadow-meetrix-orange/20 rounded-full flex items-center gap-3">
                <span>{{ $t('admin.new_vector') }}</span>
                <span class="w-5 h-5 bg-zinc-950 text-white rounded-full flex items-center justify-center text-[10px]"><i class="fas fa-plus"></i></span>
            </button>
        </div>

        <!-- System Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-12">
                <div class="bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 rounded-[40px] overflow-hidden shadow-premium">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-black/5 dark:border-white/5 bg-zinc-50 dark:bg-zinc-950">
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">{{ $t('admin.code') }}</th>
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">{{ $t('admin.discount') }}</th>
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">{{ $t('admin.usage') }}</th>
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">{{ $t('admin.expires') }}</th>
                                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 text-right">{{ $t('admin.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/5 dark:divide-white/5">
                            <tr v-for="coupon in coupons" :key="coupon.id" class="group hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-2 h-2 rounded-full" :class="coupon.is_active ? 'bg-meetrix-green' : 'bg-slate-300 dark:bg-zinc-700'"></div>
                                        <span class="text-sm font-black text-zinc-950 dark:text-white font-mono uppercase tracking-widest">{{ coupon.code }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="text-xs font-black px-3 py-1 bg-zinc-100 dark:bg-zinc-800 rounded-full text-zinc-600 dark:text-slate-400">
                                        {{ coupon.discount_type === 'percent' ? `${coupon.discount_value}%` : `$${coupon.discount_value}` }} {{ $t('admin.off') }}
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-black text-zinc-950 dark:text-white">{{ coupon.times_used }} <span class="text-slate-400">/ {{ coupon.max_usages || '∞' }}</span></span>
                                        <div class="w-24 h-1 bg-zinc-100 dark:bg-zinc-800 rounded-full mt-2 overflow-hidden">
                                            <div class="h-full bg-meetrix-orange transition-all duration-1000" 
                                                :style="{ width: coupon.max_usages ? (coupon.times_used / coupon.max_usages * 100) + '%' : '0%' }"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="text-xs font-black" :class="isExpired(coupon.expires_at) ? 'text-red-500' : 'text-slate-400'">
                                        {{ coupon.expires_at ? new Date(coupon.expires_at).toLocaleDateString() : $t('admin.never') }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="toggleStatus(coupon)" class="p-2 hover:text-meetrix-orange transition-colors">
                                            <span class="text-xs font-black uppercase tracking-tighter">{{ coupon.is_active ? $t('admin.deactivate') : $t('admin.activate') }}</span>
                                        </button>
                                        <button @click="deleteCoupon(coupon)" class="p-2 hover:text-red-500 transition-colors">
                                            <span class="text-xs font-black uppercase tracking-tighter">{{ $t('admin.delete') }}</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="coupons.length === 0">
                                <td colspan="5" class="px-8 py-20 text-center">
                                    <p class="text-[10px] font-black uppercase tracking-[0.5em] text-slate-400">{{ $t('admin.no_active_vectors') }}</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-[10000] flex items-center justify-center p-6 bg-zinc-950/80 backdrop-blur-xl animate-in fade-in duration-300">
            <div class="bg-white dark:bg-zinc-900 w-full max-w-xl rounded-[40px] p-12 border border-white/5 shadow-2xl animate-in zoom-in-95 duration-500">
                <div class="flex justify-between items-start mb-12">
                    <h3 class="text-3xl font-black text-zinc-950 dark:text-white uppercase tracking-tighter">{{ $t('admin.create_vector') }}</h3>
                    <button @click="showCreateModal = false" class="text-slate-400 hover:text-zinc-950 dark:hover:text-white transition-colors text-2xl">×</button>
                </div>

                <form @submit.prevent="createCoupon" class="space-y-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-500">{{ $t('admin.vector_code') }}</label>
                        <input v-model="form.code" type="text" placeholder="MEETRIXPRO20"
                            class="w-full bg-zinc-50 dark:bg-zinc-950 border border-black/5 dark:border-white/10 rounded-2xl px-6 py-4 text-sm font-black uppercase tracking-widest focus:border-meetrix-orange outline-none transition-all dark:text-white">
                    </div>

                    <div class="grid grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-500">{{ $t('admin.type') }}</label>
                            <select v-model="form.discount_type" 
                                class="w-full bg-zinc-50 dark:bg-zinc-950 border border-black/5 dark:border-white/10 rounded-2xl px-6 py-4 text-sm font-black uppercase tracking-widest focus:border-meetrix-orange outline-none transition-all dark:text-white">
                                <option value="percent">{{ $t('admin.percentage') }}</option>
                                <option value="fixed">{{ $t('admin.fixed_amount') }}</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-500">{{ $t('admin.value') }}</label>
                            <input v-model="form.discount_value" type="number" 
                                class="w-full bg-zinc-50 dark:bg-zinc-950 border border-black/5 dark:border-white/10 rounded-2xl px-6 py-4 text-sm font-black focus:border-meetrix-orange outline-none transition-all dark:text-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-500">{{ $t('admin.max_usages') }}</label>
                            <input v-model="form.max_usages" type="number" :placeholder="$t('admin.unlimited_placeholder')"
                                class="w-full bg-zinc-50 dark:bg-zinc-950 border border-black/5 dark:border-white/10 rounded-2xl px-6 py-4 text-sm font-black focus:border-meetrix-orange outline-none transition-all dark:text-white">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-500">{{ $t('admin.expiry_date') }}</label>
                            <input v-model="form.expires_at" type="date"
                                class="w-full bg-zinc-50 dark:bg-zinc-950 border border-black/5 dark:border-white/10 rounded-2xl px-6 py-4 text-sm font-black focus:border-meetrix-orange outline-none transition-all dark:text-white">
                        </div>
                    </div>

                    <div class="pt-8">
                        <button type="submit" :disabled="loading"
                            class="w-full py-5 bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 font-black text-xs uppercase tracking-widest rounded-3xl hover:bg-meetrix-orange dark:hover:bg-meetrix-orange transition-all disabled:opacity-50">
                            {{ loading ? $t('admin.initializing') : $t('admin.activate_vector') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '../axios';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const coupons = ref([]);
const showCreateModal = ref(false);
const loading = ref(false);

const form = ref({
    code: '',
    discount_type: 'percent',
    discount_value: 0,
    max_usages: null,
    expires_at: null
});

const fetchCoupons = async () => {
    try {
        const response = await axios.get('/api/coupons');
        coupons.value = response.data;
    } catch (error) {
        console.error('Failed to fetch coupons', error);
    }
};

const createCoupon = async () => {
    loading.value = true;
    try {
        await axios.post('/api/coupons', form.value);
        showCreateModal.value = false;
        form.value = { code: '', discount_type: 'percent', discount_value: 0, max_usages: null, expires_at: null };
        fetchCoupons();
    } catch (error) {
        console.error('Failed to create coupon', error);
    } finally {
        loading.value = false;
    }
};

const toggleStatus = async (coupon) => {
    try {
        await axios.put(`/api/coupons/${coupon.id}`, { is_active: !coupon.is_active });
        fetchCoupons();
    } catch (error) {
        console.error('Failed to toggle status', error);
    }
};

const deleteCoupon = async (coupon) => {
    if (!confirm(t('admin.destroy_vector_confirm'))) return;
    try {
        await axios.delete(`/api/coupons/${coupon.id}`);
        fetchCoupons();
    } catch (error) {
        console.error('Failed to delete coupon', error);
    }
};

const isExpired = (date) => {
    if (!date) return false;
    return new Date(date) < new Date();
};

onMounted(fetchCoupons);
</script>

<style scoped>
.shadow-premium {
    box-shadow: 0 40px 80px -20px rgba(0, 0, 0, 0.2);
}
</style>
