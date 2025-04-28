<script setup lang="ts">
import { useRoute } from "vue-router";
import { ref, onMounted, computed } from "vue";
import { useBrandOwnerAuthStore } from "~/store/brandOwnerAuth";
import ManageIdeaGrid from "~/components/dashboard/ManageIdeaGrid.vue";

definePageMeta({
  middleware: "brand-owner", // 🔒 alleen toegankelijk als ingelogd
});

const route = useRoute();
const brandOwnerAuth = useBrandOwnerAuthStore();
const owner = computed(() => brandOwnerAuth.owner);
const logout = brandOwnerAuth.logout;
const initAuth = brandOwnerAuth.initAuth;
const loading = ref(true);

onMounted(async () => {
  await initAuth();
  loading.value = false;
});
</script>

<template>
  <div class="max-w-4xl mx-auto mt-10 p-6 bg-white rounded shadow">
    <h1 class="text-3xl font-bold mb-6">
      Dashboard voor {{ route.params.slug }}
    </h1>

    <div v-if="loading">
      <p>Bezig met laden...</p>
      <!-- of spinner -->
    </div>

    <div v-else-if="owner">
      <p class="mb-2">
        Welkom, <strong>{{ owner.name }}</strong
        >!
      </p>
      <p class="mb-4">
        Merk: <strong>{{ owner.brand.title }}</strong>
      </p>

      <button
        @click="logout"
        class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded"
      >
        Uitloggen
      </button>
    </div>

    <div v-else>
      <p>Je bent niet ingelogd.</p>
    </div>
  </div>
  <ManageIdeaGrid :brandId="owner?.brand?.id" />
</template>
