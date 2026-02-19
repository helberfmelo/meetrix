<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-black text-gray-900">Your Pages</h1>
            <button @click="showCreateModal = true" class="btn-primary">
                + Create New Page
            </button>
        </div>

        <div v-if="loading" class="flex justify-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
        </div>

        <div v-else-if="pages.length > 0" class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Page</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Performance</th>
                        <th class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    <tr v-for="page in pages" :key="page.id" class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-8 py-6">
                            <div class="flex items-center">
                                <div class="h-10 w-10 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center font-bold mr-4">
                                    {{ page.title.charAt(0) }}
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-gray-900">{{ page.title }}</div>
                                    <a :href="'/p/' + page.slug" target="_blank" class="text-xs text-indigo-500 hover:underline">meetrix.io/p/{{ page.slug }}</a>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex space-x-4">
                                <div class="text-center">
                                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Views</div>
                                    <div class="text-sm font-bold text-gray-900">{{ page.views || 0 }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="flex justify-end space-x-2">
                                <router-link :to="'/dashboard/editor/' + page.slug" class="px-3 py-2 text-xs font-bold bg-white border border-gray-100 text-gray-600 rounded-lg hover:bg-gray-50">
                                    Edit
                                </router-link>
                                <button @click="deletePage(page.id)" class="px-3 py-2 text-gray-400 hover:text-red-500">
                                    🗑️
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-else class="bg-white p-20 text-center rounded-3xl border-2 border-dashed border-gray-100">
            <div class="text-6xl mb-6">✨</div>
            <h2 class="text-2xl font-black text-gray-900 mb-2">Build your first booking page</h2>
            <p class="text-gray-500 max-w-sm mx-auto mb-8">
                Create a professional page to start accepting appointments in minutes.
            </p>
            <button @click="showCreateModal = true" class="btn-primary py-4 px-8 text-lg">
                Get Started
            </button>
        </div>

        <!-- Create Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4">
            <div class="bg-white rounded-3xl p-8 max-w-md w-full shadow-2xl animate-in zoom-in-95 duration-200">
                <h3 class="text-2xl font-black text-gray-900 mb-6">New Booking Page</h3>
                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest px-1">Internal Name</label>
                        <input v-model="newPage.title" type="text" placeholder="e.g. Sales Consultation" class="w-full px-5 py-4 rounded-xl border-2 border-gray-100 focus:border-indigo-600 outline-none">
                    </div>
                </div>
                <div class="mt-8 flex space-x-3">
                    <button @click="showCreateModal = false" class="flex-1 py-4 font-bold text-gray-500 hover:bg-gray-50 rounded-xl">Cancel</button>
                    <button @click="createPage" :disabled="creating" class="flex-1 btn-primary">
                        {{ creating ? 'Creating...' : 'Create' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from '../axios';

const pages = ref([]);
const loading = ref(true);
const showCreateModal = ref(false);
const creating = ref(false);
const router = useRouter();

const newPage = ref({ title: '' });

const fetchPages = async () => {
    try {
        const response = await axios.get('/api/pages');
        pages.value = response.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

const createPage = async () => {
    if (!newPage.value.title) return;
    creating.value = true;
    try {
        // Generate a basic slug
        const slug = newPage.value.title.toLowerCase().replace(/[^a-z0-9]/g, '-') + '-' + Math.floor(Math.random() * 1000);
        const response = await axios.post('/api/pages', { ...newPage.value, slug });
        showCreateModal.value = false;
        router.push(`/dashboard/editor/${response.data.slug}`);
    } catch (e) {
        alert("Failed to create page. Try a different title.");
    } finally {
        creating.value = false;
    }
};

const deletePage = async (id) => {
    if (!confirm("Delete this page? This cannot be undone.")) return;
    try {
        await axios.delete(`/api/pages/${id}`);
        fetchPages();
    } catch (e) {
        alert("Delete failed");
    }
};

onMounted(fetchPages);
</script>
