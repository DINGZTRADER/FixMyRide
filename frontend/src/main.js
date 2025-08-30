import { createApp } from 'vue'
import App from './App.vue'
import router from './router' // Import the router
import axios from 'axios'
import './assets/theme.css'

// Set Authorization header from stored token on startup
const token = localStorage.getItem('token')
if (token) {
  axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
}

createApp(App)
  .use(router) // Tell the app to use the router
  .mount('#app')
