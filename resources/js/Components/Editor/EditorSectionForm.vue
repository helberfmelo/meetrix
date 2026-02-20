<template>
    <div class="space-y-6">
        <p class="text-sm text-gray-500 leading-relaxed">
            {{ $t('admin.form_description') }}
        </p>

        <div class="space-y-3">
            <div 
                v-for="(field, index) in fields" 
                :key="index"
                class="p-4 bg-gray-50 rounded-xl border border-gray-100 flex items-start space-x-3 group relative"
            >
                <div class="flex-1 space-y-3">
                    <div class="flex space-x-2">
                        <input 
                            v-model="field.label" 
                            class="flex-1 px-3 py-2 text-sm font-medium border-2 border-transparent focus:border-indigo-600 rounded-lg outline-none transition-all"
                            :placeholder="$t('admin.field_label_placeholder')"
                        >
                        <select 
                            v-model="field.type" 
                            class="px-3 py-2 text-sm border-2 border-transparent focus:border-indigo-600 rounded-lg outline-none bg-white"
                        >
                            <option value="text">{{ $t('admin.text_field') }}</option>
                            <option value="email">{{ $t('admin.email_field') }}</option>
                            <option value="tel">{{ $t('admin.phone_field') }}</option>
                            <option value="textarea">{{ $t('admin.long_text_field') }}</option>
                        </select>
                    </div>
                    <div class="flex items-center space-x-4 px-1">
                        <label class="flex items-center text-xs font-bold text-gray-500 cursor-pointer">
                            <input type="checkbox" v-model="field.required" class="mr-2 rounded text-indigo-600">
                            {{ $t('admin.required_label') }}
                        </label>
                        <span class="text-[10px] text-gray-300 font-mono">ID: {{ field.name }}</span>
                    </div>
                </div>
                
                <button 
                    @click="removeField(index)"
                    v-if="!isDefaultField(field.name)"
                    class="p-2 text-gray-300 hover:text-red-500 transition-colors"
                >
                    ✕
                </button>
            </div>
        </div>

        <button 
            @click="addField"
            class="w-full py-3 border-2 border-dashed border-gray-200 rounded-xl text-gray-400 font-bold hover:border-indigo-300 hover:text-indigo-600 transition-all text-sm"
        >
            {{ $t('admin.add_question') }}
        </button>
    </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps(['modelValue']);
const emit = defineEmits(['update:modelValue', 'update']);

const fields = ref([]);

onMounted(() => {
    // Initialize fields from modelValue
    if (props.modelValue && props.modelValue.config && props.modelValue.config.form_fields) {
        fields.value = JSON.parse(JSON.stringify(props.modelValue.config.form_fields));
    } else {
        // Default set
        fields.value = [
            { name: 'customer_name', label: t('admin.full_name_label'), type: 'text', required: true },
            { name: 'customer_email', label: t('admin.email_address_label'), type: 'email', required: true },
        ];
    }
});

const isDefaultField = (name) => ['customer_name', 'customer_email'].includes(name);

const addField = () => {
    const id = 'field_' + Math.random().toString(36).substr(2, 5);
    fields.value.push({
        name: id,
        label: t('admin.new_question_placeholder'),
        type: 'text',
        required: false
    });
};

const removeField = (index) => {
    fields.value.splice(index, 1);
};

watch(fields, (newVal) => {
    const updatedValue = { ...props.modelValue };
    if (!updatedValue.config) updatedValue.config = {};
    updatedValue.config.form_fields = JSON.parse(JSON.stringify(newVal));
    emit('update:modelValue', updatedValue);
    emit('update');
}, { deep: true });
</script>
