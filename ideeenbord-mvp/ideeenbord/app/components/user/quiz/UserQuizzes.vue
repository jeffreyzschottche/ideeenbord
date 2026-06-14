<script setup lang="ts">
/*
  Fetches and displays current, past, and won quiz participations for a user.
  The username is extracted from the route.
  Shows active, won, and closed quiz entries in tabs.
*/

import { ref, onMounted, computed } from "vue";
import { useRoute } from "vue-router";
import { apiFetch } from "~/composables/adapter/useApi";

const route = useRoute();
const username = route.params.slug as string;

const currentQuizzes = ref<any[]>([]);
const pastQuizzes = ref<any[]>([]);
const wonQuizzes = ref<any[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const activeTab = ref<"current" | "won" | "past">("current");

onMounted(async () => {
  try {
    const data = await apiFetch<{ current: any[]; past: any[]; won?: any[] }>(
      `/users/${username}/quiz-submissions`
    );
    currentQuizzes.value = data.current || [];
    pastQuizzes.value = data.past || [];
    wonQuizzes.value = data.won || [];
  } catch (e: any) {
    error.value = e.message || "Quizdata ophalen mislukt.";
  } finally {
    loading.value = false;
  }
});

const stats = computed(() => ({
  current: currentQuizzes.value.length,
  won: wonQuizzes.value.length,
  past: pastQuizzes.value.length,
  total: currentQuizzes.value.length + pastQuizzes.value.length + wonQuizzes.value.length,
}));

const currentList = computed(() => {
  if (activeTab.value === "current") return currentQuizzes.value;
  if (activeTab.value === "won") return wonQuizzes.value;
  return pastQuizzes.value;
});
</script>

<template>
  <div class="mt-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-400 to-purple-500 flex items-center justify-center">
          <i class="fa-solid fa-trophy text-white"></i>
        </div>
        <div>
          <h2 class="text-xl font-bold text-gray-900">Winacties</h2>
          <p class="text-sm text-gray-500">Jouw deelnames aan winacties</p>
        </div>
      </div>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="text-center">
        <i class="fa-solid fa-spinner fa-spin text-3xl text-[var(--color-brand)] mb-3"></i>
        <p class="text-gray-500">Winacties laden...</p>
      </div>
    </div>

    <!-- Error message -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-600">
      {{ error }}
    </div>

    <!-- Content -->
    <div v-else-if="stats.total">
      <!-- Won Prizes Banner -->
      <div v-if="stats.won > 0" class="won-banner mb-6">
        <div class="flex items-center gap-4">
          <div class="won-icon">
            <i class="fa-solid fa-gift"></i>
          </div>
          <div class="flex-1">
            <h3 class="font-bold text-white">Gefeliciteerd!</h3>
            <p class="text-white/80 text-sm">Je hebt {{ stats.won }} {{ stats.won === 1 ? 'winactie' : 'winacties' }} gewonnen</p>
          </div>
          <button @click="activeTab = 'won'" class="won-view-btn">
            Bekijk
            <i class="fa-solid fa-arrow-right"></i>
          </button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="stat-card stat-current">
          <div class="stat-value">{{ stats.current }}</div>
          <div class="stat-label">Actief</div>
        </div>
        <div class="stat-card stat-won">
          <div class="stat-value">{{ stats.won }}</div>
          <div class="stat-label">Gewonnen</div>
        </div>
        <div class="stat-card stat-past">
          <div class="stat-value">{{ stats.past }}</div>
          <div class="stat-label">Afgelopen</div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="tab-container mb-6">
        <button
          @click="activeTab = 'current'"
          :class="['tab-btn', activeTab === 'current' && 'active']"
        >
          <i class="fa-solid fa-play"></i>
          Actief ({{ stats.current }})
        </button>
        <button
          @click="activeTab = 'won'"
          :class="['tab-btn', activeTab === 'won' && 'active-won']"
        >
          <i class="fa-solid fa-trophy"></i>
          Gewonnen ({{ stats.won }})
        </button>
        <button
          @click="activeTab = 'past'"
          :class="['tab-btn', activeTab === 'past' && 'active']"
        >
          <i class="fa-solid fa-clock-rotate-left"></i>
          Afgelopen ({{ stats.past }})
        </button>
      </div>

      <!-- Quiz List -->
      <div v-if="currentList.length" class="space-y-3">
        <div v-for="quiz in currentList" :key="quiz.id" :class="['quiz-card', activeTab === 'won' && 'quiz-card-won']">
          <div class="flex items-center gap-4">
            <!-- Status indicator -->
            <div :class="['quiz-indicator', `quiz-${activeTab}`]">
              <i v-if="activeTab === 'current'" class="fa-solid fa-play"></i>
              <i v-else-if="activeTab === 'won'" class="fa-solid fa-trophy"></i>
              <i v-else class="fa-solid fa-check"></i>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <h3 class="font-semibold text-gray-900 line-clamp-1">{{ quiz.title }}</h3>
              <div class="flex items-center gap-3 mt-1 text-sm text-gray-500">
                <NuxtLink
                  v-if="quiz.brand"
                  :to="`/brands/${quiz.brand.slug || quiz.brand.title}`"
                  class="flex items-center gap-1 hover:text-[var(--color-brand)] transition"
                >
                  <i class="fa-solid fa-store text-xs"></i>
                  {{ quiz.brand.title }}
                </NuxtLink>
                <span v-if="activeTab === 'won'" class="flex items-center gap-1 text-yellow-600">
                  <i class="fa-solid fa-gift text-xs"></i>
                  Prijs gewonnen!
                </span>
              </div>
            </div>

            <!-- Badge / Action -->
            <span v-if="activeTab === 'won'" class="won-badge">
              <i class="fa-solid fa-star"></i>
              Winnaar
            </span>
            <span v-else-if="activeTab === 'current'" class="status-badge active">
              <i class="fa-solid fa-circle text-[6px]"></i>
              Actief
            </span>
            <span v-else class="status-badge closed">
              Gesloten
            </span>
          </div>
        </div>
      </div>

      <!-- Empty tab state -->
      <div v-else class="empty-tab-state">
        <i v-if="activeTab === 'current'" class="fa-solid fa-play text-3xl text-blue-300 mb-3"></i>
        <i v-else-if="activeTab === 'won'" class="fa-solid fa-trophy text-3xl text-yellow-300 mb-3"></i>
        <i v-else class="fa-solid fa-clock-rotate-left text-3xl text-gray-300 mb-3"></i>
        <p class="text-gray-500">
          <span v-if="activeTab === 'current'">Geen actieve winacties.</span>
          <span v-else-if="activeTab === 'won'">Nog geen winacties gewonnen.</span>
          <span v-else>Geen afgelopen winacties.</span>
        </p>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else class="empty-state">
      <div class="empty-icon">
        <i class="fa-solid fa-trophy"></i>
      </div>
      <h3 class="text-lg font-semibold text-gray-900 mb-2">Nog geen deelnames</h3>
      <p class="text-gray-500 mb-4">Je hebt nog niet meegedaan aan winacties.</p>
      <NuxtLink to="/brands" class="cta inline-flex items-center gap-2">
        <i class="fa-solid fa-search"></i>
        Ontdek winacties
      </NuxtLink>
    </div>
  </div>
</template>

<style scoped>
.won-banner {
  background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 4px 20px rgba(249, 115, 22, 0.25);
}

.won-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 20px;
}

.won-view-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  background: white;
  color: #f97316;
  border-radius: 10px;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.2s ease;
}

.won-view-btn:hover {
  transform: scale(1.05);
}

.stat-card {
  background: #f9fafb;
  border-radius: 12px;
  padding: 16px;
  text-align: center;
}

.stat-current {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.1) 100%);
}

.stat-current .stat-value {
  color: #2563eb;
}

.stat-won {
  background: linear-gradient(135deg, rgba(251, 191, 36, 0.15) 0%, rgba(245, 158, 11, 0.15) 100%);
}

.stat-won .stat-value {
  color: #d97706;
}

.stat-past {
  background: linear-gradient(135deg, rgba(107, 114, 128, 0.1) 0%, rgba(75, 85, 99, 0.1) 100%);
}

.stat-past .stat-value {
  color: #4b5563;
}

.stat-value {
  font-size: 24px;
  font-weight: 800;
}

.stat-label {
  font-size: 12px;
  color: #6b7280;
  margin-top: 2px;
}

.tab-container {
  display: flex;
  gap: 6px;
  background: #f3f4f6;
  padding: 4px;
  border-radius: 12px;
  overflow-x: auto;
}

.tab-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 10px 12px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  color: #6b7280;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.tab-btn:hover {
  color: #374151;
}

.tab-btn.active {
  background: white;
  color: #2563eb;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.tab-btn.active-won {
  background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
  color: white;
  box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
}

.quiz-card {
  background: white;
  border-radius: 12px;
  padding: 16px;
  border: 1px solid rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}

.quiz-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
  border-color: rgba(0, 0, 0, 0.1);
}

.quiz-card-won {
  background: linear-gradient(135deg, rgba(251, 191, 36, 0.05) 0%, rgba(245, 158, 11, 0.05) 100%);
  border-color: rgba(245, 158, 11, 0.2);
}

.quiz-indicator {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.quiz-current {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(37, 99, 235, 0.15) 100%);
  color: #2563eb;
}

.quiz-won {
  background: linear-gradient(135deg, rgba(251, 191, 36, 0.2) 0%, rgba(245, 158, 11, 0.2) 100%);
  color: #d97706;
}

.quiz-past {
  background: #f3f4f6;
  color: #6b7280;
}

.won-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 6px 12px;
  background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
  color: white;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
}

.status-badge.active {
  background: #dbeafe;
  color: #1e40af;
}

.status-badge.closed {
  background: #f3f4f6;
  color: #6b7280;
}

.empty-tab-state {
  text-align: center;
  padding: 32px;
  background: #f9fafb;
  border-radius: 12px;
}

.empty-state {
  text-align: center;
  padding: 48px 24px;
  background: #f9fafb;
  border-radius: 16px;
}

.empty-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 16px;
  border-radius: 50%;
  background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(124, 58, 237, 0.1) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: #8b5cf6;
}
</style>
