<script setup lang="ts">
/*
  Brand Owner Dashboard (met zijbalk tabs)
  - Bovenaan: altijd zichtbaar welkomstblok + bewerk/uitloggen
  - Links: tabs (hash-based)
  - Rechts: content per tab (één tegelijk)
*/

import { useRoute, useRouter } from "vue-router";
import { ref, onMounted, computed, watch } from "vue";
import { useBrandOwnerAuthStore } from "~/store/useBrandOwnerAuthStore";
import ManageIdeaGrid from "~/components/dashboard/brand/ManageIdeaGrid.vue";
import MainQuestionSelect from "~/components/dashboard/brand/MainQuestionSelect.vue";
import QuizBuilder from "~/components/dashboard/quiz/QuizBuilder.vue";
import QuizOverview from "~/components/dashboard/quiz/QuizOverview.vue";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";
import { useBrand } from "~/composables/brand/useBrand";
import BrandEditModal from "~/components/dashboard/brand/BrandEditModal.vue";
import RawData from "~/components/dashboard/brand/RawData.vue";
import ApiAccess from "~/components/dashboard/brand/ApiAccess.vue";
import AccountEditModal from "~/components/dashboard/account/AccountEditModal.vue";
import type { BrandOwner } from "~/types/brand-owner";
import type { Brand } from "~/types/brand";
import { apiFetch } from "~/composables/adapter/useApi";

definePageMeta({
  middleware: "brand-owner", // 🔒 protected route for brand owners only
});

const route = useRoute();
const router = useRouter();
const { triggerByKey } = useResponseDisplay();

const brandOwnerAuth = useBrandOwnerAuthStore();
const owner = computed<BrandOwner | null>(() => brandOwnerAuth.owner);
const logout = brandOwnerAuth.logout;
const initAuth = brandOwnerAuth.initAuth;

const loading = ref(true);
const showBrandEdit = ref(false);

const rawApiBase = useRuntimeConfig().public.apiBaseUrl as string;
const apiBase = rawApiBase;
const imageBase = apiBase.replace("/api", "/storage");

const { updateBrand } = useBrand();
const editing = ref<Record<string, boolean>>({});
const brand = ref<Brand | null>(null);
const fullBrand = ref<Brand | null>(null);

// Tabs
type TabId =
  | "ideas"
  | "main-question"
  | "quiz-builder"
  | "quiz-overview"
  | "raw-data"
  | "apiaccess"
  | "account";
const tabs: { id: TabId; label: string; icon?: string }[] = [
  { id: "ideas", label: "Ideeën beheren", icon: "💡" },
  { id: "main-question", label: "Hoofdvraag", icon: "❓" },
  { id: "quiz-builder", label: "Quiz aanmaken", icon: "🛠️" },
  { id: "quiz-overview", label: "Quizzes overzicht", icon: "📋" },
  { id: "raw-data", label: "Rauwe gegevens", icon: "🧾" },
  { id: "apiaccess", label: "Api toegang", icon: "🧾" },
  { id: "account", label: "Account instellingen", icon: "👤" },
];

const defaultTab: TabId = "ideas";
const activeTab = ref<TabId>(defaultTab);

function syncTabFromHash() {
  const h = (route.hash || "").replace("#", "");
  if (tabs.some((t) => t.id === h)) {
    activeTab.value = h as TabId;
  } else {
    // zet hash naar default voor consistente URL
    router.replace({ hash: `#${defaultTab}` });
    activeTab.value = defaultTab;
  }
}
function setTab(id: TabId) {
  if (activeTab.value === id) return;
  router.replace({ hash: `#${id}` });
  activeTab.value = id;
}

// Toggle/save brand fields (blijft bestaan voor toekomstige inline edits)
function toggleEdit(field: string) {
  editing.value[field] = !editing.value[field];
}
async function saveEdit(field: string) {
  if (!brand.value?.id) return;
  try {
    // @ts-ignore
    await updateBrand(brand.value.id, { [field]: brand.value[field] });
    triggerByKey("brand-updated");
    editing.value[field] = false;
  } catch {
    triggerByKey("brand-update-failed");
  }
}

async function reloadData() {
  loading.value = true;
  await initAuth();
  if (owner.value?.brand?.slug) {
    try {
      fullBrand.value = await apiFetch<Brand>(
        `/brands/${owner.value.brand.slug}`
      );
      brand.value = fullBrand.value;
    } catch {
      triggerByKey("brand-load-failed");
    }
  }
  loading.value = false;
}

onMounted(async () => {
  // init auth + brand
  await reloadData();
  // tabs
  syncTabFromHash();
});

watch(
  () => route.hash,
  () => {
    syncTabFromHash();
  }
);
</script>

<template>
  <div class="page-block">
    <h1 class="title-lg">Dashboard voor {{ route.params.slug }}</h1>

    <!-- Loader -->
    <div v-if="loading" class="muted-text">Bezig met laden...</div>

    <!-- Welkomstkop (ALTIJD zichtbaar, ook als owner nog niet geladen is) -->
    <div class="mt-3 flex items-start gap-4">
      <!-- Logo -->
      <img
        v-if="owner?.brand?.logo_path"
        :src="`${imageBase}/${owner.brand.logo_path}`"
        alt="Logo van merk"
        class="brand-logo"
      />
      <div class="flex-1">
        <p class="mb-2">
          Welkom, <strong>{{ owner?.name || "..." }}</strong
          >!
        </p>
        <p class="mb-4">
          Merk:
          <strong>{{ owner?.brand?.title || "..." }}</strong>
        </p>

        <div class="flex flex-wrap gap-3">
          <button @click="showBrandEdit = true" class="btn-link">
            ✏️ Bewerk alles
          </button>

          <button v-if="owner" @click="logout" class="btn btn--danger btn--sm">
            Uitloggen
          </button>
        </div>
      </div>
    </div>

    <!-- Brand bewerk modal -->
    <BrandEditModal
      v-if="fullBrand"
      :open="showBrandEdit"
      :brand="fullBrand"
      @close="showBrandEdit = false"
      @updated="reloadData()"
    />

    <hr class="divider" />

    <!-- Layout: Sidebar tabs + Content -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
      <!-- Sidebar -->
      <aside class="md:col-span-4 lg:col-span-3">
        <nav class="card-compact">
          <ul class="list">
            <li
              v-for="t in tabs"
              :key="t.id"
              class="list-item mb-2"
              :class="
                activeTab === t.id
                  ? 'brandColorBorder bg-[var(--color-bg)]'
                  : ''
              "
            >
              <button
                class="w-full text-left font-default"
                @click="setTab(t.id)"
              >
                <span class="mr-2">{{ t.icon }}</span
                >{{ t.label }}
              </button>
            </li>
          </ul>
        </nav>
      </aside>

      <!-- Content -->
      <section class="md:col-span-8 lg:col-span-9 space-y-4">
        <!-- Ideeën beheren -->
        <div v-if="activeTab === 'ideas'">
          <h2 class="title-md">Ideeën beheren</h2>
          <client-only>
            <ManageIdeaGrid v-if="owner?.brand?.id" :brandId="owner.brand.id" />
            <div v-else class="muted-text">Merkinformatie wordt geladen…</div>
          </client-only>
        </div>

        <!-- Hoofdvraag -->
        <div v-else-if="activeTab === 'main-question'">
          <h2 class="title-md">Hoofdvraag</h2>
          <MainQuestionSelect />
        </div>

        <!-- Quiz aanmaken -->
        <div v-else-if="activeTab === 'quiz-builder'">
          <h2 class="title-md">Quiz aanmaken</h2>
          <QuizBuilder />
        </div>

        <!-- Quizzes overzicht -->
        <div v-else-if="activeTab === 'quiz-overview'">
          <h2 class="title-md">Quizzes overzicht</h2>
          <QuizOverview />
        </div>

        <!-- Account instellingen -->
        <div v-else-if="activeTab === 'account'">
          <h2 class="title-md">Account instellingen</h2>
          <!-- Als je AccountEditModal als modaal wil: toon een knop die 'm opent;
               Als het een inline-form ondersteunt, render je 'm direct. -->
          <AccountEditModal />
        </div>

        <div v-else-if="activeTab === 'raw-data'">
          <h2 class="title-md">Rauwe gegevens</h2>
          <client-only>
            <RawData
              v-if="owner?.brand?.id && owner?.brand?.slug"
              :brandId="owner.brand.id"
              :brandSlug="owner.brand.slug"
            />
            <div v-else class="muted-text">Merkinformatie wordt geladen…</div>
          </client-only>
        </div>

        <div v-else-if="activeTab === 'apiaccess'">
          <h2 class="title-md">Rauwe gegevens</h2>
          <client-only>
            <ApiAccess
              v-if="owner?.brand?.id && owner?.brand?.slug"
              :brandId="owner.brand.id"
              :brandSlug="owner.brand.slug"
            />
            <div v-else class="muted-text">Merkinformatie wordt geladen…</div>
          </client-only>
        </div>
      </section>
    </div>
  </div>
</template>
