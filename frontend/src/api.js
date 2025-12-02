import axios from 'axios';
import { Capacitor } from '@capacitor/core';
import { SecureStoragePlugin } from 'capacitor-secure-storage-plugin';

const baseURL = '/api';

const api = axios.create({
    baseURL,
    withCredentials: true,
});

// Storage Helper
export const Storage = {
    get: async (key) => {
        if (Capacitor.isNativePlatform()) {
            try {
                const { value } = await SecureStoragePlugin.get({ key });
                return value;
            } catch { return null; }
        }
        return localStorage.getItem(key);
    },
    set: async (key, value) => {
        if (Capacitor.isNativePlatform()) {
            return await SecureStoragePlugin.set({ key, value });
        }
        return localStorage.setItem(key, value);
    },
    remove: async (key) => {
        if (Capacitor.isNativePlatform()) {
            return await SecureStoragePlugin.remove({ key });
        }
        return localStorage.removeItem(key);
    },
    clear: async () => {
        if (Capacitor.isNativePlatform()) {
            return await SecureStoragePlugin.clear();
        }
        localStorage.clear();
    }
};

// Request Interceptor
api.interceptors.request.use(async (config) => {
    const token = await Storage.get('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    // Required for localtunnel to skip the warning page
    config.headers['Bypass-Tunnel-Reminder'] = 'true';
    return config;
});

// Response Interceptor (Auto Logout)
api.interceptors.response.use(
    (response) => response,
    async (error) => {
        if (error.response?.status === 401) {
            await Storage.clear();
            if (window.location.pathname !== '/') {
                window.location.href = '/';
            }
        }
        return Promise.reject(error);
    }
);

export const login = async (username, password) => {
    const res = await api.post('/login.php', { username, password });
    if (res.data.ok && res.data.token) {
        await Storage.set('token', res.data.token);
    }
    return res.data;
};

export const logout = async () => {
    try {
        await api.post('/logout.php');
    } catch (e) { /* ignore */ }
    await Storage.clear();
};

export const uploadMeasurement = (formData) => api.post('/measurements.php', formData);
export const searchMeasurement = (token) => api.get(`/measurements.php?token=${token}`);
export const updateMeasurement = (id, data) => {
    if (data instanceof FormData) {
        return api.post(`/measurements.php?id=${id}`, data);
    } else {
        return api.put(`/measurements.php?id=${id}`, data);
    }
};

export const checkAuth = async () => {
    const token = await Storage.get('token');
    return !!token;
};

export default api;
