<template>
    <div class="space-y-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-200">{{ $t('admin.page_title') }}</label>
            <input 
                type="text" 
                :value="modelValue.title"
                @input="$emit('update:modelValue', { ...modelValue, title: $event.target.value }); $emit('update')"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-white/10 bg-white dark:bg-zinc-950 text-zinc-900 dark:text-white shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
            <p class="mt-2 text-sm text-gray-500 dark:text-slate-300">{{ $t('admin.page_title_desc') }}</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-200">{{ $t('admin.page_url') }}</label>
            <div class="mt-1 flex rounded-md shadow-sm">
                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-zinc-900 text-gray-500 dark:text-slate-300 sm:text-sm">
                    meetrix.opentshost.com/p/
                </span>
                <input 
                    type="text" 
                    :value="modelValue.slug"
                    @input="$emit('update:modelValue', { ...modelValue, slug: $event.target.value }); $emit('update')"
                    class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 dark:border-white/10 bg-white dark:bg-zinc-950 text-zinc-900 dark:text-white"
                >
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-slate-300">{{ $t('admin.page_url_desc') }}</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-200">{{ $t('admin.intro_text') }}</label>
            <textarea 
                rows="4"
                :value="modelValue.intro_text"
                @input="$emit('update:modelValue', { ...modelValue, intro_text: $event.target.value }); $emit('update')"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-white/10 bg-white dark:bg-zinc-950 text-zinc-900 dark:text-white shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            ></textarea>
            <p class="mt-2 text-sm text-gray-500 dark:text-slate-300">{{ $t('admin.intro_text_desc') }}</p>
        </div>

        <div v-if="teams.length > 0">
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-200">{{ $t('admin.page_owner') }}</label>
            <select 
                :value="modelValue.team_id"
                @change="$emit('update:modelValue', { ...modelValue, team_id: $event.target.value || null }); $emit('update')"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-white/10 bg-white dark:bg-zinc-950 text-zinc-900 dark:text-white shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
                <option :value="null">{{ $t('admin.personal_me') }}</option>
                <option v-for="team in teams" :key="team.id" :value="team.id">
                    {{ team.name }} {{ $t('admin.team_label') }}
                </option>
            </select>
            <p class="mt-2 text-sm text-gray-500 dark:text-slate-300">{{ $t('admin.page_owner_desc') }}</p>
        </div>

        <div class="pt-6 border-t border-gray-100 dark:border-white/10 space-y-6">
            <h4 class="text-sm font-bold text-gray-900 dark:text-zinc-100 uppercase tracking-wider">{{ $t('admin.after_booking') }}</h4>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-200">{{ $t('admin.display_message') }}</label>
                <textarea 
                    rows="3"
                    :value="modelValue.confirmation_message"
                    @input="$emit('update:modelValue', { ...modelValue, confirmation_message: $event.target.value }); $emit('update')"
                    :placeholder="$t('admin.confirmation_placeholder')"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-white/10 bg-white dark:bg-zinc-950 text-zinc-900 dark:text-white shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                ></textarea>
                <p class="mt-2 text-sm text-gray-500 dark:text-slate-300">{{ $t('admin.display_message_desc') }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-200">{{ $t('admin.redirect_url_label') }}</label>
                <input 
                    type="url" 
                    :value="modelValue.redirect_url"
                    @input="$emit('update:modelValue', { ...modelValue, redirect_url: $event.target.value }); $emit('update')"
                    :placeholder="$t('admin.redirect_placeholder')"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-white/10 bg-white dark:bg-zinc-950 text-zinc-900 dark:text-white shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >
                <p class="mt-2 text-sm text-gray-500 dark:text-slate-300">{{ $t('admin.redirect_url_desc') }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '../../axios';

defineProps({
    modelValue: {
        type: Object,
        required: true
    }
});
defineEmits(['update:modelValue', 'update']);

const teams = ref([]);

onMounted(async () => {
    try {
        const response = await axios.get('/api/teams');
        teams.value = response.data;
    } catch (e) {
        console.error("Failed to fetch teams", e);
    }
});
</script>
