<script setup lang="ts">
/*
  Fetches and displays quiz-related notifications for the current user.
  Filters out only notifications of type "quiz", based on username from route.
*/

import { onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import { apiFetch } from "~/composables/adapter/useApi";
import type { UserNotification } from "~/types/user";

// Get the username (slug) from the current route
const route = useRoute();
const username = route.params.slug as string;

// State for quiz notifications and loading status
const notifications = ref<UserNotification[]>([]);
const loading = ref(true);

// Load quiz-related notifications on mount
onMounted(async () => {
  try {
    const data: UserNotification[] = await apiFetch(
      `/users/${username}/notifications`
    );

    // Only keep quiz notifications
    notifications.value = data.filter((n: any) => n.type === "quiz");
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div>
    <h3 class="font-semibold text-lg mb-2">Quizmeldingen</h3>

    <!-- Show list if there are any quiz notifications -->
    <ul v-if="notifications.length">
      <li v-for="n in notifications" :key="n.timestamp">🧠 {{ n.message }}</li>
    </ul>

    <!-- Fallback if no notifications available -->
    <p v-else>Geen quizmeldingen</p>
  </div>
</template>
