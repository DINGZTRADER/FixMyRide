<template>
  <div class="container">
    <h2>Repair Requests</h2>

    <form v-if="isOwner" class="request-form" @submit.prevent="createRequest">
      <div class="field">
        <label for="car_model">Car Model:</label>
        <input id="car_model" v-model="form.car_model" required />
        <p v-if="errors.car_model" class="error-inline">{{ errors.car_model[0] }}</p>
      </div>
      <div class="field">
        <label for="issue">Issue:</label>
        <textarea id="issue" v-model="form.issue" required></textarea>
        <p v-if="errors.issue" class="error-inline">{{ errors.issue[0] }}</p>
      </div>
      <button type="submit" class="btn primary">Create Request</button>
      <p v-if="message" class="message">{{ message }}</p>
    </form>

    <div class="toolbar" v-if="isMechanic">
      <button :class="['btn', filter==='all' ? 'primary' : 'outline']" @click="setFilter('all')">All</button>
      <button :class="['btn', filter==='mine' ? 'primary' : 'outline']" @click="setFilter('mine')">Mine</button>
    </div>

    <ul class="requests">
      <li v-for="request in requests" :key="request.id" class="request-item">
        <div class="request-head">
          <strong>{{ request.car_model }}</strong>
          <span class="status" :data-status="request.status">{{ request.status }}</span>
        </div>
        <div class="meta">Created by {{ request.owner_name || 'Unknown' }} • Assigned to {{ request.assigned_to_name || 'Unassigned' }}</div>
        <div class="issue">{{ request.issue }}</div>
        <div v-if="isMechanic" class="actions">
          <button class="btn outline" type="button" @click="assign(request.id)">Assign to me</button>
          <button class="btn outline" type="button" @click="unassign(request.id)">Unassign</button>
          <button class="btn success" type="button" @click="updateStatus(request.id, 'accepted')">Accept</button>
          <button class="btn danger" type="button" @click="updateStatus(request.id, 'declined')">Decline</button>
          <button class="btn warn" type="button" @click="updateStatus(request.id, 'pending')">Pending</button>
        </div>
      </li>
    </ul>
    <div class="pager" v-if="totalPages>1">
      <button class="btn outline" @click="prevPage" :disabled="page===1">Prev</button>
      <span>Page {{ page }} of {{ totalPages }}</span>
      <button class="btn outline" @click="nextPage" :disabled="page===totalPages">Next</button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      requests: [],
      form: {
        car_model: '',
        issue: ''
      },
      errors: {},
      message: '',
      role: null,
      filter: 'all'
    };
  },
  computed: {
    isOwner() { return this.role === 'owner'; },
    isMechanic() { return this.role === 'mechanic'; }
  },
  async created() {
    this.role = (localStorage.getItem('role') || '').toLowerCase();
    await this.fetchRequests();
  },
  methods: {
    async fetchRequests() {
      try {
        const qs = (this.isMechanic && this.filter === 'mine') ? '?filter=mine' : '';
        const response = await axios.get('/api/requests' + qs);
        this.requests = response.data;
      } catch (error) {
        console.error(error);
      }
    },
    setFilter(f) {
      this.filter = f;
      this.fetchRequests();
    },
    async createRequest() {
      this.message = '';
      this.errors = {};
      try {
        await axios.post('/api/requests', this.form);
        this.message = 'Request created!';
        this.form.car_model = '';
        this.form.issue = '';
        await this.fetchRequests();
      } catch (error) {
        if (error.response && error.response.status === 422 && error.response.data.errors) {
          this.errors = error.response.data.errors;
        } else {
          this.message = 'Failed to create request';
        }
      }
    },
    async updateStatus(id, status) {
      try {
        await axios.put(`/api/requests/${id}/status`, { status });
        await this.fetchRequests();
      } catch (e) {
        console.error('Failed to update status', e);
      }
    },
    async assign(id) {
      try { await axios.post(`/api/requests/${id}/assign`); await this.fetchRequests(); } catch(e){ console.error(e); }
    },
    async unassign(id) {
      try { await axios.post(`/api/requests/${id}/unassign`); await this.fetchRequests(); } catch(e){ console.error(e); }
    }
  }
};
</script>

<style scoped>
:root { --blue: #2563eb; --yellow: #fbbf24; --border: #e5e7eb; --text: #1f2937; }
.container { max-width: 720px; margin: 0 auto; padding: 16px; color: var(--text); }

h2 { color: var(--blue); margin-bottom: 12px; }

.request-form { background: #fff; border: 1px solid var(--border); border-radius: 8px; padding: 12px; margin-bottom: 16px; }
.field { margin-bottom: 10px; }
label { display: block; font-weight: 600; margin-bottom: 6px; }
input, textarea { width: 100%; padding: 8px; border: 1px solid var(--border); border-radius: 6px; }

.btn { border: 1px solid var(--blue); color: var(--blue); background: transparent; padding: 8px 10px; border-radius: 6px; cursor: pointer; }
.btn.primary { background: var(--blue); color: #fff; }
.btn.success { background: #16a34a; border-color: #16a34a; color: #fff; }
.btn.danger { background: #dc2626; border-color: #dc2626; color: #fff; }
.btn.warn { background: var(--yellow); border-color: var(--yellow); color: #111827; }
.btn.outline { background: #fff; }
.btn + .btn { margin-left: 6px; }

.message { color: #16a34a; margin-top: 6px; }
.error-inline { color: #dc2626; margin-top: 4px; font-size: 0.9rem; }

.toolbar { margin: 10px 0 16px; }
.toolbar .btn { margin-right: 8px; }

.requests { list-style: none; padding: 0; }
.request-item { background: #fff; border: 1px solid var(--border); border-radius: 8px; padding: 12px; margin-bottom: 10px; }
.request-head { display: flex; justify-content: space-between; align-items: center; }
.meta { color: #6b7280; font-size: 0.9rem; margin: 4px 0 8px; }
.issue { white-space: pre-line; }
.status[data-status="accepted"] { color: #16a34a; }
.status[data-status="declined"] { color: #dc2626; }
.status[data-status="pending"] { color: var(--yellow); }
.actions { margin-top: 10px; }
</style>

