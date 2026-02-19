<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-black text-gray-900">Meeting Polls</h1>
            <router-link to="/dashboard/polls/create" class="btn-primary">
                + New Poll
            </router-link>
        </div>

        <div v-if="loading" class="flex justify-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
        </div>

        <div v-else-if="polls.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="poll in polls" :key="poll.id" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-start mb-4">
                    <div class="h-10 w-10 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center text-lg">📊</div>
                    <span :class="poll.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'" class="px-2 py-1 text-[10px] font-bold uppercase rounded-full">
                        {{ poll.is_active ? 'Active' : 'Draft' }}
                    </span>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">{{ poll.title }}</h3>
                <p class="text-xs text-gray-500 mb-4">{{ poll.options_count }} time options</p>
                
                <div class="flex space-x-2 pt-4 border-t border-gray-50">
                    <button @click="copyLink(poll.slug)" class="flex-1 py-2 text-xs font-bold bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100">
                        Copy Link
                    </button>
                    <button @click="deletePoll(poll.id)" class="px-3 py-2 text-gray-400 hover:text-red-500">
                        🗑️
                    </button>
                </div>
            </div>
        </div>

        <div v-else class="bg-white p-12 text-center rounded-3xl border-2 border-dashed border-gray-100">
            <div class="text-5xl mb-4">🗳️</div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">Create multiple options</h2>
            <p class="text-gray-500 max-w-sm mx-auto mb-8">
                Not sure when to meet? Create a poll with several time options and let your invitees vote for the best one.
            </p>
            <router-link to="/dashboard/polls/create" class="btn-primary">
                Create a Poll
            </router-link>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '../axios';

const polls = ref([]);
const loading = ref(true);

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
    const url = `${window.location.origin}/vote/${slug}`;
    navigator.clipboard.writeText(url);
    alert("Poll link copied to clipboard!");
};

const deletePoll = async (id) => {
    if (!confirm("Delete this poll?")) return;
    try {
        await axios.delete(`/api/polls/${id}`);
        fetchPolls();
    } catch (e) {
        alert("Failed to delete poll");
    }
};

onMounted(fetchPolls);
</script>
