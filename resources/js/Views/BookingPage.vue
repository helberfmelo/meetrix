<template>
    <div class="min-h-screen bg-gray-50/50 py-6 sm:py-12 px-3 sm:px-6 lg:px-8" :style="brandFontStyle">
        <div v-if="loading && !page" class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2" :style="{ borderColor: primaryColor }"></div>
        </div>

        <div v-else-if="page" class="max-w-5xl mx-auto">
            <!-- Booking Container -->
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col md:flex-row min-h-[620px] border border-gray-100">
                
                <!-- Left Sidebar: Page Details -->
                <div class="w-full md:w-80 bg-slate-900 p-6 sm:p-10 text-white flex flex-col justify-between" :style="sidebarStyle">
                    <div>
                        <!-- Brand Logo -->
                        <div v-if="page.config?.logo_url" class="mb-10">
                            <img :src="page.config.logo_url" alt="Logo" class="max-h-12 w-auto object-contain" />
                        </div>
                        <div v-else class="h-20 w-20 rounded-full flex items-center justify-center text-3xl font-bold mb-6 shadow-inner" 
                            :style="{ backgroundColor: primaryColor + '22', color: 'white', border: `1px solid ${primaryColor}44` }">
                            {{ page.title.charAt(0) }}
                        </div>
                        
                        <h1 class="text-xl sm:text-2xl font-black leading-tight uppercase font-outfit tracking-tighter">{{ page.title }}</h1>
                        <p class="mt-3 sm:mt-4 text-slate-400 leading-relaxed font-medium text-sm">{{ page.intro_text }}</p>

                        <div class="mt-6 sm:mt-8 space-y-4 sm:space-y-6 pt-6 sm:pt-8 border-t border-white/5">
                            <div v-if="selectedType" class="flex items-center text-slate-300">
                                <span class="w-8 h-8 flex items-center justify-center bg-white/5 rounded-lg mr-3 text-lg">
                                    <i class="fas fa-clock"></i>
                                </span>
                                <div>
                                    <p class="text-[9px] uppercase font-black tracking-[0.2em] text-slate-500">{{ $t('booking.duration') }}</p>
                                    <p class="font-bold text-sm">{{ selectedType.duration_minutes }} min</p>
                                </div>
                            </div>
                            <div v-if="selectedDate" class="flex items-center text-slate-300">
                                <span class="w-8 h-8 flex items-center justify-center bg-white/5 rounded-lg mr-3 text-lg">
                                    <i class="fas fa-calendar-alt"></i>
                                </span>
                                <div>
                                    <p class="text-[9px] uppercase font-black tracking-[0.2em] text-slate-500">{{ $t('booking.date') }}</p>
                                    <p class="font-bold text-sm">{{ formatDate(selectedDate) }}</p>
                                </div>
                            </div>
                            <div v-if="selectedSlot" class="flex items-center text-slate-300 animate-in fade-in slide-in-from-left-4">
                                <span class="w-8 h-8 flex items-center justify-center bg-white/5 rounded-lg mr-3 text-lg">
                                    <i class="fas fa-clock"></i>
                                </span>
                                <div>
                                    <p class="text-[9px] uppercase font-black tracking-[0.2em] text-slate-500">{{ $t('booking.time') }}</p>
                                    <p class="font-bold text-sm">{{ selectedSlot }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 sm:mt-8 text-zinc-600 dark:text-zinc-400 font-black uppercase text-[8px] tracking-[0.2em] sm:tracking-[0.4em] leading-none mb-1 opacity-50">
                        {{ $t('home.footer_tagline').split('//')[0] }} // <span class="text-white">{{ $t('home.footer_tagline').split('//')[1] }}</span>
                    </div>
                </div>

                <!-- Right Content: Step Manager -->
                <div class="flex-1 p-5 sm:p-10 flex flex-col bg-white">
                    
                    <!-- Progress Bar -->
                    <div class="mb-6 sm:mb-10 w-full flex space-x-2">
                        <div v-for="s in ['type', 'date', 'form']" :key="s" 
                            :class="['h-1.5 flex-1 rounded-full transition-all duration-700']"
                            :style="{ backgroundColor: (s === step || (step === 'date' && s === 'type') || step === 'form') ? primaryColor : '#f1f5f9' }">
                        </div>
                    </div>

                    <!-- Step 1: Select Type -->
                    <div v-if="step === 'type'" class="space-y-6 sm:space-y-8 animate-in fade-in zoom-in-95">
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tighter uppercase">{{ $t('booking.select_service') }}</h2>
                        <div class="grid grid-cols-1 gap-4">
                            <button 
                                v-for="type in page.appointment_types" 
                                :key="type.id"
                                @click="selectType(type)"
                                class="flex items-center justify-between p-4 sm:p-6 border-2 border-slate-50 rounded-2xl transition-all group active:scale-[0.98] hover:shadow-2xl hover:border-transparent gap-4"
                                :style="{ borderColor: hoverType === type.id ? primaryColor : '#f8fafc' }"
                                @mouseenter="hoverType = type.id"
                                @mouseleave="hoverType = null"
                            >
                                <div class="text-left">
                                    <h3 class="font-black text-slate-900 text-lg sm:text-xl uppercase tracking-tighter">{{ type.name }}</h3>
                                    <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mt-1">{{ type.duration_minutes }} MIN // SYNC_OK</p>
                                </div>
                                <div class="text-right">
                                    <span v-if="type.price > 0" class="font-black text-xl sm:text-2xl tracking-tighter" :style="{ color: primaryColor }">{{ formatCurrency(type.price) }}</span>
                                    <span v-else class="text-meetrix-green font-black text-lg sm:text-xl tracking-tighter uppercase">{{ $t('home.price_free_value') }}</span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Date & Slot Selection -->
                    <div v-if="step === 'date'" class="space-y-6 sm:space-y-8 animate-in fade-in slide-in-from-right-8">
                        <div class="flex items-center justify-between">
                            <button @click="step = 'type'" class="text-[10px] font-black uppercase tracking-[0.2em] sm:tracking-[0.3em] text-slate-400 hover:text-slate-900 transition-colors">← {{ $t('common.back') }}</button>
                            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tighter uppercase">{{ $t('booking.agenda') }}</h2>
                        </div>
                        
                        <div class="flex flex-col lg:flex-row gap-6 sm:gap-8">
                            <div class="flex-1">
                                <h3 class="text-[9px] font-black text-slate-400 uppercase tracking-[0.3em] mb-4">{{ $t('booking.upcoming') }}</h3>
                                <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-3 gap-2">
                                    <button 
                                        v-for="date in upcomingDates" 
                                        :key="date"
                                        @click="selectDate(date)"
                                        class="p-3 sm:p-4 rounded-xl border-2 flex flex-col items-center transition-all"
                                        :style="dateStyle(date)"
                                    >
                                        <span class="text-[9px] font-black uppercase tracking-tighter" :style="{ color: selectedDate === date ? 'white' : '#94a3b8' }">{{ getDayName(date) }}</span>
                                        <span class="text-lg sm:text-xl font-black" :style="{ color: selectedDate === date ? 'white' : '#1e293b' }">{{ getDayNum(date) }}</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Slots Column -->
                            <div class="flex-1 min-h-[300px]">
                                <h3 class="text-[9px] font-black text-slate-400 uppercase tracking-[0.3em] mb-4">{{ $t('booking.available_times') }}</h3>
                                <div v-if="fetchingSlots" class="flex justify-center py-10">
                                     <div class="animate-pulse flex flex-col items-center gap-4">
                                        <div class="h-10 w-32 bg-slate-50 rounded-xl"></div>
                                        <div class="h-10 w-32 bg-slate-50 rounded-xl opacity-50"></div>
                                    </div>
                                </div>
                                <div v-else-if="slots.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-2 overflow-y-auto max-h-[350px] pr-1 sm:pr-2 scrollbar-thin">
                                    <button 
                                        v-for="slot in slots" 
                                        :key="slot"
                                        @click="finishSelection(slot)"
                                        class="p-3 sm:p-4 text-center rounded-xl border-2 font-black text-xs uppercase tracking-widest transition-all active:scale-95 hover:text-white"
                                        :style="{ 
                                            borderColor: primaryColor, 
                                            color: primaryColor,
                                            backgroundColor: hoverSlot === slot ? primaryColor : 'transparent'
                                        }"
                                        @mouseenter="hoverSlot = slot"
                                        @mouseleave="hoverSlot = null"
                                    >
                                        {{ slot }}
                                    </button>
                                </div>
                                <div v-else class="text-center py-20 bg-slate-50 rounded-3xl border-2 border-dashed border-slate-100">
                                    <p class="text-slate-300 text-[10px] font-black uppercase tracking-widest">{{ $t('booking.no_slots') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Confirmation Form -->
                    <div v-if="step === 'form'" class="space-y-6 sm:space-y-8 animate-in fade-in slide-in-from-bottom-8">
                        <div class="flex items-center justify-between">
                            <button @click="step = 'date'" class="text-[10px] font-black uppercase tracking-[0.2em] sm:tracking-[0.3em] text-slate-400 hover:text-slate-900 transition-colors">← {{ $t('common.back') }}</button>
                            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tighter uppercase">{{ $t('booking.your_data') }}</h2>
                        </div>

                        <form @submit.prevent="submitBooking" class="space-y-6">
                            <div v-for="field in formFields" :key="field.name" class="space-y-2">
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                    {{ field.label }}
                                    <span v-if="field.required" class="text-meetrix-orange">*</span>
                                </label>
                                
                                <textarea 
                                    v-if="field.type === 'textarea'"
                                    v-model="customerData[field.name]"
                                    :required="field.required"
                                    class="w-full px-4 sm:px-6 py-3.5 sm:py-4 rounded-2xl border-2 border-slate-50 focus:ring-4 focus:ring-slate-100 outline-none transition-all min-h-[120px] font-medium text-sm"
                                    :style="{ focusBorderColor: primaryColor }"
                                ></textarea>
                                
                                <input 
                                    v-else
                                    v-model="customerData[field.name]" 
                                    :required="field.required" 
                                    :type="field.type" 
                                    class="w-full px-4 sm:px-6 py-3.5 sm:py-4 rounded-2xl border-2 border-slate-50 focus:ring-4 focus:ring-slate-100 outline-none transition-all font-medium text-sm"
                                >
                            </div>

                            <button 
                                type="submit" 
                                :disabled="submitting"
                                class="w-full py-5 sm:py-6 px-6 sm:px-8 rounded-2xl font-black text-xs sm:text-sm uppercase tracking-[0.15em] sm:tracking-[0.3em] text-white shadow-premium transition-all flex justify-center items-center group active:scale-95 disabled:bg-slate-300"
                                :style="{ backgroundColor: primaryColor }"
                            >
                                <span v-if="submitting" class="animate-spin mr-3">🌀</span>
                                {{ submitting ? $t('booking.processing') : $t('booking.confirm') }}
                            </button>
                        </form>
                    </div>

                    <!-- Success State -->
                    <div v-if="step === 'success'" class="flex-1 flex flex-col items-center justify-center text-center animate-in zoom-in-90 duration-700">
                        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full flex items-center justify-center text-3xl sm:text-4xl mb-6 sm:mb-8 border-4 shadow-xl"
                            :style="{ color: primaryColor, borderColor: primaryColor + '22', backgroundColor: primaryColor + '11' }">
                            <i class="fas fa-check"></i>
                        </div>
                        <h2 class="text-3xl sm:text-4xl font-black text-slate-900 mb-3 sm:mb-4 tracking-tighter">{{ $t('booking.success_title') }}</h2>
                        <p class="text-slate-500 max-w-sm mb-8 sm:mb-12 leading-tight font-bold whitespace-pre-wrap uppercase text-[10px] sm:text-xs tracking-wide sm:tracking-widest">
                            {{ page.confirmation_message || $t('booking.success_msg') }}
                        </p>
                        <router-link to="/" class="px-8 sm:px-12 py-4 sm:py-5 bg-zinc-950 text-white rounded-full font-black text-[10px] uppercase tracking-[0.2em] sm:tracking-[0.4em] hover:scale-105 transition-all shadow-premium">
                            {{ $t('booking.back_home') }}
                        </router-link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import axios from 'axios';

const { t, locale } = useI18n();
const route = useRoute();
const page = ref(null);
const loading = ref(true);
const step = ref('type'); // type, date, form, success
const hoverType = ref(null);
const hoverSlot = ref(null);

const selectedType = ref(null);
const selectedDate = ref(null);
const selectedSlot = ref(null);
const slots = ref([]);
const fetchingSlots = ref(false);

const primaryColor = computed(() => page.value?.config?.primary_color || '#4f46e5');
const brandFontStyle = computed(() => ({ 
    fontFamily: page.value?.config?.custom_font === 'Outfit' ? "'Outfit', sans-serif" : "'Inter', sans-serif" 
}));

const parseDateKey = (dateStr) => {
    const [year, month, day] = dateStr.split('-').map(Number);
    // Noon avoids date rollovers around DST boundaries.
    return new Date(year, month - 1, day, 12, 0, 0, 0);
};

const toDateKey = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const sidebarStyle = computed(() => {
    return {
        background: `linear-gradient(135deg, #0f172a 0%, #1e293b 100%)`
    }
});

const dateStyle = (date) => {
    if (selectedDate.value === date) {
        return {
            backgroundColor: primaryColor.value,
            borderColor: primaryColor.value,
            color: 'white'
        };
    }
    return {
        borderColor: '#f3f4f6',
        backgroundColor: 'transparent'
    };
};

const formFields = computed(() => {
    return page.value?.config?.form_fields || [
        { name: 'customer_name', label: t('admin.full_name_label'), type: 'text', required: true },
        { name: 'customer_email', label: t('admin.email_address_label'), type: 'email', required: true },
        { name: 'phone', label: t('admin.phone_field'), type: 'text', required: true },
    ];
});

const customerData = ref({});
const submitting = ref(false);

onMounted(async () => {
    try {
        const response = await axios.get(`/api/p/${route.params.slug}`);
        page.value = response.data;
        
        formFields.value.forEach(f => {
            customerData.value[f.name] = '';
        });
    } catch (error) {
        console.error("Page not found", error);
    } finally {
        loading.value = false;
    }
});

const selectType = (type) => {
    selectedType.value = type;
    step.value = 'date';
};

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

const selectDate = async (date) => {
    selectedDate.value = date;
    selectedSlot.value = null;
    slots.value = [];
    fetchingSlots.value = true;
    
    try {
        const response = await axios.get(`/api/p/${route.params.slug}/slots`, {
            params: {
                date: date,
                appointment_type_id: selectedType.value.id,
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
            }
        });
        slots.value = response.data;
    } catch (error) {
        console.error("Error fetching slots", error);
    } finally {
        fetchingSlots.value = false;
    }
};

const finishSelection = async (slot) => {
    selectedSlot.value = slot;
    step.value = 'form';
    try {
        axios.post(`/api/p/${route.params.slug}/click`);
    } catch (e) {}
};

const submitBooking = async () => {
    submitting.value = true;
    try {
        const response = await axios.post('/api/bookings', {
            scheduling_page_id: page.value.id,
            appointment_type_id: selectedType.value.id,
            start_at: selectedDate.value + ' ' + selectedSlot.value,
            ...customerData.value,
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
        });

        if (response.data.requires_payment && response.data.checkout_url) {
            window.location.href = response.data.checkout_url;
            return;
        }

        step.value = 'success';
        
        if (page.value.redirect_url) {
            setTimeout(() => {
                window.location.href = page.value.redirect_url;
            }, 3000);
        }
    } catch (error) {
        if (error.response?.status === 409) {
            alert(t('booking.slot_taken_alert'));
            if (selectedDate.value) {
                await selectDate(selectedDate.value);
                step.value = 'date';
            }
            return;
        }

        alert(error.response?.data?.message || t('booking.failed_alert'));
    } finally {
        submitting.value = false;
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat(locale.value, { style: 'currency', currency: page.value?.config?.currency || 'BRL' }).format(value);
};

const getDayName = (dateStr) => {
    const date = parseDateKey(dateStr);
    return date.toLocaleDateString(locale.value, { weekday: 'short' });
};

const getDayNum = (dateStr) => {
    return parseDateKey(dateStr).getDate();
};

const formatDate = (dateStr) => {
    return parseDateKey(dateStr).toLocaleDateString(locale.value, { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
};
</script>

<style scoped>
.scrollbar-thin::-webkit-scrollbar { width: 4px; }
.scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
.scrollbar-thin::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

.animate-in {
    animation: animate-in 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes animate-in {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
