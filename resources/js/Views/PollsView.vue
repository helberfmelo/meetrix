<template>
    <div class="space-y-6 sm:space-y-8">
        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3 sm:gap-4">
            <h1 class="text-2xl sm:text-3xl font-black text-zinc-950 dark:text-white">{{ $t('admin.meeting_polls') }}</h1>
            <router-link to="/dashboard/polls/create" class="btn-primary w-full sm:w-auto text-center">
                + {{ $t('admin.new_poll') }}
            </router-link>
        </div>

        <div v-if="loading" class="flex justify-center py-24">
            <i class="fas fa-circle-notch fa-spin text-4xl text-meetrix-orange"></i>
        </div>

        <div v-else-if="polls.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            <div v-for="poll in polls" :key="poll.id" class="bg-white dark:bg-zinc-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-white/10">
                <div class="flex justify-between items-start mb-4">
                    <div class="h-10 w-10 bg-orange-100 dark:bg-zinc-800 text-orange-600 dark:text-meetrix-orange rounded-lg flex items-center justify-center text-lg">
                        <i class="fas fa-poll-h"></i>
                    </div>
                    <span :class="poll.is_active ? 'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-300' : 'bg-gray-100 text-gray-600 dark:bg-zinc-800 dark:text-slate-300'" class="px-2 py-1 text-[10px] font-bold uppercase rounded-full">
                        {{ poll.is_active ? $t('admin.active') : $t('admin.draft') }}
                    </span>
                </div>
                <h3 class="text-lg font-bold text-zinc-950 dark:text-white mb-1">{{ poll.title }}</h3>
                <p class="text-xs text-gray-500 dark:text-slate-300 mb-4">{{ poll.options_count }} {{ $t('admin.time_options') }}</p>
                
                <div class="flex space-x-2 pt-4 border-t border-gray-50 dark:border-white/10">
                    <button @click="copyLink(poll.slug)" class="flex-1 py-2 text-xs font-bold bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-300 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-500/25">
                        {{ $t('admin.copy_link') }}
                    </button>
                    <button @click="deletePoll(poll.id)" class="px-3 py-2 text-slate-400 dark:text-slate-300 hover:text-red-500 transition-colors">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        </div>

        <div v-else class="bg-white dark:bg-zinc-900/50 p-8 sm:p-12 text-center rounded-[28px] sm:rounded-[40px] border border-black/5 dark:border-white/5 shadow-premium">
            <div class="text-5xl mb-4 text-meetrix-orange"><i class="fas fa-vote-yea"></i></div>
            <h2 class="text-xl font-bold text-zinc-950 dark:text-white mb-2">{{ $t('admin.create_multiple_options') }}</h2>
            <p class="text-gray-500 dark:text-slate-300 max-w-sm mx-auto mb-8">
                {{ $t('admin.poll_description') }}
            </p>
            <router-link to="/dashboard/polls/create" class="btn-primary inline-block w-full sm:w-auto">
                {{ $t('admin.create_a_poll') }}
            </router-link>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '../axios';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';
import { DEFAULT_I18N_LOCALE, urlSegmentToLocale, withLocalePrefix } from '../utils/localeRoute';

const { t } = useI18n();
const route = useRoute();
const polls = ref([]);
const loading = ref(true);
const currentLocale = () => urlSegmentToLocale(route.params.locale) || DEFAULT_I18N_LOCALE;

const fetchPolls = async () => {
    try {
        const response = await axios.get('/api/polls');
        polls.value = response.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

const copyLink = (slug) => {
    const url = `${window.location.origin}${withLocalePrefix(`/vote/${slug}`, currentLocale())}`;
    navigator.clipboard.writeText(url);
    alert(t('admin.poll_copied'));
};

const deletePoll = async (id) => {
    if (!confirm(t('admin.delete_poll_confirm'))) return;
    try {
        await axios.delete(`/api/polls/${id}`);
        fetchPolls();
    } catch (e) {
        alert(t('admin.failed_delete_poll'));
    }
};

onMounted(fetchPolls);
</script>
