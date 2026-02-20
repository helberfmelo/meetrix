<template>
    <div class="flex h-screen bg-gray-50 overflow-hidden">
        <!-- Sidebar (Editor Controls) -->
        <div :class="[isPreviewMode ? 'hidden lg:flex' : 'flex']" class="w-80 bg-white border-r border-gray-200 flex-col z-20 shadow-xl">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-bold text-gray-800">{{ $t('admin.editor') }}</h2>
                <div class="flex items-center space-x-2">
                    <button @click="isPreviewMode = !isPreviewMode" class="lg:hidden text-xs font-bold text-indigo-600">
                        {{ isPreviewMode ? $t('admin.back_to_edit') : $t('admin.preview') }}
                    </button>
                    <router-link to="/dashboard" class="text-sm text-gray-500 hover:text-indigo-600">{{ $t('admin.exit') }}</router-link>
                </div>
            </div>
            
            <!-- Tabs/Sections -->
            <nav class="flex-1 overflow-y-auto p-4 space-y-2">
                <button 
                    v-for="section in translatedSections" 
                    :key="section.id"
                    @click="activeSection = section.id"
                    :class="[
                        'w-full text-left px-4 py-3 rounded-lg flex items-center transition-colors',
                        activeSection === section.id ? 'bg-zinc-950 text-white dark:bg-zinc-800 dark:text-white' : 'text-slate-600 hover:bg-zinc-50 dark:text-slate-400 dark:hover:bg-zinc-900/50'
                    ]"
                >
                    <i :class="[section.icon, 'mr-3 text-lg']"></i>
                    {{ section.label }}
                </button>
            </nav>

            <div class="p-4 border-t border-black/5 dark:border-white/5">
                <button @click="saveChanges" class="w-full bg-zinc-950 text-white dark:bg-meetrix-orange dark:text-zinc-950 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest hover:scale-105 active:scale-95 transition-all shadow-xl flex justify-center items-center gap-2">
                    <i v-if="saving" class="fas fa-circle-notch fa-spin"></i>
                    {{ saving ? $t('admin.saving') : $t('admin.save_changes') }}
                </button>
            </div>
        </div>

        <!-- Main Content (Settings Panel) -->
        <div class="w-96 bg-white border-r border-gray-200 flex flex-col overflow-y-auto z-10 transition-all duration-300" v-if="activeSection">
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-6">{{ currentSection.label }}</h3>
                
                <!-- Dynamic Component for Section -->
                <component 
                    :is="currentSection.component" 
                    v-model="pageConfig" 
                    @update="markDirty"
                />
            </div>
        </div>

        <!-- Live Preview Area -->
        <div class="flex-1 bg-gray-100 flex items-center justify-center p-4 lg:p-8 relative overflow-hidden">
            <div class="absolute top-4 right-4 bg-white/80 backdrop-blur px-3 py-1 rounded-full text-xs font-mono text-gray-500 z-10">
                {{ $t('admin.live_preview') }}
            </div>
            
            <!-- Mock Device/Window -->
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl h-full max-h-[800px] overflow-hidden border border-gray-200 flex flex-col">
                <!-- Fake Browser Bar -->
                <div class="bg-gray-50 border-b border-gray-200 px-4 py-2 flex items-center space-x-2">
                    <div class="flex space-x-1.5">
                        <div class="w-3 h-3 rounded-full bg-red-400"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                        <div class="w-3 h-3 rounded-full bg-green-400"></div>
                    </div>
                    <div class="flex-1 bg-white border border-gray-200 rounded px-2 py-0.5 text-xs text-center text-gray-400">
                        meetrix.io/p/{{ pageConfig.slug }}
                    </div>
                </div>
                
                <!-- Real Iframe Preview -->
                <div class="flex-1 bg-white relative overflow-hidden">
                    <iframe 
                        v-if="pageConfig.slug" 
                        :src="'/p/' + pageConfig.slug + '?preview=1'" 
                        class="w-full h-full border-none"
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { usePageStore } from '../stores/page';
import { useI18n } from 'vue-i18n';

// Placeholder section components
import EditorSectionWhat from '../Components/Editor/EditorSectionWhat.vue';
import EditorSectionWhen from '../Components/Editor/EditorSectionWhen.vue';
import EditorSectionTypes from '../Components/Editor/EditorSectionTypes.vue';
import EditorSectionForm from '../Components/Editor/EditorSectionForm.vue';
import EditorSectionAnalytics from '../Components/Editor/EditorSectionAnalytics.vue';
import EditorSectionBranding from '../Components/Editor/EditorSectionBranding.vue';

const { t } = useI18n();
const route = useRoute();
const pageStore = usePageStore();
const saving = ref(false);
const isPreviewMode = ref(false);

const pageConfig = ref({
    title: '',
    slug: '',
    intro_text: '',
    views: 0,
    config: {},
    availability: [],
    appointmentTypes: []
});

const activeSection = ref('what');

const sections = [
    { id: 'what', labelKey: 'admin.basic_info', icon: 'fas fa-info-circle', component: EditorSectionWhat },
    { id: 'when', labelKey: 'admin.availability', icon: 'fas fa-calendar-alt', component: EditorSectionWhen },
    { id: 'types', labelKey: 'admin.services', icon: 'fas fa-tags', component: EditorSectionTypes },
    { id: 'form', labelKey: 'admin.booking_form', icon: 'fas fa-list-alt', component: EditorSectionForm },
    { id: 'analytics', labelKey: 'admin.analytics', icon: 'fas fa-chart-line', component: EditorSectionAnalytics },
    { id: 'branding', labelKey: 'admin.branding', icon: 'fas fa-palette', component: EditorSectionBranding },
];

const translatedSections = computed(() => sections.map(s => ({
    ...s,
    label: t(s.labelKey)
})));

const currentSection = computed(() => translatedSections.value.find(s => s.id === activeSection.value));

onMounted(async () => {
    if (route.params.slug) {
        try {
            const page = await pageStore.fetchPageBySlug(route.params.slug);
            pageConfig.value = { 
                ...page, 
                config: page.config || {}, 
                availability: page.availability_rules || [],
                appointmentTypes: page.appointment_types || []
            };
        } catch (e) {
            console.error("Failed to load page", e);
        }
    }
});

const saveChanges = async () => {
    if (!pageConfig.value.id) return;
    
    saving.value = true;
    try {
        // 1. Save Basic Info
        await pageStore.updatePage(pageConfig.value.id, {
            title: pageConfig.value.title,
            slug: pageConfig.value.slug,
            intro_text: pageConfig.value.intro_text,
            config: pageConfig.value.config,
            team_id: pageConfig.value.team_id
        });

        // 2. Save Availability (Bulk)
        if (pageConfig.value.availability) {
            await pageStore.updateAvailability(pageConfig.value.id, pageConfig.value.availability);
        }

        // 3. Save Appointment Types (Bulk)
        if (pageConfig.value.appointmentTypes) {
            await pageStore.updateAppointmentTypes(pageConfig.value.id, pageConfig.value.appointmentTypes);
        }

        alert(t('admin.save_success'));
    } catch (error) {
        console.error("Save failed", error);
        alert(t('admin.save_failed'));
    } finally {
        saving.value = false;
    }
};

const markDirty = () => {
    // Logic to enable save button or auto-save
};
</script>
