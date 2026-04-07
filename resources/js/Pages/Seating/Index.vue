<template>
  <div class="page">
    <div class="bg-grid" />
    <div class="bg-blob blob-1" />
    <div class="bg-blob blob-2" />

    <div class="page-inner">
      <header class="page-header">
        <div class="header-eyebrow">Game Night</div>
        <h1 class="header-title">Seating Planner</h1>
        <p class="header-sub">
          Select who's coming and which games are on the table —
          we'll arrange the best possible groups automatically.
        </p>
      </header>

      <div v-if="loadingData" class="loading-state">
        <div class="spinner" />
        <span>Loading your data…</span>
      </div>

      <div v-else-if="error" class="error-banner">
        {{ error }}
      </div>

      <template v-else>
        <div v-if="!result" class="selection-layout">
          <div class="selectors-row">
            <FriendSelector
              :friends="friends"
              :selected-ids="selectedFriends"
              @toggle="toggleFriend"
              @select-all="selectAllFriends"
              @clear-all="clearFriends"
            />
            <GameSelector
              :games="games"
              :selected-ids="selectedGames"
              @toggle="toggleGame"
              @select-all="selectAllGames"
              @clear-all="clearGames"
            />
          </div>

          <div class="action-footer">
            <div class="selection-summary">
              <span>{{ selectedFriends.length }} friends</span>
              <span class="dot">·</span>
              <span>{{ selectedGames.length }} games selected</span>
            </div>
            <button
              class="arrange-btn"
              :disabled="!canArrange || loading"
              @click="arrange"
            >
              <span v-if="loading" class="btn-spinner" />
              <span v-else>✦</span>
              {{ loading ? 'Arranging…' : 'Arrange Seating' }}
            </button>
          </div>
        </div>

        <SeatingResults
          v-else
          :result="result"
          @reset="reset"
        />
      </template>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useSeating } from '../composables/useSeating'
import FriendSelector  from '../components/FriendSelector.vue'
import GameSelector    from '../components/GameSelector.vue'
import SeatingResults  from '../components/SeatingResults.vue'

const {
  friends, games,
  selectedFriends, selectedGames,
  result, loading, loadingData, error,
  canArrange,
  loadData, arrange, reset,
  toggleFriend, toggleGame,
  selectAllFriends, clearFriends,
  selectAllGames, clearGames,
} = useSeating()

onMounted(loadData)
</script>

<style>

:root {
    --bg:             #f6f5f2;
    --surface:        #ffffff;
    --surface-raised: #fafaf8;
    --surface-hover:  #f2f1ee;
    --border:         #e8e6e1;
    --border-strong:  #ccc9c3;

    --text-primary:   #1a1917;
    --text-secondary: #4a4844;
    --text-muted:     #9a9590;

    --accent:         #2d6a4f;
    --accent-ghost:   #edf4f0;
    --accent-border:  #b7d9c8;
    --accent-text:    #ffffff;

    --accent2:        #7c4dff;
    --accent2-ghost:  #f3eeff;
    --accent2-border: #d0b9ff;

    --avatar-bg:      #2d6a4f;
    --avatar-text:    #ffffff;

    --pill-bg:        #fff3e0;
    --pill-text:      #bf6000;

    --warning-bg:     #fff8e7;
    --warning-border: #ffd97d;
    --warning-text:   #7d5200;
}
</style>

<style scoped>
.page {
    min-height: 100vh;
    background: var(--bg);
    position: relative;
    overflow-x: hidden;
    font-family: 'DM Sans', system-ui, sans-serif;
}

.bg-grid {
    position: fixed;
    inset: 0;
    background-image: radial-gradient(circle, #ccc8c0 1px, transparent 1px);
    background-size: 28px 28px;
    opacity: 0.35;
    pointer-events: none;
}

.bg-blob {
    position: fixed;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.18;
    pointer-events: none;
}
.blob-1 {
    width: 600px; height: 600px;
    background: #2d6a4f;
    top: -200px; left: -150px;
}
.blob-2 {
    width: 500px; height: 500px;
    background: #7c4dff;
    bottom: -100px; right: -100px;
}

.page-inner {
    position: relative;
    max-width: 960px;
    margin: 0 auto;
    padding: 60px 24px 80px;
    display: flex;
    flex-direction: column;
    gap: 40px;
}

.page-header {
    text-align: center;
}

.header-eyebrow {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: var(--accent);
    margin-bottom: 10px;
}

.header-title {
    font-family: 'Syne', sans-serif;
    font-size: clamp(2.2rem, 5vw, 3.4rem);
    font-weight: 800;
    color: var(--text-primary);
    margin: 0 0 14px;
    letter-spacing: -0.02em;
    line-height: 1.05;
}

.header-sub {
    max-width: 500px;
    margin: 0 auto;
    font-size: 1rem;
    color: var(--text-muted);
    line-height: 1.6;
}

.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    padding: 80px 0;
    color: var(--text-muted);
}

.spinner {
    width: 32px; height: 32px;
    border: 3px solid var(--border);
    border-top-color: var(--accent);
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.error-banner {
    background: #fff0f0;
    border: 1px solid #ffcdd2;
    color: #c62828;
    border-radius: 12px;
    padding: 16px 20px;
    font-size: 0.9rem;
}

.selectors-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    min-height: 380px;
}

@media (max-width: 640px) {
    .selectors-row { grid-template-columns: 1fr; }
}

.action-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 16px 24px;
}

.selection-summary {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.88rem;
    color: var(--text-muted);
}

.dot { opacity: 0.4; }

.arrange-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    background: var(--text-primary);
    color: white;
    border: none;
    border-radius: 10px;
    padding: 12px 28px;
    font-size: 0.92rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    cursor: pointer;
    transition: opacity 0.15s, transform 0.1s;
}

.arrange-btn:hover:not(:disabled) { opacity: 0.85; transform: translateY(-1px); }
.arrange-btn:active:not(:disabled) { transform: translateY(0); }
.arrange-btn:disabled { opacity: 0.4; cursor: not-allowed; }

.btn-spinner {
    width: 14px; height: 14px;
    border: 2px solid rgba(255,255,255,0.4);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}
</style>
