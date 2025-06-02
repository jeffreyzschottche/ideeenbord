<script setup lang="ts">
import { useRoute } from "vue-router";
import { ref, onMounted, computed } from "vue";
import { useBrandOwnerAuthStore } from "~/store/useBrandOwnerAuthStore";
import ManageIdeaGrid from "~/components/dashboard/ManageIdeaGrid.vue";
import MainQuestionSelect from "~/components/dashboard/MainQuestionSelect.vue";
import QuizBuilder from "~/components/dashboard/QuizBuilder.vue";
import QuizOverview from "~/components/dashboard/QuizOverview.vue";
import { useResponseDisplay } from "~/composables/useResponseDisplay";
import { useBrand } from "~/composables/useBrand";
import BrandEditModal from "~/components/dashboard/BrandEditModal.vue";
import AccountEditModal from "~/components/dashboard/AccountEditModal.vue";
import type { BrandOwner } from "~/types/brand-owner";
import type { Brand } from "~/types/brand";

const { triggerByKey } = useResponseDisplay(); // ✅ gebruik triggerByKey

const showModal = ref(false);
const rawApiBase = useRuntimeConfig().public.apiBaseUrl;
const apiBase = rawApiBase as string;
const imageBase = apiBase.replace("/api", "/storage");

const editing = ref<Record<string, boolean>>({});
const { updateBrand } = useBrand();
const brand = ref<Brand>(null);
const fullBrand = ref<Brand | null>(null);

function toggleEdit(field: string) {
  editing.value[field] = !editing.value[field];
}

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

async function reloadData() {
  loading.value = true;
  await initAuth();
  loading.value = false;
}

definePageMeta({
  middleware: "brand-owner", // 🔒 alleen toegankelijk als ingelogd
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
      <button @click="showModal = true" class="text-blue-600">
        ✏️ Bewerk alles
      </button>
      <BrandEditModal
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
  <!-- <QuizWinner /> -->
  <QuizOverview />
  <AccountEditModal />
</template>
