<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import { apiFetch } from "~/composables/useApi";
import type { Idea, IdeaNotification } from "~/types/idea";

const route = useRoute();
const username = route.params.slug as string;

const notifications = ref<IdeaNotification[]>([]);
const loading = ref(true);

onMounted(async () => {
  try {
    const data = await apiFetch<{ notifications: IdeaNotification[] }>(
      `/users/${username}/notifications`
    );
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
    <ul v-if="notifications.length">
      <li
        v-for="n in notifications"
        :key="n.timestamp"
        class="mb-2 border-b pb-2"
      >
        {{ n.message }}
        <br />
        <small class="text-gray-500">{{
          new Date(n.timestamp).toLocaleString()
        }}</small>
      </li>
    </ul>
    <p v-else>Geen meldingen over jouw ideeën.</p>
  </div>
</template>
