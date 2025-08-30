<template>
  <div class="container auth">
    <h2>Login</h2>
    <form class="card" @submit.prevent="login">
      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" v-model="email" required>
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" v-model="password" required>
      </div>
      <button class="btn primary" type="submit">Login</button>
      <p v-if="error" class="error-inline">{{ error }}</p>
    </form>
  </div>
  </template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      email: '',
      password: '',
      error: ''
    };
  },
  methods: {
    async login() {
      try {
        const response = await axios.post('/api/login', {
          email: this.email,
          password: this.password
        });
        const token = response.data.token;
        localStorage.setItem('token', token);
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        try {
          const me = await axios.get('/api/me');
          if (me && me.data) {
            if (me.data.name) localStorage.setItem('name', me.data.name);
            if (me.data.role) localStorage.setItem('role', me.data.role);
          }
        } catch (e) { /* ignore */ }
        // Notify app header to refresh auth state
        window.dispatchEvent(new Event('auth-changed'));
        this.$router.push('/requests');
      } catch (error) {
        this.error = 'Invalid credentials';
      }
    }
  }
};
</script>

<style scoped>
:root { --blue: #2563eb; --yellow: #fbbf24; --border: #e5e7eb; --text: #1f2937; }
.container.auth {
  max-width: 420px;
  margin: 0 auto;
  padding: 16px;
  color: var(--text);
  text-align: left;
  background: url('/mechanic2.png') no-repeat right bottom / 200px auto;
}
h2 { color: var(--blue); margin-bottom: 12px; text-align: center; }
.card { background: #fff; border: 1px solid var(--border); border-radius: 8px; padding: 16px; }
.field { margin-bottom: 12px; }
label { display: block; font-weight: 600; margin-bottom: 6px; }
input { width: 100%; padding: 8px; border: 1px solid var(--border); border-radius: 6px; }
.btn { border: 1px solid var(--blue); color: #fff; background: var(--blue); padding: 10px 12px; border-radius: 6px; cursor: pointer; width: 100%; }
.error-inline { color: #dc2626; margin-top: 8px; }
</style>
