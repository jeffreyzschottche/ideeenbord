<script setup lang="ts">
/*
  Displays the user's inbox containing notifications about quizzes and ideas.
  Modern tabbed interface with notification cards.
*/

import { useUserAuthStore } from "~/store/useUserAuthStore";
import { onMounted, ref, computed } from "vue";
import { useRoute } from "vue-router";
import { apiFetch } from "~/composables/adapter/useApi";
import type { UserNotification } from "~/types/user";

const route = useRoute();
const username = route.params.slug as string;
const auth = useUserAuthStore();

const loaded = ref(false);
const notifications = ref<UserNotification[]>([]);
const activeTab = ref<"all" | "ideas" | "quizzes">("all");

onMounted(async () => {
  if (!auth.user || !auth.token) {
    await auth.initAuth();
  }

  try {
    const data = await apiFetch<UserNotification[] | { notifications: UserNotification[] }>(
      `/users/${username}/notifications`
    );

    if (Array.isArray(data)) {
      notifications.value = data;
    } else if (data?.notifications) {
      notifications.value = data.notifications;
    }
  } catch {
    notifications.value = [];
  } finally {
    loaded.value = true;
  }
});

const filteredNotifications = computed(() => {
  if (activeTab.value === "all") return notifications.value;
  if (activeTab.value === "ideas") {
    return notifications.value.filter((n: any) =>
      ["idea_status", "idea_like", "idea"].includes(n.type)
    );
  }
  return notifications.value.filter((n: any) => n.type === "quiz");
});

const stats = computed(() => ({
  all: notifications.value.length,
  ideas: notifications.value.filter((n: any) =>
    ["idea_status", "idea_like", "idea"].includes(n.type)
  ).length,
  quizzes: notifications.value.filter((n: any) => n.type === "quiz").length,
}));

function getNotificationIcon(type: string) {
  if (type === "quiz") return "fa-trophy";
  if (type === "idea_like") return "fa-heart";
  if (type === "idea_status") return "fa-lightbulb";
  return "fa-bell";
}

function getNotificationColor(type: string) {
  if (type === "quiz") return "notification-quiz";
  if (type === "idea_like") return "notification-like";
  if (type === "idea_status") return "notification-status";
  return "notification-default";
}

function formatTime(timestamp: string) {
  const date = new Date(timestamp);
  const now = new Date();
  const diff = now.getTime() - date.getTime();

  const minutes = Math.floor(diff / 60000);
  const hours = Math.floor(diff / 3600000);
  const days = Math.floor(diff / 86400000);

  if (minutes < 1) return "Zojuist";
  if (minutes < 60) return `${minutes} min geleden`;
  if (hours < 24) return `${hours} uur geleden`;
  if (days < 7) return `${days} dagen geleden`;
  return date.toLocaleDateString("nl-NL");
}
</script>

<template>
  <div class="mt-8" v-if="loaded && auth.user">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center">
          <i class="fa-solid fa-inbox text-white"></i>
        </div>
        <div>
          <h2 class="text-xl font-bold text-gray-900">Inbox</h2>
          <p class="text-sm text-gray-500">Je meldingen en updates</p>
        </div>
      </div>
      <span v-if="stats.all > 0" class="notification-count">
        {{ stats.all }}
      </span>
    </div>

    <!-- Content -->
    <div v-if="stats.all">
      <!-- Tabs -->
      <div class="tab-container mb-6">
        <button
          @click="activeTab = 'all'"
          :class="['tab-btn', activeTab === 'all' && 'active']"
        >
          <i class="fa-solid fa-bell"></i>
          Alles ({{ stats.all }})
        </button>
        <button
          @click="activeTab = 'ideas'"
          :class="['tab-btn', activeTab === 'ideas' && 'active']"
        >
          <i class="fa-solid fa-lightbulb"></i>
          Ideeën ({{ stats.ideas }})
        </button>
        <button
          @click="activeTab = 'quizzes'"
          :class="['tab-btn', activeTab === 'quizzes' && 'active']"
        >
          <i class="fa-solid fa-trophy"></i>
          Quizzen ({{ stats.quizzes }})
        </button>
      </div>

      <!-- Notifications List -->
      <div v-if="filteredNotifications.length" class="space-y-3">
        <div
          v-for="notification in filteredNotifications"
          :key="notification.timestamp"
          :class="['notification-card', getNotificationColor(notification.type)]"
        >
          <div class="flex items-start gap-4">
            <!-- Icon -->
            <div :class="['notification-icon', `icon-${notification.type}`]">
              <i :class="['fa-solid', getNotificationIcon(notification.type)]"></i>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <p class="text-gray-900">{{ notification.message }}</p>
              <span class="text-xs text-gray-400 mt-1 block">
                {{ formatTime(notification.timestamp) }}
              </span>
            </div>

            <!-- Type badge -->
            <span :class="['type-badge', `badge-${notification.type}`]">
              {{ notification.type === 'quiz' ? 'Quiz' : 'Idee' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Empty tab state -->
      <div v-else class="empty-tab-state">
        <i class="fa-solid fa-bell-slash text-3xl text-gray-300 mb-3"></i>
        <p class="text-gray-500">Geen meldingen in deze categorie.</p>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else class="empty-state">
      <div class="empty-icon">
        <i class="fa-solid fa-inbox"></i>
      </div>
      <h3 class="text-lg font-semibold text-gray-900 mb-2">Je inbox is leeg</h3>
      <p class="text-gray-500">Je ontvangt hier meldingen over je ideeën en winacties.</p>
    </div>
  </div>

  <!-- Loading state -->
  <div v-else-if="!loaded" class="mt-8 flex items-center justify-center py-12">
    <div class="text-center">
      <i class="fa-solid fa-spinner fa-spin text-3xl text-[var(--color-brand)] mb-3"></i>
      <p class="text-gray-500">Inbox laden...</p>
    </div>
  </div>
</template>

<style scoped>
.notification-count {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 28px;
  height: 28px;
  padding: 0 8px;
  background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
  color: white;
  border-radius: 14px;
  font-size: 13px;
  font-weight: 700;
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
  color: var(--color-brand);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.notification-card {
  background: white;
  border-radius: 12px;
  padding: 16px;
  border: 1px solid rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}

.notification-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
  border-color: rgba(0, 0, 0, 0.1);
}

.notification-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.icon-quiz {
  background: linear-gradient(135deg, rgba(139, 92, 246, 0.15) 0%, rgba(124, 58, 237, 0.15) 100%);
  color: #8b5cf6;
}

.icon-idea_like {
  background: linear-gradient(135deg, rgba(236, 72, 153, 0.15) 0%, rgba(219, 39, 119, 0.15) 100%);
  color: #ec4899;
}

.icon-idea_status {
  background: linear-gradient(135deg, rgba(249, 115, 22, 0.15) 0%, rgba(234, 88, 12, 0.15) 100%);
  color: #f97316;
}

.icon-idea {
  background: linear-gradient(135deg, rgba(249, 115, 22, 0.15) 0%, rgba(234, 88, 12, 0.15) 100%);
  color: #f97316;
}

.type-badge {
  display: inline-flex;
  align-items: center;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
}

.badge-quiz {
  background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(124, 58, 237, 0.1) 100%);
  color: #7c3aed;
}

.badge-idea_like,
.badge-idea_status,
.badge-idea {
  background: linear-gradient(135deg, rgba(249, 115, 22, 0.1) 0%, rgba(234, 88, 12, 0.1) 100%);
  color: #ea580c;
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
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(79, 70, 229, 0.1) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: #6366f1;
}
</style>
