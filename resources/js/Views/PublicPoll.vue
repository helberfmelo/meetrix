<template>
    <div class="min-h-screen bg-gray-50/50 py-12 px-4">
        <div v-if="loading" class="flex justify-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
        </div>

        <div v-else-if="poll" class="max-w-xl mx-auto bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
            <div class="p-8 border-b border-gray-50 bg-indigo-600 text-white">
                <h1 class="text-2xl font-black mb-2">{{ poll.title }}</h1>
                <p class="text-indigo-100 text-sm opacity-80">{{ poll.description }}</p>
                <div class="mt-4 flex items-center text-xs font-bold text-indigo-200 uppercase tracking-widest">
                    <span class="mr-2">By</span>
                    <span class="text-white">{{ poll.user?.name || 'Meetrix User' }}</span>
                </div>
            </div>

            <div class="p-8 space-y-6">
                <!-- Customer Info -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest px-1">Your Name</label>
                        <input v-model="voter.customer_name" type="text" placeholder="John Doe" class="w-full px-4 py-3 rounded-xl border-2 border-gray-50 focus:border-indigo-600 outline-none">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest px-1">Your Email</label>
                        <input v-model="voter.customer_email" type="email" placeholder="john@example.com" class="w-full px-4 py-3 rounded-xl border-2 border-gray-50 focus:border-indigo-600 outline-none">
                    </div>
                </div>

                <div class="space-y-3">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest px-1 flex justify-between">
                        Choose your availability
                        <span>{{ totalVotes }} votes so far</span>
                    </h3>
                    
                    <div v-for="option in poll.options" :key="option.id" class="relative group">
                        <label 
                            :class="[votedOptions.has(option.id) ? 'border-indigo-600 bg-indigo-50/30' : 'border-gray-50 hover:border-gray-200']"
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
                                <span class="text-[10px] text-gray-400 uppercase font-bold">votes</span>
                            </div>
                        </label>
                    </div>
                </div>

                <button @click="submitVotes" :disabled="submitting || !selectedOptions.length" class="w-full btn-primary py-5 shadow-lg shadow-indigo-100 disabled:opacity-50">
                    {{ submitting ? 'Submitting...' : 'Submit My Availability' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import axios from '../axios';

const route = useRoute();
const poll = ref(null);
const loading = ref(true);
const submitting = ref(false);

const voter = ref({ customer_name: '', customer_email: '' });
const selectedOptions = ref([]);
const votedOptions = ref(new Set()); // Local tracking for immediate feedback

const totalVotes = computed(() => {
    if (!poll.value) return 0;
    return poll.value.options.reduce((sum, opt) => sum + opt.votes_count, 0);
});

const fetchPoll = async () => {
    try {
        const response = await axios.get(`/api/p/polls/${route.params.slug}`);
        poll.value = response.data;
    } catch (e) {
        alert("Poll not found or inactive");
    } finally {
        loading.value = false;
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' });
};

const formatTime = (date) => {
    return new Date(date).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
};

const submitVotes = async () => {
    if (!voter.value.customer_name || !voter.value.customer_email) {
        return alert("Please enter your name and email");
    }

    submitting.value = true;
    try {
        // Submit each selected option
        for (const optionId of selectedOptions.value) {
            await axios.post(`/api/poll-options/${optionId}/vote`, voter.value);
        }
        alert("Thank you for your response!");
        fetchPoll(); // Refresh counts
        selectedOptions.value = [];
    } catch (e) {
        alert("Submission failed. You might have already voted for these options.");
    } finally {
        submitting.value = false;
    }
};

onMounted(fetchPoll);
</script>
