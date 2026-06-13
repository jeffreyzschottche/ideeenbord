<script setup lang="ts">
/*
  Brand Owner Dashboard — app-shell met gegroepeerde sidebar, live KPI-strip
  en een contextkop per sectie. Tabs zijn hash-based.
*/

import { useRoute, useRouter } from "vue-router";
import { ref, onMounted, computed, watch } from "vue";
import { useBrandOwnerAuthStore } from "~/store/useBrandOwnerAuthStore";
import ManageIdeaGrid from "~/components/dashboard/brand/ManageIdeaGrid.vue";
import MainQuestionSelect from "~/components/dashboard/brand/MainQuestionSelect.vue";
import QuizBuilder from "~/components/dashboard/quiz/QuizBuilder.vue";
import QuizOverview from "~/components/dashboard/quiz/QuizOverview.vue";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";
import BrandEditModal from "~/components/dashboard/brand/BrandEditModal.vue";
import RawData from "~/components/dashboard/brand/RawData.vue";
import BrandReport from "~/components/dashboard/brand/BrandReport.vue";
import LiveStats from "~/components/dashboard/brand/LiveStats.vue";
import AccountEditModal from "~/components/dashboard/account/AccountEditModal.vue";
import { reportService } from "~/services/api/brand/reportService";
import type { BrandOwner } from "~/types/brand-owner";
import type { Brand } from "~/types/brand";
import { apiFetch } from "~/composables/adapter/useApi";
import { storageBaseFromApiBase } from "~/utils/apiUrl";

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
const sidebarOpen = ref(false);
const onboardingDismissed = ref(false);

const rawApiBase = useRuntimeConfig().public.apiBaseUrl as string;
const imageBase = storageBaseFromApiBase(rawApiBase);

const brand = ref<Brand | null>(null);
const fullBrand = ref<Brand | null>(null);

// ---- Live KPI's voor de header ----
const stats = ref<any | null>(null);
const statsLoading = ref(false);

const kpis = computed(() => {
  const t = stats.value?.totals;
  return [
    { label: "Ideeën", icon: "fa-lightbulb", value: t ? t.ideas : null },
    { label: "Deelnemers", icon: "fa-users", value: t ? t.participants : null },
    {
      label: "Netto sentiment",
      icon: "fa-heart",
      value: t ? `${t.net_sentiment >= 0 ? "+" : ""}${t.net_sentiment}` : null,
    },
    { label: "Gem. score", icon: "fa-star", value: t ? t.avg_rating ?? "—" : null },
  ];
});

async function loadStats() {
  const id = owner.value?.brand?.id;
  if (!id) return;
  statsLoading.value = true;
  try {
    stats.value = await reportService.stats(id);
  } catch {
    /* niet kritisch — KPI's blijven leeg */
  } finally {
    statsLoading.value = false;
  }
}

// ---- Onboarding (aan de slag) ----
const onboardingSteps = computed(() => {
  const t = stats.value?.totals;
  return [
    {
      key: "profile",
      icon: "fa-store",
      title: "Maak je merkprofiel compleet",
      text: "Voeg je logo en gegevens toe via 'Merk bewerken'.",
      done: !!brand.value?.logo_path,
      run: () => (showBrandEdit.value = true),
    },
    {
      key: "mq",
      icon: "fa-circle-question",
      title: "Stel je hoofdvraag in",
      text: "Kies dé vraag die je publiek beantwoordt.",
      done: !!brand.value?.main_question_id,
      run: () => setTab("main-question"),
    },
    {
      key: "ideas",
      icon: "fa-lightbulb",
      title: "Verzamel je eerste ideeën",
      text: "Beheer en reageer op binnenkomende ideeën.",
      done: !!(t && t.ideas > 0),
      run: () => setTab("ideas"),
    },
    {
      key: "report",
      icon: "fa-file-lines",
      title: "Genereer je eerste AI-rapport",
      text: "Zet je klantdata om in concrete adviezen.",
      done: false,
      run: () => setTab("report"),
    },
  ];
});
const onboardingDoneCount = computed(
  () => onboardingSteps.value.filter((s) => s.done).length
);
// Verberg zodra de kernstappen (hoofdvraag + eerste ideeën) klaar zijn.
const coreOnboardingDone = computed(
  () => !!brand.value?.main_question_id && !!(stats.value?.totals?.ideas > 0)
);
const showOnboarding = computed(
  () => !onboardingDismissed.value && !loading.value && !coreOnboardingDone.value
);
function dismissOnboarding() {
  onboardingDismissed.value = true;
  const id = owner.value?.brand?.id;
  if (import.meta.client && id) localStorage.setItem(`ib-onboarding-${id}`, "1");
}

// ---- Tabs ----
type TabId =
  | "ideas"
  | "main-question"
  | "quiz-builder"
  | "quiz-overview"
  | "live-stats"
  | "report"
  | "raw-data"
  | "account";

type Tab = { id: TabId; label: string; icon: string; group: string; desc: string };

const tabs: Tab[] = [
  { id: "ideas", label: "Ideeën beheren", icon: "fa-lightbulb", group: "Beheer", desc: "Beheer, pin en wijzig de status van ingezonden ideeën." },
  { id: "main-question", label: "Hoofdvraag", icon: "fa-circle-question", group: "Beheer", desc: "Stel de centrale vraag die je publiek beantwoordt." },
  { id: "quiz-builder", label: "Quiz aanmaken", icon: "fa-wand-magic-sparkles", group: "Beheer", desc: "Bouw een quiz met prijs om betrokkenheid te stimuleren." },
  { id: "quiz-overview", label: "Quizzes", icon: "fa-list-check", group: "Beheer", desc: "Bekijk en beheer je lopende en afgeronde quizzes." },
  { id: "live-stats", label: "Live statistieken", icon: "fa-chart-line", group: "Inzichten", desc: "Realtime cijfers en grafieken van je merk." },
  { id: "report", label: "AI-rapport", icon: "fa-file-lines", group: "Inzichten", desc: "Genereer een diepgaand adviesrapport per periode." },
  { id: "raw-data", label: "Data", icon: "fa-database", group: "Inzichten", desc: "Exporteer je data als CSV, JSON of XML." },
  { id: "account", label: "Account", icon: "fa-gear", group: "Account", desc: "Beheer je accountinstellingen." },
];

const groups = computed(() => {
  const order = ["Beheer", "Inzichten", "Account"];
  return order
    .map((name) => ({ name, items: tabs.filter((t) => t.group === name) }))
    .filter((g) => g.items.length);
});

const defaultTab: TabId = "ideas";
const activeTab = ref<TabId>(defaultTab);
const activeMeta = computed(() => tabs.find((t) => t.id === activeTab.value) ?? tabs[0]);

function syncTabFromHash() {
  const h = (route.hash || "").replace("#", "");
  if (tabs.some((t) => t.id === h)) {
    activeTab.value = h as TabId;
  } else {
    router.replace({ hash: `#${defaultTab}` });
    activeTab.value = defaultTab;
  }
}
function setTab(id: TabId) {
  sidebarOpen.value = false;
  if (activeTab.value === id) return;
  router.replace({ hash: `#${id}` });
  activeTab.value = id;
}

async function reloadData() {
  loading.value = true;
  await initAuth();
  if (owner.value?.brand?.slug) {
    try {
      fullBrand.value = await apiFetch<Brand>(`/brands/${owner.value.brand.slug}`);
      brand.value = fullBrand.value;
    } catch {
      triggerByKey("brand-load-failed");
    }
  }
  loading.value = false;
}

onMounted(async () => {
  await reloadData();
  const id = owner.value?.brand?.id;
  if (import.meta.client && id && localStorage.getItem(`ib-onboarding-${id}`)) {
    onboardingDismissed.value = true;
  }
  syncTabFromHash();
  loadStats();
});

watch(
  () => route.hash,
  () => syncTabFromHash()
);
</script>

<template>
  <div class="min-h-screen bg-[var(--color-bg)]">
    <div class="max-w-7xl mx-auto px-4 py-6 md:py-8">
      <!-- Header -->
      <header
        class="relative overflow-hidden rounded-3xl bg-[var(--color-nav)] text-white p-6 md:p-8"
      >
        <div class="absolute -top-20 -right-16 w-72 h-72 rounded-full bg-[var(--color-brand)]/25 blur-3xl"></div>
        <div class="absolute -bottom-24 -left-10 w-72 h-72 rounded-full bg-white/5 blur-3xl"></div>

        <div class="relative flex flex-col lg:flex-row lg:items-center gap-5">
          <button
            type="button"
            class="relative group w-16 h-16 md:w-20 md:h-20 shrink-0"
            title="Logo wijzigen"
            @click="showBrandEdit = true"
          >
            <img
              v-if="owner?.brand?.logo_path"
              :src="`${imageBase}/${owner.brand.logo_path}`"
              alt="Logo van merk"
              class="w-full h-full rounded-2xl object-contain bg-white p-2 shadow-lg"
            />
            <span
              v-else
              class="w-full h-full rounded-2xl bg-white/10 flex items-center justify-center"
            >
              <i class="fa-solid fa-lightbulb text-3xl text-[var(--color-brand)]"></i>
            </span>
            <span
              class="absolute -bottom-1.5 -right-1.5 w-7 h-7 rounded-full bg-[var(--color-brand)] text-white flex items-center justify-center shadow-md ring-2 ring-[var(--color-nav)] group-hover:scale-110 transition"
            >
              <i class="fa-solid fa-pen text-[11px]"></i>
            </span>
          </button>

          <div class="flex-1 min-w-0">
            <p class="text-sm text-gray-300">Welkom terug,</p>
            <h1 class="text-2xl md:text-3xl font-extrabold leading-tight truncate">
              {{ owner?.name || "..." }}
            </h1>
            <p class="text-gray-300 mt-1 text-sm">
              Dashboard voor
              <span class="font-semibold text-white">{{ owner?.brand?.title || "..." }}</span>
            </p>
          </div>

          <div class="flex flex-wrap gap-2">
            <button
              class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-sm font-semibold transition"
              @click="showBrandEdit = true"
            >
              <i class="fa-solid fa-pen mr-1.5"></i> Merk bewerken
            </button>
            <button
              v-if="owner"
              class="px-4 py-2 rounded-xl bg-[var(--color-error)] hover:opacity-90 text-sm font-semibold transition"
              @click="logout"
            >
              <i class="fa-solid fa-right-from-bracket mr-1.5"></i> Uitloggen
            </button>
          </div>
        </div>

        <!-- Live KPI-strip -->
        <div class="relative mt-6 grid grid-cols-2 lg:grid-cols-4 gap-3">
          <div
            v-for="k in kpis"
            :key="k.label"
            class="rounded-2xl bg-white/10 backdrop-blur px-4 py-3 flex items-center gap-3"
          >
            <span class="w-9 h-9 rounded-xl bg-[var(--color-brand)]/90 flex items-center justify-center shrink-0">
              <i class="fa-solid text-white text-sm" :class="k.icon"></i>
            </span>
            <div class="min-w-0">
              <p class="text-xl font-extrabold leading-none">
                <span v-if="k.value !== null">{{ k.value }}</span>
                <span v-else class="inline-block w-8 h-5 rounded bg-white/20 animate-pulse align-middle"></span>
              </p>
              <p class="text-[11px] text-gray-300 mt-1 truncate">{{ k.label }}</p>
            </div>
          </div>
        </div>
      </header>

      <!-- Brand bewerk modal -->
      <BrandEditModal
        v-if="fullBrand"
        :open="showBrandEdit"
        :brand="fullBrand"
        @close="showBrandEdit = false"
        @updated="() => { reloadData(); loadStats(); }"
      />

      <!-- Aan de slag — onboarding -->
      <div
        v-if="showOnboarding"
        class="card p-5 md:p-6 mt-5 relative overflow-hidden"
        v-reveal="{ y: 16 }"
      >
        <button
          class="absolute top-4 right-4 w-8 h-8 rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition"
          title="Verbergen"
          @click="dismissOnboarding"
        >
          <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="flex items-center gap-3 mb-1">
          <span class="w-10 h-10 rounded-2xl bg-[var(--color-brand)]/12 flex items-center justify-center shrink-0">
            <i class="fa-solid fa-rocket text-[var(--color-brand)]"></i>
          </span>
          <div>
            <h2 class="text-lg font-extrabold text-[var(--color-nav)] leading-tight">Aan de slag</h2>
            <p class="muted-text text-sm">
              {{ onboardingDoneCount }} van {{ onboardingSteps.length }} stappen voltooid
            </p>
          </div>
        </div>

        <!-- Voortgangsbalk -->
        <div class="h-2 rounded-full bg-gray-100 overflow-hidden my-4">
          <div
            class="h-full rounded-full bg-[var(--color-brand)] transition-all duration-500"
            :style="{ width: `${(onboardingDoneCount / onboardingSteps.length) * 100}%` }"
          ></div>
        </div>

        <ul class="grid sm:grid-cols-2 gap-3">
          <li v-for="s in onboardingSteps" :key="s.key">
            <button
              class="w-full text-left flex items-start gap-3 rounded-2xl border p-3.5 transition hover:shadow-sm"
              :class="s.done ? 'border-green-200 bg-green-50/60' : 'border-gray-100 hover:border-[var(--color-brand)]/40'"
              @click="s.run"
            >
              <span
                class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                :class="s.done ? 'bg-green-500 text-white' : 'bg-[var(--color-brand)]/10 text-[var(--color-brand)]'"
              >
                <i class="fa-solid" :class="s.done ? 'fa-check' : s.icon"></i>
              </span>
              <span class="min-w-0">
                <span class="block font-semibold text-[var(--color-nav)] text-sm leading-snug">{{ s.title }}</span>
                <span class="block muted-text text-xs mt-0.5">{{ s.text }}</span>
              </span>
            </button>
          </li>
        </ul>
      </div>

      <!-- Mobile tab opener -->
      <button
        class="md:hidden mt-5 w-full card px-4 py-3 flex items-center justify-between font-semibold text-[var(--color-nav)]"
        @click="sidebarOpen = !sidebarOpen"
      >
        <span><i class="fa-solid mr-2" :class="activeMeta.icon"></i>{{ activeMeta.label }}</span>
        <i class="fa-solid" :class="sidebarOpen ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
      </button>

      <!-- Layout -->
      <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mt-5 md:mt-6">
        <!-- Sidebar -->
        <aside
          class="md:col-span-4 lg:col-span-3"
          :class="sidebarOpen ? 'block' : 'hidden md:block'"
        >
          <nav class="card p-3 md:sticky md:top-6 space-y-4">
            <div v-for="g in groups" :key="g.name">
              <p class="px-3 mb-1 text-[11px] font-bold uppercase tracking-wider text-gray-400">
                {{ g.name }}
              </p>
              <ul class="space-y-1">
                <li v-for="t in g.items" :key="t.id">
                  <button
                    class="dash-navlink"
                    :class="activeTab === t.id ? 'dash-navlink--active' : ''"
                    @click="setTab(t.id)"
                  >
                    <i class="fa-solid w-5 text-center" :class="t.icon"></i>
                    <span>{{ t.label }}</span>
                  </button>
                </li>
              </ul>
            </div>
          </nav>
        </aside>

        <!-- Content -->
        <section class="md:col-span-8 lg:col-span-9 space-y-5">
          <!-- Contextkop -->
          <div class="card p-5 md:p-6 flex items-start gap-4">
            <span class="w-11 h-11 rounded-2xl bg-[var(--color-brand)]/12 flex items-center justify-center shrink-0">
              <i class="fa-solid text-lg text-[var(--color-brand)]" :class="activeMeta.icon"></i>
            </span>
            <div>
              <h2 class="text-xl font-extrabold text-[var(--color-nav)] leading-tight">{{ activeMeta.label }}</h2>
              <p class="muted-text text-sm mt-0.5">{{ activeMeta.desc }}</p>
            </div>
          </div>

          <!-- Tab content -->
          <div class="card p-5 md:p-7">
            <div v-if="loading" class="muted-text">Bezig met laden…</div>

            <template v-else>
              <div v-if="activeTab === 'ideas'">
                <client-only>
                  <ManageIdeaGrid v-if="owner?.brand?.id" :brandId="owner.brand.id" />
                  <div v-else class="muted-text">Merkinformatie wordt geladen…</div>
                </client-only>
              </div>

              <div v-else-if="activeTab === 'main-question'">
                <MainQuestionSelect />
              </div>

              <div v-else-if="activeTab === 'quiz-builder'">
                <QuizBuilder />
              </div>

              <div v-else-if="activeTab === 'quiz-overview'">
                <QuizOverview />
              </div>

              <div v-else-if="activeTab === 'live-stats'">
                <client-only>
                  <LiveStats v-if="owner?.brand?.id" :brandId="owner.brand.id" />
                  <div v-else class="muted-text">Merkinformatie wordt geladen…</div>
                </client-only>
              </div>

              <div v-else-if="activeTab === 'report'">
                <client-only>
                  <BrandReport
                    v-if="owner?.brand?.id"
                    :brandId="owner.brand.id"
                    :brandName="owner?.brand?.title ?? ''"
                    :brandLogoUrl="owner?.brand?.logo_path ? `${imageBase}/${owner.brand.logo_path}` : null"
                  />
                  <div v-else class="muted-text">Merkinformatie wordt geladen…</div>
                </client-only>
              </div>

              <div v-else-if="activeTab === 'raw-data'">
                <client-only>
                  <RawData
                    v-if="owner?.brand?.id && owner?.brand?.slug"
                    :brandId="owner.brand.id"
                    :brandSlug="owner.brand.slug"
                  />
                  <div v-else class="muted-text">Merkinformatie wordt geladen…</div>
                </client-only>
              </div>

              <div v-else-if="activeTab === 'account'">
                <AccountEditModal />
              </div>
            </template>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<style scoped>
.dash-navlink {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 0.65rem;
  text-align: left;
  padding: 0.7rem 0.85rem;
  border-radius: 0.85rem;
  font-weight: 600;
  font-size: 0.9rem;
  color: #4b5563;
  transition: background-color 0.18s, color 0.18s, transform 0.05s;
}
.dash-navlink:hover {
  background: var(--color-bg);
  color: var(--color-nav);
}
.dash-navlink:active {
  transform: translateY(0.5px);
}
.dash-navlink--active {
  background: var(--color-nav);
  color: #fff;
  box-shadow: 0 6px 16px rgba(31, 41, 55, 0.18);
}
.dash-navlink--active:hover {
  background: var(--color-nav);
  color: #fff;
}
</style>
