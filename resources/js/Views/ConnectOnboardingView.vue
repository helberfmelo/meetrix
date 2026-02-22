<template>
    <div class="space-y-8 py-6 animate-in fade-in duration-700">
        <header class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl sm:text-4xl font-black text-zinc-950 dark:text-white tracking-tight uppercase">Onboarding Financeiro</h1>
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.25em] mt-2">Stripe Connect embutido na Meetrix</p>
            </div>
            <div class="px-4 py-2 rounded-xl border border-black/10 dark:border-white/10 text-[10px] font-black uppercase tracking-wider">
                Modo: {{ formatMode(status?.account_mode) }}
            </div>
        </header>

        <section class="rounded-3xl border border-black/5 dark:border-white/10 bg-white dark:bg-zinc-900 p-6 sm:p-8 space-y-6">
            <div v-if="loadingStatus" class="text-sm text-slate-500">
                Carregando status financeiro...
            </div>

            <div v-else-if="statusError" class="space-y-4">
                <p class="text-sm text-red-500">{{ statusError }}</p>
                <button
                    @click="reload"
                    class="rounded-xl px-4 py-3 bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 text-[10px] font-black uppercase tracking-[0.2em]"
                >
                    Tentar novamente
                </button>
            </div>

            <div v-else-if="isSchedulingOnly" class="space-y-4">
                <p class="text-sm text-slate-600 dark:text-slate-300">
                    Esta conta esta em modo apenas agenda. O onboarding financeiro fica disponivel apenas em <strong>scheduling_with_payments</strong>.
                </p>
                <button
                    @click="goToAccount"
                    class="rounded-xl px-4 py-3 bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 text-[10px] font-black uppercase tracking-[0.2em]"
                >
                    Ir para Conta
                </button>
            </div>

            <div v-else-if="!status?.payments_enabled_for_user" class="space-y-4">
                <p class="text-sm text-slate-600 dark:text-slate-300">
                    Pagamentos ainda nao estao habilitados para esta conta no rollout atual.
                </p>
                <button
                    @click="goToAccount"
                    class="rounded-xl px-4 py-3 bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 text-[10px] font-black uppercase tracking-[0.2em]"
                >
                    Voltar para Conta
                </button>
            </div>

            <div v-else-if="status?.receiving_ready" class="space-y-5">
                <div class="rounded-2xl border border-meetrix-green/40 bg-meetrix-green/10 px-4 py-4">
                    <p class="text-sm font-semibold text-meetrix-green">Conta apta para recebimento. Nenhuma acao adicional obrigatoria agora.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <p class="text-slate-600 dark:text-slate-300"><span class="font-bold">Conta conectada:</span> {{ status?.connected_account_summary?.provider_account_id || '-' }}</p>
                    <p class="text-slate-600 dark:text-slate-300"><span class="font-bold">Status:</span> {{ status?.connected_account_summary?.status || '-' }}</p>
                    <p class="text-slate-600 dark:text-slate-300"><span class="font-bold">Charges:</span> {{ boolLabel(status?.connected_account_summary?.charges_enabled) }}</p>
                    <p class="text-slate-600 dark:text-slate-300"><span class="font-bold">Payouts:</span> {{ boolLabel(status?.connected_account_summary?.payouts_enabled) }}</p>
                </div>
                <button
                    @click="goToDashboard"
                    class="rounded-xl px-4 py-3 bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 text-[10px] font-black uppercase tracking-[0.2em]"
                >
                    Ir para Dashboard
                </button>
            </div>

            <div v-else class="space-y-5">
                <div class="space-y-2">
                    <p class="text-sm text-slate-600 dark:text-slate-300">
                        Complete o onboarding financeiro abaixo sem sair do Meetrix.
                    </p>
                    <p class="text-xs text-slate-500">
                        Depois de concluir no componente Stripe, clique em <strong>Atualizar status</strong>.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <p class="text-slate-600 dark:text-slate-300"><span class="font-bold">Onboarding obrigatorio:</span> {{ boolLabel(status?.onboarding_required) }}</p>
                    <p class="text-slate-600 dark:text-slate-300"><span class="font-bold">Recebimento apto:</span> {{ boolLabel(status?.receiving_ready) }}</p>
                </div>

                <div
                    ref="onboardingContainer"
                    class="min-h-[440px] rounded-2xl border border-black/10 dark:border-white/10 bg-zinc-50 dark:bg-zinc-950 p-3"
                ></div>

                <p v-if="embedError" class="text-sm text-red-500">{{ embedError }}</p>

                <div class="flex flex-wrap gap-3">
                    <button
                        @click="refreshStatusAfterOnboarding"
                        class="rounded-xl px-4 py-3 bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 text-[10px] font-black uppercase tracking-[0.2em]"
                    >
                        Atualizar status
                    </button>
                    <button
                        @click="reloadEmbedded"
                        class="rounded-xl px-4 py-3 border border-black/10 dark:border-white/10 text-[10px] font-black uppercase tracking-[0.2em]"
                    >
                        Recarregar componente
                    </button>
                    <button
                        @click="goToAccount"
                        class="rounded-xl px-4 py-3 border border-black/10 dark:border-white/10 text-[10px] font-black uppercase tracking-[0.2em]"
                    >
                        Ir para Conta
                    </button>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from '../axios';

const CONNECT_JS_SOURCE = 'https://connect-js.stripe.com/v1.0/connect.js';

const router = useRouter();
const onboardingContainer = ref(null);
const status = ref(null);
const loadingStatus = ref(true);
const statusError = ref('');
const embedError = ref('');
const pollingHandle = ref(null);

let connectInstance = null;
let onboardingElement = null;

const isSchedulingOnly = computed(() => (status.value?.account_mode || 'scheduling_only') !== 'scheduling_with_payments');

const parseApiError = (fallbackMessage, error) => {
    const payload = error?.response?.data || {};
    const message = payload.message || fallbackMessage;
    return payload.error_code ? `[${payload.error_code}] ${message}` : message;
};

const boolLabel = (value) => (value ? 'Sim' : 'Nao');

const formatMode = (mode) => {
    if (mode === 'scheduling_with_payments') return 'Agenda + cobranca';
    return 'Apenas agenda';
};

const stopPolling = () => {
    if (!pollingHandle.value) return;
    window.clearInterval(pollingHandle.value);
    pollingHandle.value = null;
};

const cleanupEmbeddedComponent = () => {
    if (onboardingElement?.remove) {
        onboardingElement.remove();
    }

    if (onboardingContainer.value) {
        onboardingContainer.value.innerHTML = '';
    }

    onboardingElement = null;
    connectInstance = null;
};

const fetchStatus = async ({ silent = false } = {}) => {
    if (!silent) {
        loadingStatus.value = true;
        statusError.value = '';
    }

    try {
        const { data } = await axios.get('/api/payments/connect/status');
        status.value = data;
    } catch (error) {
        statusError.value = parseApiError('Falha ao carregar status financeiro.', error);
    } finally {
        if (!silent) {
            loadingStatus.value = false;
        }
    }
};

const ensureConnectJs = () => {
    if (typeof window === 'undefined') {
        return Promise.reject(new Error('Ambiente de navegador indisponivel.'));
    }

    if (window.StripeConnect?.init) {
        return Promise.resolve(window.StripeConnect);
    }

    if (window.__meetrixConnectJsPromise) {
        return window.__meetrixConnectJsPromise;
    }

    window.__meetrixConnectJsPromise = new Promise((resolve, reject) => {
        const existingScript = document.querySelector(`script[src="${CONNECT_JS_SOURCE}"]`);
        if (existingScript) {
            existingScript.addEventListener('load', () => resolve(window.StripeConnect), { once: true });
            existingScript.addEventListener('error', () => reject(new Error('Falha ao carregar ConnectJS.')), { once: true });
            return;
        }

        const script = document.createElement('script');
        script.src = CONNECT_JS_SOURCE;
        script.async = true;
        script.onload = () => resolve(window.StripeConnect);
        script.onerror = () => reject(new Error('Falha ao carregar ConnectJS.'));
        document.head.appendChild(script);
    });

    return window.__meetrixConnectJsPromise;
};

const canInitializeEmbedded = () => {
    return Boolean(
        status.value
        && !status.value.receiving_ready
        && !isSchedulingOnly.value
        && status.value.payments_enabled_for_user
    );
};

const initializeEmbeddedComponent = async () => {
    if (!canInitializeEmbedded()) {
        cleanupEmbeddedComponent();
        return;
    }

    const publishableKey = status.value?.connect_publishable_key;
    if (!publishableKey) {
        embedError.value = 'STRIPE_KEY nao configurada para inicializar o ConnectJS.';
        return;
    }

    embedError.value = '';

    try {
        const StripeConnect = await ensureConnectJs();
        if (!StripeConnect?.init) {
            throw new Error('ConnectJS nao disponivel no navegador.');
        }

        cleanupEmbeddedComponent();
        connectInstance = StripeConnect.init({
            publishableKey,
            fetchClientSecret: async () => {
                const { data } = await axios.post('/api/payments/connect/embedded/session');
                if (!data?.client_secret) {
                    throw new Error('Sessao embutida invalida.');
                }
                return data.client_secret;
            },
        });

        onboardingElement = connectInstance.create('account-onboarding');
        if (!onboardingElement) {
            throw new Error('Falha ao renderizar componente account-onboarding.');
        }

        await nextTick();
        onboardingContainer.value?.replaceChildren(onboardingElement);
    } catch (error) {
        embedError.value = parseApiError('Falha ao inicializar onboarding embutido.', error);
    }
};

const refreshStatusAfterOnboarding = async () => {
    await fetchStatus();
    await initializeEmbeddedComponent();
};

const reloadEmbedded = async () => {
    embedError.value = '';
    await initializeEmbeddedComponent();
};

const reload = async () => {
    await fetchStatus();
    await initializeEmbeddedComponent();
};

const goToAccount = () => router.push('/dashboard/account');
const goToDashboard = () => router.push('/dashboard');

const startPolling = () => {
    stopPolling();
    pollingHandle.value = window.setInterval(async () => {
        if (!canInitializeEmbedded()) {
            stopPolling();
            return;
        }

        await fetchStatus({ silent: true });

        if (status.value?.receiving_ready) {
            cleanupEmbeddedComponent();
            stopPolling();
        }
    }, 15000);
};

onMounted(async () => {
    await fetchStatus();
    await initializeEmbeddedComponent();

    if (canInitializeEmbedded()) {
        startPolling();
    }
});

onBeforeUnmount(() => {
    stopPolling();
    cleanupEmbeddedComponent();
});
</script>
