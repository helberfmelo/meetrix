<template>
    <router-view></router-view>
</template>

<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from './stores/auth';
import axios from 'axios';

const authStore = useAuthStore();

onMounted(() => {
    // Restore token from localStorage
    const token = localStorage.getItem('token');
    if (token) {
        authStore.token = token;
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        console.log('Auth token restored');
    }
});
</script>
