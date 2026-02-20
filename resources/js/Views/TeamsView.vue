<template>
    <div class="space-y-6 sm:space-y-8">
        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3 sm:gap-4">
            <h1 class="text-2xl sm:text-3xl font-black text-zinc-950 dark:text-white">{{ $t('common.teams') }}</h1>
            <button @click="showCreateModal = true" class="btn-primary w-full sm:w-auto">
                + {{ $t('admin.create_team') }}
            </button>
        </div>

        <!-- Teams List -->
        <div v-if="loading" class="flex justify-center py-24">
            <i class="fas fa-circle-notch fa-spin text-4xl text-meetrix-orange"></i>
        </div>

        <div v-else-if="teams.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            <div v-for="team in teams" :key="team.id" class="bg-white dark:bg-zinc-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-white/10 hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <div class="h-12 w-12 bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-300 rounded-xl flex items-center justify-center text-xl font-bold">
                        {{ team.name.charAt(0) }}
                    </div>
                    <span class="px-2 py-1 bg-gray-100 dark:bg-zinc-800 text-gray-500 dark:text-slate-300 text-[10px] font-bold uppercase rounded">
                        {{ team.pivot.role }}
                    </span>
                </div>
                <h3 class="text-xl font-bold text-zinc-950 dark:text-white mb-1">{{ team.name }}</h3>
                <p class="text-xs text-gray-400 dark:text-slate-400 font-mono mb-4">slug: {{ team.slug }}</p>
                
                <div class="flex items-center space-x-2 mb-6">
                    <div class="flex -space-x-2">
                        <div v-for="i in 3" :key="i" class="h-6 w-6 rounded-full border-2 border-white dark:border-zinc-900 bg-gray-200 dark:bg-zinc-700"></div>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-slate-300 font-medium">{{ $t('admin.members_coming_soon') }}</span>
                </div>

                <div class="flex space-x-2">
                    <button @click="inviteMember(team)" class="flex-1 py-2 text-xs font-bold border-2 border-gray-100 dark:border-white/10 rounded-lg hover:border-indigo-200 dark:hover:border-indigo-400/40 hover:text-indigo-600 dark:hover:text-indigo-300 transition-all text-zinc-700 dark:text-slate-100">
                        {{ $t('admin.invite_member') }}
                    </button>
                    <router-link :to="'/teams/' + team.id" class="flex-1 py-2 text-xs font-bold bg-gray-50 dark:bg-zinc-800 text-gray-600 dark:text-slate-100 text-center rounded-lg hover:bg-gray-100 dark:hover:bg-zinc-700">
                        {{ $t('common.settings') }}
                    </router-link>
                </div>
            </div>
        </div>

        <div v-else class="bg-white dark:bg-zinc-900/50 p-8 sm:p-12 text-center rounded-[28px] sm:rounded-[40px] border border-black/5 dark:border-white/5 shadow-premium">
            <div class="text-5xl mb-4 text-meetrix-orange"><i class="fas fa-users-viewfinder"></i></div>
            <h2 class="text-xl font-bold text-zinc-950 dark:text-white mb-2">{{ $t('admin.no_teams_yet') }}</h2>
            <p class="text-gray-500 dark:text-slate-300 max-w-sm mx-auto mb-8">
                {{ $t('admin.teams_description') }}
            </p>
            <button @click="showCreateModal = true" class="btn-primary w-full sm:w-auto">
                {{ $t('admin.create_first_team') }}
            </button>
        </div>

        <!-- Create Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-zinc-900 w-full max-w-md rounded-3xl shadow-2xl p-5 sm:p-8 animate-in zoom-in-95 duration-200 border border-gray-100 dark:border-white/10">
                <h2 class="text-xl sm:text-2xl font-black text-zinc-950 dark:text-white mb-6">{{ $t('admin.create_new_team') }}</h2>
                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-gray-400 dark:text-slate-400 uppercase tracking-wider px-1">{{ $t('admin.team_name') }}</label>
                        <input v-model="newTeam.name" type="text" :placeholder="$t('admin.team_name_placeholder')" class="w-full px-5 py-4 rounded-xl border-2 border-gray-100 dark:border-white/10 bg-white dark:bg-zinc-950 text-zinc-900 dark:text-white focus:border-indigo-600 outline-none transition-all">
                    </div>
                </div>
                <div class="flex flex-col-reverse sm:flex-row gap-3 mt-8">
                    <button @click="showCreateModal = false" class="flex-1 py-4 font-bold text-gray-400 dark:text-slate-400 hover:text-gray-600 dark:hover:text-slate-200">{{ $t('admin.cancel') }}</button>
                    <button @click="createTeam" :disabled="creating" class="flex-1 btn-primary py-4">
                        {{ creating ? $t('admin.creating') : $t('admin.create_team') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '../axios';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const teams = ref([]);
const loading = ref(true);
const creating = ref(false);
const showCreateModal = ref(false);
const newTeam = ref({ name: '' });

const fetchTeams = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/teams');
        teams.value = response.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

const createTeam = async () => {
    if (!newTeam.value.name) return;
    creating.value = true;
    try {
        await axios.post('/api/teams', newTeam.value);
        showCreateModal.value = false;
        newTeam.value.name = '';
        fetchTeams();
    } catch (e) {
        alert(t('admin.failed_create_team'));
    } finally {
        creating.value = false;
    }
};

const inviteMember = async (team) => {
    const email = prompt(t('admin.enter_email'));
    if (!email) return;

    try {
        await axios.post(`/api/teams/${team.id}/invite`, { email });
        alert(t('admin.invited_success'));
    } catch (e) {
        alert(e.response?.data?.message || t('admin.invitation_failed'));
    }
};

onMounted(fetchTeams);
</script>
