<template>
  <div class="home">
    <section class="hero">
      <div class="hero-copy">
        <h1>Keep Your Ride Running Smooth</h1>
        <p>Match owners with mechanics quickly. Create requests, assign work, and track status — all in one place.</p>
        <div class="cta">
          <router-link class="btn primary" to="/requests">Get Started</router-link>
          <router-link class="btn outline" to="/register">Join Now</router-link>
        </div>
      </div>
      <div class="hero-art">
        <img src="/mechanic.png" alt="Mechanic" />
        <img class="alt" src="/mechanic2.png" alt="Mechanic at work" />
      </div>
    </section>
  </div>
  </template>

<script>
export default {
  name: 'Home',
};
</script>

<style scoped>
:root { --blue: #2563eb; --yellow: #fbbf24; --border: #e5e7eb; --text: #1f2937; }
.home { padding: 24px; }
.hero { display: grid; grid-template-columns: 1.2fr 1fr; gap: 20px; align-items: center; }
.hero-copy h1 { color: var(--blue); margin: 0 0 10px; font-size: 32px; }
.hero-copy p { color: #374151; margin: 0 0 16px; }
.cta { display: flex; gap: 10px; }
.btn { border: 1px solid var(--blue); color: var(--blue); background: #fff; padding: 10px 12px; border-radius: 8px; text-decoration: none; font-weight: 600; }
.btn.primary { background: var(--blue); color: #fff; }
.btn.outline:hover { background: #eef2ff; }
.hero-art { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.hero-art img { width: 100%; border-radius: 12px; border: 1px solid var(--border); background: #fff; }
.hero-art img.alt { transform: translateY(10px); }
@media (max-width: 800px) { .hero { grid-template-columns: 1fr; } }
</style>
