<template>
    <div class="space-y-6 sm:space-y-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 sm:mb-8">
            <div>
                <h1 class="text-3xl sm:text-5xl font-black text-zinc-950 dark:text-white tracking-tighter uppercase font-outfit">YOUR_PAGES<span class="text-meetrix-orange">.NODES</span></h1>
                <p class="text-slate-500 font-bold text-[10px] sm:text-xs uppercase tracking-[0.2em] sm:tracking-[0.4em] mt-2">{{ $t('admin.active_deployments') }}</p>
            </div>
            <button @click="showCreateModal = true" class="w-full md:w-auto px-6 sm:px-8 py-3.5 sm:py-4 bg-meetrix-orange text-zinc-950 rounded-2xl font-black text-[10px] sm:text-xs uppercase tracking-[0.15em] sm:tracking-[0.2em] hover:scale-105 active:scale-95 transition-all shadow-xl shadow-meetrix-orange/20">
                <i class="fas fa-plus mr-2"></i> {{ $t('onboarding.page_title') }}
            </button>
        </div>

        <div v-if="loading" class="flex justify-center py-24">
            <i class="fas fa-circle-notch fa-spin text-4xl text-meetrix-orange"></i>
        </div>

        <div v-else-if="pages.length > 0" class="bg-white dark:bg-zinc-900/40 rounded-[28px] sm:rounded-[40px] border border-black/5 dark:border-white/5 overflow-hidden shadow-premium">
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-black/5 dark:divide-white/5">
                    <thead class="bg-zinc-50 dark:bg-zinc-950/50">
                        <tr>
                            <th class="px-6 lg:px-10 py-5 lg:py-6 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $t('admin.instance_name') }}</th>
                            <th class="px-6 lg:px-10 py-5 lg:py-6 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $t('admin.analytics') }}</th>
                            <th class="px-6 lg:px-10 py-5 lg:py-6 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $t('admin.control_panel') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        <tr v-for="page in pages" :key="page.id" class="group hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors">
                            <td class="px-6 lg:px-10 py-6 lg:py-8">
                                <div class="flex items-center">
                                    <div class="h-11 w-11 lg:h-12 lg:w-12 bg-zinc-100 dark:bg-zinc-900 text-zinc-950 dark:text-meetrix-orange rounded-2xl flex items-center justify-center font-black text-base lg:text-lg border border-black/5 dark:border-white/5 shadow-sm group-hover:scale-110 transition-transform">
                                        {{ page.title.charAt(0) }}
                                    </div>
                                    <div class="ml-4 lg:ml-6 flex flex-col min-w-0">
                                        <div class="text-sm font-black text-zinc-950 dark:text-white uppercase tracking-tight truncate">{{ page.title }}</div>
                                        <a :href="'/p/' + page.slug" target="_blank" class="text-[10px] font-bold text-slate-400 hover:text-meetrix-orange transition-colors uppercase tracking-widest mt-1 truncate">meetrix.opentshost.com/p/{{ page.slug }}</a>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 lg:px-10 py-6 lg:py-8">
                                <div class="flex gap-8">
                                    <div>
                                        <div class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">{{ $t('admin.analytics') }}</div>
                                        <div class="text-xl font-black text-zinc-950 dark:text-white tabular-nums tracking-tighter">{{ page.views || 0 }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 lg:px-10 py-6 lg:py-8 text-right">
                                <div class="flex justify-end items-center gap-3 lg:gap-4">
                                    <router-link :to="'/dashboard/editor/' + page.slug" class="px-4 lg:px-5 py-2.5 text-[10px] font-black bg-zinc-100 dark:bg-zinc-800 text-zinc-950 dark:text-white rounded-xl border border-black/5 dark:border-white/5 hover:bg-zinc-950 hover:text-white dark:hover:bg-white dark:hover:text-zinc-950 transition-all uppercase tracking-widest">
                                        {{ $t('admin.editor') }}
                                    </router-link>
                                    <button @click="deletePage(page.id)" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition-all">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="md:hidden p-4 space-y-3">
                <article v-for="page in pages" :key="`mobile-${page.id}`" class="rounded-2xl border border-black/5 dark:border-white/5 bg-zinc-50/70 dark:bg-zinc-950/40 p-4 space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="h-10 w-10 bg-zinc-100 dark:bg-zinc-900 text-zinc-950 dark:text-meetrix-orange rounded-xl flex items-center justify-center font-black text-sm border border-black/5 dark:border-white/5">
                            {{ page.title.charAt(0) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-sm font-black text-zinc-950 dark:text-white uppercase tracking-tight truncate">{{ page.title }}</h3>
                            <a :href="'/p/' + page.slug" target="_blank" class="text-[10px] font-bold text-slate-400 hover:text-meetrix-orange transition-colors uppercase tracking-wide mt-1 block truncate">meetrix.opentshost.com/p/{{ page.slug }}</a>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-[10px] uppercase tracking-widest">
                        <span class="font-black text-slate-400">{{ $t('admin.analytics') }}</span>
                        <span class="font-black text-zinc-950 dark:text-white">{{ page.views || 0 }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <router-link :to="'/dashboard/editor/' + page.slug" class="text-center px-4 py-2.5 text-[10px] font-black bg-zinc-950 text-white dark:bg-white dark:text-zinc-950 rounded-xl transition-all uppercase tracking-widest">
                            {{ $t('admin.editor') }}
                        </router-link>
                        <button @click="deletePage(page.id)" class="px-4 py-2.5 text-[10px] font-black text-red-500 bg-red-50 dark:bg-red-500/10 rounded-xl transition-all uppercase tracking-widest">
                            {{ $t('admin.delete') }}
                        </button>
                    </div>
                </article>
            </div>
        </div>

        <div v-else class="bg-white dark:bg-zinc-900/50 p-8 sm:p-20 text-center rounded-[28px] sm:rounded-[40px] border border-black/5 dark:border-white/5 shadow-premium">
            <div class="text-5xl sm:text-6xl mb-5 sm:mb-6 text-meetrix-orange"><i class="fas fa-magic-wand-sparkles"></i></div>
            <h2 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white mb-2">{{ $t('admin.build_first_page_title') }}</h2>
            <p class="text-gray-500 max-w-sm mx-auto mb-6 sm:mb-8 text-sm">
                {{ $t('admin.build_first_page_desc') }}
            </p>
            <button @click="showCreateModal = true" class="w-full sm:w-auto px-8 py-4 bg-meetrix-orange text-zinc-950 rounded-2xl font-black text-[10px] sm:text-xs uppercase tracking-[0.15em] sm:tracking-[0.2em] hover:scale-105 active:scale-95 transition-all shadow-xl shadow-meetrix-orange/20">
                {{ $t('home.get_started') }}
            </button>
        </div>

        <!-- Create Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/40 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-zinc-900 rounded-[28px] sm:rounded-[40px] p-5 sm:p-10 lg:p-12 max-w-md w-full shadow-premium animate-in zoom-in-95 duration-200 border border-black/5 dark:border-white/5">
                <h3 class="text-2xl sm:text-3xl font-black text-zinc-950 dark:text-white mb-6 uppercase tracking-tighter">{{ $t('onboarding.page_title') }} // {{ $t('admin.new_instance') }}</h3>
                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">{{ $t('admin.internal_name') }}</label>
                        <input v-model="newPage.title" type="text" placeholder="e.g. Sales Consultation" class="w-full px-6 py-4 rounded-2xl bg-zinc-50 dark:bg-zinc-950 border-2 border-black/5 dark:border-white/5 focus:border-meetrix-orange outline-none text-zinc-950 dark:text-white font-bold transition-all">
                    </div>
                </div>
                <div class="mt-8 sm:mt-12 flex gap-3 sm:gap-4">
                    <button @click="showCreateModal = false" class="flex-1 py-4 font-black text-[10px] uppercase tracking-widest text-slate-400 hover:text-zinc-950 dark:hover:text-white transition-colors">{{ $t('common.cancel') }}</button>
                    <button @click="createPage" :disabled="creating" class="flex-1 py-4 bg-meetrix-orange text-zinc-950 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:scale-105 active:scale-95 transition-all shadow-lg shadow-meetrix-orange/20">
                        {{ creating ? $t('admin.initializing') : $t('admin.activate') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import axios from '../axios';

const { t } = useI18n();
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
        alert(t('admin.save_failed'));
    } finally {
        creating.value = false;
    }
};

const deletePage = async (id) => {
    if (!confirm(t('admin.destroy_vector_confirm'))) return;
    try {
        await axios.delete(`/api/pages/${id}`);
        fetchPages();
    } catch (e) {
        alert(t('admin.save_failed'));
    }
};

onMounted(fetchPages);
</script>
