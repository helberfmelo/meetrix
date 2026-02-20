<template>
    <div class="max-w-2xl mx-auto space-y-6 sm:space-y-8">
        <div class="flex items-center space-x-3 sm:space-x-4">
            <router-link to="/dashboard/polls" class="text-gray-400 dark:text-slate-400 hover:text-gray-600 dark:hover:text-slate-200">← {{ $t('common.back') }}</router-link>
            <h1 class="text-2xl sm:text-3xl font-black text-zinc-950 dark:text-white">{{ $t('admin.poll_create_title') }}</h1>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-5 sm:p-8 rounded-3xl shadow-xl border border-gray-100 dark:border-white/10 space-y-6">
            <div class="space-y-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-gray-400 dark:text-slate-400 uppercase tracking-widest px-1">{{ $t('admin.poll_title_label') }}</label>
                    <input v-model="poll.title" type="text" :placeholder="$t('admin.poll_title_placeholder')" class="w-full px-5 py-4 rounded-xl border-2 border-gray-100 dark:border-white/10 bg-white dark:bg-zinc-950 text-zinc-900 dark:text-white focus:border-indigo-600 outline-none transition-all">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-gray-400 dark:text-slate-400 uppercase tracking-widest px-1">{{ $t('admin.poll_description_optional') }}</label>
                    <textarea v-model="poll.description" rows="3" class="w-full px-5 py-4 rounded-xl border-2 border-gray-100 dark:border-white/10 bg-white dark:bg-zinc-950 text-zinc-900 dark:text-white focus:border-indigo-600 outline-none transition-all"></textarea>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex justify-between items-center px-1">
                    <label class="text-xs font-bold text-gray-400 dark:text-slate-400 uppercase tracking-widest">{{ $t('admin.poll_time_options') }}</label>
                    <button @click="addOption" class="text-indigo-600 dark:text-indigo-300 text-xs font-bold hover:underline">+ {{ $t('admin.poll_add_option') }}</button>
                </div>
                
                <div v-for="(option, index) in poll.options" :key="index" class="flex items-start space-x-3 group">
                    <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <input v-model="option.start_at" type="datetime-local" class="px-4 py-3 rounded-xl border-2 border-gray-100 dark:border-white/10 bg-white dark:bg-zinc-950 text-zinc-900 dark:text-white focus:border-indigo-600 outline-none">
                        <input v-model="option.end_at" type="datetime-local" class="px-4 py-3 rounded-xl border-2 border-gray-100 dark:border-white/10 bg-white dark:bg-zinc-950 text-zinc-900 dark:text-white focus:border-indigo-600 outline-none">
                    </div>
                    <button @click="removeOption(index)" v-if="poll.options.length > 1" class="mt-2 sm:mt-0 text-gray-300 dark:text-slate-400 hover:text-red-500 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-all">✕</button>
                </div>
            </div>

            <button @click="savePoll" :disabled="saving" class="w-full btn-primary py-4 sm:py-5 text-base sm:text-lg">
                {{ saving ? $t('admin.poll_creating') : $t('admin.poll_launch') }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import axios from '../axios';

const router = useRouter();
const { t } = useI18n();
const saving = ref(false);

const poll = ref({
    title: '',
    description: '',
    options: [
        { start_at: '', end_at: '' }
    ]
});

const addOption = () => poll.value.options.push({ start_at: '', end_at: '' });
const removeOption = (index) => poll.value.options.splice(index, 1);

const savePoll = async () => {
    if (!poll.value.title) return alert(t('admin.poll_enter_title_alert'));
    
    saving.value = true;
    try {
        await axios.post('/api/polls', poll.value);
        router.push('/dashboard/polls');
    } catch (e) {
        alert(t('admin.poll_create_failed_alert'));
    } finally {
        saving.value = false;
    }
};
</script>
