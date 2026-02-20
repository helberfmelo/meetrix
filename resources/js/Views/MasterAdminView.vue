<template>
    <div class="space-y-8 py-6 animate-in fade-in duration-700">
        <header class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <h1 class="text-3xl sm:text-4xl font-black text-zinc-950 dark:text-white tracking-tight uppercase">{{ $t('admin.master_admin_title') }}</h1>
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.25em] mt-2">Operacao SaaS: clientes, pagamentos, cupons e atividade</p>
            </div>
            <button
                class="rounded-xl px-4 py-3 bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 text-[10px] font-black uppercase tracking-[0.2em]"
                @click="refreshAll"
            >
                Recarregar Painel
            </button>
        </header>

        <section v-if="overview" class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-8 gap-3">
            <article class="rounded-2xl border border-black/5 dark:border-white/10 bg-white dark:bg-zinc-900 p-4" v-for="card in kpiCards" :key="card.label">
                <p class="text-[9px] font-black uppercase tracking-wider text-slate-500">{{ card.label }}</p>
                <p class="text-2xl font-black text-zinc-950 dark:text-white mt-2">{{ card.value }}</p>
            </article>
        </section>

        <section class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <article class="xl:col-span-2 rounded-3xl border border-black/5 dark:border-white/10 bg-white dark:bg-zinc-900 p-6 space-y-4">
                <div class="flex flex-col md:flex-row gap-3">
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Buscar cliente por nome, email ou tenant"
                        class="flex-1 rounded-xl px-4 py-3 bg-zinc-50 dark:bg-zinc-950 border border-black/10 dark:border-white/10 text-zinc-900 dark:text-white"
                    >
                    <select v-model="filters.status" class="rounded-xl px-4 py-3 bg-zinc-50 dark:bg-zinc-950 border border-black/10 dark:border-white/10 text-zinc-900 dark:text-white">
                        <option value="all">Todos</option>
                        <option value="active">Ativos</option>
                        <option value="inactive">Inativos</option>
                    </select>
                    <select v-model="filters.subscription" class="rounded-xl px-4 py-3 bg-zinc-50 dark:bg-zinc-950 border border-black/10 dark:border-white/10 text-zinc-900 dark:text-white">
                        <option value="all">Todos planos</option>
                        <option value="free">{{ $t('admin.plan_free') }}</option>
                        <option value="pro">{{ $t('admin.plan_pro') }}</option>
                        <option value="enterprise">{{ $t('admin.plan_enterprise') }}</option>
                    </select>
                    <button
                        @click="fetchCustomers"
                        class="rounded-xl px-4 py-3 bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 text-[10px] font-black uppercase tracking-[0.2em]"
                    >
                        Filtrar
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-[10px] uppercase tracking-wider text-slate-500">
                                <th class="py-2">Cliente</th>
                                <th class="py-2">Status</th>
                                <th class="py-2">Plano</th>
                                <th class="py-2">Paginas</th>
                                <th class="py-2">Times</th>
                                <th class="py-2">Pagos</th>
                                <th class="py-2">Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="customer in customers" :key="customer.id" class="border-t border-black/5 dark:border-white/10">
                                <td class="py-2">
                                    <p class="font-bold text-zinc-900 dark:text-zinc-100">{{ customer.name }}</p>
                                    <p class="text-xs text-slate-500">{{ customer.email }}</p>
                                </td>
                                <td class="py-2">
                                    <span class="px-2 py-1 rounded-full text-[10px] font-black uppercase" :class="customer.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                                        {{ customer.is_active ? 'ativo' : 'inativo' }}
                                    </span>
                                </td>
                                <td class="py-2 text-zinc-900 dark:text-zinc-100">{{ customer.subscription_tier || 'free' }}</td>
                                <td class="py-2 text-zinc-900 dark:text-zinc-100">{{ customer.scheduling_pages_count }}</td>
                                <td class="py-2 text-zinc-900 dark:text-zinc-100">{{ customer.owned_teams_count }}</td>
                                <td class="py-2 text-zinc-900 dark:text-zinc-100">{{ customer.payments_paid_count }}</td>
                                <td class="py-2">
                                    <button
                                        @click="openCustomer(customer)"
                                        class="px-3 py-1 rounded-lg border border-black/10 dark:border-white/10 text-[10px] font-black uppercase tracking-wider"
                                    >
                                        Detalhes
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="rounded-3xl border border-black/5 dark:border-white/10 bg-white dark:bg-zinc-900 p-6 space-y-4">
                <h2 class="text-lg font-black text-zinc-950 dark:text-white uppercase tracking-wide">Atividade Recente</h2>
                <div v-if="overview?.recent_activity?.length" class="space-y-3 max-h-[420px] overflow-auto pr-2">
                    <div
                        v-for="item in overview.recent_activity"
                        :key="item.id"
                        class="rounded-xl border border-black/5 dark:border-white/10 p-3"
                    >
                        <p class="text-xs font-bold text-zinc-900 dark:text-zinc-100">
                            {{ item.action }} - {{ item.target?.email || 'n/a' }}
                        </p>
                        <p class="text-[10px] text-slate-500 mt-1">{{ formatDate(item.created_at) }}</p>
                    </div>
                </div>
                <p v-else class="text-sm text-slate-500">Sem atividade registrada.</p>
            </article>
        </section>

        <section v-if="selectedCustomer" class="rounded-3xl border border-black/5 dark:border-white/10 bg-white dark:bg-zinc-900 p-6 space-y-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black text-zinc-950 dark:text-white uppercase tracking-wide">Conta Selecionada</h2>
                    <p class="text-sm text-slate-500">{{ selectedCustomer.customer.name }} - {{ selectedCustomer.customer.email }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button
                        @click="runAction('activate')"
                        class="px-4 py-2 rounded-xl bg-green-600 text-white text-[10px] font-black uppercase tracking-wider"
                    >
                        Ativar
                    </button>
                    <button
                        @click="runAction('deactivate')"
                        class="px-4 py-2 rounded-xl bg-red-600 text-white text-[10px] font-black uppercase tracking-wider"
                    >
                        Desativar
                    </button>
                    <button
                        @click="runAction('reset_onboarding')"
                        class="px-4 py-2 rounded-xl bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 text-[10px] font-black uppercase tracking-wider"
                    >
                        Resetar Onboarding
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                <article class="rounded-2xl border border-black/5 dark:border-white/10 p-4">
                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">{{ $t('common.teams') }}</p>
                    <p class="text-2xl font-black text-zinc-900 dark:text-zinc-100 mt-2">{{ selectedCustomer.customer.owned_teams?.length || 0 }}</p>
                </article>
                <article class="rounded-2xl border border-black/5 dark:border-white/10 p-4">
                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Paginas</p>
                    <p class="text-2xl font-black text-zinc-900 dark:text-zinc-100 mt-2">{{ selectedCustomer.customer.scheduling_pages?.length || 0 }}</p>
                </article>
                <article class="rounded-2xl border border-black/5 dark:border-white/10 p-4">
                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Integracoes</p>
                    <p class="text-2xl font-black text-zinc-900 dark:text-zinc-100 mt-2">{{ selectedCustomer.customer.integrations?.length || 0 }}</p>
                </article>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <article class="rounded-2xl border border-black/5 dark:border-white/10 p-4">
                    <h3 class="text-sm font-black uppercase tracking-wider text-zinc-900 dark:text-zinc-100 mb-3">Pagamentos</h3>
                    <div v-if="selectedCustomer.billing_transactions?.length" class="space-y-2 max-h-64 overflow-auto pr-1">
                        <div v-for="item in selectedCustomer.billing_transactions" :key="item.id" class="text-xs border-b border-black/5 dark:border-white/10 pb-2">
                            <p class="font-bold">{{ item.status }} - {{ formatCurrency(item.amount, item.currency) }}</p>
                            <p class="text-slate-500">{{ item.description || '-' }}</p>
                        </div>
                    </div>
                    <p v-else class="text-sm text-slate-500">Sem registros.</p>
                </article>

                <article class="rounded-2xl border border-black/5 dark:border-white/10 p-4">
                    <h3 class="text-sm font-black uppercase tracking-wider text-zinc-900 dark:text-zinc-100 mb-3">{{ $t('common.bookings') }}</h3>
                    <div v-if="selectedCustomer.bookings?.length" class="space-y-2 max-h-64 overflow-auto pr-1">
                        <div v-for="booking in selectedCustomer.bookings" :key="booking.id" class="text-xs border-b border-black/5 dark:border-white/10 pb-2">
                            <p class="font-bold">{{ booking.customer_name }} - {{ booking.status }}</p>
                            <p class="text-slate-500">{{ booking.scheduling_page?.slug }} - {{ formatDate(booking.start_at) }}</p>
                        </div>
                    </div>
                    <p v-else class="text-sm text-slate-500">Sem agendamentos.</p>
                </article>
            </div>
        </section>

        <p v-if="feedback" class="text-sm font-medium text-meetrix-green">{{ feedback }}</p>
        <p v-if="error" class="text-sm font-medium text-red-500">{{ error }}</p>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import axios from '../axios';

const { t } = useI18n();
const overview = ref(null);
const customers = ref([]);
const selectedCustomer = ref(null);
const feedback = ref('');
const error = ref('');

const filters = ref({
    search: '',
    status: 'all',
    subscription: 'all',
});

const kpiCards = computed(() => {
    if (!overview.value?.kpis) return [];

    return [
        { label: 'Clientes', value: overview.value.kpis.clients_total },
        { label: 'Ativos', value: overview.value.kpis.clients_active },
        { label: 'Inativos', value: overview.value.kpis.clients_inactive },
        { label: t('common.teams'), value: overview.value.kpis.teams_total },
        { label: 'Paginas', value: overview.value.kpis.pages_total },
        { label: t('common.bookings'), value: overview.value.kpis.bookings_total },
        { label: 'Pagamentos', value: overview.value.kpis.payments_paid_total },
        { label: 'Receita Mes', value: formatCurrency(overview.value.kpis.revenue_current_month, 'BRL') },
    ];
});

const resetMessages = () => {
    feedback.value = '';
    error.value = '';
};

const fetchOverview = async () => {
    const { data } = await axios.get('/api/super-admin/overview');
    overview.value = data;
};

const fetchCustomers = async () => {
    const { data } = await axios.get('/api/super-admin/customers', {
        params: {
            search: filters.value.search || undefined,
            status: filters.value.status,
            subscription: filters.value.subscription,
        },
    });

    customers.value = data.data || [];
};

const openCustomer = async (customer) => {
    const { data } = await axios.get(`/api/super-admin/customers/${customer.id}`);
    selectedCustomer.value = data;
};

const runAction = async (action) => {
    if (!selectedCustomer.value?.customer?.id) return;

    resetMessages();
    const reason = window.prompt('Motivo da acao (opcional):', '') ?? '';

    try {
        await axios.post(`/api/super-admin/customers/${selectedCustomer.value.customer.id}/actions`, {
            action,
            reason,
        });
        feedback.value = 'Acao aplicada com sucesso.';
        await Promise.all([fetchOverview(), fetchCustomers(), openCustomer(selectedCustomer.value.customer)]);
    } catch (e) {
        error.value = e.response?.data?.message || 'Falha ao aplicar acao.';
    }
};

const refreshAll = async () => {
    resetMessages();
    try {
        await Promise.all([fetchOverview(), fetchCustomers()]);
        feedback.value = 'Painel atualizado.';
    } catch (e) {
        error.value = 'Falha ao atualizar painel.';
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

onMounted(async () => {
    try {
        await refreshAll();
    } catch (e) {
        error.value = 'Nao foi possivel carregar o painel Master Admin.';
    }
});
</script>
