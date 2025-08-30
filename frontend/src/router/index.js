import { createRouter, createWebHistory } from 'vue-router';
import Home from '../views/Home.vue';
import Register from '../views/Register.vue';
import Login from '../views/Login.vue';
import Requests from '../views/Requests.vue';

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home,
    meta: { title: 'FixMyRide – Home' },
  },
  {
    path: '/register',
    name: 'Register',
    component: Register,
    meta: { title: 'FixMyRide – Register' },
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { title: 'FixMyRide – Login' },
  },
  {
    path: '/requests',
    name: 'Requests',
    component: Requests,
    meta: { requiresAuth: true, roles: ['owner', 'mechanic'], title: 'FixMyRide – Requests' },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const loggedIn = localStorage.getItem('token');
  const role = (localStorage.getItem('role') || '').toLowerCase();

  if (to.matched.some(record => record.meta.requiresAuth) && !loggedIn) {
    return next('/login');
  }

  // If already authenticated, avoid auth pages
  if (loggedIn && (to.path === '/login' || to.path === '/register')) {
    return next('/requests');
  }

  // Enforce role-specific routes if defined
  const requiredRoles = to.matched.find(r => r.meta && r.meta.roles)?.meta.roles;
  if (requiredRoles && role && !requiredRoles.includes(role)) {
    return next('/');
  }

  // Set document title if provided
  const nearestWithTitle = [...to.matched].reverse().find(r => r.meta && r.meta.title);
  if (nearestWithTitle) {
    document.title = nearestWithTitle.meta.title;
  } else {
    document.title = 'FixMyRide';
  }

  next();
});

export default router;
