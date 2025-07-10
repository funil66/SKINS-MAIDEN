<template>
  <div class="transaction-detail">
    <h1>Transaction Detail</h1>
    <div v-if="transaction">
      <h2>Transaction ID: {{ transaction.id }}</h2>
      <p><strong>User:</strong> {{ transaction.user.name }}</p>
      <p><strong>Amount:</strong> {{ transaction.amount }}</p>
      <p><strong>Status:</strong> {{ transaction.status }}</p>
      <p><strong>Created At:</strong> {{ formatDate(transaction.created_at) }}</p>
    </div>
    <div v-else>
      <p>Loading transaction details...</p>
    </div>
    <router-link to="/dashboard">Back to Dashboard</router-link>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
  data() {
    return {
      transaction: null,
    };
  },
  computed: {
    ...mapGetters(['getTransactionById']),
  },
  created() {
    const transactionId = this.$route.params.id;
    this.transaction = this.getTransactionById(transactionId);
  },
  methods: {
    formatDate(date) {
      return new Date(date).toLocaleString();
    },
  },
};
</script>

<style scoped>
.transaction-detail {
  padding: 20px;
}
</style>