<template>
  <div id="nav">
    <div class="brand">
      <img class="logo" src="/logo.png" alt="FixMyRide logo" />
      <h1>FixMyRide</h1>
    </div>
    <button class="menu-toggle" @click="mobileOpen = !mobileOpen">Menu</button>
    <div :class="['nav-links', mobileOpen ? 'open' : '']">
      <router-link to="/">Home</router-link>
      <template v-if="!isAuthed">
        <router-link to="/register">Register</router-link>
        <router-link to="/login">Login</router-link>
      </template>
      <template v-else>
        <span class="hello">Hi, {{ userName }}</span>
        <router-link to="/requests">Requests</router-link>
        <button class="linklike" @click="logout">Logout</button>
      </template>
    </div>
  </div>
  <router-view />
  <footer class="site-footer">
    <div class="footer-inner">
      <div class="brand">
        <img class="logo" src="/logo.png" alt="FixMyRide logo" />
        <span class="name">FixMyRide</span>
      </div>
      <nav class="links">
        <router-link to="/">Home</router-link>
        <router-link to="/requests">Requests</router-link>
        <router-link v-if="!isAuthed" to="/register">Register</router-link>
        <router-link v-if="!isAuthed" to="/login">Login</router-link>
      </nav>
      <div class="copy">© {{ new Date().getFullYear() }} FixMyRide</div>
    </div>
  </footer>
</template>

<script>
import axios from 'axios';
export default {
  name: 'AppRoot',
  data() {
    return {
      isAuthed: !!localStorage.getItem('token'),
      userName: localStorage.getItem('name') || 'User',
      mobileOpen: false,
    };
  },
  methods: {
    async syncAuth() {
      const token = localStorage.getItem('token');
      this.isAuthed = !!token;
      if (token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        const cached = localStorage.getItem('name');
        if (!cached) {
          try {
            const me = await axios.get('/api/me');
            if (me && me.data) {
              if (me.data.name) {
                localStorage.setItem('name', me.data.name);
                this.userName = me.data.name;
              }
              if (me.data.role) {
                localStorage.setItem('role', me.data.role);
              }
            }
          } catch (e) {}
        } else {
          this.userName = cached;
        }
      } else {
        delete axios.defaults.headers.common['Authorization'];
        this.userName = 'User';
      }
    },
    logout() {
      axios.post('/api/logout').finally(() => {
        localStorage.removeItem('token');
        localStorage.removeItem('name');
        localStorage.removeItem('role');
        delete axios.defaults.headers.common['Authorization'];
        this.isAuthed = false;
        window.dispatchEvent(new Event('auth-changed'));
        this.$router.push('/login');
      });
    }
  },
  mounted() {
    window.addEventListener('auth-changed', this.syncAuth);
    window.addEventListener('storage', this.syncAuth);
    this.syncAuth();
  },
  beforeUnmount() {
    window.removeEventListener('auth-changed', this.syncAuth);
    window.removeEventListener('storage', this.syncAuth);
  }
}
</script>

<style>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
}

#nav { padding: 12px 16px; background: #2563eb; color: #fff; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.brand { display: flex; align-items: center; gap: 8px; margin-right: 12px; }
.brand .logo { width: 36px; height: 36px; object-fit: contain; }
#nav h1 { font-size: 20px; margin: 0; }
.nav-links { display: flex; align-items: center; gap: 8px; }
#nav a { font-weight: 600; color: #fff; text-decoration: none; margin: 0 8px; }
#nav a.router-link-exact-active, #nav a.router-link-active { color: #fbbf24; }
.menu-toggle { display: none; margin-left: auto; background: none; border: 1px solid #fff; color: #fff; padding: 4px 8px; border-radius: 4px; }
@media (max-width: 700px) {
  .nav-links { display: none; width: 100%; }
  .nav-links.open { display: flex; flex-direction: column; gap: 6px; }
  .menu-toggle { display: block; margin-left: auto; }
}

.linklike {
  background: none;
  border: none;
  color: #fff;
  font-weight: bold;
  cursor: pointer;
  padding: 0;
}

.hello { font-weight: 600; }

.site-footer { background: #1e3a8a; color: #fff; margin-top: 24px; }
.site-footer .footer-inner { max-width: 1100px; margin: 0 auto; padding: 16px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
.site-footer .links a { color: #fff; margin: 0 8px; text-decoration: none; font-weight: 600; }
.site-footer .brand .name { font-weight: 700; }
.site-footer .copy { opacity: 0.85; }
</style>


.site-footer .links .router-link-active { text-decoration: underline; }
