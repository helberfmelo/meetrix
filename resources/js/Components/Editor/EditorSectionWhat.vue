<template>
    <div class="space-y-6">
        <div>
            <label class="block text-sm font-medium text-gray-700">Page Title</label>
            <input 
                type="text" 
                :value="modelValue.title"
                @input="$emit('update:modelValue', { ...modelValue, title: $event.target.value }); $emit('update')"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
            <p class="mt-2 text-sm text-gray-500">The public name of your booking page.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Page URL</label>
            <div class="mt-1 flex rounded-md shadow-sm">
                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                    meetrix.io/p/
                </span>
                <input 
                    type="text" 
                    :value="modelValue.slug"
                    @input="$emit('update:modelValue', { ...modelValue, slug: $event.target.value }); $emit('update')"
                    class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300"
                >
            </div>
            <p class="mt-2 text-sm text-gray-500">Unique identifier for your page.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Intro Text</label>
            <textarea 
                rows="4"
                :value="modelValue.intro_text"
                @input="$emit('update:modelValue', { ...modelValue, intro_text: $event.target.value }); $emit('update')"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            ></textarea>
            <p class="mt-2 text-sm text-gray-500">A brief description displayed on your booking page.</p>
        </div>

        <div v-if="teams.length > 0">
            <label class="block text-sm font-medium text-gray-700">Page Owner</label>
            <select 
                :value="modelValue.team_id"
                @change="$emit('update:modelValue', { ...modelValue, team_id: $event.target.value || null }); $emit('update')"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
                <option :value="null">Personal (Me)</option>
                <option v-for="team in teams" :key="team.id" :value="team.id">
                    {{ team.name }} (Team)
                </option>
            </select>
            <p class="mt-2 text-sm text-gray-500">Assign this page to a team to share management with members.</p>
        </div>

        <div class="pt-6 border-t border-gray-100 space-y-6">
            <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider">After Booking</h4>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Display Message</label>
                <textarea 
                    rows="3"
                    :value="modelValue.confirmation_message"
                    @input="$emit('update:modelValue', { ...modelValue, confirmation_message: $event.target.value }); $emit('update')"
                    placeholder="Your booking is confirmed! We'll see you soon."
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                ></textarea>
                <p class="mt-2 text-sm text-gray-500">Shown to customers immediately after a successful booking.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Redirect URL (Optional)</label>
                <input 
                    type="url" 
                    :value="modelValue.redirect_url"
                    @input="$emit('update:modelValue', { ...modelValue, redirect_url: $event.target.value }); $emit('update')"
                    placeholder="https://yourwebsite.com/thanks"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >
                <p class="mt-2 text-sm text-gray-500">Automatically send customers to this URL after 3 seconds.</p>
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
