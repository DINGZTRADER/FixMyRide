<template>
  <div class="container">
    <h2>Repair Requests</h2>

    <form v-if="isOwner" class="request-form" @submit.prevent="createRequest">
      <div class="field">
        <label for="car_make">Car Make</label>
        <input id="car_make" v-model="form.car_make" required />
        <p v-if="errors.car_make" class="error-inline">{{ errors.car_make[0] }}</p>
      </div>
      <div class="field">
        <label for="car_model">Car Model</label>
        <input id="car_model" v-model="form.car_model" required />
        <p v-if="errors.car_model" class="error-inline">{{ errors.car_model[0] }}</p>
      </div>
      <div class="field">
        <label for="year">Year of Manufacture</label>
        <input id="year" type="number" min="1900" :max="currentYear" v-model.number="form.year" @input="onYearInput" />
        <p v-if="errors.year" class="error-inline">{{ errors.year[0] }}</p>
      </div>
      <div class="field">
        <label for="problem_type">Problem</label>
        <select id="problem_type" v-model="form.problem_type" required :class="{ invalid: errors.problem_type }">
          <option>Engine</option>
          <option>Electrical</option>
          <option>Brakes</option>
          <option>Transmission</option>
          <option>Suspension</option>
          <option>General Service</option>
          <option>Other</option>
        </select>
        <p v-if="errors.problem_type" class="error-inline">{{ errors.problem_type[0] }}</p>
      </div>
      <div class="field">
        <label for="phone_number">Owner Phone</label>
        <input id="phone_number" v-model="form.phone_number" @input="onPhoneInput" required placeholder="e.g. +256701234567" pattern="[+0-9\-\s()]{7,}" :class="{ invalid: errors.phone_number }" />
        <p v-if="errors.phone_number" class="error-inline">{{ errors.phone_number[0] }}</p>
      </div>
      <div class="field">
        <label><input type="checkbox" v-model="form.willing_to_pay" /> Willing to pay 30k call-out fee</label>
      </div>
      <div class="field">
        <label for="issue">Additional Details</label>
        <textarea id="issue" v-model="form.issue" placeholder="Optional"></textarea>
        <p v-if="errors.issue" class="error-inline">{{ errors.issue[0] }}</p>
      </div>
      <button type="submit" class="btn primary">Create Request</button>
      <p v-if="message" class="message">{{ message }}</p>
    </form>

    <div class="toolbar" v-if="isMechanic">
      <button :class="['btn', filter==='all' ? 'primary' : 'outline']" @click="setFilter('all')">All</button>
      <button :class="['btn', filter==='mine' ? 'primary' : 'outline']" @click="setFilter('mine')">Mine</button>
    </div>

    <div class="filters">
      <label>
        Car Make:
        <select v-model="selectedMake">
          <option value="">All</option>
          <option v-for="m in makes" :key="m" :value="m">{{ m }}</option>
        </select>
      </label>
      <label>
        Sort:
        <select v-model="sortBy">
          <option value="date_desc">Date (newest)</option>
          <option value="date_asc">Date (oldest)</option>
          <option value="make_az">Make A–Z</option>
        </select>
      </label>
    </div>

    <ul class="requests">
      <li v-for="request in visibleRequests" :key="request.id" class="request-item">
        <div class="request-head">
          <strong>{{ request.car_make }} {{ request.car_model }}<span v-if="request.year"> ({{ request.year }})</span></strong>
          <span class="status" :data-status="request.status">{{ request.status }}</span>
        </div>
        <div class="fields">
          <div><span class="label">Date:</span> <span class="value">{{ new Date(request.created_at).toLocaleString() }}</span></div>
          <div><span class="label">Problem:</span> <span class="value">{{ request.problem_type || '—' }}</span></div>
          <div><span class="label">Owner:</span> <span class="value">{{ request.owner_name || 'Unknown' }}<span v-if="request.phone_number">, {{ request.phone_number }}</span></span></div>
          <div><span class="label">Assigned to:</span> <span class="value">{{ request.assigned_to_name || 'Unassigned' }}</span></div>
          <div><span class="label">Call-out fee:</span> <span class="value">{{ request.willing_to_pay ? 'Yes (30k)' : 'No' }}</span></div>
          <div v-if="request.issue"><span class="label">Details:</span> <span class="value">{{ request.issue }}</span></div>
        </div>
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
        car_make: '',
        car_model: '',
        year: '',
        problem_type: 'General Service',
        phone_number: '',
        willing_to_pay: false,
        issue: ''
      },
      errors: {},
      message: '',
      role: null,
      filter: 'all',
      page: 1,
      perPage: 10,
      totalPages: 1,
      selectedMake: '',
      sortBy: 'date_desc'
    };
  },
  computed: {
    isOwner() { return this.role === 'owner'; },
    isMechanic() { return this.role === 'mechanic'; },
    currentYear() { return new Date().getFullYear(); },
    makes() {
      const set = new Set(this.requests.map(r => r.car_make).filter(Boolean));
      return Array.from(set).sort((a,b)=> String(a).localeCompare(String(b)));
    },
    visibleRequests() {
      let list = this.requests.slice();
      if (this.selectedMake) list = list.filter(r => r.car_make === this.selectedMake);
      if (this.sortBy === 'date_desc') list.sort((a,b)=> new Date(b.created_at)-new Date(a.created_at));
      else if (this.sortBy === 'date_asc') list.sort((a,b)=> new Date(a.created_at)-new Date(b.created_at));
      else if (this.sortBy === 'make_az') list.sort((a,b)=> String(a.car_make||'').localeCompare(String(b.car_make||'')));
      return list;
    }
  },
  async created() {
    this.role = (localStorage.getItem('role') || '').toLowerCase();
    await this.fetchRequests();
  },
  methods: {
    onPhoneInput() {
      const raw = this.form.phone_number || '';
      this.form.phone_number = raw.replace(/[^+0-9()\s-]/g, '');
    },
    onYearInput() {
      let y = parseInt(this.form.year, 10);
      if (Number.isFinite(y)) {
        if (y < 1900) y = 1900;
        if (y > this.currentYear) y = this.currentYear;
        this.form.year = y;
      }
    },
    async fetchRequests() {
      try {
        const params = new URLSearchParams();
        if (this.isMechanic && this.filter === 'mine') params.append('filter','mine');
        params.append('page', String(this.page));
        params.append('per_page', String(this.perPage));
        const response = await axios.get('/api/requests?' + params.toString());
        const payload = response.data;
        if (payload && payload.data && Array.isArray(payload.data)) {
          this.requests = payload.data;
          this.totalPages = payload.last_page || 1;
          this.page = payload.current_page || 1;
        } else if (Array.isArray(payload)) {
          this.requests = payload;
          this.totalPages = 1;
        }
      } catch (error) {
        console.error(error);
      }
    },
    setFilter(f) {
      this.filter = f;
      this.page = 1;
      this.fetchRequests();
    },
    async createRequest() {
      this.message = '';
      this.errors = {};
      try {
        await axios.post('/api/requests', this.form);
        this.message = 'Request created!';
        this.form = { car_make: '', car_model: '', year: '', problem_type: 'General Service', phone_number: '', willing_to_pay: false, issue: '' };
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
    },
    nextPage() { if (this.page < this.totalPages) { this.page++; this.fetchRequests(); } },
    prevPage() { if (this.page > 1) { this.page--; this.fetchRequests(); } }
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
input, textarea, select { width: 100%; padding: 8px; border: 1px solid var(--border); border-radius: 6px; }

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

.filters { display: flex; gap: 12px; align-items: center; margin: 10px 0 16px; }
.filters select { padding: 6px 8px; border: 1px solid var(--border); border-radius: 6px; }

.requests { list-style: none; padding: 0; }
.request-item { background: #fff; border: 1px solid var(--border); border-radius: 8px; padding: 12px; margin-bottom: 10px; }
.request-head { display: flex; justify-content: space-between; align-items: center; }
.fields { color: #374151; font-size: 0.95rem; margin: 6px 0 8px; display: grid; grid-template-columns: 1fr; gap: 4px; }
.fields .label { color: #6b7280; font-weight: 600; margin-right: 6px; }
.fields .value { color: #111827; }
.issue { white-space: pre-line; }
.status[data-status="accepted"] { color: #16a34a; }
.status[data-status="declined"] { color: #dc2626; }
.status[data-status="pending"] { color: var(--yellow); }
.actions { margin-top: 10px; }

.pager { display: flex; align-items: center; gap: 8px; margin-top: 12px; }
.pager .btn { padding: 6px 10px; }

.invalid { border-color: #dc2626 !important; }
</style>

