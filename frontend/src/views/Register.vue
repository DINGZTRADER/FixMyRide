<template>
  <div class="container auth">
    <h1>Register</h1>
    <form @submit.prevent="handleRegister" class="card">
      <div class="field">
        <label for="name">Name</label>
        <input type="text" id="name" v-model="form.name" required />
      </div>
      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" v-model="form.email" required />
        <p v-if="errors && errors.email" class="error-inline">{{ errors.email[0] }}</p>
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" v-model="form.password" required />
        <p v-if="errors && errors.password" class="error-inline">{{ errors.password[0] }}</p>
      </div>
      <div class="field">
        <label for="password_confirmation">Confirm Password</label>
        <input type="password" id="password_confirmation" v-model="form.password_confirmation" required />
        <p v-if="errors && errors.password_confirmation" class="error-inline">{{ errors.password_confirmation[0] }}</p>
      </div>
      <div class="field">
        <label for="role">Role</label>
        <select id="role" v-model="form.role">
          <option value="user">User</option>
          <option value="owner">Owner</option>
          <option value="mechanic">Mechanic</option>
        </select>
        <p v-if="errors && errors.role" class="error-inline">{{ errors.role[0] }}</p>
      </div>
      <button type="submit" class="btn primary">Register</button>
    </form>
    <p v-if="message" class="message">{{ message }}</p>
    <div v-if="errors && errors.general" class="errors">
      <ul>
        <li v-for="(error, key) in errors" :key="key" v-if="key === 'general'">{{ error[0] }}</li>
      </ul>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'Register',
  data() {
    return {
      form: {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: 'user',
      },
      message: '',
      errors: {},
    };
  },
  methods: {
    async handleRegister() {
      this.message = 'Submitting...';
      this.errors = {};
      try {
        const response = await axios.post('/api/register', this.form);
        this.message = 'Registration successful!';
        this.$router.push('/login');
      } catch (error) {
        this.message = 'An error occurred.';
        if (error.response && error.response.data) {
          const apiErrors = error.response.data.errors || {};
          const apiMessage = error.response.data.message;
          this.errors = { ...apiErrors };
          if (apiMessage && !Object.keys(apiErrors).length) {
            this.errors.general = [apiMessage];
          }
        }
      }
    },
  },
};
</script>

<style scoped>
:root { --blue: #2563eb; --yellow: #fbbf24; --border: #e5e7eb; --text: #1f2937; }
.container.auth {
  max-width: 480px;
  margin: 0 auto;
  padding: 16px;
  color: var(--text);
  text-align: left;
  background: url('/mechanic2.png') no-repeat right bottom / 220px auto;
}
h1 { color: var(--blue); margin-bottom: 12px; text-align: center; }
.card { background: #fff; border: 1px solid var(--border); border-radius: 8px; padding: 16px; }
.field { margin-bottom: 12px; }
label { display: block; font-weight: 600; margin-bottom: 6px; }
input, select { width: 100%; padding: 8px; border: 1px solid var(--border); border-radius: 6px; }
.btn { border: 1px solid var(--blue); color: #fff; background: var(--blue); padding: 10px 12px; border-radius: 6px; cursor: pointer; width: 100%; }
.errors { color: #dc2626; margin-top: 8px; }
.error-inline { color: #dc2626; margin-top: 4px; font-size: 0.9rem; }
.message { color: #16a34a; margin-top: 8px; text-align: center; }
</style>
