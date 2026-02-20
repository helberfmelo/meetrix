<template>
    <div class="min-h-screen flex items-center justify-center bg-zinc-50 dark:bg-zinc-950">
        <div class="text-center space-y-4">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 dark:border-indigo-400 mx-auto"></div>
            <h2 class="text-xl font-bold text-zinc-950 dark:text-white">{{ $t('admin.integration_finalizing') }}</h2>
            <p class="text-slate-500 dark:text-slate-300">{{ $t('admin.integration_syncing') }}</p>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import axios from '../axios';

const route = useRoute();
const router = useRouter();
useI18n();

onMounted(async () => {
    const code = route.query.code;
    const service = route.params.service; // We'll define route as /integrations/:service/callback

    if (!code) {
        router.push('/dashboard/integrations');
        return;
    }

    try {
        await axios.post(`/api/integrations/${service}/callback`, { code });
        router.push({ path: '/dashboard/integrations', query: { success: 1 } });
    } catch (e) {
        console.error(e);
        router.push({ path: '/dashboard/integrations', query: { error: 1 } });
    }
});
</script>
