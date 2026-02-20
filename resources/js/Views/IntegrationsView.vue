<template>
    <div class="space-y-6 sm:space-y-8">
        <h1 class="text-2xl sm:text-3xl font-black text-zinc-950 dark:text-white">{{ $t('common.integrations') }}</h1>
        <p class="text-gray-500 dark:text-slate-300 max-w-2xl text-sm sm:text-base">
            {{ $t('admin.integrations_description') }}
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
            <!-- Google Calendar -->
            <div class="bg-white dark:bg-zinc-900 p-5 sm:p-8 rounded-3xl border border-gray-100 dark:border-white/10 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-6">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/a/a5/Google_Calendar_icon_%282020%29.svg" class="h-10 w-10" alt="Google Calendar">
                        <span v-if="isGoogleConnected" class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">{{ $t('admin.connected') }}</span>
                    </div>
                    <h3 class="text-xl font-bold text-zinc-950 dark:text-white mb-2">{{ $t('admin.google_calendar') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-slate-300 leading-relaxed mb-6">
                        {{ $t('admin.google_description') }}
                    </p>
                </div>
                
                <div v-if="!isGoogleConnected">
                    <button @click="connectGoogle" :disabled="loading" class="w-full py-4 bg-zinc-50 dark:bg-zinc-950 border-2 border-black/5 dark:border-white/10 rounded-xl font-bold text-zinc-800 dark:text-slate-100 hover:border-meetrix-orange transition-all flex items-center justify-center gap-3">
                        <i v-if="loading" class="fas fa-circle-notch fa-spin"></i>
                        <i v-else class="fab fa-google"></i>
                        {{ $t('admin.connect_google') }}
                    </button>
                </div>
                <div v-else class="space-y-3">
                    <div class="p-4 bg-gray-50 dark:bg-zinc-950 rounded-xl text-xs font-medium text-gray-600 dark:text-slate-300 flex justify-between items-center">
                        <span>{{ googleIntegration?.meta?.email || $t('admin.connected_account') }}</span>
                        <button @click="disconnect(googleIntegration.id)" class="text-red-500 hover:underline">{{ $t('admin.disconnect') }}</button>
                    </div>
                </div>
            </div>

            <!-- Microsoft Outlook -->
            <div class="bg-white dark:bg-zinc-900 p-5 sm:p-8 rounded-3xl border border-gray-100 dark:border-white/10 shadow-sm flex flex-col justify-between opacity-70 dark:opacity-80 grayscale">
                <div>
                    <div class="flex items-center justify-between mb-6">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/d/df/Microsoft_Office_Outlook_%282018%E2%80%93present%29.svg" class="h-10 w-10" alt="Outlook">
                        <span class="px-3 py-1 bg-gray-200 dark:bg-zinc-800 text-gray-600 dark:text-slate-200 text-xs font-bold rounded-full text-nowrap">{{ $t('admin.coming_soon') }}</span>
                    </div>
                    <h3 class="text-xl font-bold text-zinc-950 dark:text-white mb-2">{{ $t('admin.outlook_calendar') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-slate-300 leading-relaxed mb-6">
                        {{ $t('admin.outlook_description') }}
                    </p>
                </div>
                <button disabled class="w-full py-4 bg-gray-50 dark:bg-zinc-950 border-2 border-gray-100 dark:border-white/10 rounded-xl font-bold text-gray-400 dark:text-slate-400 cursor-not-allowed">
                    {{ $t('admin.soon') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from '../axios';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const integrations = ref([]);
const loading = ref(false);

const isGoogleConnected = computed(() => integrations.value.some(i => i.service === 'google'));
const googleIntegration = computed(() => integrations.value.find(i => i.service === 'google'));

const fetchIntegrations = async () => {
    try {
        const response = await axios.get('/api/integrations');
        integrations.value = response.data;
    } catch (e) {
        console.error(e);
    }
};

const connectGoogle = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/integrations/google/redirect');
        window.location.href = response.data.url;
    } catch (e) {
        alert(t('admin.failed_google_oauth'));
    } finally {
        loading.value = false;
    }
};

const disconnect = async (id) => {
    if (!confirm(t('admin.disconnect_confirm'))) return;
    try {
        await axios.delete(`/api/integrations/${id}`);
        fetchIntegrations();
    } catch (e) {
        alert(t('admin.failed_disconnect'));
    }
};

onMounted(fetchIntegrations);
</script>
