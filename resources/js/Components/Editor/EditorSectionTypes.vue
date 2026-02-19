<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h4 class="font-medium text-gray-900">Service Types</h4>
            <button @click="addType" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">+ Add New</button>
        </div>

        <div class="space-y-4">
            <div v-for="(type, index) in modelValue.appointmentTypes" :key="index" class="p-4 border border-gray-200 rounded-lg bg-gray-50 relative group">
                <button @click="removeType(index)" class="absolute top-2 right-2 text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                    🗑️
                </button>
                
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase">Service Name</label>
                        <input 
                            type="text" 
                            v-model="type.name"
                            @input="update"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase">Duration (mins)</label>
                            <input 
                                type="number" 
                                v-model.number="type.duration_minutes"
                                @input="update"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase">Price (BRL)</label>
                            <input 
                                type="number" 
                                v-model.number="type.price"
                                @input="update"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            >
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="modelValue.appointmentTypes.length === 0" class="text-center py-8 border-2 border-dashed border-gray-200 rounded-lg">
                <p class="text-sm text-gray-400">No services defined. Add one to start booking.</p>
            </div>
        </div>
    </div>
</template>

<script setup>
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
        name: 'New Service',
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
