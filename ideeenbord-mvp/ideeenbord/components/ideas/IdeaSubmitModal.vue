<template>
  <client-only>
    <teleport to="body" v-if="mounted">
      <div
        class="fixed inset-0 bg-black/70 flex items-center justify-center z-[100]"
      >
        <form
          @submit.prevent="submitNewIdea"
          class="bg-white p-6 rounded-lg w-full max-w-lg space-y-6"
        >
          <!-- 1. A-Z index -->
          <div class="flex flex-wrap justify-center gap-1">
            <button
              v-for="letter in letters"
              :key="letter"
              @click="setLetter(letter)"
              :disabled="!lettersWithBrands.has(letter)"
              class="w-8 h-8 flex items-center justify-center rounded text-sm font-medium"
              :class="[
                currentLetter === letter
                  ? 'bg-brand text-white'
                  : 'bg-gray-200 text-gray-700',
                !lettersWithBrands.has(letter) &&
                  'opacity-40 cursor-not-allowed',
              ]"
            >
              {{ letter }}
            </button>
          </div>

          <!-- 2. Verticale scrolllijst -->
          <div ref="scrollEl" class="max-h-[260px] overflow-y-auto pr-2">
            <div
              v-for="brand in filteredBrands"
              :key="brand.id"
              @click="selectBrand(brand)"
              class="flex items-center gap-3 py-2 px-3 cursor-pointer rounded hover:bg-gray-100"
              :class="[
                'border-2',
                selectedBrand === brand.id
                  ? 'border-brand bg-brand/10'
                  : 'border-transparent',
              ]"
            >
              <img
                :src="correctImageUrl(brand.logo_path)"
                :alt="brand.title"
                class="w-14 h-14 object-contain rounded bg-white shrink-0"
                loading="lazy"
              />
              <span
                class="text-sm line-clamp-2"
                :class="selectedBrand === brand.id ? 'font-semibold' : ''"
              >
                {{ brand.title }}
              </span>
            </div>
          </div>

          <!-- 3. Titel & beschrijving -->
          <input
            v-model="title"
            placeholder="Titel van je idee"
            class="block w-full p-2 rounded brandColorBorder"
            required
          />
          <textarea
            v-model="description"
            placeholder="Beschrijving"
            class="block w-full p-2 rounded brandColorBorder"
            rows="4"
          />

          <button type="submit" class="cta w-full">Plaats idee</button>

          <p
            class="text-center text-sm text-gray-500 cursor-pointer bg-nav text-white p-2 font-bold rounded-xl"
            @click="$emit('close')"
          >
            Sluiten
          </p>
        </form>
      </div>
    </teleport>
  </client-only>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, nextTick } from "vue";
import { apiFetch } from "~/composables/adapter/useApi";
import { useIdeas } from "~/composables/ideas/useIdeas";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";
import type { Brand } from "~/types/brand";

const emit = defineEmits(["close"]);
const { trigger } = useResponseDisplay();

/* ───────── Mount & brands ophalen ───────── */
const mounted = ref(false);
const brands = ref<Brand[]>([]);

onMounted(async () => {
  mounted.value = true;
  brands.value = (await apiFetch<Brand[]>("/brands?accepted=1")).sort((a, b) =>
    a.title.localeCompare(b.title)
  );
});

/* ───────── A-Z index ───────── */
const letters = Array.from({ length: 26 }, (_, i) =>
  String.fromCharCode(65 + i)
);
const currentLetter = ref("A");

const lettersWithBrands = computed(() => {
  const set = new Set<string>();
  brands.value.forEach((b) => set.add(b.title[0].toUpperCase()));
  return set;
});

function setLetter(letter: string) {
  if (!lettersWithBrands.value.has(letter)) return;
  currentLetter.value = letter;
  nextTick(() => (scrollEl.value!.scrollTop = 0));
}

/* ───────── Filter + selectie ───────── */
const filteredBrands = computed(() =>
  brands.value.filter((b) => b.title[0].toUpperCase() === currentLetter.value)
);

const selectedBrand = ref<number | null>(null);
function selectBrand(b: Brand) {
  selectedBrand.value = b.id;
}

/* ───────── Form-state & submit ───────── */
const title = ref("");
const description = ref("");

async function submitNewIdea() {
  if (!selectedBrand.value) {
    trigger("Kies eerst een merk 😅", "warning");
    return;
  }
  const { submitIdea } = useIdeas(selectedBrand.value);
  try {
    await submitIdea(title.value, description.value);
    trigger("Idee succesvol geplaatst!", "success");
    emit("close");
  } catch {
    trigger("Plaatsen mislukt, probeer opnieuw.", "error");
  }
}

/* ───────── Helper ───────── */
function correctImageUrl(url: string) {
  const apiBase = useRuntimeConfig().public.apiBaseUrl as string;
  return apiBase.replace("/api", "/storage") + "/" + url;
}

/* ───────── Scroll ref ───────── */
const scrollEl = ref<HTMLElement | null>(null);
</script>

<style scoped>
.brandColorBorder {
  border: 2px solid var(--color-brand);
}
</style>
