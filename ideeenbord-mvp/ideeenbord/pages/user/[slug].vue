<script setup lang="ts">
import { onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "~/store/auth";

const auth = useAuthStore();
const route = useRoute();
const router = useRouter();

const routeUsername = route.params.slug; // let op: jouw bestand heet [slug].vue

onMounted(async () => {
  // ✅ Initieel auth ophalen
  if (!auth.user || !auth.token) {
    await auth.initAuth();
  }

  const currentUsername = auth.user?.username;

  // ❌ Niet ingelogd óf andere gebruiker
  if (!auth.token || !currentUsername || currentUsername !== routeUsername) {
    return router.push("/login");
  }
});
</script>

<template>
  <div v-if="auth.user">
    <h1>Welkom, {{ auth.user.name }} 👋</h1>
    <p>Gebruikersnaam: {{ auth.user.username }}</p>
  </div>
</template>
