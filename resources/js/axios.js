import axios from 'axios';
import { extractLocaleSegment, resolveLocalePreference, stripLocalePrefix, withLocalePrefix } from './utils/localeRoute';

const api = axios.create({
    baseURL: '/',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
    withCredentials: true,
    withXSRFToken: true
});

// Request interceptor to inject Bearer token
api.interceptors.request.use(config => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Response interceptor for error handling
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response && error.response.status === 401) {
            // Redirect to login if unauthenticated (and not already there)
            if (stripLocalePrefix(window.location.pathname) !== '/login') {
                const locale = resolveLocalePreference({
                    urlLocale: extractLocaleSegment(window.location.pathname),
                    savedLocale: localStorage.getItem('locale'),
                    browserLocale: navigator.language,
                });

                window.location.href = withLocalePrefix('/login', locale);
            }
        }
        return Promise.reject(error);
    }
);

export default api;
