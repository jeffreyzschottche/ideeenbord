<script setup lang="ts">
import { onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import { apiFetch } from "~/composables/useApi";

const route = useRoute();
const username = route.params.slug as string;

const notifications = ref([]);
const loading = ref(true);

onMounted(async () => {
  try {
    const data: any = await apiFetch(`/users/${username}/notifications`);
    notifications.value = data.notifications.filter(
      (n: any) => n.type === "quiz"
    );
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div>
    <h3 class="font-semibold text-lg mb-2">Quizmeldingen</h3>
    <ul v-if="notifications.length">
      <li v-for="n in notifications" :key="n.timestamp">🧠 {{ n.message }}</li>
    </ul>
    <p v-else>Geen quizmeldingen</p>
  </div>
</template>
