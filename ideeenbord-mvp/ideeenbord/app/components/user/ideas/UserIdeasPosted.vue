<script setup lang="ts">
/*
  Fetches and displays all ideas posted by a specific user.
  The username is taken from the route (slug).
  Shows loading and error states, and triggers a UI message on failure.
*/

import { ref, onMounted, computed } from "vue";
import { useRoute } from "vue-router";
import { apiFetch } from "~/composables/adapter/useApi";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";
import { ideaService } from "~/services/api/ideas/ideaService";

const route = useRoute();
const username = route.params.slug as string;
const { triggerByKey } = useResponseDisplay();

const ideas = ref<any[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);

onMounted(async () => {
  try {
    const ideaData: any = await apiFetch(`/users/${username}/ideas`);
    ideas.value = ideaData;
  } catch (e: any) {
    triggerByKey("ideas-fetch-failed");
  } finally {
    loading.value = false;
  }
});

async function deleteIdea(id: number) {
  if (!confirm("Weet je zeker dat je dit idee wilt verwijderen?")) return;

  try {
    await ideaService.deleteIdea(id);
    ideas.value = ideas.value.filter((i) => i.id !== id);
    triggerByKey("idea-deleted");
  } catch (e) {
    triggerByKey("idea-delete-failed");
  }
}

function getStatusInfo(status: string) {
  const statusMap: Record<string, { label: string; class: string; icon: string }> = {
    pending: { label: "In afwachting", class: "status-pending", icon: "fa-clock" },
    approved: { label: "Goedgekeurd", class: "status-approved", icon: "fa-check" },
    rejected: { label: "Afgewezen", class: "status-rejected", icon: "fa-times" },
    in_progress: { label: "In ontwikkeling", class: "status-progress", icon: "fa-gear" },
    implemented: { label: "Geïmplementeerd", class: "status-implemented", icon: "fa-star" },
  };
  return statusMap[status] || { label: status, class: "status-pending", icon: "fa-question" };
}

const stats = computed(() => ({
  total: ideas.value.length,
  totalLikes: ideas.value.reduce((sum, i) => sum + (i.likes || 0), 0),
  approved: ideas.value.filter((i) => i.status === "approved" || i.status === "implemented").length,
}));
</script>

<template>
  <div class="mt-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center">
          <i class="fa-solid fa-lightbulb text-white"></i>
        </div>
        <div>
          <h2 class="text-xl font-bold text-gray-900">Jouw ideeën</h2>
          <p class="text-sm text-gray-500">Ideeën die je hebt gedeeld met merken</p>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div v-if="!loading && ideas.length" class="grid grid-cols-3 gap-4 mb-6">
      <div class="stat-card">
        <div class="stat-value">{{ stats.total }}</div>
        <div class="stat-label">Ideeën</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">{{ stats.totalLikes }}</div>
        <div class="stat-label">Likes</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">{{ stats.approved }}</div>
        <div class="stat-label">Geaccepteerd</div>
      </div>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="text-center">
        <i class="fa-solid fa-spinner fa-spin text-3xl text-[var(--color-brand)] mb-3"></i>
        <p class="text-gray-500">Ideeën laden...</p>
      </div>
    </div>

    <!-- Error message -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-600">
      {{ error }}
    </div>

    <!-- Ideas Grid -->
    <div v-else-if="ideas.length" class="space-y-4">
      <div v-for="idea in ideas" :key="idea.id" class="idea-card">
        <div class="flex items-start gap-4">
          <!-- Brand Logo -->
          <div class="brand-logo-wrap shrink-0">
            <img
              v-if="idea.brand?.logo_path"
              :src="idea.brand.logo_path"
              :alt="idea.brand?.title"
              class="w-full h-full object-contain"
            />
            <i v-else class="fa-solid fa-building text-gray-400"></i>
          </div>

          <!-- Content -->
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-3 mb-2">
              <NuxtLink
                :to="`/brands/${idea.brand?.slug}?idea=${idea.id}`"
                class="font-bold text-gray-900 hover:text-[var(--color-brand)] transition line-clamp-1"
              >
                {{ idea.title }}
              </NuxtLink>
              <span :class="['status-badge', getStatusInfo(idea.status).class]">
                <i :class="['fa-solid', getStatusInfo(idea.status).icon]"></i>
                {{ getStatusInfo(idea.status).label }}
              </span>
            </div>

            <p class="text-sm text-gray-600 line-clamp-2 mb-3">{{ idea.description }}</p>

            <!-- Meta -->
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-4 text-sm">
                <NuxtLink
                  :to="`/brands/${idea.brand?.slug}`"
                  class="flex items-center gap-1.5 text-gray-500 hover:text-[var(--color-brand)] transition"
                >
                  <i class="fa-solid fa-store text-xs"></i>
                  {{ idea.brand?.title || idea.brand?.slug }}
                </NuxtLink>
                <span class="flex items-center gap-1.5 text-green-600">
                  <i class="fa-solid fa-thumbs-up text-xs"></i>
                  {{ idea.likes || 0 }}
                </span>
                <span class="flex items-center gap-1.5 text-red-400">
                  <i class="fa-solid fa-thumbs-down text-xs"></i>
                  {{ idea.dislikes || 0 }}
                </span>
              </div>

              <button
                class="delete-btn"
                @click="deleteIdea(idea.id)"
                title="Verwijder dit idee"
              >
                <i class="fa-solid fa-trash-alt"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else class="empty-state">
      <div class="empty-icon">
        <i class="fa-solid fa-lightbulb"></i>
      </div>
      <h3 class="text-lg font-semibold text-gray-900 mb-2">Nog geen ideeën</h3>
      <p class="text-gray-500 mb-4">Je hebt nog geen ideeën gedeeld met merken.</p>
      <NuxtLink to="/brands" class="cta inline-flex items-center gap-2">
        <i class="fa-solid fa-plus"></i>
        Deel je eerste idee
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

.idea-card {
  background: white;
  border-radius: 16px;
  padding: 20px;
  border: 1px solid rgba(0, 0, 0, 0.05);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  transition: all 0.2s ease;
}

.idea-card:hover {
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  border-color: var(--color-brand);
}

.brand-logo-wrap {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: #f9fafb;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
}

.status-pending {
  background: #fef3c7;
  color: #92400e;
}

.status-approved {
  background: #d1fae5;
  color: #065f46;
}

.status-rejected {
  background: #fee2e2;
  color: #991b1b;
}

.status-progress {
  background: #dbeafe;
  color: #1e40af;
}

.status-implemented {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  color: #92400e;
}

.delete-btn {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: #fef2f2;
  color: #ef4444;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  transition: all 0.2s ease;
  opacity: 0.7;
}

.delete-btn:hover {
  background: #fee2e2;
  opacity: 1;
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
  background: linear-gradient(135deg, rgba(249, 115, 22, 0.1) 0%, rgba(251, 191, 36, 0.1) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: var(--color-brand);
}
</style>
