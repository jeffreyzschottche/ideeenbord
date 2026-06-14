<script setup lang="ts">
/*
  Shows an overview of ideas the user has liked or disliked.
  Fetches idea details based on the user's liked_posts and disliked_posts IDs.
*/

import { ref, onMounted, computed } from "vue";
import { useUserAuthStore } from "~/store/useUserAuthStore";
import { apiFetch } from "~/composables/adapter/useApi";

const auth = useUserAuthStore();
const likedIdeas = ref<any[]>([]);
const dislikedIdeas = ref<any[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const activeTab = ref<"liked" | "disliked">("liked");

onMounted(async () => {
  try {
    if (!auth.user) {
      await auth.initAuth();
    }

    const likedIds = auth.user?.liked_posts || [];
    const dislikedIds = auth.user?.disliked_posts || [];

    if (likedIds.length > 0) {
      likedIdeas.value = await apiFetch(`/ideas`, {
        method: "GET",
        params: { ids: likedIds.join(",") },
      });
    }

    if (dislikedIds.length > 0) {
      dislikedIdeas.value = await apiFetch(`/ideas`, {
        method: "GET",
        params: { ids: dislikedIds.join(",") },
      });
    }
  } catch (err: any) {
    error.value = err?.message || "Fout bij laden van ideeën";
  } finally {
    loading.value = false;
  }
});

const stats = computed(() => ({
  liked: likedIdeas.value.length,
  disliked: dislikedIdeas.value.length,
  total: likedIdeas.value.length + dislikedIdeas.value.length,
}));

const currentIdeas = computed(() =>
  activeTab.value === "liked" ? likedIdeas.value : dislikedIdeas.value
);
</script>

<template>
  <div class="mt-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-pink-400 to-purple-500 flex items-center justify-center">
          <i class="fa-solid fa-heart text-white"></i>
        </div>
        <div>
          <h2 class="text-xl font-bold text-gray-900">Jouw ratings</h2>
          <p class="text-sm text-gray-500">Ideeën die je hebt gewaardeerd</p>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div v-if="!loading && stats.total" class="grid grid-cols-3 gap-4 mb-6">
      <div class="stat-card stat-liked">
        <div class="stat-value">{{ stats.liked }}</div>
        <div class="stat-label">Geliked</div>
      </div>
      <div class="stat-card stat-disliked">
        <div class="stat-value">{{ stats.disliked }}</div>
        <div class="stat-label">Gedisliked</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">{{ stats.total }}</div>
        <div class="stat-label">Totaal</div>
      </div>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="text-center">
        <i class="fa-solid fa-spinner fa-spin text-3xl text-[var(--color-brand)] mb-3"></i>
        <p class="text-gray-500">Ratings laden...</p>
      </div>
    </div>

    <!-- Error message -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-600">
      {{ error }}
    </div>

    <!-- Content -->
    <div v-else-if="stats.total">
      <!-- Tabs -->
      <div class="tab-container mb-6">
        <button
          @click="activeTab = 'liked'"
          :class="['tab-btn', activeTab === 'liked' && 'active-liked']"
        >
          <i class="fa-solid fa-thumbs-up"></i>
          Geliked ({{ stats.liked }})
        </button>
        <button
          @click="activeTab = 'disliked'"
          :class="['tab-btn', activeTab === 'disliked' && 'active-disliked']"
        >
          <i class="fa-solid fa-thumbs-down"></i>
          Gedisliked ({{ stats.disliked }})
        </button>
      </div>

      <!-- Ideas List -->
      <div v-if="currentIdeas.length" class="space-y-3">
        <div v-for="idea in currentIdeas" :key="idea.id" class="rating-card">
          <div class="flex items-center gap-4">
            <!-- Rating indicator -->
            <div :class="['rating-indicator', activeTab === 'liked' ? 'liked' : 'disliked']">
              <i :class="['fa-solid', activeTab === 'liked' ? 'fa-thumbs-up' : 'fa-thumbs-down']"></i>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <NuxtLink
                :to="`/brands/${idea.brand?.slug}?idea=${idea.id}`"
                class="font-semibold text-gray-900 hover:text-[var(--color-brand)] transition line-clamp-1"
              >
                {{ idea.title }}
              </NuxtLink>
              <div class="flex items-center gap-3 mt-1 text-sm text-gray-500">
                <NuxtLink
                  v-if="idea.brand"
                  :to="`/brands/${idea.brand.slug}`"
                  class="flex items-center gap-1 hover:text-[var(--color-brand)] transition"
                >
                  <i class="fa-solid fa-store text-xs"></i>
                  {{ idea.brand.title }}
                </NuxtLink>
                <span class="flex items-center gap-1">
                  <i class="fa-solid fa-chart-bar text-xs"></i>
                  {{ idea.status }}
                </span>
              </div>
            </div>

            <!-- Arrow -->
            <NuxtLink
              :to="`/brands/${idea.brand?.slug}?idea=${idea.id}`"
              class="arrow-btn"
            >
              <i class="fa-solid fa-arrow-right"></i>
            </NuxtLink>
          </div>
        </div>
      </div>

      <!-- Empty tab state -->
      <div v-else class="empty-tab-state">
        <i :class="['fa-solid text-3xl mb-3', activeTab === 'liked' ? 'fa-thumbs-up text-green-300' : 'fa-thumbs-down text-red-300']"></i>
        <p class="text-gray-500">Nog geen ideeën {{ activeTab === 'liked' ? 'geliked' : 'gedisliked' }}.</p>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else class="empty-state">
      <div class="empty-icon">
        <i class="fa-solid fa-heart"></i>
      </div>
      <h3 class="text-lg font-semibold text-gray-900 mb-2">Nog geen ratings</h3>
      <p class="text-gray-500 mb-4">Je hebt nog geen ideeën gewaardeerd.</p>
      <NuxtLink to="/brands" class="cta inline-flex items-center gap-2">
        <i class="fa-solid fa-search"></i>
        Ontdek ideeën
      </NuxtLink>
    </div>
  </div>
</template>

<style scoped>
.stat-card {
  background: linear-gradient(135deg, rgba(249, 115, 22, 0.08) 0%, rgba(251, 191, 36, 0.08) 100%);
  border-radius: 12px;
  padding: 16px;
  text-align: center;
}

.stat-liked {
  background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(16, 185, 129, 0.1) 100%);
}

.stat-liked .stat-value {
  color: #16a34a;
}

.stat-disliked {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.1) 100%);
}

.stat-disliked .stat-value {
  color: #dc2626;
}

.stat-value {
  font-size: 24px;
  font-weight: 800;
  color: var(--color-brand);
}

.stat-label {
  font-size: 12px;
  color: #6b7280;
  margin-top: 2px;
}

.tab-container {
  display: flex;
  gap: 8px;
  background: #f3f4f6;
  padding: 4px;
  border-radius: 12px;
}

.tab-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 16px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  color: #6b7280;
  transition: all 0.2s ease;
}

.tab-btn:hover {
  color: #374151;
}

.tab-btn.active-liked {
  background: white;
  color: #16a34a;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.tab-btn.active-disliked {
  background: white;
  color: #dc2626;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.rating-card {
  background: white;
  border-radius: 12px;
  padding: 16px;
  border: 1px solid rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}

.rating-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
  border-color: rgba(0, 0, 0, 0.1);
}

.rating-indicator {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.rating-indicator.liked {
  background: linear-gradient(135deg, rgba(34, 197, 94, 0.15) 0%, rgba(16, 185, 129, 0.15) 100%);
  color: #16a34a;
}

.rating-indicator.disliked {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(220, 38, 38, 0.15) 100%);
  color: #dc2626;
}

.arrow-btn {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #f3f4f6;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #9ca3af;
  transition: all 0.2s ease;
}

.arrow-btn:hover {
  background: var(--color-brand);
  color: white;
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
  background: linear-gradient(135deg, rgba(236, 72, 153, 0.1) 0%, rgba(167, 139, 250, 0.1) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: #ec4899;
}
</style>
