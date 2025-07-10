<template>
  <div class="dashboard">
    <h1>Dashboard</h1>
    <div class="dashboard-content">
      <section class="user-stats">
        <h2>User Statistics</h2>
        <p>Total Users: {{ totalUsers }}</p>
        <p>Active Users: {{ activeUsers }}</p>
      </section>
      <section class="transaction-stats">
        <h2>Transaction Statistics</h2>
        <p>Total Transactions: {{ totalTransactions }}</p>
        <p>Successful Transactions: {{ successfulTransactions }}</p>
      </section>
      <section class="kyc-stats">
        <h2>KYC Status</h2>
        <p>Pending KYC: {{ pendingKYC }}</p>
        <p>Approved KYC: {{ approvedKYC }}</p>
      </section>
    </div>
    <div class="recent-activity">
      <h2>Recent Activity</h2>
      <ul>
        <li v-for="activity in recentActivities" :key="activity.id">
          {{ activity.description }} - {{ activity.timestamp }}
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      totalUsers: 0,
      activeUsers: 0,
      totalTransactions: 0,
      successfulTransactions: 0,
      pendingKYC: 0,
      approvedKYC: 0,
      recentActivities: []
    };
  },
  mounted() {
    this.fetchDashboardData();
  },
  methods: {
    async fetchDashboardData() {
      try {
        const response = await this.$http.get('/api/dashboard');
        const data = response.data;
        this.totalUsers = data.totalUsers;
        this.activeUsers = data.activeUsers;
        this.totalTransactions = data.totalTransactions;
        this.successfulTransactions = data.successfulTransactions;
        this.pendingKYC = data.pendingKYC;
        this.approvedKYC = data.approvedKYC;
        this.recentActivities = data.recentActivities;
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
      }
    }
  }
};
</script>

<style scoped>
.dashboard {
  padding: 20px;
}

.dashboard-content {
  display: flex;
  justify-content: space-between;
}

.user-stats, .transaction-stats, .kyc-stats {
  border: 1px solid #ccc;
  padding: 10px;
  border-radius: 5px;
  width: 30%;
}

.recent-activity {
  margin-top: 20px;
}

.recent-activity ul {
  list-style-type: none;
  padding: 0;
}
</style>