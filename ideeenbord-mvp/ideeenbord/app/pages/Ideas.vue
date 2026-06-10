<template>
  <div class="font-default pb-12">
    <PageHero
      eyebrow="Ideeën"
      :title="content['hero-title'] || 'Ontdek ideeën van de community'"
      :subtitle="content['hero-paragraph'] || 'Bekijk wat anderen aan merken voorstellen en geef jouw favorieten een duwtje.'"
    />

    <div class="container mx-auto px-4">
      <!-- loading / error -->
      <p v-if="loading" class="text-center animate-pulse">Laden…</p>
      <p v-else-if="error" class="text-center text-red-600">{{ error }}</p>

      <!-- layout komt ALTIJD zodra er geen fout/loading is -->
      <div v-else class="flex flex-col md:flex-row gap-8">
      <!-- ───────── LINKERKOLUM – filters ───────── -->
      <aside class="md:w-1/4 card p-5 h-fit md:sticky md:top-24">
        <!-- zoekveld -->
        <div class="relative mb-5">
          <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
          <input
            v-model="search"
            type="text"
            placeholder="Zoek op merk of inhoud…"
            class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 focus:border-[var(--color-brand)] focus:ring-2 focus:ring-[var(--color-brand)]/20 outline-none"
          />
        </div>

        <h3 class="text-sm font-bold uppercase tracking-wider text-[var(--color-nav)] mb-3">Merken</h3>
        <div class="space-y-1 max-h-72 overflow-y-auto pr-1">
          <label
            v-for="b in uniqueBrands"
            :key="b"
            class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-[var(--color-bg)] cursor-pointer"
          >
            <input
              type="checkbox"
              :value="b"
              v-model="selectedBrands"
              class="w-4 h-4 rounded accent-[var(--color-brand)]"
            />
            <span class="text-gray-700 text-sm">{{ b }}</span>
          </label>
        </div>
      </aside>

      <!-- ───────── RECHTS – grid ───────── -->
      <section class="md:w-3/4">
        <div
          v-if="filtered.length"
          class="grid gap-6 md:grid-cols-2 lg:grid-cols-3"
        >
          <IdeaCardGeneral
            v-for="row in filtered"
            :key="row.idea.id"
            :idea="row.idea"
            :brand="row.brand"
            @like="likeIdea"
            @dislike="dislikeIdea"
          />
        </div>

        <!-- geen resultaten -->
        <p v-else class="text-center text-gray-500 py-8">
          Geen ideeën gevonden.
        </p>
      </section>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
usePageSeo({
  title: "Ideeën",
  description:
    "Bekijk en ontdek de ideeën die gebruikers met merken delen op Ideeënbord.",
});
useJsonLd(
  breadcrumbLd([
    { name: "Home", path: "/" },
    { name: "Ideeën", path: "/Ideas" },
  ])
);
import { ref, computed, onMounted } from "vue";
import { apiFetch } from "~/composables/adapter/useApi";
import { useUserAuthStore } from "~/store/useUserAuthStore";
import IdeaCardGeneral from "~/components/ideas/IdeaCardGeneral.vue";
import type { Idea } from "~/types/idea";
import type { Brand } from "~/types/brand";
import { useCmsContent } from "~/composables/content/useCmsContent";
import { useRuntimeConfig } from "nuxt/app";

const { content, isLoading } = useCmsContent("ideeen");
const ready = computed(
  () => !isLoading.value && Object.keys(content.value).length
);

function imageUrl(path?: string) {
  if (!path) return "";
  if (
    path.startsWith("http") ||
    path.startsWith("//") ||
    path.startsWith("/img")
  )
    return path;
  const base = (useRuntimeConfig().public.apiBaseUrl as string).replace(
    "/api",
    "/storage"
  );
  return `${base}/${path.replace(/^\/+/, "")}`;
}

/* ─── state ─── */
const ideas = ref<Idea[]>([]);
const brandLookup = ref<Record<number, Brand>>({});
const loading = ref(true);
const error = ref<string | null>(null);

const search = ref("");
const selectedBrands = ref<string[]>([]);

const auth = useUserAuthStore();

/* ─── fetch feed + brands once ─── */
onMounted(async () => {
  try {
    ideas.value = await apiFetch<Idea[]>("/ideas-feed");

    const allBrands = await apiFetch<Brand[]>("/brands");
    const map: Record<number, Brand> = {};
    allBrands.forEach((b) => (map[b.id] = b));
    brandLookup.value = map;
  } catch (e) {
    console.error(e);
    error.value = "Kon ideeën of merken niet laden.";
  } finally {
    loading.value = false;
  }
});

/* ─── unieke merknamen ─── */
const uniqueBrands = computed(() =>
  [...new Set(Object.values(brandLookup.value).map((b) => b.title))].sort()
);

/* ─── samengestelde rows ─── */
const ideasWithBrand = computed(() =>
  ideas.value.map((i) => ({
    idea: i,
    brand: brandLookup.value[i.brand_id] || null,
  }))
);

/* ─── filterlogica ─── */
const filtered = computed(() =>
  ideasWithBrand.value.filter((row) => {
    const needle = search.value.trim().toLowerCase();

    const matchesSearch =
      !needle ||
      (
        row.idea.title +
        " " +
        row.idea.description +
        " " +
        (row.brand?.title || "")
      )
        .toLowerCase()
        .includes(needle);

    const matchesBrand =
      !selectedBrands.value.length ||
      (row.brand && selectedBrands.value.includes(row.brand.title));

    return matchesSearch && matchesBrand;
  })
);

/* ─── like / dislike – kleine UI-update ─── */
async function likeIdea(id: number) {
  if (!auth.token) return navigateTo("/login");
  await apiFetch(`/ideas/${id}/like`, { method: "POST" });
  const it = ideas.value.find((x) => x.id === id);
  if (it) it.likes++;
}
async function dislikeIdea(id: number) {
  if (!auth.token) return navigateTo("/login");
  await apiFetch(`/ideas/${id}/dislike`, { method: "POST" });
  const it = ideas.value.find((x) => x.id === id);
  if (it) it.dislikes++;
}
</script>
