<template>
    <div class="space-y-8">
        <!-- Logo Upload (Mock) -->
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-widest">{{ $t('admin.logo_url_label') }}</label>
            <input 
                type="text" 
                v-model="config.logo_url" 
                @input="update"
                placeholder="https://example.com/logo.png"
                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all outline-none"
            />
            <p class="mt-2 text-[10px] text-gray-500 font-bold uppercase tracking-widest">{{ $t('admin.logo_pro_tip') }}</p>
        </div>

        <!-- Primary Color -->
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-widest">{{ $t('admin.primary_color_label') }}</label>
            <div class="flex items-center gap-4">
                <input 
                    type="color" 
                    v-model="config.primary_color" 
                    @change="update"
                    class="w-12 h-12 rounded-lg border-0 cursor-pointer p-0"
                />
                <input 
                    type="text" 
                    v-model="config.primary_color" 
                    @input="update"
                    placeholder="#4f46e5"
                    class="flex-1 px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all outline-none font-mono"
                />
            </div>
        </div>

        <!-- Typography -->
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-widest">{{ $t('admin.typography_label') }}</label>
            <select 
                v-model="config.custom_font" 
                @change="update"
                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 transition-all outline-none appearance-none"
            >
                <option value="Inter">{{ $t('admin.inter_standard') }}</option>
                <option value="Outfit">{{ $t('admin.outfit_premium') }}</option>
                <option value="system-ui">{{ $t('admin.system_default') }}</option>
            </select>
        </div>

        <!-- Pro Features (WhatsApp) -->
        <div class="p-6 bg-zinc-950 rounded-3xl text-white relative overflow-hidden">
            <div class="absolute right-0 top-0 bg-meetrix-orange text-[8px] font-black px-2 py-1 uppercase tracking-widest">{{ $t('admin.pro_feature_tag') }}</div>
            <div class="flex items-start gap-4">
                <div class="flex-1">
                    <h4 class="text-sm font-black uppercase tracking-widest mb-1 text-meetrix-orange">{{ $t('admin.coming_soon') }}</h4>
                    <p class="text-xs text-slate-500 font-medium">{{ $t('admin.whatsapp_pro_desc') }}</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" v-model="config.whatsapp_enabled" @change="update" class="sr-only peer">
                    <div class="w-11 h-6 bg-zinc-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-meetrix-orange"></div>
                </label>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({ config: {} })
    }
});

const emit = defineEmits(['update:modelValue', 'update']);

const config = ref({
    logo_url: props.modelValue.config?.logo_url || '',
    primary_color: props.modelValue.config?.primary_color || '#4f46e5',
    custom_font: props.modelValue.config?.custom_font || 'Inter',
    whatsapp_enabled: props.modelValue.config?.whatsapp_enabled || false
});

const update = () => {
    const newVal = { 
        ...props.modelValue, 
        config: { 
            ...props.modelValue.config, 
            ...config.value 
        } 
    };
    emit('update:modelValue', newVal);
    emit('update');
};

watch(() => props.modelValue.config, (newConfig) => {
    if (newConfig) {
        config.value = {
            logo_url: newConfig.logo_url || '',
            primary_color: newConfig.primary_color || '#4f46e5',
            custom_font: newConfig.custom_font || 'Inter',
            whatsapp_enabled: newConfig.whatsapp_enabled || false
        };
    }
}, { deep: true });
</script>
