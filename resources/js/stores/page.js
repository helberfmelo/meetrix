import { defineStore } from 'pinia';
import axios from '../axios';
import i18n from '../i18n';

export const usePageStore = defineStore('page', {
    state: () => ({
        pages: [],
        currentPage: null,
        loading: false,
        error: null
    }),

    getters: {
        activePagesCount: (state) => state.pages.filter(p => p.is_active).length,
        totalBookingsCount: (state) => {
            // In a real app we might fetch this separately or eager load it
            // For now, let's assume dashboard stats come from a dedicated stats endpoint
            // or we sum up if we have bookings loaded.
            // Placeholder: return 0 if no bookings relationship loaded yet
            return 0;
        }
    },

    actions: {
        async fetchPages() {
            this.loading = true;
            try {
                const response = await axios.get('/api/pages');
                this.pages = response.data;
            } catch (error) {
                this.error = error.response?.data?.message || i18n.global.t('errors.fetch_pages');
                console.error(this.error);
            } finally {
                this.loading = false;
            }
        },

        async createPage(pageData) {
            this.loading = true;
            try {
                const response = await axios.post('/api/pages', pageData);
                this.pages.push(response.data);
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || i18n.global.t('errors.create_page');
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async fetchPageBySlug(slug) {
            this.loading = true;
            try {
                const response = await axios.get(`/api/pages/${slug}`);
                this.currentPage = response.data;
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || i18n.global.t('errors.fetch_page');
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async updatePage(id, data) {
            this.loading = true;
            try {
                const response = await axios.put(`/api/pages/${id}`, data);
                // Update local state if needed
                if (this.currentPage && this.currentPage.id === id) {
                    this.currentPage = { ...this.currentPage, ...response.data };
                }
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || i18n.global.t('errors.update_page');
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async updateAvailability(pageId, rules) {
            this.loading = true;
            try {
                const response = await axios.put(`/api/pages/${pageId}/availability/bulk`, { rules });
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || i18n.global.t('errors.update_availability');
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async updateAppointmentTypes(pageId, types) {
            this.loading = true;
            try {
                const response = await axios.put(`/api/pages/${pageId}/appointment-types/bulk`, { types });
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || i18n.global.t('errors.update_services');
                throw error;
            } finally {
                this.loading = false;
            }
        }
    }
});
