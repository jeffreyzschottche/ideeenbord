<script setup lang="ts">
/*
  Fetches and displays idea-related notifications for a user.
  Filters only relevant types ("idea_status" and "idea_like").
  Triggered on component mount, based on the username in the route.
*/

import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import { apiFetch } from "~/composables/useApi";
import type { Idea, IdeaNotification } from "~/types/idea";

// Extract username (slug) from current route
const route = useRoute();
const username = route.params.slug as string;

// Reactive state for notifications and loading state
const notifications = ref<IdeaNotification[]>([]);
const loading = ref(true);

// Fetch notifications once the component is mounted
onMounted(async () => {
  try {
    const data = await apiFetch<{ notifications: IdeaNotification[] }>(
      `/users/${username}/notifications`
    );

    // Only keep relevant notification types
    notifications.value = data.notifications.filter((n: any) =>
      ["idea_status", "idea_like"].includes(n.type)
    );
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div>
    <h3 class="font-semibold text-lg mb-2">📝 Idee-meldingen</h3>

    <!-- Display list of filtered notifications -->
    <ul v-if="notifications.length">
      <li
        v-for="n in notifications"
        :key="n.timestamp"
        class="mb-2 border-b pb-2"
      >
        {{ n.message }}
        <br />
        <small class="text-gray-500">
          {{ new Date(n.timestamp).toLocaleString() }}
        </small>
      </li>
    </ul>

    <!-- Fallback message if no notifications found -->
    <p v-else>Geen meldingen over jouw ideeën.</p>
  </div>
</template>
