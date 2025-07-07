<template>
  <div class="container mx-auto py-12 px-4 font-default">
    <!-- WINACTIE HERO -->
    <section
      v-if="ready"
      class="max-w-4xl mx-auto text-center space-y-6 px-4 pb-16"
    >
      <img
        v-if="content['hero-image']"
        :src="imageUrl(content['hero-image'])"
        class="mx-auto w-full max-w-md rounded-xl shadow"
        alt="Winactie"
      />

      <h1 class="text-3xl md:text-4xl font-bold dark-text">
        {{ content["hero-title"] }}
      </h1>
      <p class="font-alt text-lg main-text">
        {{ content["hero-paragraph"] }}
      </p>

      <NuxtLink to="/participants" class="cta inline-block">
        {{ content["cta-label"] || "Zoek brands" }}
      </NuxtLink>
    </section>

    <!-- loading / error -->
    <p v-if="loading" class="text-center animate-pulse">Laden…</p>
    <p v-else-if="error" class="text-center text-red-600">{{ error }}</p>

    <!-- layout komt ALTIJD zodra er geen fout/loading is -->
    <div v-else class="flex flex-col md:flex-row gap-8">
      <!-- ───────── LINKERKOLUM – filters ───────── -->
      <aside
        class="md:w-1/4 border-color-ideas p-6 rounded-lg shadow-lg shadow-[var(--color-brand)] h-fit"
      >
        <!-- zoekveld -->
        <input
          v-model="search"
          type="text"
          placeholder="Zoek op merk of inhoud…"
          class="w-full mb-4 p-2 rounded border border-color-ideas text-dark"
        />

        <h3 class="text-xl font-bold text-[var(--color-brand)] mb-3">Merken</h3>
        <div v-for="b in uniqueBrands" :key="b" class="mb-1">
          <input
            type="checkbox"
            :id="b"
            :value="b"
            v-model="selectedBrands"
            class="mr-2 w-4 h-4 rounded-sm accent-[var(--color-brand)] /* vult en kleurt het vinkje */ ring-1 ring-inset ring-[var(--color-brand)] /* ‘rand’ in brand-kleur */ focus:ring-2 focus:ring-[var(--color-brand)] focus:ring-offset-0"
          />
          <label :for="b" class="text-dark">{{ b }}</label>
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
</template>

<script setup lang="ts">
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
