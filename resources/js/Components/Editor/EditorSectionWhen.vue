<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h4 class="font-medium text-gray-900">{{ $t('admin.weekly_availability') }}</h4>
            <button @click="addRule" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">{{ $t('admin.add_rule') }}</button>
        </div>

        <div class="space-y-4">
            <div v-for="(rule, index) in rules" :key="index" class="p-4 border border-gray-200 rounded-lg bg-gray-50 relative group">
                <button @click="removeRule(index)" class="absolute top-2 right-2 text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                    🗑️
                </button>

                <div class="space-y-4">
                    <!-- Days Picker -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">{{ $t('admin.days_of_week') }}</label>
                        <div class="flex flex-wrap gap-2">
                            <button 
                                v-for="day in translatedDays" 
                                :key="day.id"
                                @click="toggleDay(index, day.id)"
                                :class="[
                                    'px-3 py-1 text-xs rounded-full border transition-colors',
                                    rule.days_of_week.includes(day.id) 
                                        ? 'bg-indigo-600 text-white border-indigo-600' 
                                        : 'bg-white text-gray-600 border-gray-200 hover:border-indigo-300'
                                ]"
                            >
                                {{ day.label.substring(0, 3) }}
                            </button>
                        </div>
                    </div>

                    <!-- Times -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase">{{ $t('admin.start_time') }}</label>
                            <input 
                                type="time" 
                                v-model="rule.start_time"
                                @change="update"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase">{{ $t('admin.end_time') }}</label>
                            <input 
                                type="time" 
                                v-model="rule.end_time"
                                @change="update"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            >
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="rules.length === 0" class="text-center py-8 border-2 border-dashed border-gray-200 rounded-lg">
                <p class="text-sm text-gray-400">{{ $t('admin.no_availability_set') }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['update:modelValue', 'update']);

const days = [
    { id: 1, labelKey: 'admin.monday' },
    { id: 2, labelKey: 'admin.tuesday' },
    { id: 3, labelKey: 'admin.wednesday' },
    { id: 4, labelKey: 'admin.thursday' },
    { id: 5, labelKey: 'admin.friday' },
    { id: 6, labelKey: 'admin.saturday' },
    { id: 0, labelKey: 'admin.sunday' },
];

const translatedDays = computed(() => days.map(d => ({
    ...d,
    label: t(d.labelKey)
})));

const rules = computed(() => props.modelValue.availability);

const update = () => {
    emit('update:modelValue', props.modelValue);
    emit('update');
};

const addRule = () => {
    const newRules = [...props.modelValue.availability, {
        days_of_week: [1, 2, 3, 4, 5],
        start_time: '09:00:00',
        end_time: '17:00:00',
        breaks: [],
        status: 'active'
    }];
    emit('update:modelValue', { ...props.modelValue, availability: newRules });
    emit('update');
};

const removeRule = (index) => {
    const newRules = props.modelValue.availability.filter((_, i) => i !== index);
    emit('update:modelValue', { ...props.modelValue, availability: newRules });
    emit('update');
};

const toggleDay = (ruleIndex, dayId) => {
    const rule = props.modelValue.availability[ruleIndex];
    if (rule.days_of_week.includes(dayId)) {
        rule.days_of_week = rule.days_of_week.filter(id => id !== dayId);
    } else {
        rule.days_of_week.push(dayId);
    }
    update();
};
</script>
