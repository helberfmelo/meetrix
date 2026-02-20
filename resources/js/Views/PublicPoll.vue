<template>
    <div class="min-h-screen bg-gray-50/50 py-8 sm:py-12 px-4">
        <div v-if="loading" class="flex justify-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
        </div>

        <div v-else-if="poll" class="max-w-xl mx-auto bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
            <div class="p-5 sm:p-8 border-b border-gray-50 bg-indigo-600 text-white">
                <h1 class="text-xl sm:text-2xl font-black mb-2">{{ poll.title }}</h1>
                <p class="text-indigo-100 text-sm opacity-80">{{ poll.description }}</p>
                <div class="mt-4 flex items-center text-xs font-bold text-indigo-200 uppercase tracking-widest">
                    <span class="mr-2">{{ $t('poll.by') }}</span>
                    <span class="text-white">{{ poll.user?.name || $t('poll.anonymous_user') }}</span>
                </div>
            </div>

            <div class="p-5 sm:p-8 space-y-6">
                <!-- Customer Info -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest px-1">{{ $t('poll.your_name') }}</label>
                        <input v-model="voter.customer_name" type="text" :placeholder="$t('poll.name_placeholder')" class="w-full px-4 py-3 rounded-xl border-2 border-gray-50 focus:border-indigo-600 outline-none">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest px-1">{{ $t('poll.your_email') }}</label>
                        <input v-model="voter.customer_email" type="email" :placeholder="$t('poll.email_placeholder')" class="w-full px-4 py-3 rounded-xl border-2 border-gray-50 focus:border-indigo-600 outline-none">
                    </div>
                </div>

                <div class="space-y-3">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest px-1 flex justify-between">
                        {{ $t('poll.choose_availability') }}
                        <span>{{ $t('poll.votes_count', { count: totalVotes }) }}</span>
                    </h3>
                    
                    <div v-for="option in poll.options" :key="option.id" class="relative group">
                        <label 
                            :class="[selectedOptions.includes(option.id) ? 'border-indigo-600 bg-indigo-50/30' : 'border-gray-50 hover:border-gray-200']"
                            class="flex items-center p-5 rounded-2xl border-2 cursor-pointer transition-all"
                        >
                            <input type="checkbox" :value="option.id" v-model="selectedOptions" class="hidden">
                            <div class="flex-1">
                                <div class="font-bold text-gray-900 leading-tight">
                                    {{ formatDate(option.start_at) }}
                                </div>
                                <div class="text-xs text-gray-400 mt-1">
                                    {{ formatTime(option.start_at) }} — {{ formatTime(option.end_at) }}
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="text-xs font-black text-gray-900">{{ option.votes_count }}</span>
                                <span class="text-[10px] text-gray-400 uppercase font-bold">{{ $t('poll.votes_label') }}</span>
                            </div>
                        </label>
                    </div>
                </div>

                <button @click="submitVotes" :disabled="submitting || !selectedOptions.length" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-5 rounded-2xl shadow-lg shadow-indigo-100 disabled:opacity-50 transition-all">
                    {{ submitting ? $t('poll.submitting') : $t('poll.submit_availability') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import axios from '../axios';

const { t, locale } = useI18n();
const route = useRoute();
const poll = ref(null);
const loading = ref(true);
const submitting = ref(false);

const voter = ref({ customer_name: '', customer_email: '' });
const selectedOptions = ref([]);

const totalVotes = computed(() => {
    if (!poll.value) return 0;
    return poll.value.options.reduce((sum, opt) => sum + opt.votes_count, 0);
});

const fetchPoll = async () => {
    try {
        const response = await axios.get(`/api/p/polls/${route.params.slug}`);
        poll.value = response.data;
    } catch (e) {
        alert(t('poll.not_found'));
    } finally {
        loading.value = false;
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString(locale.value, { weekday: 'long', month: 'short', day: 'numeric' });
};

const formatTime = (date) => {
    return new Date(date).toLocaleTimeString(locale.value, { hour: 'numeric', minute: '2-digit' });
};

const submitVotes = async () => {
    if (!voter.value.customer_name || !voter.value.customer_email) {
        return alert(t('poll.enter_info_alert'));
    }

    submitting.value = true;
    try {
        // Submit each selected option
        for (const optionId of selectedOptions.value) {
            await axios.post(`/api/poll-options/${optionId}/vote`, voter.value);
        }
        alert(t('poll.thanks_alert'));
        fetchPoll(); // Refresh counts
        selectedOptions.value = [];
    } catch (e) {
        alert(t('poll.submit_failed_alert'));
    } finally {
        submitting.value = false;
    }
};

onMounted(fetchPoll);
</script>
