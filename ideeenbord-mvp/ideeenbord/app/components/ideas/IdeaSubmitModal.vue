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
          <div>
            <input
              v-model="title"
              placeholder="Titel van je idee (min. 5 karakters)"
              class="block w-full p-2 rounded brandColorBorder"
              :class="{ 'border-red-500': titleError }"
              required
              minlength="5"
              maxlength="100"
            />
            <p v-if="titleError" class="text-red-500 text-xs mt-1">{{ titleError }}</p>
            <p class="text-gray-400 text-xs mt-1 text-right">{{ title.length }}/100</p>
          </div>
          <div>
            <textarea
              v-model="description"
              placeholder="Beschrijving (min. 20 karakters)"
              class="block w-full p-2 rounded brandColorBorder"
              :class="{ 'border-red-500': descriptionError }"
              rows="4"
              minlength="20"
              maxlength="1000"
            />
            <p v-if="descriptionError" class="text-red-500 text-xs mt-1">{{ descriptionError }}</p>
            <p class="text-gray-400 text-xs mt-1 text-right">{{ description.length }}/1000</p>
          </div>

          <button type="submit" class="cta w-full" :disabled="!isFormValid">Plaats idee</button>

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
import { storageBaseFromApiBase } from "~/utils/apiUrl";
import { ref, onMounted, computed, nextTick } from "vue";
import { apiFetch } from "~/composables/adapter/useApi";
import { useIdeas } from "~/composables/ideas/useIdeas";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";
import type { Brand } from "~/types/brand";
import { useProfanity } from "~/composables/useProfanity";

const emit = defineEmits(["close"]);
const { trigger, triggerByKey } = useResponseDisplay();
const { init: initProfanity, validate } = useProfanity();

/* ───────── Mount & brands ophalen ───────── */
const mounted = ref(false);
const brands = ref<Brand[]>([]);

onMounted(async () => {
  mounted.value = true;
  brands.value = (await apiFetch<Brand[]>("/brands?accepted=1")).sort((a, b) =>
    a.title.localeCompare(b.title)
  );
  await initProfanity();
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

/* Validatie */
const MIN_TITLE_LENGTH = 5;
const MAX_TITLE_LENGTH = 100;
const MIN_DESC_LENGTH = 20;
const MAX_DESC_LENGTH = 1000;

const titleError = computed(() => {
  if (title.value.length === 0) return "";
  if (title.value.length < MIN_TITLE_LENGTH) return `Titel moet minimaal ${MIN_TITLE_LENGTH} karakters zijn`;
  return "";
});

const descriptionError = computed(() => {
  if (description.value.length === 0) return "";
  if (description.value.length < MIN_DESC_LENGTH) return `Beschrijving moet minimaal ${MIN_DESC_LENGTH} karakters zijn`;
  return "";
});

const isFormValid = computed(() => {
  return (
    title.value.length >= MIN_TITLE_LENGTH &&
    title.value.length <= MAX_TITLE_LENGTH &&
    description.value.length >= MIN_DESC_LENGTH &&
    description.value.length <= MAX_DESC_LENGTH &&
    selectedBrand.value !== null
  );
});

async function submitNewIdea() {
  if (!selectedBrand.value) {
    trigger("Kies eerst een merk", "warning");
    return;
  }
  if (title.value.length < MIN_TITLE_LENGTH) {
    trigger(`Titel moet minimaal ${MIN_TITLE_LENGTH} karakters zijn`, "warning");
    return;
  }
  if (description.value.length < MIN_DESC_LENGTH) {
    trigger(`Beschrijving moet minimaal ${MIN_DESC_LENGTH} karakters zijn`, "warning");
    return;
  }
  if (!validate(title.value, description.value)) {
    triggerByKey("profanity-detected");
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
  return storageBaseFromApiBase(apiBase) + "/" + url;
}

/* ───────── Scroll ref ───────── */
const scrollEl = ref<HTMLElement | null>(null);
</script>

<style scoped>
.brandColorBorder {
  border: 2px solid var(--color-brand);
}
</style>
