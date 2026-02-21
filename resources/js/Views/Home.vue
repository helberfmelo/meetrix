
<template>

        <!-- Hero Section: Asymmetric Tension -->
        <section class="relative pt-24 sm:pt-32 lg:pt-64 pb-20 sm:pb-32 px-4 sm:px-6 lg:px-24 min-h-[70vh] sm:min-h-[80vh] flex items-center overflow-hidden">
            <div class="max-w-screen-2xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-24 items-center w-full">
                <div class="lg:col-span-8 animate-in fade-in slide-in-from-left-8 duration-1000">
                    <h1 class="text-[clamp(2rem,8vw,5.5rem)] leading-[0.9] sm:leading-[0.85] font-black text-zinc-950 dark:text-white font-outfit tracking-tighter uppercase mb-6 sm:mb-8 max-w-4xl">
                        {{ $t('home.hero_title_1') }}<br/>
                        <span class="text-meetrix-orange">{{ $t('home.hero_title_2') }}</span>
                    </h1>
                </div>
                <div class="lg:col-span-4 flex flex-col items-start gap-8 animate-in fade-in slide-in-from-bottom-8 duration-1000 delay-300">
                    <p class="text-lg sm:text-xl md:text-2xl text-slate-500 dark:text-slate-400 max-w-md leading-tight font-medium">
                        {{ $t('home.hero_subtitle') }}
                    </p>
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 w-full sm:w-auto">
                        <router-link
                            to="/login"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 rounded-2xl border border-black/10 dark:border-white/20 bg-white/80 dark:bg-zinc-900/80 text-[10px] sm:text-xs font-black uppercase tracking-[0.2em] text-zinc-700 dark:text-slate-200 hover:border-meetrix-orange hover:text-zinc-950 dark:hover:text-white transition-all shadow-lg"
                        >
                            {{ $t('login.title') }}
                        </router-link>
                        
                        <router-link
                            to="/onboarding"
                            @click="trackFunnel('cta_main_click', { placement: 'hero' })"
                            class="w-full sm:w-auto group inline-flex items-center justify-center gap-3 sm:gap-4 px-6 py-3 rounded-2xl bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 hover:bg-meetrix-orange hover:text-zinc-950 transition-all shadow-xl"
                        >
                            <span class="w-8 h-8 sm:w-9 sm:h-9 rounded-full border border-white/30 dark:border-zinc-950/20 flex items-center justify-center group-hover:border-zinc-950/30 group-hover:bg-zinc-950/10 transition-all duration-500">
                                <i class="fas fa-chevron-right text-white dark:text-zinc-950 group-hover:text-zinc-950 transition-colors"></i>
                            </span>
                            <span class="text-[10px] sm:text-xs font-black uppercase tracking-[0.2em]">{{ $t('home.get_started') }}</span>
                        </router-link>
                    </div>
                </div>
            </div>

            <!-- Background Aesthetic: Raw depth -->
            <div class="absolute -z-10 top-0 right-0 w-2/3 h-full opacity-20 pointer-events-none">
                <div class="absolute top-1/4 right-0 w-[800px] h-[800px] bg-meetrix-orange/20 rounded-full blur-[200px] animate-pulse"></div>
                <div class="absolute bottom-1/4 -left-1/4 w-[600px] h-[600px] bg-meetrix-green/10 rounded-full blur-[150px]"></div>
            </div>
            
            <!-- Grid noise -->
            <div class="absolute inset-0 -z-20 opacity-[0.03] pointer-events-none" 
                 style="background-image: url('https://www.transparenttextures.com/patterns/graphy-dark.png');"></div>
        </section>

        <!-- Dynamic Content: Feature Fragments -->
        <section class="py-20 sm:py-32 px-4 sm:px-6 lg:px-24 border-t border-black/5 dark:border-white/5">
            <div class="max-w-screen-2xl mx-auto">
                <!-- Full Width Title -->
                <div class="mb-14 sm:mb-24 text-center max-w-4xl mx-auto">
                    <h2 class="text-[clamp(2rem,7vw,4.5rem)] font-black text-zinc-950 dark:text-white uppercase tracking-tighter mb-6 sm:mb-8 leading-[0.95] sm:leading-[0.9]">{{ $t('home.features_title') }}</h2>
                    <p class="text-base sm:text-xl text-slate-500 font-medium leading-relaxed">{{ $t('home.features_subtitle') }}</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-12">
                    <div v-for="i in 3" :key="i" class="reveal-on-scroll group h-full">
                        <div class="h-full bg-zinc-50 dark:bg-zinc-900/40 p-6 sm:p-12 rounded-[2rem] sm:rounded-[3.5rem] border border-black/5 dark:border-white/5 hover:border-meetrix-orange/30 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-all duration-700 flex flex-col justify-between translate-y-8 opacity-0 group-[.in-view]:translate-y-0 group-[.in-view]:opacity-100"
                            :style="{ transitionDelay: `${i * 150}ms` }">
                            <div>
                                <span class="text-meetrix-orange font-black text-[9px] sm:text-[10px] uppercase tracking-[0.3em] sm:tracking-[0.4em] mb-8 sm:mb-12 block">
                                    <i class="fas fa-microchip mr-2"></i> 0{{ i }} // {{ $t('home.feature_label') }}
                                </span>
                                <h3 class="text-2xl sm:text-3xl font-black text-zinc-950 dark:text-white mb-4 sm:mb-6 uppercase font-outfit leading-none">{{ $t(`home.feature_${i}_title`) }}</h3>
                                <p class="text-base sm:text-lg text-slate-500 leading-snug font-medium mb-8 sm:mb-12">{{ $t(`home.feature_${i}_desc`) }}</p>
                            </div>
                            <!-- Small aesthetic marker instead of big mystery box -->
                            <div class="w-8 h-8 rounded-full border border-black/10 dark:border-white/20 flex items-center justify-center opacity-40 group-hover:opacity-100 group-hover:border-meetrix-orange transition-all duration-500">
                                <div class="w-2 h-2 rounded-full bg-zinc-400 dark:bg-zinc-600 group-hover:bg-meetrix-orange transition-all"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Organization -->
        <section class="py-16 sm:py-20 px-4 sm:px-6 lg:px-24">
            <div class="max-w-screen-2xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-8">
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-meetrix-orange mb-3">ORGANIZACAO</p>
                    <h2 class="text-[clamp(1.7rem,5vw,3rem)] font-black text-zinc-950 dark:text-white uppercase tracking-tighter leading-[0.9]">
                        Organize atendimentos, confirme presenca e reduza faltas.
                    </h2>
                    <p class="mt-4 text-base sm:text-lg text-slate-500 dark:text-slate-300 max-w-3xl">
                        Agenda online, lembretes automatizados e fluxo de atendimento mais previsivel para operacoes presenciais e remotas.
                    </p>
                </div>
                <div class="lg:col-span-4">
                    <router-link to="/onboarding?mode=scheduling_only" @click="trackFunnel('path_selected', { path: 'scheduling_only', placement: 'organization' })" class="inline-flex items-center justify-center w-full px-8 py-4 rounded-2xl bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 text-[10px] sm:text-xs font-black uppercase tracking-[0.2em] hover:bg-meetrix-orange hover:text-zinc-950 transition-colors">
                        Organizar minha agenda
                    </router-link>
                </div>
            </div>
        </section>

        <!-- Optional Payments -->
        <section class="py-16 sm:py-20 px-4 sm:px-6 lg:px-24 bg-zinc-950 text-white dark:bg-zinc-900">
            <div class="max-w-screen-2xl mx-auto">
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-meetrix-orange mb-3">COBRANCA OPCIONAL</p>
                <h2 class="text-[clamp(1.7rem,5vw,3rem)] font-black uppercase tracking-tighter leading-[0.9]">
                    Ative cobranca integrada quando fizer sentido no seu modelo.
                </h2>
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm sm:text-base font-semibold text-slate-200">
                    <div class="rounded-2xl bg-white/5 border border-white/10 px-4 py-4">Pagamento antecipado</div>
                    <div class="rounded-2xl bg-white/5 border border-white/10 px-4 py-4">Sinal para garantir horario</div>
                    <div class="rounded-2xl bg-white/5 border border-white/10 px-4 py-4">Pre-autorizacao</div>
                    <div class="rounded-2xl bg-white/5 border border-white/10 px-4 py-4">Recorrencia para pacotes</div>
                </div>
                <router-link to="/onboarding?mode=scheduling_with_payments" @click="trackFunnel('path_selected', { path: 'scheduling_with_payments', placement: 'payments' })" class="mt-7 inline-flex items-center justify-center px-8 py-4 rounded-2xl bg-meetrix-orange text-zinc-950 text-[10px] sm:text-xs font-black uppercase tracking-[0.22em] hover:bg-white transition-colors">
                        {{ $t('home.activate_billing') }}
                </router-link>
            </div>
        </section>

        <!-- Financial Gain -->
        <section class="py-16 sm:py-20 px-4 sm:px-6 lg:px-24">
            <div class="max-w-screen-2xl mx-auto">
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-meetrix-orange mb-3">GANHO FINANCEIRO</p>
                <h2 class="text-[clamp(1.7rem,5vw,3rem)] font-black text-zinc-950 dark:text-white uppercase tracking-tighter leading-[0.9]">
                    Menos faltas, menos inadimplencia, mais previsibilidade.
                </h2>
                <p class="mt-4 text-base sm:text-lg text-slate-500 dark:text-slate-300 max-w-3xl">
                    Evitar uma falta recorrente por mes pode pagar o custo da plataforma. O ganho vem da disciplina operacional e da confirmacao automatica.
                </p>
            </div>
        </section>

        <!-- Pricing: Extreme Contrast -->
        <section class="py-20 sm:py-32 px-4 sm:px-6 lg:px-24 bg-zinc-50 dark:bg-zinc-950 text-zinc-950 dark:text-white transition-colors duration-500">
            <div class="max-w-screen-2xl mx-auto flex flex-col lg:flex-row gap-24 items-start">
                <div class="lg:w-1/2 lg:sticky lg:top-32 pb-12">
                    <h2 class="text-[clamp(2rem,10vw,6.5rem)] leading-[0.9] sm:leading-[0.85] font-black uppercase tracking-tighter mb-8 sm:mb-12">
                        {{ $t('home.pricing_title_1') }} <span class="text-slate-400 dark:text-slate-600">{{ $t('home.pricing_title_2') }}</span><br/>
                        {{ $t('home.pricing_title_3') }} <span class="text-meetrix-orange">{{ $t('home.pricing_title_4') }}</span>
                    </h2>
                    
                    <!-- Billing Toggle -->
                    <div class="flex flex-wrap items-center gap-2 sm:gap-6 mt-8 sm:mt-12 bg-white dark:bg-zinc-900 p-2 rounded-2xl sm:rounded-full border border-black/5 dark:border-white/5 w-fit shadow-premium">
                        <button @click="billingCycle = 'monthly'" 
                            class="px-4 sm:px-8 py-2.5 sm:py-3 rounded-full text-[9px] sm:text-[10px] font-black uppercase tracking-widest transition-all"
                            :class="billingCycle === 'monthly' ? 'bg-zinc-900 text-white dark:bg-zinc-700/50 dark:text-white/90' : 'text-slate-400 hover:text-zinc-950 dark:hover:text-white'">
                            {{ $t('home.billing_monthly') }}
                        </button>
                        <button @click="billingCycle = 'annual'" 
                            class="px-4 sm:px-8 py-2.5 sm:py-3 rounded-full text-[9px] sm:text-[10px] font-black uppercase tracking-widest transition-all relative"
                            :class="billingCycle === 'annual' ? 'bg-zinc-900 text-white dark:bg-zinc-700/50 dark:text-white/90' : 'text-slate-400 hover:text-zinc-950 dark:hover:text-white'">
                            {{ $t('home.billing_annual') }}
                            <span class="absolute -top-4 -right-4 bg-meetrix-orange text-zinc-950 text-[8px] px-2 py-0.5 rounded-full animate-bounce font-black">-20%</span>
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-2 mt-6">
                        <span class="px-3 py-1 rounded-full bg-zinc-950 text-white dark:bg-white dark:text-zinc-950 text-[10px] font-black uppercase tracking-[0.2em]">
                            {{ regionLabel }}
                        </span>
                        <span class="px-3 py-1 rounded-full border border-black/10 dark:border-white/20 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 dark:text-slate-300">
                            {{ regionPricing.currency }}
                        </span>
                    </div>
                </div>
                <div class="w-full lg:w-1/2 grid grid-cols-1 sm:grid-cols-2 gap-px bg-zinc-200 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 overflow-hidden rounded-5xl shadow-2xl">
                    <!-- Free -->
                    <div class="bg-white dark:bg-zinc-900 p-8 sm:p-12 flex flex-col h-full justify-between">
                        <div>
                            <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-8">{{ $t('home.plan_free') }}</h3>
                            <div class="text-4xl sm:text-6xl font-black mb-2 dark:text-white">{{ formatCurrency(schedulePrice) }}</div>
                            <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-8">
                                {{ billingCycle === 'monthly' ? $t('home.per_month') : $t('home.per_year_incentive') }}
                            </div>
                            <ul class="space-y-4 mb-20">
                                <li v-for="feat in $tm('home.free_features')" :key="feat" class="flex items-center text-sm font-bold text-slate-600 dark:text-slate-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-zinc-300 dark:bg-zinc-700 mr-3"></span> {{ $rt(feat) }}
                                </li>
                            </ul>
                        </div>
                        <router-link to="/onboarding" class="w-full py-4 text-center border-2 border-zinc-950 dark:border-white text-zinc-950 dark:text-white font-black text-xs uppercase tracking-widest hover:bg-zinc-950 hover:text-white dark:hover:bg-white dark:hover:text-zinc-950 active:scale-95 transition-all">
                            {{ $t('home.get_started') }}
                        </router-link>
                    </div>
                    <!-- Pro -->
                    <div class="bg-zinc-100 dark:bg-zinc-800 text-zinc-950 dark:text-white p-8 sm:p-12 flex flex-col h-full justify-between relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-tr from-meetrix-orange/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-1000"></div>
                        <div class="relative z-10">
                            <div class="flex justify-between items-center mb-8">
                                <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">{{ $t('home.plan_pro') }}</h3>
                                <span class="bg-meetrix-orange text-zinc-950 text-[10px] px-3 py-1 font-black rounded-full uppercase shadow-lg shadow-meetrix-orange/20">{{ $t('home.plan_pro_tag') }}</span>
                            </div>
                            <div class="text-4xl sm:text-6xl font-black mb-2 text-meetrix-orange">{{ formatCurrency(paymentsProPrice) }}</div>
                            <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">
                                {{ billingCycle === 'monthly' ? $t('home.per_month') : $t('home.per_year_incentive') }}
                            </div>
                            <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-8">
                                Fee Pro: {{ formatPercent(regionPricing.payments.feePro) }} | Premium: {{ formatCurrency(paymentsPremiumPrice) }} / {{ formatPercent(regionPricing.payments.feePremium) }}
                            </div>
                            <ul class="space-y-4 mb-20">
                                <li v-for="feat in $tm('home.pro_features')" :key="feat" class="flex items-center text-sm font-bold text-slate-600 dark:text-slate-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-meetrix-orange mr-3"></span> {{ $rt(feat) }}
                                </li>
                            </ul>
                        </div>
                        <router-link to="/onboarding" class="relative z-10 w-full py-4 text-center bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 font-black text-xs uppercase tracking-widest hover:scale-[1.02] active:scale-[0.98] transition-all shadow-xl shadow-black/10">
                            {{ $t('home.get_trial') }}
                        </router-link>
                    </div>
                </div>
            </div>
            <div class="max-w-screen-2xl mx-auto mt-4 text-xs text-slate-500 dark:text-slate-300">
                {{ pricingNotice }}
            </div>
        </section>

        <!-- Integrations -->
        <section class="py-20 sm:py-24 px-4 sm:px-6 lg:px-24 border-t border-black/5 dark:border-white/5">
            <div class="max-w-screen-2xl mx-auto">
                <div class="max-w-4xl mb-10">
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-meetrix-orange mb-4">INTEGRACOES</p>
                    <h2 class="text-[clamp(1.8rem,6vw,3.6rem)] font-black text-zinc-950 dark:text-white uppercase tracking-tighter leading-[0.92]">
                        Conecte agenda, reuniao e operacao no mesmo fluxo.
                    </h2>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="rounded-2xl border border-black/5 dark:border-white/10 bg-zinc-100 dark:bg-zinc-900/50 px-5 py-4 text-center text-sm font-black uppercase tracking-[0.18em]">Google Calendar</div>
                    <div class="rounded-2xl border border-black/5 dark:border-white/10 bg-zinc-100 dark:bg-zinc-900/50 px-5 py-4 text-center text-sm font-black uppercase tracking-[0.18em]">Outlook</div>
                    <div class="rounded-2xl border border-black/5 dark:border-white/10 bg-zinc-100 dark:bg-zinc-900/50 px-5 py-4 text-center text-sm font-black uppercase tracking-[0.18em]">Zoom</div>
                    <div class="rounded-2xl border border-black/5 dark:border-white/10 bg-zinc-100 dark:bg-zinc-900/50 px-5 py-4 text-center text-sm font-black uppercase tracking-[0.18em]">Google Meet</div>
                </div>
            </div>
        </section>

        <!-- Transition -->
        <section class="py-20 sm:py-24 px-4 sm:px-6 lg:px-24">
            <div class="max-w-screen-2xl mx-auto rounded-4xl bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 p-8 sm:p-12 border border-black/5 dark:border-white/10">
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-meetrix-orange mb-4">TRANSICAO</p>
                <h2 class="text-[clamp(1.8rem,6vw,3.4rem)] font-black uppercase tracking-tighter leading-[0.9]">
                    Ja usa outro sistema de agendamento?
                </h2>
                <p class="mt-4 max-w-3xl text-base sm:text-lg opacity-80">
                    Migre gradualmente, mantenha sua operacao e evolua de agenda para agenda com cobranca sem reiniciar sua estrutura.
                </p>
                <router-link to="/onboarding" @click="trackFunnel('cta_main_click', { placement: 'transition' })" class="mt-7 inline-flex items-center justify-center px-7 py-3 rounded-2xl bg-meetrix-orange text-zinc-950 text-[10px] sm:text-xs font-black uppercase tracking-[0.2em] hover:bg-white transition-colors">
                    Importar minha agenda
                </router-link>
            </div>
        </section>

        <!-- Comparison -->
        <section class="py-20 sm:py-24 px-4 sm:px-6 lg:px-24 border-t border-black/5 dark:border-white/5">
            <div class="max-w-screen-2xl mx-auto">
                <h2 class="text-[clamp(1.8rem,6vw,3.6rem)] font-black text-zinc-950 dark:text-white uppercase tracking-tighter leading-[0.9]">
                    Comparacao direta: agendar apenas ou operar com receita.
                </h2>
                <div class="mt-10 grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <article class="rounded-3xl border border-black/5 dark:border-white/10 bg-zinc-100 dark:bg-zinc-900/50 p-6">
                        <h3 class="text-xs font-black uppercase tracking-[0.3em] text-slate-500 mb-4">OUTROS FLUXOS</h3>
                        <ul class="space-y-3 text-sm sm:text-base font-semibold text-slate-600 dark:text-slate-300">
                            <li class="flex items-start gap-3"><i class="fas fa-minus mt-1 text-slate-400 text-xs"></i><span>Apenas agendam</span></li>
                            <li class="flex items-start gap-3"><i class="fas fa-minus mt-1 text-slate-400 text-xs"></i><span>Cobranca separada do agendamento</span></li>
                            <li class="flex items-start gap-3"><i class="fas fa-minus mt-1 text-slate-400 text-xs"></i><span>Confirmacao financeira fora do fluxo</span></li>
                        </ul>
                    </article>
                    <article class="rounded-3xl border border-meetrix-orange/40 bg-white dark:bg-zinc-950 p-6">
                        <h3 class="text-xs font-black uppercase tracking-[0.3em] text-meetrix-orange mb-4">MEETRIX</h3>
                        <ul class="space-y-3 text-sm sm:text-base font-semibold text-zinc-700 dark:text-slate-200">
                            <li class="flex items-start gap-3"><i class="fas fa-check mt-1 text-meetrix-orange text-xs"></i><span>Agenda inteligente com confirmacao</span></li>
                            <li class="flex items-start gap-3"><i class="fas fa-check mt-1 text-meetrix-orange text-xs"></i><span>Cobranca no agendamento quando necessario</span></li>
                            <li class="flex items-start gap-3"><i class="fas fa-check mt-1 text-meetrix-orange text-xs"></i><span>Progressao de valor por etapa do atendimento</span></li>
                        </ul>
                    </article>
                </div>
            </div>
        </section>

        <!-- Financial Advantage -->
        <section class="py-20 sm:py-24 px-4 sm:px-6 lg:px-24">
            <div class="max-w-screen-2xl mx-auto">
                <h2 class="text-[clamp(1.8rem,6vw,3.5rem)] font-black text-zinc-950 dark:text-white uppercase tracking-tighter leading-[0.9]">
                    Use como agenda ou aumente receita cobrando no agendamento.
                </h2>
                <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="rounded-2xl bg-zinc-100 dark:bg-zinc-900/50 border border-black/5 dark:border-white/10 p-5 text-sm font-bold">Mensalidade competitiva</div>
                    <div class="rounded-2xl bg-zinc-100 dark:bg-zinc-900/50 border border-black/5 dark:border-white/10 p-5 text-sm font-bold">Menos faltas e cancelamentos</div>
                    <div class="rounded-2xl bg-zinc-100 dark:bg-zinc-900/50 border border-black/5 dark:border-white/10 p-5 text-sm font-bold">Fluxo financeiro previsivel por atendimento</div>
                </div>
            </div>
        </section>

        <!-- Migration -->
        <section class="py-16 sm:py-20 px-4 sm:px-6 lg:px-24">
            <div class="max-w-screen-2xl mx-auto rounded-4xl border border-black/5 dark:border-white/10 bg-zinc-100 dark:bg-zinc-900/50 p-8 sm:p-10">
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-meetrix-orange mb-3">MIGRACAO</p>
                <h2 class="text-[clamp(1.7rem,5vw,3rem)] font-black text-zinc-950 dark:text-white uppercase tracking-tighter leading-[0.9]">
                    Migre sua agenda em minutos.
                </h2>
                <p class="mt-4 text-base sm:text-lg text-slate-500 dark:text-slate-300 max-w-3xl">
                    Traga seus fluxos atuais, preserve seu ritmo de atendimento e evolua sem interromper operacao.
                </p>
                <router-link to="/onboarding" @click="trackFunnel('cta_main_click', { placement: 'migration' })" class="mt-7 inline-flex items-center justify-center px-8 py-4 rounded-2xl bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 text-[10px] sm:text-xs font-black uppercase tracking-[0.2em] hover:bg-meetrix-orange hover:text-zinc-950 transition-colors">
                    Importar minha agenda
                </router-link>
            </div>
        </section>

        <!-- Proof -->
        <section class="py-20 sm:py-24 px-4 sm:px-6 lg:px-24 border-y border-black/5 dark:border-white/5">
            <div class="max-w-screen-2xl mx-auto">
                <h2 class="text-[clamp(1.8rem,6vw,3.5rem)] font-black text-zinc-950 dark:text-white uppercase tracking-tighter leading-[0.9]">
                    Prova social em resultado operacional.
                </h2>
                <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-5">
                    <article class="rounded-3xl bg-zinc-100 dark:bg-zinc-900/50 border border-black/5 dark:border-white/10 p-6">
                        <p class="text-4xl font-black text-zinc-950 dark:text-white">+12k</p>
                        <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-300">atendimentos organizados</p>
                    </article>
                    <article class="rounded-3xl bg-zinc-100 dark:bg-zinc-900/50 border border-black/5 dark:border-white/10 p-6">
                        <p class="text-4xl font-black text-zinc-950 dark:text-white">-37%</p>
                        <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-300">faltas em operacoes recorrentes</p>
                    </article>
                    <article class="rounded-3xl bg-zinc-100 dark:bg-zinc-900/50 border border-black/5 dark:border-white/10 p-6">
                        <p class="text-4xl font-black text-zinc-950 dark:text-white">+R$ 2.4M</p>
                        <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-300">volume processado em agendas pagas</p>
                    </article>
                </div>
            </div>
        </section>

        <!-- Final CTA -->
        <section class="py-20 sm:py-24 px-4 sm:px-6 lg:px-24 bg-zinc-950 text-white dark:bg-zinc-900">
            <div class="max-w-screen-2xl mx-auto text-center">
                <h2 class="text-[clamp(1.9rem,6vw,4rem)] font-black uppercase tracking-tighter leading-[0.9]">
                    Comece organizando sua agenda e ative cobrancas quando quiser.
                </h2>
                <p class="mt-5 text-base sm:text-lg text-slate-300 max-w-3xl mx-auto">
                    Sem forcar decisao inicial. Primeiro, eficiencia operacional. Depois, monetizacao progressiva.
                </p>
                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4">
                    <router-link to="/onboarding" @click="trackFunnel('cta_main_click', { placement: 'final' })" class="inline-flex items-center justify-center px-8 py-4 rounded-2xl bg-meetrix-orange text-zinc-950 text-[10px] sm:text-xs font-black uppercase tracking-[0.22em] hover:bg-white transition-colors">
                        Comecar gratuitamente
                    </router-link>
                    <router-link to="/onboarding?mode=scheduling_with_payments" @click="trackFunnel('path_selected', { path: 'scheduling_with_payments', placement: 'final' })" class="inline-flex items-center justify-center px-8 py-4 rounded-2xl border border-white/20 text-white text-[10px] sm:text-xs font-black uppercase tracking-[0.22em] hover:bg-white hover:text-zinc-950 transition-colors">
                        {{ $t('home.activate_billing') }}
                    </router-link>
                </div>
            </div>
        </section>

</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from '../axios';

const billingCycle = ref('monthly');
const route = useRoute();
const catalog = ref(null);

const fallbackGeoPricing = {
    BR: {
        currency: 'BRL',
        schedule: { starter: 29, annualStarter: 23 },
        payments: { pro: 39, annualPro: 31, premium: 79, annualPremium: 63, feePro: 2.5, feePremium: 1.5 },
    },
    USD: {
        currency: 'USD',
        schedule: { starter: 7, annualStarter: 6 },
        payments: { pro: 9, annualPro: 7, premium: 19, annualPremium: 15, feePro: 1.25, feePremium: 0.75 },
    },
    EUR: {
        currency: 'EUR',
        schedule: { starter: 7, annualStarter: 6 },
        payments: { pro: 9, annualPro: 7, premium: 19, annualPremium: 15, feePro: 1.25, feePremium: 0.75 },
    },
};

const activeLocale = computed(() => {
    const rawLocale = Array.isArray(route.params.locale) ? route.params.locale[0] : route.params.locale;
    return String(rawLocale || 'en').replace('_', '-');
});

const inferredRegionCode = computed(() => {
    const queryRegion = String(route.query.region || '').toUpperCase();
    if (queryRegion === 'BR') return 'BR';
    if (queryRegion === 'US' || queryRegion === 'USD' || queryRegion === 'NA') return 'USD';
    if (queryRegion === 'EU' || queryRegion === 'EUR') return 'EUR';

    const locale = activeLocale.value.toLowerCase();
    if (locale === 'pt-br') return 'BR';
    if (locale.startsWith('en')) return 'USD';
    return 'EUR';
});

const regionCode = computed(() => String(catalog.value?.region || inferredRegionCode.value).toUpperCase());

const regionPricing = computed(() => {
    if (!catalog.value?.plans) {
        return fallbackGeoPricing[regionCode.value] || fallbackGeoPricing.EUR;
    }

    const schedule = catalog.value.plans.scheduling_only || {};
    const payments = catalog.value.plans.scheduling_with_payments || {};
    const fallback = fallbackGeoPricing[regionCode.value] || fallbackGeoPricing.EUR;

    return {
        currency: catalog.value.currency || fallback.currency,
        schedule: {
            starter: Number(schedule.monthly_price ?? fallback.schedule.starter),
            annualStarter: Number(schedule.annual_price ?? fallback.schedule.annualStarter),
        },
        payments: {
            pro: Number(payments.monthly_price ?? fallback.payments.pro),
            annualPro: Number(payments.annual_price ?? fallback.payments.annualPro),
            premium: Number(payments.premium_price ?? fallback.payments.premium),
            annualPremium: Number(
                payments.premium_price ? Math.round(Number(payments.premium_price) * 0.8) : fallback.payments.annualPremium
            ),
            feePro: Number(payments.platform_fee_percent ?? fallback.payments.feePro),
            feePremium: Number(payments.premium_fee_percent ?? fallback.payments.feePremium),
        },
    };
});

const schedulePrice = computed(() => {
    return billingCycle.value === 'monthly'
        ? regionPricing.value.schedule.starter
        : regionPricing.value.schedule.annualStarter;
});

const paymentsProPrice = computed(() => {
    return billingCycle.value === 'monthly'
        ? regionPricing.value.payments.pro
        : regionPricing.value.payments.annualPro;
});

const paymentsPremiumPrice = computed(() => {
    return billingCycle.value === 'monthly'
        ? regionPricing.value.payments.premium
        : regionPricing.value.payments.annualPremium;
});

const regionLabel = computed(() => {
    if (regionCode.value === 'BR') return 'Brasil';
    if (regionCode.value === 'USD') return 'US/CA/AU';
    return 'Europa e demais';
});

const pricingNotice = computed(() => {
    if (regionCode.value === 'BR') {
        return 'Tabela BRL ativa. Modo Agenda e Agenda + Cobranca exibidos para operacao no Brasil.';
    }
    if (regionCode.value === 'USD') {
        return 'Tabela USD ativa para US/CA/AU com fee reduzida e competitividade internacional.';
    }
    return 'Tabela EUR ativa para Europa e demais regioes com precificacao local.';
});

const formatCurrency = (value) => new Intl.NumberFormat(activeLocale.value, {
    style: 'currency',
    currency: regionPricing.value.currency,
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
}).format(value);

const formatPercent = (value) => `${new Intl.NumberFormat(activeLocale.value, {
    minimumFractionDigits: value % 1 === 0 ? 0 : 2,
    maximumFractionDigits: 2,
}).format(value)}%`;

const trackFunnel = (event, payload = {}) => {
    if (typeof window === 'undefined') return;

    const funnelEvent = {
        event,
        source: 'home',
        locale: activeLocale.value,
        region: regionCode.value,
        timestamp: new Date().toISOString(),
        ...payload,
    };

    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push(funnelEvent);

    window.dispatchEvent(new CustomEvent('meetrix:funnel', { detail: funnelEvent }));
};

const fetchPricingCatalog = async () => {
    try {
        const countryCode = String(route.query.country_code || route.query.country || '').toUpperCase();
        const { data } = await axios.get('/api/pricing/catalog', {
            params: {
                country_code: countryCode || undefined,
                locale: activeLocale.value,
            },
        });

        catalog.value = data;
    } catch (error) {
        console.warn('Pricing catalog fallback enabled:', error?.message || error);
        catalog.value = null;
    }
};

onMounted(() => {
    fetchPricingCatalog();

    // Basic Intersection Observer for scroll reveals
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('in-view');
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.reveal-on-scroll').forEach(el => observer.observe(el));

    // Emergency Reveal: If observer fails or user doesn't scroll immediately
    setTimeout(() => {
        document.querySelectorAll('.reveal-on-scroll').forEach(el => {
            el.classList.add('in-view');
        });
    }, 2000);
});
</script>

<style scoped>
/* Custom Animate-in utilities if not present in tailwind */
.animate-in {
    animation: animate-in 1s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes animate-in {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in { opacity: 0; animation-name: fade-in; }
.slide-in-from-left-8 { --tw-enter-translate-x: -2rem; }
.slide-in-from-bottom-8 { --tw-enter-translate-y: 2rem; }

@keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }
</style>
