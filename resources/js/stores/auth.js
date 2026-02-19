import { defineStore } from 'pinia';
import axios from '../axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('token') || null,
        loading: false,
        error: null
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
    },

    actions: {
        async login(email, password) {
            this.loading = true;
            this.error = null;
            try {
                // Get CSRF cookie first (Sanctum protection)
                await axios.get('/sanctum/csrf-cookie');

                const response = await axios.post('/api/login', { email, password });

                this.token = response.data.access_token;
                this.user = response.data.user;

                // Save to local storage
                localStorage.setItem('token', this.token);

                // Update default axios header
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;

                return true;
            } catch (err) {
                this.error = err.response?.data?.message || 'Login failed';
                return false;
            } finally {
                this.loading = false;
            }
        },

        logout() {
            this.token = null;
            this.user = null;
            localStorage.removeItem('token');
            delete axios.defaults.headers.common['Authorization'];
            // Optional: call API logout endpoint
        }
    }
});
