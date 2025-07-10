// filepath: iron-code-skins/iron-code-skins/frontend/src/services/api.js
import axios from 'axios';

const API_BASE_URL = 'http://localhost:8000/api'; // Adjust the base URL as needed

const apiClient = axios.create({
    baseURL: API_BASE_URL,
    timeout: 10000,
    headers: {
        'Content-Type': 'application/json',
    },
});

// User Authentication
export const login = async (credentials) => {
    return await apiClient.post('/auth/login', credentials);
};

export const logout = async () => {
    return await apiClient.post('/auth/logout');
};

// KYC Management
export const submitKYC = async (kycData) => {
    return await apiClient.post('/kyc/submit', kycData);
};

// Transaction Management
export const createTransaction = async (transactionData) => {
    return await apiClient.post('/transactions', transactionData);
};

export const getTransactionDetails = async (transactionId) => {
    return await apiClient.get(`/transactions/${transactionId}`);
};

// Audit Logging
export const getAuditLogs = async () => {
    return await apiClient.get('/audit-logs');
};

// Error handling interceptor
apiClient.interceptors.response.use(
    response => response,
    error => {
        // Handle errors globally
        console.error('API Error:', error);
        return Promise.reject(error);
    }
);