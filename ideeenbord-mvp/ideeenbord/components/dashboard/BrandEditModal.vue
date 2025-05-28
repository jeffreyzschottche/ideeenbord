<template>
  <div
    v-if="open"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
  >
    <div class="bg-white w-full max-w-xl p-6 rounded shadow relative">
      <h2 class="text-2xl font-bold mb-4">Bewerk Merkgegevens</h2>

      <label class="block font-semibold mb-1">Titel</label>
      <input v-model="form.title" class="w-full border p-2 mb-3 rounded" />

      <label class="block font-semibold mb-1">Categorie</label>
      <input v-model="form.category" class="w-full border p-2 mb-3 rounded" />

      <label class="block font-semibold mb-1">Website URL</label>
      <input
        v-model="form.website_url"
        class="w-full border p-2 mb-3 rounded"
      />

      <label class="block font-semibold mb-1">Introductie</label>
      <textarea
        v-model="form.intro"
        class="w-full border p-2 mb-3 rounded"
      ></textarea>

      <label class="block font-semibold mb-1">Korte Introductie</label>
      <input
        v-model="form.intro_short"
        class="w-full border p-2 mb-3 rounded"
      />

      <label class="block font-semibold mb-1">Email</label>
      <input v-model="form.email" class="w-full border p-2 mb-3 rounded" />

      <label class="block font-semibold mb-1">Abonnement</label>
      <input
        v-model="form.subscription"
        class="w-full border p-2 mb-3 rounded"
      />

      <label class="block font-semibold mb-1">Socials (JSON)</label>
      <textarea
        v-model="form.socials"
        class="w-full border p-2 mb-4 rounded"
        rows="3"
      ></textarea>

      <div class="flex justify-between">
        <button @click="closeModal" class="text-gray-500">Annuleer</button>
        <button
          @click="submitForm"
          class="bg-blue-600 text-white px-4 py-2 rounded"
        >
          Opslaan
        </button>
      </div>

      <button class="absolute top-3 right-3 text-gray-600" @click="closeModal">
        ✖️
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from "vue";
import { useResponseDisplay } from "~/composables/useResponseDisplay";
import { useBrandUpdater } from "~/composables/useBrandUpdater";
import type { Brand, SocialItem } from "~/types/brand";

const props = defineProps<{ open: boolean; brand: Brand }>();
const emit = defineEmits(["close", "updated"]);
const { trigger } = useResponseDisplay();
const { updateBrand } = useBrandUpdater();

const form = ref({
  title: "",
  category: "",
  website_url: "",
  intro: "",
  intro_short: "",
  email: "",
  subscription: "",
  socials: "",
});

watch(
  () => props.brand,
  (newBrand) => {
    if (newBrand) {
      form.value = {
        title: newBrand.title || "",
        category: newBrand.category || "",
        website_url: newBrand.website_url || "",
        intro: newBrand.intro || "",
        intro_short: newBrand.intro_short || "",
        email: newBrand.email || "",
        subscription: newBrand.subscription || "",
        socials: "",
      };
    }
  },
  { immediate: true }
);

function closeModal() {
  emit("close");
}

let socialsParsed: SocialItem[] = [];

try {
  socialsParsed = JSON.parse(form.value.socials);
  if (!Array.isArray(socialsParsed)) socialsParsed = [];
} catch (e) {
  socialsParsed = [];
}

async function submitForm() {
  try {
    const updates = {
      ...form.value,
      socials: socialsParsed,
    };
    await updateBrand(props.brand.id, updates);
    trigger("Merkgegevens bijgewerkt!", "success");
    emit("updated");
    closeModal();
  } catch (e) {
    trigger("Fout bij bijwerken merk", "error");
  }
}
</script>

<style scoped>
textarea,
input {
  font-family: inherit;
}
</style>
