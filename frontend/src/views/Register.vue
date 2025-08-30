<template>
  <div>
    <h1>Register</h1>
    <form @submit.prevent="handleRegister">
      <div>
        <label for="name">Name:</label>
        <input type="text" id="name" v-model="form.name" required />
      </div>
      <div>
        <label for="email">Email:</label>
        <input type="email" id="email" v-model="form.email" required />
      </div>
      <div>
        <label for="password">Password:</label>
        <input type="password" id="password" v-model="form.password" required />
      </div>
      <div>
        <label for="password_confirmation">Confirm Password:</label>
        <input type="password" id="password_confirmation" v-model="form.password_confirmation" required />
      </div>
      <button type="submit">Register</button>
    </form>
    <p v-if="message">{{ message }}</p>
  </div>
</template>

<script>
export default {
  name: 'Register',
  data() {
    return {
      form: {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: 'user', // Default role
      },
      message: '',
    };
  },
  methods: {
    async handleRegister() {
      this.message = 'Submitting...';
      try {
        const response = await fetch('/api/register', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: JSON.stringify(this.form),
        });

        const data = await response.json();

        if (!response.ok) {
          // Handle errors (e.g., validation errors)
          this.message = data.message || 'An error occurred.';
          console.error('Registration failed:', data);
        } else {
          // Handle success
          this.message = 'Registration successful!';
          console.log('Registration successful:', data);
        }
      } catch (error) {
        this.message = 'A network error occurred.';
        console.error('Network error:', error);
      }
    },
  },
};
</script>

<style scoped>
form div {
  margin-bottom: 1rem;
}
label {
  margin-right: 0.5rem;
}
</style>