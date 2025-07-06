<script setup lang="ts">
/*
  This is the main dashboard page for brand owners.
  It allows owners to:
    - View and edit their brand information
    - Manage ideas submitted to their brand
    - Select a main question to display
    - Create and review quizzes
    - Edit their account details
  All data is loaded based on the slug from the route and the authenticated brand owner.
*/

import { useRoute } from "vue-router";
import { ref, onMounted, computed } from "vue";
import { useBrandOwnerAuthStore } from "~/store/useBrandOwnerAuthStore";
import ManageIdeaGrid from "~/components/dashboard/brand/ManageIdeaGrid.vue";
import MainQuestionSelect from "~/components/dashboard/brand/MainQuestionSelect.vue";
import QuizBuilder from "~/components/dashboard/quiz/QuizBuilder.vue";
import QuizOverview from "~/components/dashboard/quiz/QuizOverview.vue";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";
import { useBrand } from "~/composables/brand/useBrand";
import BrandEditModal from "~/components/dashboard/brand/BrandEditModal.vue";
import AccountEditModal from "~/components/dashboard/account/AccountEditModal.vue";
import type { BrandOwner } from "~/types/brand-owner";
import type { Brand } from "~/types/brand";
import { apiFetch } from "~/composables/adapter/useApi";

const { triggerByKey } = useResponseDisplay();

const showModal = ref(false);
const rawApiBase = useRuntimeConfig().public.apiBaseUrl;
const apiBase = rawApiBase as string;
const imageBase = apiBase.replace("/api", "/storage");

const editing = ref<Record<string, boolean>>({});
const { updateBrand } = useBrand();
const brand = ref<Brand>(null);
const fullBrand = ref<Brand | null>(null);

// Toggle edit mode for a field
function toggleEdit(field: string) {
  editing.value[field] = !editing.value[field];
}

// Save a single edited field
async function saveEdit(field: string) {
  if (!brand.value?.id) return;
  try {
    await updateBrand(brand.value.id, { [field]: brand.value[field] });
    triggerByKey("brand-updated");
    editing.value[field] = false;
  } catch (e) {
    triggerByKey("brand-update-failed");
  }
}

// Reload brand and auth data
async function reloadData() {
  loading.value = true;
  await initAuth();
  loading.value = false;
}

definePageMeta({
  middleware: "brand-owner", // 🔒 protected route for brand owners only
});

const route = useRoute();
const brandOwnerAuth = useBrandOwnerAuthStore();
const owner = computed<BrandOwner | null>(() => brandOwnerAuth.owner);
const logout = brandOwnerAuth.logout;
const initAuth = brandOwnerAuth.initAuth;
const loading = ref(true);

onMounted(async () => {
  await initAuth();
  loading.value = false;
  if (owner.value?.brand?.slug) {
    try {
      fullBrand.value = await apiFetch<Brand>(
        `/brands/${owner.value.brand.slug}`
      );
    } catch (err) {
      triggerByKey("brand-load-failed");
    }
  }
});
</script>

<template>
  <div class="max-w-4xl mx-auto mt-10 p-6 bg-white rounded shadow">
    <h1 class="text-3xl font-bold mb-6">
      Dashboard voor {{ route.params.slug }}
    </h1>
    <div v-if="loading">
      <p>Bezig met laden...</p>
      <!-- or loading spinner -->
    </div>

    <div v-else-if="owner">
      <p class="mb-2">
        Welkom, <strong>{{ owner.name }}</strong
        >!
      </p>
      <p class="mb-4">
        Merk: <strong>{{ owner.brand.title }}</strong>
      </p>
      <button @click="showModal = true" class="text-blue-600">
        ✏️ Bewerk alles
      </button>
      <BrandEditModal
        v-if="fullBrand"
        :open="showModal"
        :brand="fullBrand"
        @close="showModal = false"
        @updated="reloadData()"
      />

      <img
        v-if="owner.brand.logo_path"
        :src="`${imageBase}/${owner.brand.logo_path}`"
        alt="Logo van merk"
        class="w-48 h-auto mb-4 rounded"
      />

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
  <client-only>
    <ManageIdeaGrid :brandId="owner.brand.id" v-if="owner?.brand?.id" />
  </client-only>
  <MainQuestionSelect />
  <QuizBuilder />
  <QuizOverview />
  <AccountEditModal />
</template>
