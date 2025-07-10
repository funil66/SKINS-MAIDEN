<template>
  <div class="home">
    <header>
      <h1>Welcome to Iron Code Skins</h1>
      <p>Your trusted platform for secure transactions of digital assets.</p>
    </header>
    
    <section class="features">
      <h2>Features</h2>
      <ul>
        <li>Secure Escrow Services</li>
        <li>Real-time Transaction Tracking</li>
        <li>Comprehensive KYC Processes</li>
        <li>Blockchain Integration for Transparency</li>
      </ul>
    </section>

    <section class="cta">
      <h2>Get Started</h2>
      <router-link to="/dashboard" class="btn">Go to Dashboard</router-link>
    </section>
  </div>
</template>

<script>
export default {
  name: 'Home',
};
</script>

<style scoped>
.home {
  text-align: center;
  padding: 20px;
}

.features {
  margin: 20px 0;
}

.cta {
  margin-top: 30px;
}

.btn {
  padding: 10px 20px;
  background-color: #007bff;
  color: white;
  text-decoration: none;
  border-radius: 5px;
}

.btn:hover {
  background-color: #0056b3;
}
</style>