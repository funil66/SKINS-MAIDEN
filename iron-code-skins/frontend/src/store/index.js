// filepath: iron-code-skins/frontend/src/store/index.js
import { createStore } from 'vuex';

const store = createStore({
  state: {
    user: null,
    transactions: [],
    kycDocuments: [],
    auditLogs: [],
  },
  mutations: {
    setUser(state, user) {
      state.user = user;
    },
    setTransactions(state, transactions) {
      state.transactions = transactions;
    },
    addTransaction(state, transaction) {
      state.transactions.push(transaction);
    },
    setKYCDocuments(state, documents) {
      state.kycDocuments = documents;
    },
    addKYCDocument(state, document) {
      state.kycDocuments.push(document);
    },
    setAuditLogs(state, logs) {
      state.auditLogs = logs;
    },
    addAuditLog(state, log) {
      state.auditLogs.push(log);
    },
  },
  actions: {
    fetchUser({ commit }, userId) {
      // Fetch user data from API and commit to state
    },
    fetchTransactions({ commit }) {
      // Fetch transactions from API and commit to state
    },
    fetchKYCDocuments({ commit }) {
      // Fetch KYC documents from API and commit to state
    },
    fetchAuditLogs({ commit }) {
      // Fetch audit logs from API and commit to state
    },
  },
  getters: {
    isAuthenticated(state) {
      return !!state.user;
    },
    getUser(state) {
      return state.user;
    },
    getTransactions(state) {
      return state.transactions;
    },
    getKYCDocuments(state) {
      return state.kycDocuments;
    },
    getAuditLogs(state) {
      return state.auditLogs;
    },
  },
});

export default store;