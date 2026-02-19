<template>
    <div class="max-w-2xl mx-auto space-y-8">
        <div class="flex items-center space-x-4">
            <router-link to="/dashboard/polls" class="text-gray-400 hover:text-gray-600">← Back</router-link>
            <h1 class="text-3xl font-black text-gray-900">New Meeting Poll</h1>
        </div>

        <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100 space-y-6">
            <div class="space-y-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-widest px-1">Poll Title</label>
                    <input v-model="poll.title" type="text" placeholder="e.g. Project Review Session" class="w-full px-5 py-4 rounded-xl border-2 border-gray-100 focus:border-indigo-600 outline-none transition-all">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-widest px-1">Description (Optional)</label>
                    <textarea v-model="poll.description" rows="3" class="w-full px-5 py-4 rounded-xl border-2 border-gray-100 focus:border-indigo-600 outline-none transition-all"></textarea>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex justify-between items-center px-1">
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Time Options</label>
                    <button @click="addOption" class="text-indigo-600 text-xs font-bold hover:underline">+ Add Alternative</button>
                </div>
                
                <div v-for="(option, index) in poll.options" :key="index" class="flex items-center space-x-3 group">
                    <div class="flex-1 grid grid-cols-2 gap-3">
                        <input v-model="option.start_at" type="datetime-local" class="px-4 py-3 rounded-xl border-2 border-gray-100 focus:border-indigo-600 outline-none">
                        <input v-model="option.end_at" type="datetime-local" class="px-4 py-3 rounded-xl border-2 border-gray-100 focus:border-indigo-600 outline-none">
                    </div>
                    <button @click="removeOption(index)" v-if="poll.options.length > 1" class="text-gray-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all">✕</button>
                </div>
            </div>

            <button @click="savePoll" :disabled="saving" class="w-full btn-primary py-5 text-lg">
                {{ saving ? 'Creating...' : 'Launch Poll' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from '../axios';

const router = useRouter();
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
    if (!poll.value.title) return alert("Please enter a title");
    
    saving.value = true;
    try {
        await axios.post('/api/polls', poll.value);
        router.push('/dashboard/polls');
    } catch (e) {
        alert("Failed to create poll. Check dates.");
    } finally {
        saving.value = false;
    }
};
</script>
