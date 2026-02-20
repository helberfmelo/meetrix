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
        async register(userData) {
            this.loading = true;
            this.error = null;
            try {
                await axios.get('/sanctum/csrf-cookie');
                const response = await axios.post('/api/register', userData);

                this.token = response.data.access_token;
                // Fetch user to populate state
                await this.fetchUser();

                localStorage.setItem('token', this.token);
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
                return true;
            } catch (err) {
                this.error = err.response?.data?.message || 'Registration failed';
                return false;
            } finally {
                this.loading = false;
            }
        },

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

        async fetchUser() {
            if (!this.token) return;
            try {
                const response = await axios.get('/api/user');
                this.user = response.data;
            } catch (err) {
                this.logout();
            }
        },

        async init() {
            if (this.token && !this.user) {
                await this.fetchUser();
            }
        },

        logout() {
            this.token = null;
            this.user = null;
            localStorage.removeItem('token');
            delete axios.defaults.headers.common['Authorization'];
        }
    }
});
