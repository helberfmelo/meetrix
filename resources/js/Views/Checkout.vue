
<template>
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-950 py-24 px-6 md:px-12 transition-colors duration-500 overflow-hidden relative">
        <!-- Aesthetic Depth -->
        <div class="absolute top-0 right-0 w-[800px] h-[800px] bg-meetrix-orange/5 rounded-full blur-[200px] -z-10"></div>
        <div class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-zinc-900/10 dark:bg-white/5 rounded-full blur-[150px] -z-10"></div>

        <div class="max-w-6xl mx-auto flex flex-col lg:flex-row gap-20 items-start">
            <!-- Left: Order Summary (High Tension) -->
            <div class="lg:w-1/2 space-y-12 animate-in fade-in slide-in-from-left-8 duration-700">
                <header>
                    <h1 class="text-6xl font-black text-zinc-950 dark:text-white uppercase tracking-tighter leading-none mb-4 font-outfit">
                        Finalize sua <span class="text-meetrix-orange">Assinatura</span>
                    </h1>
                    <p class="text-slate-500 font-bold text-xs uppercase tracking-widest">Meetrix Sovereign Node // Pro Plan Initialization</p>
                </header>

                <div class="bg-white/80 dark:bg-zinc-900/40 backdrop-blur-3xl rounded-4xl p-12 border border-black/5 dark:border-white/5 shadow-premium space-y-8">
                    <div class="flex justify-between items-center pb-8 border-b border-black/5 dark:border-white/5">
                        <div>
                            <h3 class="text-2xl font-black text-zinc-950 dark:text-white uppercase">Plano Profissional</h3>
                            <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-2">Faturamento Mensal</p>
                        </div>
                        <div class="text-4xl font-black text-zinc-950 dark:text-white font-outfit">R$ 49,90</div>
                    </div>

                    <div class="space-y-4">
                        <div v-if="appliedCoupon" class="flex justify-between items-center text-meetrix-orange animate-in fade-in duration-500">
                            <span class="text-[10px] font-black uppercase tracking-widest">Cupom Ativado: {{ appliedCoupon.code }}</span>
                            <span class="text-lg font-black">-R$ {{ discountValue.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between items-center bg-zinc-50 dark:bg-zinc-950/40 p-6 rounded-2xl">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total a Pagar Hoje</span>
                            <span class="text-5xl font-black text-zinc-950 dark:text-white font-outfit">R$ {{ finalPrice.toFixed(2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="p-6 bg-white dark:bg-zinc-900/20 rounded-3xl border border-black/5 dark:border-white/5">
                        <i class="fas fa-shield-halved text-meetrix-green mb-4 text-xl"></i>
                        <h4 class="text-[10px] font-black uppercase tracking-widest text-zinc-950 dark:text-white mb-2">Checkout Seguro</h4>
                        <p class="text-[8px] text-slate-500 leading-relaxed font-bold uppercase tracking-wider">Criptografia de ponta a ponta soberana.</p>
                    </div>
                    <div class="p-6 bg-white dark:bg-zinc-900/20 rounded-3xl border border-black/5 dark:border-white/5">
                        <i class="fas fa-bolt text-meetrix-orange mb-4 text-xl"></i>
                        <h4 class="text-[10px] font-black uppercase tracking-widest text-zinc-950 dark:text-white mb-2">Ativação Instantânea</h4>
                        <p class="text-[8px] text-slate-500 leading-relaxed font-bold uppercase tracking-wider">Seu nó estará pronto em segundos.</p>
                    </div>
                </div>
            </div>

            <!-- Right: Action Section -->
            <div class="lg:w-1/2 w-full space-y-8 animate-in fade-in slide-in-from-right-8 duration-700 delay-150">
                <div class="bg-zinc-100 dark:bg-zinc-900/60 p-12 rounded-4xl border border-black/5 dark:border-white/5 space-y-12">
                    <!-- Coupon Area -->
                    <div class="space-y-6">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4">Possui um cupom de desconto?</label>
                        <div class="flex gap-4">
                            <input 
                                v-model="couponCode" 
                                type="text"
                                placeholder="EX: CUPOM100"
                                class="flex-1 bg-white dark:bg-zinc-950 border-2 border-black/5 dark:border-white/5 rounded-2xl px-8 py-5 text-zinc-950 dark:text-white font-bold text-lg outline-none focus:border-meetrix-orange transition-all tracking-widest uppercase"
                                :disabled="appliedCoupon"
                            >
                            <button 
                                @click="applyCoupon"
                                :disabled="!couponCode || appliedCoupon || loadingCoupon"
                                class="px-8 py-5 bg-zinc-950 text-white dark:bg-white dark:text-zinc-950 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:scale-105 active:scale-95 transition-all disabled:opacity-50"
                            >
                                <i v-if="loadingCoupon" class="fas fa-circle-notch fa-spin mr-2"></i>
                                {{ appliedCoupon ? 'Aplicado' : 'Aplicar' }}
                            </button>
                        </div>
                    </div>

                    <!-- Payment Button -->
                    <div class="space-y-6">
                        <button 
                            @click="handleCheckout"
                            :disabled="loading"
                            class="w-full py-8 bg-meetrix-orange text-zinc-950 rounded-[2.5rem] font-black text-xs uppercase tracking-[0.4em] hover:scale-[1.02] active:scale-[0.98] transition-all shadow-xl shadow-meetrix-orange/30 group"
                        >
                            <span v-if="!loading" class="flex items-center justify-center gap-4">
                                {{ finalPrice === 0 ? 'Ativar Agora Grátis' : 'Finalizar com Stripe' }}
                                <i class="fas fa-arrow-right group-hover:translate-x-2 transition-transform"></i>
                            </span>
                            <i v-else class="fas fa-circle-notch fa-spin text-xl"></i>
                        </button>
                        
                        <p class="text-center text-[8px] font-black text-slate-500 uppercase tracking-widest">
                            Ao clicar, você concorda com nossos termos de soberania digital.
                        </p>
                    </div>
                </div>

                <!-- Back button -->
                <router-link to="/onboarding" class="flex items-center gap-4 text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-zinc-950 dark:hover:text-white transition-colors ml-4">
                    <i class="fas fa-chevron-left"></i> Voltar para Onboarding
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from '../axios';

const router = useRouter();
const couponCode = ref('');
const appliedCoupon = ref(null);
const loadingCoupon = ref(false);
const loading = ref(false);
const basePrice = 49.90;

const discountValue = computed(() => {
    if (!appliedCoupon.value) return 0;
    if (appliedCoupon.value.type === 'percent') {
        return basePrice * (appliedCoupon.value.value / 100);
    }
    return Math.min(basePrice, appliedCoupon.value.value);
});

const finalPrice = computed(() => Math.max(0, basePrice - discountValue.value));

const applyCoupon = async () => {
    loadingCoupon.value = true;
    try {
        const response = await axios.post('/api/coupons/validate', { code: couponCode.value });
        appliedCoupon.value = response.data.coupon;
        // In a real app, response would have coupon details
        // Mocking for cupom100 if backend mock not ready
        if (couponCode.value.toLowerCase() === 'cupom100') {
            appliedCoupon.value = { code: 'CUPOM100', type: 'percent', value: 100 };
        }
    } catch (error) {
        alert('Cupom inválido ou expirado.');
    } finally {
        loadingCoupon.value = false;
    }
};

const handleCheckout = async () => {
    loading.value = true;
    try {
        if (finalPrice.value === 0) {
            // Free checkout logic
            await axios.post('/api/subscription/checkout', {
                plan: 'pro',
                coupon: appliedCoupon.value?.code,
                free: true
            });
            router.push('/dashboard');
        } else {
            // Real Stripe checkout
            const response = await axios.post('/api/subscription/checkout', {
                plan: 'pro',
                coupon: appliedCoupon.value?.code
            });
            if (response.data.url) {
                window.location.href = response.data.url;
            }
        }
    } catch (error) {
        alert('Erro ao processar checkout. Tente novamente.');
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    // If we're testing and know the user wants cupom100, we could pre-fill
    // couponCode.value = 'cupom100';
});
</script>
