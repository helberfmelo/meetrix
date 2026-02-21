<template>
    <div class="space-y-8 py-6 animate-in fade-in duration-700">
        <header class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl sm:text-4xl font-black text-zinc-950 dark:text-white tracking-tight uppercase">Conta</h1>
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.25em] mt-2">Perfil, seguranca, preferencias e cobranca</p>
            </div>
            <div v-if="summary?.user" class="px-4 py-2 rounded-xl border border-black/10 dark:border-white/10 text-[10px] font-black uppercase tracking-wider">
                Plano: {{ summary.user.subscription_tier || 'free' }}
            </div>
        </header>

        <section class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <article class="xl:col-span-2 rounded-3xl border border-black/5 dark:border-white/10 bg-white dark:bg-zinc-900 p-6 space-y-6">
                <h2 class="text-lg font-black text-zinc-950 dark:text-white uppercase tracking-wide">Perfil</h2>
                <form @submit.prevent="saveProfile" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input
                        v-model="profile.name"
                        type="text"
                        placeholder="Nome"
                        class="w-full rounded-xl px-4 py-3 bg-zinc-50 dark:bg-zinc-950 border border-black/10 dark:border-white/10 text-zinc-900 dark:text-white"
                    >
                    <input
                        v-model="profile.email"
                        type="email"
                        placeholder="E-mail"
                        class="w-full rounded-xl px-4 py-3 bg-zinc-50 dark:bg-zinc-950 border border-black/10 dark:border-white/10 text-zinc-900 dark:text-white"
                    >
                    <input
                        v-model="profile.country_code"
                        type="text"
                        maxlength="2"
                        placeholder="Pais (BR)"
                        class="w-full rounded-xl px-4 py-3 bg-zinc-50 dark:bg-zinc-950 border border-black/10 dark:border-white/10 text-zinc-900 dark:text-white uppercase"
                    >
                    <button
                        type="submit"
                        :disabled="savingProfile"
                        class="rounded-xl px-4 py-3 bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 text-[10px] font-black uppercase tracking-[0.2em] disabled:opacity-60"
                    >
                        {{ savingProfile ? 'Salvando...' : 'Salvar Perfil' }}
                    </button>
                </form>
            </article>

            <div class="space-y-6">
                <article class="rounded-3xl border border-black/5 dark:border-white/10 bg-white dark:bg-zinc-900 p-6 space-y-4">
                    <h2 class="text-lg font-black text-zinc-950 dark:text-white uppercase tracking-wide">Resumo</h2>
                    <div v-if="summary?.user" class="space-y-2 text-sm">
                        <p class="text-slate-600 dark:text-slate-300"><span class="font-bold">E-mail:</span> {{ summary.user.email }}</p>
                        <p class="text-slate-600 dark:text-slate-300"><span class="font-bold">Modo:</span> {{ formatMode(summary.user.account_mode) }}</p>
                        <p class="text-slate-600 dark:text-slate-300"><span class="font-bold">Regiao:</span> {{ summary.user.region || 'BR' }}</p>
                        <p class="text-slate-600 dark:text-slate-300"><span class="font-bold">Moeda:</span> {{ summary.user.currency || 'BRL' }}</p>
                        <p class="text-slate-600 dark:text-slate-300"><span class="font-bold">Idioma:</span> {{ summary.user.preferred_locale || 'auto' }}</p>
                        <p class="text-slate-600 dark:text-slate-300"><span class="font-bold">Fuso horário:</span> {{ summary.user.timezone || 'UTC' }}</p>
                        <p class="text-slate-600 dark:text-slate-300"><span class="font-bold">Paginas:</span> {{ summary.user.scheduling_pages_count }}</p>
                        <p class="text-slate-600 dark:text-slate-300"><span class="font-bold">Times:</span> {{ summary.user.teams_count }}</p>
                        <div class="pt-3 border-t border-black/5 dark:border-white/10">
                            <button
                                v-if="summary.user.account_mode === 'scheduling_only'"
                                :disabled="upgradingMode"
                                @click="upgradeToPayments"
                                class="w-full rounded-xl px-4 py-3 bg-meetrix-orange text-zinc-950 text-[10px] font-black uppercase tracking-[0.2em] disabled:opacity-60"
                            >
                                {{ upgradingMode ? $t('account.activating_payments') : $t('account.activate_billing') }}
                            </button>
                            <p v-else class="text-[10px] font-black uppercase tracking-[0.15em] text-meetrix-green">
                                Cobranca integrada ativa para esta conta.
                            </p>
                        </div>
                    </div>
                    <p v-else class="text-sm text-slate-500">Carregando...</p>
                </article>

                <article class="rounded-3xl border border-black/5 dark:border-white/10 bg-white dark:bg-zinc-900 p-6 space-y-4">
                    <h2 class="text-lg font-black text-zinc-950 dark:text-white uppercase tracking-wide">Assinatura</h2>
                    <form @submit.prevent="changeSubscription" class="space-y-3">
                        <select
                            v-model="subscription.plan"
                            class="w-full rounded-xl px-4 py-3 bg-zinc-50 dark:bg-zinc-950 border border-black/10 dark:border-white/10 text-zinc-900 dark:text-white"
                        >
                            <option value="free">Agenda</option>
                            <option value="pro">Pro</option>
                            <option value="enterprise">Enterprise</option>
                        </select>

                        <select
                            v-model="subscription.interval"
                            :disabled="subscription.plan === 'free'"
                            class="w-full rounded-xl px-4 py-3 bg-zinc-50 dark:bg-zinc-950 border border-black/10 dark:border-white/10 text-zinc-900 dark:text-white disabled:opacity-50"
                        >
                            <option value="monthly">Mensal</option>
                            <option value="annual">Anual</option>
                        </select>

                        <input
                            v-model="subscription.reason"
                            type="text"
                            maxlength="500"
                            placeholder="Motivo (opcional)"
                            class="w-full rounded-xl px-4 py-3 bg-zinc-50 dark:bg-zinc-950 border border-black/10 dark:border-white/10 text-zinc-900 dark:text-white"
                        >

                        <p class="text-xs text-slate-500">
                            Valor estimado:
                            <span class="font-bold text-zinc-900 dark:text-zinc-100">
                                {{ formatCurrency(selectedSubscriptionPrice, subscriptionCurrency) }}
                            </span>
                        </p>

                        <button
                            type="submit"
                            :disabled="updatingSubscription"
                            class="w-full rounded-xl px-4 py-3 bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 text-[10px] font-black uppercase tracking-[0.2em] disabled:opacity-60"
                        >
                            {{ updatingSubscription ? $t('account.updating') : $t('account.update_plan') }}
                        </button>
                    </form>

                    <button
                        @click="cancelCurrentSubscription"
                        :disabled="cancelingSubscription || !canCancelSubscription"
                        class="w-full rounded-xl px-4 py-3 border border-red-300 text-red-600 text-[10px] font-black uppercase tracking-[0.2em] disabled:opacity-50"
                    >
                        {{ cancelingSubscription ? $t('account.canceling') : $t('account.cancel_subscription') }}
                    </button>
                </article>
            </div>
        </section>

        <section class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <article class="rounded-3xl border border-black/5 dark:border-white/10 bg-white dark:bg-zinc-900 p-6 space-y-6">
                <h2 class="text-lg font-black text-zinc-950 dark:text-white uppercase tracking-wide">Preferencias</h2>
                <form @submit.prevent="savePreferences" class="space-y-4">
                    <input
                        v-model="preferences.preferred_locale"
                        type="text"
                        placeholder="Idioma (pt-BR)"
                        class="w-full rounded-xl px-4 py-3 bg-zinc-50 dark:bg-zinc-950 border border-black/10 dark:border-white/10 text-zinc-900 dark:text-white"
                    >
                    <input
                        v-model="preferences.timezone"
                        type="text"
                        placeholder="Fuso horário (America/Sao_Paulo)"
                        class="w-full rounded-xl px-4 py-3 bg-zinc-50 dark:bg-zinc-950 border border-black/10 dark:border-white/10 text-zinc-900 dark:text-white"
                    >
                    <button
                        type="submit"
                        :disabled="savingPreferences"
                        class="rounded-xl px-4 py-3 bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 text-[10px] font-black uppercase tracking-[0.2em] disabled:opacity-60"
                    >
                        {{ savingPreferences ? 'Salvando...' : 'Salvar Preferencias' }}
                    </button>
                </form>
            </article>

            <article class="rounded-3xl border border-black/5 dark:border-white/10 bg-white dark:bg-zinc-900 p-6 space-y-6">
                <h2 class="text-lg font-black text-zinc-950 dark:text-white uppercase tracking-wide">Seguranca</h2>
                <form @submit.prevent="savePassword" class="space-y-4">
                    <input
                        v-model="password.current_password"
                        type="password"
                        placeholder="Senha atual"
                        class="w-full rounded-xl px-4 py-3 bg-zinc-50 dark:bg-zinc-950 border border-black/10 dark:border-white/10 text-zinc-900 dark:text-white"
                    >
                    <input
                        v-model="password.password"
                        type="password"
                        placeholder="Nova senha"
                        class="w-full rounded-xl px-4 py-3 bg-zinc-50 dark:bg-zinc-950 border border-black/10 dark:border-white/10 text-zinc-900 dark:text-white"
                    >
                    <input
                        v-model="password.password_confirmation"
                        type="password"
                        placeholder="Confirmar nova senha"
                        class="w-full rounded-xl px-4 py-3 bg-zinc-50 dark:bg-zinc-950 border border-black/10 dark:border-white/10 text-zinc-900 dark:text-white"
                    >
                    <button
                        type="submit"
                        :disabled="savingPassword"
                        class="rounded-xl px-4 py-3 bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 text-[10px] font-black uppercase tracking-[0.2em] disabled:opacity-60"
                    >
                        {{ savingPassword ? $t('account.updating') : $t('account.update_password') }}
                    </button>
                </form>
            </article>
        </section>

        <section class="rounded-3xl border border-black/5 dark:border-white/10 bg-white dark:bg-zinc-900 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-black text-zinc-950 dark:text-white uppercase tracking-wide">Historico de Cobranca</h2>
                <button
                    class="text-[10px] font-black uppercase tracking-wider text-slate-500"
                    @click="loadBillingHistory"
                >
                    Recarregar
                </button>
            </div>

            <div v-if="billing.length === 0" class="text-sm text-slate-500 py-6">Sem transacoes registradas.</div>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-[10px] uppercase tracking-wider text-slate-500">
                            <th class="py-2">Data</th>
                            <th class="py-2">Fonte</th>
                            <th class="py-2">Status</th>
                            <th class="py-2">Valor</th>
                            <th class="py-2">Descricao</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in billing" :key="item.id" class="border-t border-black/5 dark:border-white/10">
                            <td class="py-2 text-zinc-900 dark:text-zinc-100">{{ formatDate(item.created_at) }}</td>
                            <td class="py-2 text-zinc-900 dark:text-zinc-100">{{ item.source }}</td>
                            <td class="py-2">
                                <span class="px-2 py-1 rounded-full text-[10px] font-black uppercase" :class="statusClass(item.status)">
                                    {{ item.status }}
                                </span>
                            </td>
                            <td class="py-2 text-zinc-900 dark:text-zinc-100">{{ formatCurrency(item.amount, item.currency) }}</td>
                            <td class="py-2 text-zinc-900 dark:text-zinc-100">{{ item.description || '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <p v-if="feedback" class="text-sm font-medium text-meetrix-green">{{ feedback }}</p>
        <p v-if="error" class="text-sm font-medium text-red-500">{{ error }}</p>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from '../axios';

const router = useRouter();
const summary = ref(null);
const billing = ref([]);

const profile = ref({
    name: '',
    email: '',
    country_code: '',
});

const preferences = ref({
    preferred_locale: '',
    timezone: 'UTC',
});

const password = ref({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const subscription = ref({
    plan: 'free',
    interval: 'monthly',
    reason: '',
});

const savingProfile = ref(false);
const savingPreferences = ref(false);
const savingPassword = ref(false);
const upgradingMode = ref(false);
const updatingSubscription = ref(false);
const cancelingSubscription = ref(false);
const feedback = ref('');
const error = ref('');

const subscriptionOptions = computed(() => summary.value?.subscription_options?.plans || {});
const subscriptionCurrency = computed(() => summary.value?.subscription_options?.currency || summary.value?.user?.currency || 'BRL');

const selectedSubscriptionPrice = computed(() => {
    const currentPlan = subscriptionOptions.value?.[subscription.value.plan];
    if (!currentPlan) return 0;

    if (subscription.value.plan === 'free') {
        return Number(currentPlan.prices?.monthly || 0);
    }

    return Number(currentPlan.prices?.[subscription.value.interval] || 0);
});

const canCancelSubscription = computed(() => (summary.value?.user?.subscription_tier || 'free') !== 'free');

const resetMessages = () => {
    feedback.value = '';
    error.value = '';
};

const parseApiError = (fallbackMessage, e) => {
    const payload = e?.response?.data || {};
    const message = payload.message || fallbackMessage;
    return payload.error_code ? `[${payload.error_code}] ${message}` : message;
};

const loadSummary = async () => {
    const { data } = await axios.get('/api/account/summary');
    summary.value = data;

    profile.value = {
        name: data.user?.name || '',
        email: data.user?.email || '',
        country_code: data.user?.country_code || '',
    };

    preferences.value = {
        preferred_locale: data.user?.preferred_locale || '',
        timezone: data.user?.timezone || 'UTC',
    };

    subscription.value.plan = data.user?.subscription_tier || 'free';
    subscription.value.interval = data.user?.billing_cycle || 'monthly';
};

const loadBillingHistory = async () => {
    const { data } = await axios.get('/api/account/billing-history');
    billing.value = data.data || [];
};

const saveProfile = async () => {
    resetMessages();
    savingProfile.value = true;
    try {
        await axios.put('/api/account/profile', profile.value);
        await loadSummary();
        feedback.value = 'Perfil atualizado.';
    } catch (e) {
        error.value = parseApiError('Falha ao atualizar perfil.', e);
    } finally {
        savingProfile.value = false;
    }
};

const savePreferences = async () => {
    resetMessages();
    savingPreferences.value = true;
    try {
        await axios.put('/api/account/preferences', preferences.value);
        await loadSummary();
        feedback.value = 'Preferencias atualizadas.';
    } catch (e) {
        error.value = parseApiError('Falha ao atualizar preferencias.', e);
    } finally {
        savingPreferences.value = false;
    }
};

const savePassword = async () => {
    resetMessages();
    savingPassword.value = true;
    try {
        await axios.put('/api/account/password', password.value);
        password.value = {
            current_password: '',
            password: '',
            password_confirmation: '',
        };
        feedback.value = 'Senha atualizada.';
    } catch (e) {
        error.value = parseApiError('Falha ao atualizar senha.', e);
    } finally {
        savingPassword.value = false;
    }
};

const formatDate = (value) => {
    if (!value) return '-';
    return new Date(value).toLocaleString();
};

const formatCurrency = (value, currency = 'BRL') => {
    if (value === null || value === undefined) return '-';
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency }).format(Number(value));
};

const statusClass = (status) => {
    if (status === 'paid') return 'bg-green-100 text-green-700';
    if (status === 'pending') return 'bg-yellow-100 text-yellow-700';
    if (status === 'failed') return 'bg-red-100 text-red-700';
    return 'bg-zinc-100 text-zinc-700';
};

const formatMode = (mode) => {
    if (mode === 'scheduling_with_payments') return 'Agenda + cobranca';
    return 'Apenas agenda';
};

const upgradeToPayments = async () => {
    resetMessages();
    upgradingMode.value = true;

    try {
        await axios.patch('/api/account/mode', {
            account_mode: 'scheduling_with_payments',
        });

        await loadSummary();
        feedback.value = 'Modo atualizado para agenda + cobranca.';
        router.push('/checkout');
    } catch (e) {
        error.value = parseApiError('Falha ao atualizar modo de conta.', e);
    } finally {
        upgradingMode.value = false;
    }
};

const changeSubscription = async () => {
    resetMessages();
    updatingSubscription.value = true;

    try {
        await axios.post('/api/account/subscription/change', {
            plan: subscription.value.plan,
            interval: subscription.value.plan === 'free' ? 'monthly' : subscription.value.interval,
            reason: subscription.value.reason || null,
        });

        subscription.value.reason = '';
        await Promise.all([loadSummary(), loadBillingHistory()]);
        feedback.value = 'Assinatura atualizada com sucesso.';
    } catch (e) {
        error.value = parseApiError('Falha ao atualizar assinatura.', e);
    } finally {
        updatingSubscription.value = false;
    }
};

const cancelCurrentSubscription = async () => {
    if (!canCancelSubscription.value) return;

    const confirmed = window.confirm('Confirmar cancelamento da assinatura atual?');
    if (!confirmed) return;

    resetMessages();
    cancelingSubscription.value = true;

    try {
        await axios.post('/api/account/subscription/cancel', {
            reason: 'self_service_cancel',
        });

        await Promise.all([loadSummary(), loadBillingHistory()]);
        feedback.value = 'Assinatura cancelada com sucesso.';
    } catch (e) {
        error.value = parseApiError('Falha ao cancelar assinatura.', e);
    } finally {
        cancelingSubscription.value = false;
    }
};

onMounted(async () => {
    try {
        await Promise.all([loadSummary(), loadBillingHistory()]);
    } catch (e) {
        error.value = 'Nao foi possivel carregar os dados da conta.';
    }
});
</script>
