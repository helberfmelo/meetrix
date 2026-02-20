<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h4 class="font-medium text-zinc-900 dark:text-zinc-100">{{ $t('admin.service_types') }}</h4>
            <button @click="addType" class="text-sm text-indigo-600 dark:text-indigo-300 hover:text-indigo-800 dark:hover:text-indigo-200 font-medium">{{ $t('admin.add_new') }}</button>
        </div>

        <div class="space-y-4">
            <div v-for="(type, index) in modelValue.appointmentTypes" :key="index" class="p-4 border border-gray-200 dark:border-white/10 rounded-lg bg-gray-50 dark:bg-zinc-900/60 relative">
                <button
                    @click="removeType(index)"
                    class="absolute top-2 right-2 w-8 h-8 rounded-lg border border-gray-200 dark:border-white/10 text-gray-500 dark:text-slate-300 hover:text-red-500 hover:border-red-200 dark:hover:border-red-500/30 transition-colors"
                >
                    🗑️
                </button>
                
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-slate-300 uppercase">{{ $t('admin.service_name') }}</label>
                        <input 
                            type="text" 
                            v-model="type.name"
                            @input="update"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-white/10 bg-white dark:bg-zinc-950 text-zinc-900 dark:text-white shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-slate-300 uppercase">{{ $t('admin.duration_mins') }}</label>
                            <input 
                                type="number" 
                                v-model.number="type.duration_minutes"
                                @input="update"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-white/10 bg-white dark:bg-zinc-950 text-zinc-900 dark:text-white shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-slate-300 uppercase">{{ $t('admin.price_currency', { currency: type.currency || 'BRL' }) }}</label>
                            <input 
                                type="number" 
                                v-model.number="type.price"
                                @input="update"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-white/10 bg-white dark:bg-zinc-950 text-zinc-900 dark:text-white shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            >
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="modelValue.appointmentTypes.length === 0" class="text-center py-8 border-2 border-dashed border-gray-200 dark:border-white/10 rounded-lg">
                <p class="text-sm text-gray-400 dark:text-slate-300">{{ $t('admin.no_services_defined') }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['update:modelValue', 'update']);

const update = () => {
    emit('update:modelValue', props.modelValue);
    emit('update');
};

const addType = () => {
    const newTypes = [...props.modelValue.appointmentTypes, {
        name: t('admin.new_service_placeholder'),
        duration_minutes: 30,
        price: 0,
        currency: 'BRL',
        is_active: true
    }];
    emit('update:modelValue', { ...props.modelValue, appointmentTypes: newTypes });
    emit('update');
};

const removeType = (index) => {
    const newTypes = props.modelValue.appointmentTypes.filter((_, i) => i !== index);
    emit('update:modelValue', { ...props.modelValue, appointmentTypes: newTypes });
    emit('update');
};
</script>
